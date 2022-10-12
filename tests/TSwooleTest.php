<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Test;

trait TSwooleTest
{
    protected static function __startServer(): void
    {
        static::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__) . '/example/bin/imi-swoole', 'swoole/start'], null, static::$env);
        $process->start();
    }

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        if (!\extension_loaded('swoole'))
        {
            static::markTestSkipped('no swoole');
        }
        parent::setUpBeforeClass();
    }
}
