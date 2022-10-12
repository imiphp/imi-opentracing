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
 * @Bean("DbTracer")
 */
class DbTracer
{
    /**
     * 是否已启用.
     */
    protected bool $enable = false;

    /**
     * 服务名称，如果为空字符串则不使用服务名称.
     */
    protected ?string $serviceName = 'db';

    protected string $operationPrepare = 'db.prepare';

    protected string $operationExecute = 'db.execute';

    protected string $tagSql = 'db.sql';

    public function __init(): void
    {
        if ($this->enable)
        {
            AopManager::addAround('Imi\Db\*Drivers\*\Driver', 'exec', new DelayBeanCallable('DbTracer', 'aopExecute'));
            AopManager::addAround('Imi\Db\*Drivers\*\Driver', 'query', new DelayBeanCallable('DbTracer', 'aopExecute'));
            AopManager::addAround('Imi\Db\*Drivers\*\Driver', 'batchExec', new DelayBeanCallable('DbTracer', 'aopExecute'));

            AopManager::addAround('Imi\Db\*Drivers\*\Driver', 'prepare', new DelayBeanCallable('DbTracer', 'aopPrepare'));
            AopManager::addAround('Imi\Db\*Drivers\*\Statement', 'execute', new DelayBeanCallable('DbTracer', 'aopStatementExecute'));
        }
    }

    /**
     * @return mixed
     */
    public function aopExecute(AroundJoinPoint $joinPoint)
    {
        $tracer = $this->getTracer($isNew);
        $scope = TracerUtil::startRootActiveSpan($tracer, $this->operationExecute);
        $span = $scope->getSpan();
        try
        {
            [$sql] = $joinPoint->getArgs();
            $span->setTag($this->tagSql, $sql);

            return $joinPoint->proceed();
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
                TracerUtil::flush($tracer);
            }
        }
    }

    /**
     * @return mixed
     */
    public function aopPrepare(AroundJoinPoint $joinPoint)
    {
        $tracer = $this->getTracer($isNew);
        $scope = TracerUtil::startRootActiveSpan($tracer, $this->operationPrepare);
        $span = $scope->getSpan();
        try
        {
            [$sql] = $joinPoint->getArgs();
            $span->setTag($this->tagSql, $sql);

            return $joinPoint->proceed();
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
                TracerUtil::flush($tracer);
            }
        }
    }

    /**
     * @return mixed
     */
    public function aopStatementExecute(AroundJoinPoint $joinPoint)
    {
        $tracer = $this->getTracer($isNew);
        $scope = TracerUtil::startRootActiveSpan($tracer, $this->operationExecute);
        $span = $scope->getSpan();
        try
        {
            /** @var \Imi\Db\Interfaces\IStatement $statement */
            $statement = $joinPoint->getTarget();
            $span->setTag($this->tagSql, $statement->getSql());

            return $joinPoint->proceed();
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
                TracerUtil::flush($tracer);
            }
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
