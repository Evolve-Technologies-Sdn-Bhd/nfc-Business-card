<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Observers\SubscriptionObserver;
use Illuminate\Support\ServiceProvider;
use App\Services\FileUploadService;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService();
        });

        // Configure default Guzzle HTTP client with SSL settings for Windows development
        $this->app->bind(Client::class, function ($app) {
            $options = [];

            $verifySSL = env('CURL_VERIFY_SSL', true);
            if ($verifySSL === false || $verifySSL === 'false') {
                $options['verify'] = false;
            } else {
                // Use downloaded CA bundle if available
                $cacertPath = storage_path('cacert.pem');
                if (file_exists($cacertPath)) {
                    $options['verify'] = $cacertPath;
                }
            }

            return new Client($options);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // For Windows development: disable SSL verification globally if configured
        $verifySSL = env('CURL_VERIFY_SSL', true);
        if ($verifySSL === false || $verifySSL === 'false') {
            // Set default stream context for SSL
            stream_context_set_default([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
        }

        // Single source of truth: subscriptions table → write-through sync legacy user columns
        Subscription::observe(SubscriptionObserver::class);
    }
}
