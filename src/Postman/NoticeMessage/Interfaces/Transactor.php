<?php
/**
 * Transactor.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Postman\NoticeMessage\Interfaces;

use Generator;

interface Transactor
{
    public function setTarget(Target $target);

    public function setTargets(Generator $targetGenerator);

    public function pushCallback($callback);

    public function send($data);
}