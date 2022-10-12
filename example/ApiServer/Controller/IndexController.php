<?php

declare(strict_types=1);

namespace app\ApiServer\Controller;

use app\Service\TestService;
use Imi\Aop\Annotation\Inject;
use Imi\Controller\HttpController;
use Imi\Db\Db;
use Imi\OpenTracing\Facade\Tracer;
use Imi\OpenTracing\Util\TracerUtil;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;

/**
 * @Controller("/")
 */
class IndexController extends HttpController
{
    /**
     * @Inject
     */
    protected TestService $testService;

    /**
     * @Action
     * @Route("/")
     *
     * @return mixed
     */
    public function index()
    {
        // startActiveSpan
        $scope = Tracer::startActiveSpan('write1');
        $this->response->getBody()->write('imi');
        $scope->close();

        // startRootActiveSpan
        $tracer = Tracer::createTracer('backend1');
        $scope1 = TracerUtil::startRootActiveSpan($tracer, 'test1');

        $scope2 = $tracer->startActiveSpan('test1-1');
        $scope2->close();

        $scope1->close();

        // startSpan
        $span = Tracer::startSpan('write2');
        $span->finish();

        // backend1 test2
        $span = $tracer->startSpan('test2');
        $span->finish();
        $tracer->flush();

        return $this->response;
    }

    /**
     * @Action
     *
     * @return mixed
     */
    public function exception()
    {
        throw new \RuntimeException('gg');
    }

    /**
     * @Action
     *
     * @return mixed
     */
    public function add($a, $b)
    {
        return [
            'result' => $this->testService->add($a, $b),
        ];
    }

    /**
     * @Action
     *
     * @return mixed
     */
    public function autoOperationName()
    {
        $this->testService->autoOperationName();
    }

    /**
     * @Action
     *
     * @return mixed
     */
    public function ignoredException()
    {
        $this->testService->ignoredException();
    }

    /**
     * @Action
     *
     * @return mixed
     */
    public function db()
    {
        $db = Db::getInstance();
        $db->query('select 123');

        $stmt = $db->prepare('select ?');
        $stmt->execute([1]);
    }
}
