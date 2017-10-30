<?php
/**
 * ProcessKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleHttpServer;

/**
 * 处理核心
 *
 * @package Dybasedev\Keeper\Http
 */
interface ProcessKernel
{
    public function getProcessName(): string;

    public function init(SwooleHttpServer $server, $workerId);

    public function process(Request $request, Response $response);

    public function destroy(SwooleHttpServer $server, $workerId);
}