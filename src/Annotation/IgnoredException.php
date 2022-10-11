<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 在异常类上声明本注解，捕获到该注解时不会认为错误.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class IgnoredException extends Base
{
}
