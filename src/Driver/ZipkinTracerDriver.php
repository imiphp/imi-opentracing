<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Driver;

use Imi\Log\Log;
use Imi\OpenTracing\Contract\ITracerDriver;
use OpenTracing\Tracer;
use Zipkin\Endpoint;
use Zipkin\TracingBuilder;

class ZipkinTracerDriver implements ITracerDriver
{
    public function createTracer(string $serviceName, array $config = []): Tracer
    {
        $endpoint = Endpoint::create($serviceName);
        $reporter = new \Zipkin\Reporters\Http($config['reporter'], null, Log::get());
        $samplerCreator = $config['sampler']['creator'] ?? '\Zipkin\Samplers\BinarySampler::createAsAlwaysSample';
        $samplerCreatorParams = $config['sampler']['creatorParams'] ?? [];
        /** @var \Zipkin\Sampler $sampler */
        $sampler = $samplerCreator(...$samplerCreatorParams);
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        return new \ZipkinOpenTracing\Tracer($tracing);
    }
}
