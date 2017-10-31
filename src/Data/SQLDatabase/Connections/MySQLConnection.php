<?php
/**
 * MySQLConnection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Connections;


use Dybasedev\Keeper\Data\SQLDatabase\Connection;
use PDO;

class MySQLConnection extends Connection
{
    public function getPdoDsn(): string
    {
        return sprintf("mysql:host=%s;dbname=%s;port=%s;charset=%s",
            $this->options['host'], $this->options['database'],
            $this->options['port'], $this->options['charset']);
    }

    public function createPdoInstance(): PDO
    {
        return new PDO($this->getPdoDsn(), $this->options['username'], $this->options['password']);
    }


}