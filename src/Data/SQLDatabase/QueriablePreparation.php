<?php
/**
 * QueriablePreparation.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


class QueriablePreparation extends ExecutablePreparation
{
    /**
     * @var callable
     */
    protected $fetcher;

    /**
     * @param callable $fetcher
     *
     * @return QueriablePreparation
     */
    public function setFetcher(callable $fetcher): QueriablePreparation
    {
        $this->fetcher = $fetcher;

        return $this;
    }
}