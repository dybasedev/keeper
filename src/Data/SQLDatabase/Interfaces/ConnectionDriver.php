<?php
/**
 * ConnectionDriver.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Interfaces;


use PDO;

interface ConnectionDriver
{
    /**
     * @param array $options
     *
     * @return $this
     */
    public function setConnectOptions(array $options);

    /**
     * @return string
     */
    public function getPdoDsn(): string;

    /**
     * @return PDO
     */
    public function createPdoInstance(): PDO;
}