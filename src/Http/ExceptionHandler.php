<?php
/**
 * ExceptionHandler.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Illuminate\Contracts\Config\Repository;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class ExceptionHandler
{
    /**
     * @var ContextContainer
     */
    protected $container;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * ExceptionHandler constructor.
     *
     * @param ContextContainer $container
     * @param Repository       $config
     */
    public function __construct(ContextContainer $container, Repository $config)
    {
        $this->container = $container;
        $this->config    = $config;
    }

    public function handle(\Throwable $exception, SwooleResponse $response)
    {
        $statusCode = 500;
        $headers    = [];

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $headers    = $exception->getHeaders();
        }

        $html = (new SymfonyExceptionHandler($this->config->get('global.debug',
            false)))->getHtml(FlattenException::create($exception));

        (new Response($html, $statusCode, $headers))->setSwooleResponse($response)->send();
    }
}