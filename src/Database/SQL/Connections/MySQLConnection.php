<?php
/**
 * MySQLConnection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\SQL\Connections;


use Dybasedev\Keeper\Database\SQL\Connection;
use PDO;

class MySQLConnection extends Connection
{
    /**
     * @return PDO
     */
    protected function createDriverInstance(): PDO
    {
        if (isset($this->options['unix_socket'])) {
            $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s',
                $this->options['unix_socket'], $this->options['database'], $this->options['charset']);
        } else {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->options['host'], $this->options['port'],
                $this->options['database'], $this->options['charset']);
        }


        $pdo = new PDO($dsn, $this->options['username'], $this->options['password']);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($this->options['attributes'])) {
            foreach ($this->options['attributes'] as $attribute => $value) {
                $pdo->setAttribute($attribute, $value);
            }
        }

        return $pdo;
    }

    /**
     * @throws \Throwable
     */
    protected function createSavePoint()
    {
        $this->execute('SAVEPOINT ' . ($this->transactions + 1));
    }

    /**
     * @param $toLevel
     *
     * @throws \Throwable
     */
    protected function rollbackSavePoint($toLevel)
    {
        $this->execute('ROLLBACK TO SAVEPOINT ' . $toLevel);
    }
}