<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Test;

trait TWorkermanTest
{
    protected static function __startServer(): void
    {
        static::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__) . '/example/bin/imi-workerman', 'workerman/start'], null, static::$env);
        $process->start();
    }
}
