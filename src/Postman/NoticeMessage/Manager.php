<?php
/**
 * Manager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Postman\NoticeMessage;


use Dybasedev\Keeper\Postman\NoticeMessage\Interfaces\Transactor;
use Generator;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;

class Manager
{
    protected $registeredTransactor = [];

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Manager constructor.
     *
     * @param Container  $container
     * @param Repository $config
     */
    public function __construct(Container $container, Repository $config)
    {
        $this->container = $container;
        $this->config    = $config;
    }

    /**
     * @param string $transactorName
     *
     * @return Transactor
     */
    public function createMessageNoticeProcess(string $transactorName): Transactor
    {
        if (!isset($this->registeredTransactor[$transactorName])) {
            throw new \InvalidArgumentException();
        }

        if ($this->container->bound($this->registeredTransactor[$transactorName])) {
            return $this->container->make($this->registeredTransactor[$transactorName]);
        } else {
            $className = $this->registeredTransactor[$transactorName];

            return new $className($this);
        }
    }

    /**
     * @param string|string[]|Generator $transactorsName
     *
     * @return MultipartMessagePusher
     */
    public function through($transactorsName)
    {
        return (new MultipartMessagePusher())->setTransactorInstances((function ($transactorsName) {
            $transactorsName = !($transactorsName instanceof Generator) && !is_array($transactorsName)
                ? (array)$transactorsName
                : $transactorsName;

            foreach ($transactorsName as $transactorName) {
                yield $this->createMessageNoticeProcess($transactorName);
            }
        })($transactorsName));
    }
}