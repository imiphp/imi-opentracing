<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Test;

trait TFpmTest
{
    protected static function __startServer(): void
    {
        static::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__) . '/example/bin/imi-cli', 'fpm/start'], null, static::$env);
        $process->start();
    }
}
