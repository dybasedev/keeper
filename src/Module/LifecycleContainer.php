<?php
/**
 * LifecycleContainer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Module;

use Dybasedev\Keeper\Module\Exceptions\ConflictException;
use Dybasedev\Keeper\Module\Exceptions\NotFoundException;
use Dybasedev\KeeperContracts\Module\Container;

class LifecycleContainer implements Container
{
    protected $instances = [];
    
    protected $bindings = [];
    
    protected $singletons = [];

    protected $immutables = [];
    
    public function get($id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->bindings[$id])) {
            throw new NotFoundException();
        }

        $instance = call_user_func_array($this->bindings[$id], [$this]);

        if (isset($this->singletons[$id])) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    public function has($id)
    {
        return isset($this->bindings[$id]);
    }

    public function remove($id)
    {
        unset($this->immutables[$id], $this->singletons[$id], $this->instances[$id], $this->bindings[$id]);
    }

    /**
     * @param      $id
     * @param      $object
     * @param bool $immutable
     * @param bool $singleton
     *
     * @return void
     */
    public function bind($id, $object, $immutable = true, $singleton = false)
    {
        if (isset($this->bindings[$id])) {
            if (isset($this->immutables[$id])) {
                throw new ConflictException($this->bindings[$id], new ConflictException($id));
            }

            unset($this->singletons[$id]);
            unset($this->immutables[$id]);
        }

        $this->bindings[$id] = $object;

        if ($immutable) {
            $this->immutables[$id] = true;
        }

        if ($singleton) {
            $this->singletons[$id] = true;
        }
    }

    public function singleton($id, $object, $immutable = true)
    {
        $this->bind($id, $object, $immutable, true);
    }

    public function immutable($id)
    {
        if (isset($this->immutables[$id])) {
            throw new ConflictException($id);
        }

        $this->immutables[$id] = true;
    }

    public function instance($id, $object, $immutable = false)
    {
        $this->bindings[$id] = $object;
        $this->instances[$id] = &$this->bindings[$id];

        if ($immutable) {
            $this->immutables[$id] = true;
        }
    }

}