<?php
/**
 * RedisDriver.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Session\Drivers;


use Dybasedev\Keeper\Http\Interfaces\SessionDriver;
use Dybasedev\Keeper\Http\Session\Session;
use Redis;

class RedisDriver implements SessionDriver
{
    const REDIS_KEY_PREFIX = 'keeper:session:';

    /**
     * @var Redis
     */
    protected $redisConnection;

    /**
     * RedisDriver constructor.
     *
     * @param Redis      $redisConnection
     */
    public function __construct(Redis $redisConnection)
    {
        $this->redisConnection = $redisConnection;
    }

    public function find($sessionId)
    {
        if (empty($sessionId)) {
            return null;
        }

        if ($this->redisConnection->exists($key = $this->sessionKey($sessionId))) {
            return unserialize($this->redisConnection->get(self::REDIS_KEY_PREFIX . $sessionId));
        }

        return null;
    }

    protected function sessionKey($sessionId)
    {
        return self::REDIS_KEY_PREFIX . $sessionId;
    }

    public function store($sessionId, $data, int $lifetime = null)
    {
        if (empty($sessionId)) {
            return false;
        }

        return $this->redisConnection->setex($this->sessionKey($sessionId), $lifetime, serialize($data));
    }

}