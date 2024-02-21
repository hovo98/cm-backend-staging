<?php

namespace App\Providers;

use App\Broker;
use App\Channels\UserCheckMailChannel;
use App\Company;
use App\Deal;
use App\Lender;
use App\Observers\CompanyObserver;
use App\Observers\DealObserver;
use App\Observers\QuoteObserver;
use App\Observers\UserObserver;
use App\Quote;
use App\Services\Payment\DummyStripeService;
use App\Services\Payment\PaymentInterface;
use App\Services\Payment\StripePaymentService;
use App\Services\RealTime\DummyRealTimeService;
use App\Services\RealTime\PusherRealTimeService;
use App\Services\RealTime\RealTimeServiceInterface;
use App\Services\VideoCall\VideoCallInterface;
use App\Services\VideoCall\ZoomVideoCall;
use App\User;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $inTestMode = config('app.env') == 'testing';

        // Force HTTPS on assets anywhere but in local
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        if (! $inTestMode && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // Check if Notification should be sent
        $this->app->bind(MailChannel::class, UserCheckMailChannel::class);

        $this->app->bind(RealTimeServiceInterface::class, PusherRealTimeService::class);
        $this->app->bind(PaymentInterface::class, StripePaymentService::class);
        $this->app->bind(VideoCallInterface::class, ZoomVideoCall::class);

        if ($inTestMode) {
            $this->app->bind(RealTimeServiceInterface::class, DummyRealTimeService::class);
            $this->app->bind(PaymentInterface::class, DummyStripeService::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Deal::observe(DealObserver::class);
        Broker::observe(UserObserver::class);
        Lender::observe(UserObserver::class);
        Quote::observe(QuoteObserver::class);
        Company::observe(CompanyObserver::class);
        Cashier::useCustomerModel(User::class);
    }
}
