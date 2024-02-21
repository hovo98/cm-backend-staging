<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Broker;
use App\GraphQL\Mutations\Chat\SendMessageTrait;
use App\Jobs\BetaUserApproved;
use App\Jobs\CreateLenderDealPreferencesFile;
use App\Lender;
use App\Services\RealTime\RealTimeServiceInterface;
use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\HasApiTokens;

/**
 * Class UsersAccounts
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UsersAccounts extends Controller
{
    use SendMessageTrait;
    use HasApiTokens;

    public function __construct(protected RealTimeServiceInterface $realTimeService)
    {
    }

    /**
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        if ($request->get('email') != null) {
            $limit = 100;
        } else {
            $limit = $request->get('entries') ?? 10;
        }

        // Get only Brokers and Lenders to list them

        $users = User::when($request->get('email') != null, function ($q) use ($request) {
            if ($request->get('email')) {
                return $q->where('role', '!=', 'admin')->where('email', 'like', $request->get('email'));
            } else {
                return $q->where('role', '!=', 'admin');
            }
        }, function ($q) {
            return $q->where('role', '!=', 'admin');
        })->paginate($limit)->withQueryString();

        $plans = [
            config('stripe.small_monthly') => 'Starter / Monthly',
            config('stripe.small_yearly') => 'Starter / Yearly',
            config('stripe.medium_monthly') => 'Advance / Monthly',
            config('stripe.medium_yearly') => 'Advance / Yearly',
            config('stripe.large_monthly') => 'Professional / Monthly',
            config('stripe.large_yearly') => 'Professional / Yearly',
            config('stripe.extra_large_monthly') => 'Unlimited / Monthly',
            config('stripe.extra_large_yearly') => 'Unlimited / Yearly',
        ];

        return view('pages.user.users')
            ->with([
                'users' => $users,
                'plans' => $plans
            ]);
    }

    /**
     * @return Application|Factory|View
     */
    public function blocked(Request $request)
    {
        //Check if a search happened, if yes only one user will show, therefore we don't limit the query.
        if ($request->get('email') != null) {
            $limit = 100;
        } else {
            $limit = $request->get('entries') ?? 10;
        }

        // Get only users from trash
        $users = User::when($request->get('email') != null, function ($q) use ($request) {
            return $q->where('role', '!=', 'admin')->where('email', 'like', $request->get('email'))->onlyTrashed();
        }, function ($q) {
            return User::where('role', '!=', 'admin')->onlyTrashed();
        })->paginate($limit)->withQueryString();

        return view('pages.user.blocked')->with('users', $users);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function restore($id)
    {
        // Find User in trash
        $user = User::withTrashed()->find($id);
        // Check role
        if ($user->role === 'lender') {
            $model = Lender::withTrashed()->find($user->id);
        } else {
            $model = Broker::withTrashed()->find($user->id);
        }

        // Restore User with his relationship
        $model->restore();

        session()->flash('message', 'User has been unblocked');

        return redirect()->route('users-blocked');
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function destroy($id, $flag)
    {
        $user = User::find($id);

        if (! $user) {
            session()->flash('message', 'This user cannot be found. Please contact the maintenance team for help.');

            return redirect()->route('users');
        }

        $this->logoutBetaUser($user->id);
        $refreshTokenRepository = app(\Laravel\Passport\RefreshTokenRepository::class);
        foreach (User::find($id)->tokens as $token) {
            $token->revoke();
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        }

        // Check role
        if ($user->role === 'lender') {
            $model = Lender::find($user->id);
        } else {
            $model = Broker::find($user->id);
        }
        // Delete or Block User
        if ($flag === 'block') {
            $model->delete();
            $message = 'User has been blocked';
        } elseif ($flag === 'delete') {
            try {
                $model->forceDelete();
            } catch (\Throwable $exception) {
                session()->flash('message', 'This user cannot be permanently deleted. Please contact the maintenance team for help.');

                return redirect()->route('users');
            }

            $message = 'User has been deleted';
        }

        session()->flash('message', $message);

        return redirect()->route('users');
    }

    public function giftSub(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', "Selected user not found");
            return redirect()->back();
        }

        if ($user->role !== 'broker') {
            session()->flash('error', "Selected user must be a broker");
            return redirect()->back();
        }

        $user->newSubscription('default', $request->get('plan'))
            ->trialDays($request->get('duration'))
            ->create();


        session()->flash('message', "User subscribed to plan successfully");

        return redirect()->back();
    }

    /**
     * Process the selected chunk of Lenders into the CSV file
     *
     * @param  Request  $request
     */
    public function exportLenders(Request $request)
    {
        CreateLenderDealPreferencesFile::dispatch(auth()->user());

        session()->flash('message', 'File will be sent to your email when completed..');
        return redirect()->back();
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return RedirectResponse
     */
    public function approveBeta(Request $request, $id): RedirectResponse
    {
        $user = User::find($id);
        // Check role
        if ($user->role === 'lender') {
            $model = Lender::find($user->id);
        } else {
            $model = Broker::find($user->id);

            BetaUserApproved::dispatch($model->id);
        }

        if (intval($model->beta_user)) {
            $updateUser = false;
            $msgUpdate = 'User is no longer a beta user';
        } else {
            $updateUser = true;
            $msgUpdate = 'User is now a beta user';
        }

        $this->logoutBetaUser($user->id);

        // Change authorization User
        $model->update([
            'beta_user' => $updateUser,
        ]);

        session()->flash('message', $msgUpdate);

        return redirect()->route('users');
    }

    private function logoutBetaUser($id)
    {
        $user = User::find($id);

        $this->realTimeService->trigger('beta-user', 'BetaUser', [
            'user_id' => $user->id
        ]);
    }
}
