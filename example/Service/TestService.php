<?php

declare(strict_types=1);

namespace app\Service;

use app\Exception\GGException;
use Imi\Db\Db;
use Imi\OpenTracing\Annotation\Tag;
use Imi\OpenTracing\Annotation\Trace;
use Imi\OpenTracing\Facade\Tracer;
use Imi\OpenTracing\Util\TracerUtil;
use Imi\Redis\Redis;

class TestService
{
    /**
     * @Trace("add")
     * @Tag(key="method.params.a", value="{params.0}")
     * @Tag(key="method.params.b", value="{params.1}")
     * @Tag(key="method.returnValue", value="{returnValue}")
     * @Tag(key="method.message", value="{params.0}+{params.1}={returnValue}")
     *
     * @param int|float $a
     * @param int|float $b
     *
     * @return int|float
     */
    public function add($a, $b)
    {
        return $a + $b;
    }

    /**
     * @Trace
     */
    public function autoOperationName(): void
    {
    }

    /**
     * @Trace
     */
    public function ignoredException(): void
    {
        throw new GGException();
    }

    /**
     * @Trace
     */
    public function common(): void
    {
        $result = $this->add(mt_rand(), mt_rand());
        Db::exec('select ?', [$result]);
        Redis::set('imi:opentracing:test', (string) $result);

        $tracer = Tracer::createTracer('common_test');
        $scope1 = TracerUtil::startRootActiveSpan($tracer, 'test1');

        $scope2 = $tracer->startActiveSpan('test1-1');
        $scope2->close();

        $scope1->close();
        $tracer->flush();
    }
}
