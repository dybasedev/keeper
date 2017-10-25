<?php
/**
 * MySQLGrammar.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Grammars;

use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammar;

class MySQLGrammar extends Grammar
{
    protected $supportCommands = [];

    /**
     * MySQLGrammar constructor.
     *
     * @param array $structures
     */
    public function __construct(array $structures)
    {
        parent::__construct($structures);

        $this->registerStructureCompileCommands();
    }

    private function registerStructureCompileCommands()
    {
        $this->supportCommands['condition']    = $this->compileWhere();
        $this->supportCommands['nested-open']  = $this->compileNestedOpen();
        $this->supportCommands['nested-close'] = $this->compileNestedClose();
    }

    public function compileStructure($index, $command, $parameters = null, array $previous = null)
    {
        if (!isset($this->supportCommands[$command])) {
            throw new \InvalidArgumentException();
        }

        return ($this->supportCommands[$command])($index, $parameters, $previous);
    }

    protected function compileWhere()
    {
        return function ($index, $parameters, $previous) {
            $body = sprintf("%s %s %s", $parameters['body']['column'], $parameters['body']['operator'],
                $parameters['body']['value']);

            $command = null;
            if (is_array($previous)) {
                list(, $command, , ) = $previous;
            }

            if ($index != 0 && $command !== 'nested-open') {
                yield $parameters['logical'];
                yield $body;
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
                list(, $command, , ) = $previous;
            }

            if ($index != 0 && $command !== 'nested-open') {
                yield $parameters['logical'];
                yield '(';
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
}