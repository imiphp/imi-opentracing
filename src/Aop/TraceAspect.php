<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Aop;

use Imi\Aop\Annotation\Around;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\AroundJoinPoint;
use Imi\Aop\PointCutType;
use Imi\Bean\Annotation\AnnotationManager;
use Imi\Bean\BeanFactory;
use Imi\OpenTracing\Annotation\Tag;
use Imi\OpenTracing\Annotation\Trace;
use Imi\OpenTracing\Facade\Tracer;
use Imi\OpenTracing\Util\SpanUtil;
use Imi\OpenTracing\Util\TracerUtil;
use Imi\Util\ObjectArrayHelper;

/**
 * @Aspect
 */
class TraceAspect
{
    /**
     * @PointCut(
     *         type=PointCutType::ANNOTATION,
     *         allow={
     *             Trace::class
     *         }
     * )
     * @Around
     *
     * @return mixed
     */
    public function around(AroundJoinPoint $joinPoint)
    {
        $class = BeanFactory::getObjectClass($joinPoint->getTarget());
        $method = $joinPoint->getMethod();
        /** @var Trace $traceAnnotation */
        $traceAnnotation = AnnotationManager::getMethodAnnotations($class, $method, Trace::class)[0];
        $tracer = Tracer::getTracer($traceAnnotation->serviceName);
        if ('' === $traceAnnotation->operationName)
        {
            $operationName = $class . '::' . $method . '()';
        }
        else
        {
            $operationName = $traceAnnotation->operationName;
        }
        $scope = $tracer->startActiveSpan($operationName);
        $span = $scope->getSpan();
        try
        {
            return $returnValue = $joinPoint->proceed();
        }
        catch (\Throwable $th)
        {
            SpanUtil::log($span, $th);
            throw $th;
        }
        finally
        {
            $context = [
                'params'      => $joinPoint->getArgs(),
                'returnValue' => $returnValue ?? null,
            ];
            /** @var Tag $tagAnnotation */
            foreach (AnnotationManager::getMethodAnnotations($class, $method, Tag::class) as $tagAnnotation)
            {
                $value = $tagAnnotation->value;
                if (\is_string($value))
                {
                    $value = preg_replace_callback('/\{([^\}]+)\}/', static fn (array $matches): string => (string) ObjectArrayHelper::get($context, $matches[1]), $value);
                }
                $span->setTag($tagAnnotation->key, $value);
            }
            $span->finish();
            TracerUtil::flush($tracer);
        }
    }
}
