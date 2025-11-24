<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingStatusUpdated;
use App\Events\PaymentConfirmed;
use App\Events\PaymentStatusUpdated;
use App\Listeners\SendBookingCancellationSms;
use App\Listeners\SendBookingStatusUpdateSms;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Listeners\SendPaymentConfirmationSms;
use App\Listeners\SendPaymentStatusUpdateSms;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // SMS & Email Event Listeners
        // Checkout/booking notifications disabled; only payment notifications remain

        PaymentConfirmed::class => [
            SendPaymentConfirmationSms::class,
            SendPaymentConfirmationEmail::class,
        ],

        BookingCancelled::class => [
            SendBookingCancellationSms::class,
        ],

        // Status Update SMS Event Listeners
        BookingStatusUpdated::class => [
            SendBookingStatusUpdateSms::class,
        ],

        PaymentStatusUpdated::class => [
            SendPaymentStatusUpdateSms::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

