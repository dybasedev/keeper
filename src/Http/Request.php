<?php
/**
 * Request.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Swoole\Http\Request as SwooleRequest;

/**
 * Class Request
 *
 * 请求
 *
 * @package Dybasedev\Keeper\Http
 */
class Request extends SymfonyRequest
{
    /**
     * 从 Swoole Request 实例创建请求实体
     *
     * @param SwooleRequest $request
     *
     * @return static
     */
    public static function createFromSwooleRequest(SwooleRequest $request)
    {
        if (isset($request->server)) {
            $keys = array_map('strtoupper', array_keys($request->server));
            $server = array_combine($keys, array_values($request->server));
        } else {
            $server = [];
        }

        if (isset($request->header)) {
            $keys = array_map(function ($value) {
                return 'HTTP_' . str_replace('-', '_', strtoupper($value));
            }, array_keys($request->header));
            $server = array_merge($server, array_combine($keys, array_values($request->header)));
        }

        $_GET    = isset($request->get) ? $request->get : [];
        $_POST   = isset($request->post) ? $request->post : [];
        $_FILES  = isset($request->files) ? $request->files : [];
        $_COOKIE = isset($request->cookie) ? $request->cookie : [];
        $_SERVER = $server;

        return new static($_GET, $_POST, [], $_COOKIE, $_FILES, $server);
    }
}