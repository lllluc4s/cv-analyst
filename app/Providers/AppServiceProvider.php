<?php

namespace App\Providers;

use App\Services\AnalyticsService;
use App\Services\GeoLocationService;
use App\Services\ResendService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GeoLocationService::class, function ($app) {
            return new GeoLocationService();
        });
        
        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService($app->make(GeoLocationService::class));
        });
        
        $this->app->singleton(ResendService::class, function ($app) {
            return new ResendService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observers
        \App\Models\DiaNaoTrabalhado::observe(\App\Observers\DiaNaoTrabalhadoObserver::class);
        
        // Registrar event listeners
        Event::listen(
            \App\Events\CandidaturaMovedToContratado::class,
            \App\Listeners\CreateColaboradorFromCandidatura::class
        );
        
        // Configurar TrustedProxy para garantir que os IPs reais sejam capturados
        if (config('trustedproxy.proxies')) {
            $trustedHeaderSet = \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR | 
                               \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST | 
                               \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT | 
                               \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO;
            
            \Illuminate\Http\Request::setTrustedProxies(
                config('trustedproxy.proxies') === '*' ? ['*'] : explode(',', config('trustedproxy.proxies')),
                $trustedHeaderSet
            );
        }
    }
}
