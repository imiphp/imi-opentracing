<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Contract;

use OpenTracing\Tracer;

interface ITracerDriver
{
    public function createTracer(string $serviceName, array $config = []): Tracer;
}
