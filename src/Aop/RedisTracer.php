<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Aop;

use Imi\Aop\AopManager;
use Imi\Aop\AroundJoinPoint;
use Imi\Bean\Annotation\Bean;
use Imi\OpenTracing\Facade\Tracer;
use Imi\OpenTracing\Util\SpanUtil;
use Imi\OpenTracing\Util\TracerUtil;
use Imi\Util\DelayBeanCallable;
use Imi\Util\Text;

/**
 * @Bean("RedisTracer")
 */
class RedisTracer
{
    /**
     * 是否已启用.
     */
    protected bool $enable = false;

    /**
     * 服务名称，如果为空字符串则不使用服务名称.
     */
    protected ?string $serviceName = 'redis';

    protected string $operationPrefix = 'redis.';

    protected string $tagParams = 'redis.params';

    protected string $tagReturnValue = 'redis.returnValue';

    public function __init(): void
    {
        if ($this->enable)
        {
            AopManager::addAround(\Imi\Redis\RedisHandler::class, '*', new DelayBeanCallable('RedisTracer', 'aop'));
        }
    }

    /**
     * @return mixed
     */
    public function aop(AroundJoinPoint $joinPoint)
    {
        $method = $joinPoint->getMethod();
        if (\in_array($method, ['__call', 'reconnect', 'evalEx', 'scan', 'scanEach', 'hscan', 'hscanEach', 'sscan', 'sscanEach', 'zscan', 'zscanEach']))
        {
            $args = $joinPoint->getArgs();
            if ('__call' === $method)
            {
                $method = $args[0];
                $args = $args[1];
            }
            $tracer = $this->getTracer($isNew);
            $scope = TracerUtil::startRootActiveSpan($tracer, $this->operationPrefix . $method);
            $span = $scope->getSpan();
            try
            {
                $span->setTag($this->tagParams, json_encode($args, \JSON_UNESCAPED_UNICODE));
                $result = $joinPoint->proceed();
                $span->setTag($this->tagReturnValue, json_encode($result, \JSON_UNESCAPED_UNICODE));

                return $result;
            }
            catch (\Throwable $th)
            {
                SpanUtil::log($span, $th);
                throw $th;
            }
            finally
            {
                $span->finish();
                if ($isNew)
                {
                    $tracer->flush();
                }
            }
        }
        else
        {
            return $joinPoint->proceed();
        }
    }

    /**
     * 是否已启用.
     */
    public function isEnabled(): bool
    {
        return $this->enable;
    }

    private function getTracer(?bool &$isNew): \OpenTracing\Tracer
    {
        if (Text::isEmpty($this->serviceName))
        {
            $isNew = false;

            return Tracer::getTracer();
        }
        else
        {
            $isNew = true;

            return Tracer::createTracer($this->serviceName);
        }
    }
}
