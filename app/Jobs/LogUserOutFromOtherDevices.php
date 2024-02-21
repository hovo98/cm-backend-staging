<?php

namespace App\Jobs;

use App\Services\RealTime\RealTimeServiceInterface;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogUserOutFromOtherDevices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->logoutUserByIdAllDevices($this->user);
        $this->logoutWithPusher($this->user);
    }

    private function logoutUserByIdAllDevices(User $user)
    {
        $i = 0;

        foreach ($user->activeTokens as $token) {
            if ($i !== 0) {
                $this->revokeAccessAndRefreshTokens($token->id);
            }
            $i++;
        }
    }

    protected function revokeAccessAndRefreshTokens($tokenId)
    {
        $tokenRepository = app('Laravel\Passport\TokenRepository');
        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

        $tokenRepository->revokeAccessToken($tokenId);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
    }

    private function logoutWithPusher(User $user)
    {
        $realTimeService = app()->make(RealTimeServiceInterface::class);

        $realTimeService->trigger('beta-user', 'BetaUser', [
            'user_id' => $user->id
        ]);
    }
}
