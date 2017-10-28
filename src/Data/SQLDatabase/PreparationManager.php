<?php
/**
 * PreparationManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;

use PDO;
use RuntimeException;

class PreparationManager
{
    /**
     * @var array
     */
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
     * @param string                $key
     * @param ExecutablePreparation $preparation
     * @param bool                  $conflictWarning
     *
     * @return $this
     */
    public function registerStatement(
        string $key,
        ExecutablePreparation $preparation,
        $conflictWarning = true
    ) {
        if ($conflictWarning && isset($this->prepared[$key])) {
            throw new RuntimeException("Source key conflict: {$key}");
        }

        $pdoStatement = $this->getPdoInstance()->prepare($preparation->statement);

        $this->prepared[$key] = [$pdoStatement, $preparation];

        return $this;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getPreparation(string $key)
    {
        return $this->prepared[$key] ?? null;
    }

    /**
     * @return PDO
     */
    private function getPdoInstance()
    {
        return $this->pdoInstance;
    }
}