<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Test\Jaeger;

use Imi\OpenTracing\Test\BaseTest;

abstract class JaegerBaseTest extends BaseTest
{
    protected static array $env = ['IMI_TRACER' => 'jaeger'];
}
