<?php

declare(strict_types=1);

namespace Imi\OpenTracing;

use Imi\App;
use Imi\Bean\Annotation\Bean;
use Imi\OpenTracing\Contract\ITracerDriver;
use Imi\RequestContext;
use OpenTracing\Scope;
use OpenTracing\Span;
use OpenTracing\StartSpanOptions;
use OpenTracing\Tracer as OpenTracingTracer;

/**
 * @Bean("Tracer")
 */
class TracerManager
{
    protected string $driver = '';

    protected array $options = [];

    private ?ITracerDriver $driverInstance = null;

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getTracer(?string $serviceName = null): OpenTracingTracer
    {
        $serviceName ??= ($this->options['serviceName'] ?? 'imi');
        $context = RequestContext::getContext();
        $key = 'tracer.activeTracer.' . $serviceName;

        return $context[$key] ??= $this->createTracer($serviceName);
    }

    public function createTracer(?string $serviceName = null): OpenTracingTracer
    {
        if (!$this->driverInstance)
        {
            if ('' === $this->driver)
            {
                throw new \InvalidArgumentException('Config @app.beans.Tracer.driver cannot be empty');
            }
            // @phpstan-ignore-next-line
            $this->driverInstance = App::getBean($this->driver);
        }

        return $this->driverInstance->createTracer($serviceName ?? $this->options['serviceName'] ?? 'imi', $this->options['config'] ?? []);
    }

    /**
     * @param array|StartSpanOptions $options
     */
    public function startActiveSpan(string $operationName, $options = []): Scope
    {
        return $this->getTracer()->startActiveSpan($operationName, $options);
    }

    /**
     * @param array|StartSpanOptions $options
     */
    public function startSpan(string $operationName, $options = []): Span
    {
        return $this->getTracer()->startSpan($operationName, $options);
    }
}
