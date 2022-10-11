<?php

declare(strict_types=1);

namespace Imi\OpenTracing;

use Imi\App;
use Imi\Bean\Annotation\Bean;
use Imi\OpenTracing\Contract\ITracerDriver;
use Imi\RequestContext;
use OpenTracing\Scope;
use OpenTracing\Span;
use OpenTracing\Tracer as OpenTracingTracer;

/**
 * @Bean("Tracer")
 */
class TracerManager
{
    protected string $driver = '';

    protected array $options = [];

    protected ?ITracerDriver $driverInstance = null;

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
            $this->driverInstance = App::getBean($this->driver);
        }

        return $this->driverInstance->createTracer($serviceName ?? $this->options['serviceName'] ?? 'imi', $this->options['config'] ?? []);
    }

    public function startActiveSpan(string $operationName, $options = []): Scope
    {
        return $this->getTracer()->startActiveSpan($operationName, $options);
    }

    public function startSpan(string $operationName, $options = []): Span
    {
        return $this->getTracer()->startSpan($operationName, $options);
    }

    public function startServiceActiveSpan(?string $serviceName, string $operationName, $options = []): Scope
    {
        $tracer = $this->getTracer($serviceName);
        if (!isset($options['child_of']) && !$tracer->getActiveSpan())
        {
            $options['child_of'] = self::getTracer()->getActiveSpan();
        }

        return $tracer->startActiveSpan($operationName, $options);
    }

    public function startServiceSpan(?string $serviceName, string $operationName, $options = []): Span
    {
        return $this->getTracer($serviceName)->startSpan($operationName, $options);
    }
}
