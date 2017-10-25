<?php
/**
 * MySQLGrammar.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Grammars;

use Dybasedev\Keeper\Data\SQLDatabase\Query\Expression;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammar;

class MySQL extends Grammar
{

    protected function registerStructureCompileCommands()
    {
        $this->supportCommands['table']        = $this->compileTable();
        $this->supportCommands['condition']    = $this->compileWhere();
        $this->supportCommands['nested-open']  = $this->compileNestedOpen();
        $this->supportCommands['nested-close'] = $this->compileNestedClose();
    }

    protected function compileWhere()
    {
        return function ($index, $parameters, $previous) {
            $body = sprintf("%s %s %s", $this->wrapField($parameters['body']['column']),
                $parameters['body']['operator'],
                $parameters['body']['value']);

            $command = null;
            if (is_array($previous)) {
                list(, $command, ,) = $previous;
            }

            if ($index != 0 && $command !== 'nested-open') {
                yield from [$parameters['logical'], $body];
            } else {
                yield $body;
            }
        };
    }

    protected function compileNestedOpen()
    {
        return function ($index, $parameters, $previous) {
            $command = null;
            if (is_array($previous)) {
                list(, $command, ,) = $previous;
            }

            if ($index != 0 && $command !== 'nested-open') {
                yield from [$parameters['logical'], '('];
            } else {
                yield '(';
            }
        };
    }

    protected function compileNestedClose()
    {
        return function () {
            return ')';
        };
    }

    protected function compileJoin()
    {
        return function () {

        };
    }

    protected function compileTable()
    {
        return function ($index, $parameters) {
            if ($parameters['alias']) {
                return "( {$parameters['table']} ) as {$this->wrapField($parameters['alias'])}";
            }

            return $parameters['table'] instanceof Expression
                ? "( {$parameters['table']->getExpression()} )"
                : $this->wrapField($parameters['table']);
        };
    }

    protected function compileSelect()
    {
        return function ($index, $parameters) {

        };
    }
}