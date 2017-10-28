<?php
/**
 * MultipartMessagePusher.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Postman\NoticeMessage;

use Closure;
use Dybasedev\Keeper\Postman\NoticeMessage\Interfaces\Target;
use Dybasedev\Keeper\Postman\NoticeMessage\Interfaces\Transactor;
use Generator;

class MultipartMessagePusher implements Transactor
{
    protected $transactors;

    public function setTransactorInstances($transactors)
    {
        $this->transactors = $transactors;

        return $this;
    }

    protected function transactorDump(Closure $callback)
    {
        foreach ($this->transactors as $transactor) {
            $callback($transactor);
        }

        return $this;
    }

    public function setTarget(Target $target)
    {
        return $this->transactorDump(function (Transactor $transactor) use ($target) {
            $transactor->setTarget($target);
        });
    }

    public function setTargets(Generator $targetGenerator)
    {
        return $this->transactorDump(function (Transactor $transactor) use ($targetGenerator) {
            $transactor->setTargets($targetGenerator);
        });
    }

    public function pushCallback($callback)
    {
        return $this->transactorDump(function (Transactor $transactor) use ($callback) {
            $transactor->pushCallback($callback);
        });
    }

    public function send($data)
    {
        return $this->transactorDump(function (Transactor $transactor) use ($data) {
            $transactor->send($data);
        });
    }

}