<?php
/**
 * Pipeline.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing;


use Illuminate\Pipeline\Pipeline as LaravelPipeline;

class Pipeline extends LaravelPipeline
{
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {

                /** @var Middleware $pipe */
                $pipe = $this->getContainer()->make($pipe);

                return $pipe->handle($passable, $stack);
            };
        };
    }
}