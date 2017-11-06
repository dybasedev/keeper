<?php
/**
 * ContextContainer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Illuminate\Container\Container;

class ContextContainer extends Container
{
    /**
     * @var string
     */
    public $basePath;

    /**
     * @var
     */
    public $applicationPath;

    /**
     * ContextContainer constructor.
     *
     * @param $basePath
     */
    public function __construct(string $basePath)
    {
        $this->setBasePath($basePath);
    }

    public function setBasePath(string $basePath)
    {
        $this['path.base'] = $this->basePath = realpath($basePath);

        return $this;
    }

    public function setApplicationPath(string $appPath)
    {
        $this['path.app'] = $this->applicationPath = realpath($appPath);

        return $this;
    }
}