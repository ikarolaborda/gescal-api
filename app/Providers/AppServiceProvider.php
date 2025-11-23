<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Document;
use App\Models\Family;
use App\Models\Person;
use App\Observers\FamilyObserver;
use App\Observers\PersonObserver;
use App\Observers\PIIAccessObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for business rules
        Person::observe(PersonObserver::class);
        Family::observe(FamilyObserver::class);

        // Register PII access logging observers
        if (config('lgpd.audit.enabled', true)) {
            Person::observe(PIIAccessObserver::class);
            Document::observe(PIIAccessObserver::class);
            Address::observe(PIIAccessObserver::class);
            Family::observe(PIIAccessObserver::class);
        }
    }
}
