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
    public function init(SwooleHttpServer $server, $workerId);

    public function process(Request $request, Response $response);

    public function destroy(SwooleHttpServer $server, $workerId);
}