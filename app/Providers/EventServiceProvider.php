<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\DealPublished::class => [
            \App\Listeners\NotifyPublishedDeal::class,
            \App\Listeners\DealMarkPremiumIfSubscribed::class,
        ],
        \App\Events\DealPurchased::class => [
            \App\Listeners\DealMarkPremiumFromPurchase::class,
        ],
        \Illuminate\Auth\Events\PasswordReset::class => [
            \App\Listeners\SendPasswordResetNotification::class,
        ],
        \Illuminate\Mail\Events\MessageSending::class => [
            \App\Listeners\ModifyOutGoingEmailHeaders::class,
            \App\Listeners\LogSendingEmail::class,
            \App\Listeners\CheckAllowedEmails::class,
        ],
        \Illuminate\Mail\Events\MessageSent::class => [
            \App\Listeners\LogSentEmail::class,
        ],
        \App\Events\QuotePublished::class => [
            \App\Listeners\CheckDealQuoteLimit::class
        ],
        \App\Events\QuoteRejected::class => [
            \App\Listeners\SendQuoteRejectedNotification::class,
            \App\Listeners\ResetDealQuoteLimitReached::class,
        ],
        \Laravel\Cashier\Events\WebhookReceived::class => [
            \App\Listeners\ProcessStripeWebhook::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \App\Listeners\UserEventSubscriber::class,
        \App\Listeners\CompanyEventSubscriber::class,
        \App\Listeners\QuoteEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
