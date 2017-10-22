<?php
/**
 * BaseStatement.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query;


class BaseStatement
{
    /**
     * @var string|Expression
     */
    protected $table;

    /**
     * @var array
     */
    protected $structure = [];

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @param string|Expression $table
     *
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    protected function addStatementStructure($key, $type, $structure = null, $bindings = null)
    {
        $this->structure[$key][] = [$type, $structure];
        $this->bindings[$key][]  = $bindings;

        return $this;
    }

    public function buildSql()
    {
        $sqlStructures = [];

        if (isset($this->structure['where'])) {
            $conditions = '';
            $afterNested = false;
            foreach ($this->structure['where'] as $index => list($type, $structure)) {
                switch ($type) {
                    case 'condition':
                        $condition = sprintf("%s %s %s", $structure['body']['column'], $structure['body']['operator'],
                            $structure['body']['value']);
                        if ($index != 0 && !$afterNested) {
                            $conditions .= sprintf(" %s %s", strtoupper($structure['logical']), $condition);
                        } else {
                            $conditions .= $condition;
                            $afterNested = false;
                        }
                        break;
                    case 'nested-open':
                        $afterNested = true;
                        $conditions .= ' ' . strtoupper($structure['logical']) . ' (';
                        break;
                    case 'nested-close':
                        $afterNested = false;
                        $conditions .= ')';
                        break;
                }
            }

            $sqlStructures['where'] = $conditions;
        }

        return $sqlStructures;
    }

    public function getBindings()
    {
        return $this->bindings;
    }
}