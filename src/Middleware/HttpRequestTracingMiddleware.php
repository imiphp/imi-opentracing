<?php

declare(strict_types=1);

namespace Imi\OpenTracing\Middleware;

use Imi\OpenTracing\Facade\Tracer;
use Imi\OpenTracing\Util\SpanUtil;
use Imi\Server\Http\Message\Proxy\ResponseProxy;
use Imi\Swoole\Util\Coroutine;
use Imi\Worker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpRequestTracingMiddleware implements MiddlewareInterface
{
    protected string $operationName = 'request';

    protected string $workerId = 'worker.id';

    protected string $coroutineId = 'coroutine.id';

    protected string $url = 'http.url';

    protected string $method = 'http.method';

    protected string $header = 'http.header';

    protected string $statusCode = 'http.status_code';

    /**
     * 处理方法.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tracer = Tracer::getTracer();
        $scope = $tracer->startActiveSpan($this->operationName);
        $span = $scope->getSpan();
        try
        {
            $span->setTag($this->workerId, Worker::getWorkerId());
            $span->setTag($this->coroutineId, Coroutine::getCid());
            $span->setTag($this->url, (string) $request->getUri());
            $span->setTag($this->method, $request->getMethod());
            foreach ($request->getHeaders() as $key => $value)
            {
                $span->setTag($this->header . '.' . $key, implode(', ', $value));
            }
            $response = $handler->handle($request);

            return $response;
        }
        catch (\Throwable $th)
        {
            SpanUtil::log($span, $th);
            throw $th;
        }
        finally
        {
            if (isset($response))
            {
                $statusCode = $response->getStatusCode();
            }
            else
            {
                $statusCode = ResponseProxy::getStatusCode();
            }
            $span->setTag($this->statusCode, $statusCode);
            $span->finish();
            $tracer->flush();
        }
    }
}
