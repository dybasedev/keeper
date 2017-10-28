<?php
/**
 * ExecutablePreparation.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Closure;
use PDOStatement;

class ExecutablePreparation
{
    /**
     * @var string
     */
    public $statement;

    /**
     * @var Closure
     */
    protected $binder;

    /**
     * ExecutablePreparation constructor.
     *
     * @param string  $statement
     * @param Closure $binder
     */
    public function __construct($statement, Closure $binder)
    {
        $this->statement = $statement;
        $this->binder    = $binder;
    }

    /**
     * @param PDOStatement $PDOStatement
     * @param              $parameters
     *
     * @return void
     */
    public function binder(PDOStatement $PDOStatement, $parameters)
    {
        ($this->binder)($PDOStatement, $parameters);
    }
}