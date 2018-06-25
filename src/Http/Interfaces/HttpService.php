<?php
/**
 * HttpService.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;

use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

interface HttpService
{
    /**
     * Initialize something
     *
     * @param SwooleHttpServer $server
     * @param                  $workerId
     *
     * @return mixed
     */
    public function init(SwooleHttpServer $server, $workerId);

    /**
     * Http service provide process
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function process(Request $request, Response $response);

    /**
     * The method will be called before server worker stop
     *
     * @param SwooleHttpServer $server
     * @param                  $workerId
     *
     * @return mixed
     */
    public function destroy(SwooleHttpServer $server, $workerId);
}