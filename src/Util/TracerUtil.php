<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Util;

use Imi\OpenTracing\Facade\Tracer;
use OpenTracing\Scope;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\StartSpanOptions;

class TracerUtil
{
    private function __construct()
    {
    }

    /**
     * @param array|StartSpanOptions $options
     */
    public static function startRootActiveSpan(\OpenTracing\Tracer $tracer, string $operationName, $options = [], ?string $parentServiceName = null): Scope
    {
        if ($activeSpan = Tracer::getTracer($parentServiceName)->getActiveSpan())
        {
            self::setOptionParent($options, $activeSpan);
        }

        return $tracer->startActiveSpan($operationName, $options);
    }

    /**
     * @param array|StartSpanOptions $options
     * @param Span|SpanContext       $parent
     */
    public static function setOptionParent(&$options, $parent): void
    {
        if (\is_array($options))
        {
            $options['child_of'] = $parent;
        }
        else
        {
            $options = $options->withParent($parent);
        }
    }
}
