<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
    require __DIR__ . '/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$tracer = (new TracerProvider(
    [
        new SimpleSpanProcessor(
            new OpenTelemetry\Contrib\Zipkin\Exporter(
                PsrTransportFactory::discover()->create('http://collector:9411/api/v2/spans', 'application/json')
            ),
        ),
    ],
    new AlwaysOnSampler(),
))->getTracer('Laravel');

$request = Request::createFromGlobals();
$rootSpan = $tracer->spanBuilder($request->getUri())->startSpan();
$rootScope = $rootSpan->activate();

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);

$rootScope->detach();
$rootSpan->end();
