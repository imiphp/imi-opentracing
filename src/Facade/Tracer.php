<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Facade;

use Imi\Facade\Annotation\Facade;
use Imi\Facade\BaseFacade;

/**
 * @Facade(class="Tracer", request=false, args={})
 *
 * @method static string getDriver()
 * @method static array getOptions()
 * @method static \OpenTracing\Tracer getTracer(?string $serviceName = NULL)
 * @method static \OpenTracing\Scope startActiveSpan(string $operationName, $options = [])
 * @method static \OpenTracing\Span startSpan(string $operationName, $options = [])
 * @method static \OpenTracing\Scope startServiceActiveSpan(?string $serviceName, string $operationName, $options = [])
 * @method static \OpenTracing\Span startServiceSpan(?string $serviceName, string $operationName, $options = [])
 */
class Tracer extends BaseFacade
{
}
