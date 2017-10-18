<?php
/**
 * SessionDriver.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;


use Dybasedev\Keeper\Http\Session\Context;

interface SessionDriver
{
    public function find($sessionId);

    public function store($sessionId, Context $context, int $lifetime = null);
}