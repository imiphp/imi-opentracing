<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Driver;

use Imi\Log\Log;
use Imi\OpenTracing\Contract\ITracerDriver;
use Jaeger\Config;
use OpenTracing\Tracer;

class JaegerTracerDriver implements ITracerDriver
{
    public function createTracer(string $serviceName, array $config = []): Tracer
    {
        $config = new Config($config, $serviceName, Log::get());

        return $config->initializeTracer();
    }
}
