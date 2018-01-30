<?php
/**
 * ConfigurationLoader.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use FilesystemIterator;
use Illuminate\Config\Repository;
use SplFileInfo;

class ConfigurationLoader extends Repository
{
    protected $configurationPath;

    /**
     * ConfigurationLoader constructor.
     *
     * @param string $configurationPath
     */
    public function __construct(string $configurationPath = null)
    {
        $this->configurationPath = $configurationPath;
        $configurations          = $this->loadConfigurationFile($configurationPath);

        parent::__construct($configurations);
    }

    protected function getConfigurationFile($file)
    {
        if (is_string($file)) {
            $file = new SplFileInfo($file);
        }

        if ($file->isFile() && $file->isReadable() && $file->getExtension() == 'php') {
            $pathinfo = pathinfo($file->getPathname());

            return [$pathinfo['filename'] => require $file->getRealPath()];
        }

        return [];
    }

    public function reload()
    {
        $this->items = $this->loadConfigurationFile($this->configurationPath);
    }

    /**
     * @param string $configurationPath
     *
     * @return array
     */
    protected function loadConfigurationFile(string $configurationPath): array
    {
        $configurations = [];

        if ($configurationPath && is_dir($configurationPath)) {
            if (is_dir($configurationPath)) {
                $configurationFiles = new FilesystemIterator($configurationPath, FilesystemIterator::SKIP_DOTS);

                /** @var SplFileInfo $configurationFiles */
                foreach ($configurationFiles as $file) {
                    $configurations += $this->getConfigurationFile($file);
                }
            } else {
                if (is_file($configurationPath)) {
                    $configurations += $this->getConfigurationFile($configurationPath);
                }
            }
        }

        return $configurations;
    }
}