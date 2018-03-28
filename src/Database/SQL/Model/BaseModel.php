<?php
/**
 * BaseModel.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\SQL\Model;

abstract class BaseModel
{
    protected $attributes = [];

    protected $attributeSetters = [];

    protected $attributeGetters = [];

    protected $nativeAttributes = [];

    protected $primaryKey = 'id';

    public function __get($name)
    {
        if (isset($this->attributeGetters[$name])) {
            return ($this->attributeGetters[$name])($this->attributes[$name]);
        }
        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        if (isset($this->attributeSetters[$name])) {
            ($this->attributeSetters[$name])($value);
        } else {
            $this->attributes[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    public function getAttributes($native = true)
    {
        if ($native && $this->nativeAttributes) {
            $attributes = [];
            foreach ($this->nativeAttributes as $attribute) {
                $attributes[$attribute] = $this->{$attribute};
            }

            return $this->attributes + $attributes;
        }

        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}