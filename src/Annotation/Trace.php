<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 如果该方法有 Trace 注解，代理会创建本地span。span操作名称的值将由 $operationName 获取。如果 $operationName 的值是空白字符串，操作名称将被设置为"类名::方法名()".
 *
 * @Annotation
 * @Target({"METHOD"})
 *
 * @property string      $operationName
 * @property string|null $serviceName
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Trace extends Base
{
    /**
     * {@inheritDoc}
     */
    protected ?string $defaultFieldName = 'operationName';

    public function __construct(?array $__data = null, string $operationName = '', ?string $serviceName = null)
    {
        parent::__construct(...\func_get_args());
    }
}
