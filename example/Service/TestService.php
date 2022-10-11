<?php

declare(strict_types=1);

namespace app\Service;

use app\Exception\GGException;
use Imi\OpenTracing\Annotation\Tag;
use Imi\OpenTracing\Annotation\Trace;

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
}
