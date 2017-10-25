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
    protected $structures = [];

    /**
     * Grammar constructor.
     *
     * @param array $structures
     */
    public function __construct(array $structures)
    {
        $this->structures = $structures;
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

    abstract public function compileStructure($index, $command, $parameters = null, array $previous = null);
}