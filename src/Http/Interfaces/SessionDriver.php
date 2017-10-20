<?php
/**
 * SessionDriver.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;


use Dybasedev\Keeper\Http\Session\Session;

interface SessionDriver
{
    public function find($sessionId);

    public function store($sessionId, $data, int $lifetime = null);

    public function has($sessionId);
}