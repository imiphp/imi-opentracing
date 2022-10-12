<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Test;

use function Imi\env;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Yurun\Util\HttpRequest;

abstract class BaseTest extends TestCase
{
    protected static Process $process;

    protected static string $httpHost = '';

    protected static array $env = [];

    protected static function __startServer(): void
    {
        throw new \RuntimeException('You must implement the __startServer() method');
    }

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$httpHost = env('HTTP_SERVER_HOST', 'http://127.0.0.1:8080/');
        static::__startServer();
        $httpRequest = new HttpRequest();
        for ($i = 0; $i < 30; ++$i)
        {
            sleep(1);
            if ('imi' === $httpRequest->timeout(3000)->get(self::$httpHost)->body())
            {
                sleep(1);

                return;
            }
        }
        throw new \RuntimeException('Server started failed');
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        if (isset(self::$process))
        {
            self::$process->stop(10, \SIGTERM);
        }
    }

    public function testException(): void
    {
        $this->assertEquals('gg', (new HttpRequest())->get(self::$httpHost . 'exception')->json(true)['message'] ?? null);
    }

    public function testAdd(): void
    {
        $this->assertEquals(3, (new HttpRequest())->get(self::$httpHost . 'add', ['a' => 1, 'b' => 2])->json(true)['result'] ?? null);
    }

    public function testAutoOperationName(): void
    {
        $this->assertEquals('[]', (new HttpRequest())->get(self::$httpHost . 'autoOperationName')->body());
    }

    public function testDb(): void
    {
        $this->assertEquals('[]', (new HttpRequest())->get(self::$httpHost . 'db')->body());
    }

    public function testRedis(): void
    {
        $this->assertEquals('a', (new HttpRequest())->get(self::$httpHost . 'redis')->json(true)['result'] ?? null);
    }

    public function testCommon(): void
    {
        $this->assertEquals('[]', (new HttpRequest())->get(self::$httpHost . 'common')->body());
    }
}
