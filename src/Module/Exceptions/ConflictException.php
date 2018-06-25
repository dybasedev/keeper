<?php
/**
 * ConflictException.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Module\Exceptions;


use Throwable;

class ConflictException extends ContainerException
{
    public $conflictObject;

    /**
     * ConflictException constructor.
     *
     * @param                 $conflictObject
     * @param Throwable|null $previous
     */
    public function __construct($conflictObject, Throwable $previous = null)
    {
        $this->conflictObject = $conflictObject;

        parent::__construct("Conflict", 0, $previous);
    }
}