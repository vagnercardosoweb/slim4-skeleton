<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Tests\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

trait MockTestTrait
{
    /**
     * @param string $class
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mock(string $class): MockObject
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class not found: %s', $class));
        }

        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock()
        ;

        if ($this->container instanceof ContainerInterface && method_exists($this->container, 'set')) {
            $this->container->set($class, $mock);
        }

        return $mock;
    }
}
