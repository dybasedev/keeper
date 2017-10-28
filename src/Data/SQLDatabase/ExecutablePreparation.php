<?php
/**
 * ExecutablePreparation.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


class ExecutablePreparation
{
    /**
     * @var string
     */
    public $statement;

    /**
     * @var callable
     */
    protected $binder;

    /**
     * ExecutablePreparation constructor.
     *
     * @param string   $statement
     * @param callable $binder
     */
    public function __construct($statement, callable $binder)
    {
        $this->statement = $statement;
        $this->binder    = $binder;
    }

}