<?php
/**
 * StatementProcessed.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Events;


class StatementProcessed
{
    public $sql;
    public $bindings;
    public $time;

    /**
     * StatementProcessed constructor.
     *
     * @param $sql
     * @param $bindings
     * @param $time
     */
    public function __construct($sql, $bindings, $time)
    {
        $this->sql      = $sql;
        $this->bindings = $bindings;
        $this->time     = $time;
    }


}