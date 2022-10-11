<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 调用追踪的 tag.
 *
 * @Annotation
 * @Target({"METHOD"})
 *
 * @property string $key
 * @property mixed  $value
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Tag extends Base
{
    /**
     * @param mixed $value
     */
    public function __construct(?array $__data = null, string $key = '', $value = '')
    {
        parent::__construct(...\func_get_args());
    }
}
