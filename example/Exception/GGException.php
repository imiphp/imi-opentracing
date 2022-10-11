<?php

declare(strict_types=1);

namespace app\Exception;

use Imi\OpenTracing\Annotation\IgnoredException;
use RuntimeException;

/**
 * @IgnoredException
 */
class GGException extends RuntimeException
{
}
