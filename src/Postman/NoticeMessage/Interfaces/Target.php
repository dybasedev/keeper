<?php
/**
 * Target.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Postman\NoticeMessage\Interfaces;


interface Target
{
    /**
     * 获取指定字段数据
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getFieldValue(string $field);
}