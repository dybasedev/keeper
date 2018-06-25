<?php
/**
 * CannotModifyImmutableBindingException.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Module\Exceptions;


class CannotModifyImmutableBindingException extends ContainerException
{
    public $binding;

    /**
     * CannotModifyImmutableBindingException constructor.
     *
     * @param $binding
     */
    public function __construct($binding)
    {
        $this->binding = $binding;

        parent::__construct('Cannot modify immutable binding');
    }
}