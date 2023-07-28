<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\OpenTelemetry\Facades\Measure;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        global $tracer;
        PendingRequest::macro('withTrace2', function () use ($tracer) {
            if ($span = $tracer->spanBuilder("withTrace")->startSpan()) {
                $headers['traceparent'] = sprintf(
                    '%s-%s-%s-%02x',
                    '00',
                    $span->getContext()->getTraceId(),
                    $span->getContext()->getSpanId(),
                    $span->getContext()->getTraceFlags(),
                );

                /** @var PendingRequest $this */
                $this->withHeaders($headers);
            }

            return $this;
        });
    }

    public function boot()
    {
        Paginator::useBootstrap();
        View::composer('layouts.app', function ($view) {
            $navbarCategories = Category::with('subCategory')->take(7)->get();
            $view->with([
                'navbarCategories' => $navbarCategories
            ]);
        });
        // View::composer('shop.index', function ($view) {
        //     $productCategories = Category::inRandomOrder()->take(6)->get();
        //     $view->with([
        //         'productCategories' => $productCategories
        //     ]);
        // });
    }
}
