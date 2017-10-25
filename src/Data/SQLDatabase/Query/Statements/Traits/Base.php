<?php
/**
 * Base.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits;


trait Base
{
    abstract protected function addStatementStructure($key, $command, $parameters = null, $bindings = null);
    abstract protected function addStatementStructureWithoutBindings($key, $command, $parameters = null);
}