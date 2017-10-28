<?php
/**
 * PrepareQuerySourceManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Closure;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class PrepareQuerySourceManager
{
    protected $prepared = [];

    /**
     * @var PDO
     */
    protected $pdoInstance;

    /**
     * PrepareQuerySourceManager constructor.
     *
     * @param PDO $pdoInstance
     */
    public function __construct(PDO $pdoInstance)
    {
        $this->pdoInstance = $pdoInstance;
    }


    /**
     * @param string       $method
     * @param string       $key
     * @param string       $statement
     * @param null|Closure $binders
     * @param bool         $conflictWarning
     *
     * @return $this
     */
    public function registerStatement(
        string $method,
        string $key,
        string $statement,
        $binders = null,
        $conflictWarning = true
    ) {
        if (!in_array($method, ['execute', 'query'])) {
            throw new InvalidArgumentException('Not support method.');
        }

        if ($conflictWarning && isset($this->prepared[$method][$key])) {
            throw new RuntimeException("Source key conflict: {$key}");
        }

        $prepare = $this->getPdoInstance()->prepare($statement);

        $this->prepared[$method][$key] = [$prepare, $binders];

        return $this;
    }

    /**
     * 注册一个执行语句
     *
     * @param string       $key
     * @param string       $statement
     * @param null|Closure $binders
     * @param bool         $conflictWarning
     *
     * @return PrepareQuerySourceManager
     */
    public function registerExecuteStatement(string $key, string $statement, $binders = null, $conflictWarning = true)
    {
        return $this->registerStatement('execute', $key, $statement, $binders, $conflictWarning);
    }

    /**
     * 注册一个查询语句
     *
     * @param string       $key
     * @param string       $statement
     * @param null|Closure $binders
     * @param bool         $conflictWarning
     *
     * @return PrepareQuerySourceManager
     */
    public function registerQueryStatement(string $key, string $statement, $binders = null, $conflictWarning = true)
    {
        return $this->registerStatement('query', $key, $statement, $binders, $conflictWarning);
    }

    /**
     * @return PDO
     */
    private function getPdoInstance()
    {
        return $this->pdoInstance;
    }
}