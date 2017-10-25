<?php
/**
 * Grammar.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query;


abstract class Grammar
{
    const TYPE_SELECT = 'select';
    const TYPE_DELETE = 'delete';
    const TYPE_UPDATE = 'update';
    const TYPE_INSERT = 'insert';

    protected $structures = [];

    protected $supportCommands = [];

    /**
     * Grammar constructor.
     *
     * @param array $structures
     */
    public function __construct(array $structures)
    {
        $this->structures = $structures;
        $this->registerStructureCompileCommands();
    }

    abstract protected function registerStructureCompileCommands();

    public function compileStructure($index, $command, $parameters = null, array $previous = null)
    {
        if (!isset($this->supportCommands[$command])) {
            throw new \InvalidArgumentException();
        }

        return ($this->supportCommands[$command])($index, $parameters, $previous);
    }

    public function compile()
    {
        $compiles = [];

        $previous = null;
        foreach ($this->structures as $key => $structure) {
            $subStatements = [];
            foreach ($structure as $index => list($command, $parameters)) {
                $result   = $this->compileStructure($index, $command, $parameters, $previous);
                $previous = [$index, $command, $parameters, $previous];

                if ($result instanceof \Generator) {
                    foreach ($result as $value) {
                        $subStatements[] = $value;
                    }
                } else {
                    $subStatements[] = $result;
                }
            }

            $compiles[$key] = implode(' ', $subStatements);
        }

        return $compiles;
    }

    /**
     * @param string $statementType
     *
     * @return string
     */
    public function exportSql(string $statementType)
    {
        $result    = $this->compile();
        $statement = null;

        switch ($statementType) {
            case self::TYPE_SELECT:
                $statement = $this->buildSelectStatement($result);
        }

        return $statement;
    }

    public function buildSelectStatement(array $compiledStructures)
    {
        $compiledStructures['select'] = $compiledStructures['select'] ?? '*';

        $statement = "select {$compiledStructures['select']}";
        if (isset($compiledStructures['table'])) {
            $statement .= " from {$compiledStructures['table']}";
        }

        if (isset($compiledStructures['where'])) {
            $statement .= " where {$compiledStructures['where']}";
        }

        return $statement;
    }

    public function wrapField($field, $table = null)
    {
        if ($field !== '*') {
            if (strpos($field, '.') !== false) {
                list($table, $field) = explode('.', $field);
            }

            $field = "`{$field}`";
        }

        if (!is_null($table)) {
            return "`{$table}`.$field";
        }

        return $field;
    }

}