<?php
/**
 * SessionHandle.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Middlewares;


use Closure;
use Dybasedev\Keeper\Http\Session\Manager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Monolog\Logger;

class SessionHandle
{
    /**
     * @var Manager
     */
    protected $sessionManager;

    /**
     * @var CookieJar
     */
    protected $cookie;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * SessionHandle constructor.
     *
     * @param Manager    $sessionManager
     * @param CookieJar  $cookie
     * @param Repository $config
     */
    public function __construct(Manager $sessionManager, CookieJar $cookie, Repository $config)
    {
        $this->sessionManager = $sessionManager;
        $this->cookie = $cookie;
        $this->config = $config;
    }


    public function handle(Request $request, Closure $next)
    {
        $sessionId = $this->sessionIdGetter($request);
        $this->sessionManager->openSession($sessionId ?: $newSessionId = $this->generateSessionId());

        /** @var Response $response */
        $response = $next($request);

        $this->sessionManager->closeSession();

        if (isset($newSessionId)) {
            $response->headers->setCookie($this->cookie->make($this->config->get('session.cookie'), $newSessionId));
        }

        return $response;
    }

    protected function sessionIdGetter(Request $request)
    {
        $sessionId = $request->cookie($key = $this->config->get('session.cookie'));

        return $sessionId;
    }

    protected function generateSessionId()
    {
        return Str::random(40);
    }
}