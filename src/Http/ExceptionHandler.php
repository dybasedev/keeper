<?php
/**
 * ExceptionHandler.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Illuminate\Contracts\Config\Repository;
use Monolog\Logger;
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
     * @var Logger
     */
    protected $logger;

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

    protected function writeLog($level, $message, $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     *
     * @return ExceptionHandler
     */
    public function setLogger(Logger $logger): ExceptionHandler
    {
        $this->logger = $logger;

        return $this;
    }


    public function handle(\Throwable $exception, SwooleResponse $response)
    {
        $this->logger->log(Logger::ERROR, (string)$exception);

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