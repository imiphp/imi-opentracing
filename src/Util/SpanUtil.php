<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Util;

use Imi\Bean\Annotation\AnnotationManager;
use Imi\OpenTracing\Annotation\IgnoredException;
use OpenTracing\Span;

class SpanUtil
{
    private function __construct()
    {
    }

    /**
     * @param array|\Throwable            $fields
     * @param int|float|DateTimeInterface $timestamp
     */
    public static function log(Span $span, $fields, $timestamp = null): void
    {
        if ($fields instanceof \Throwable)
        {
            if (AnnotationManager::getClassAnnotations(\get_class($fields), IgnoredException::class, true, true))
            {
                return;
            }
            else
            {
                $span->setTag(\OpenTracing\Tags\ERROR, true);
                $destFields = [
                    'class'   => \get_class($fields),
                    'code'    => $fields->getCode(),
                    'message' => $fields->getMessage(),
                    'file'    => $fields->getFile(),
                    'line'    => $fields->getLine(),
                    'trace'   => $fields->getTraceAsString(),
                ];
            }
        }
        else
        {
            $destFields = $fields;
        }
        $span->log($destFields, $timestamp);
    }
}
