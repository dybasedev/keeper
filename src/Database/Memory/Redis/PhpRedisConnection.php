<?php
/**
 * PhpRedisConnection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\Memory\Redis;


use Redis;
use RuntimeException;

class PhpRedisConnection extends Connection
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Redis
     */
    protected $redisInstance;

    protected function createDriverInstance()
    {
        $redisConnection = new Redis();
        if (!$redisConnection->connect($this->options['host'] ?? '127.0.0.1', $this->options['port'] ?? 6379)) {
            throw new RuntimeException('Redis connect failed.');
        }

        if (!empty($this->options['password'])) {
            $redisConnection->auth($this->options['password']);
        }

        if (!$redisConnection->select($database = $this->options['database'])) {
            throw new RuntimeException("Cannot select index[{$database}] on the redis server.");
        }

        return $redisConnection;
    }

    public function disconnect()
    {
        $this->redisInstance->close();

        parent::disconnect();
    }
}