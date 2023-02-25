<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Tests\Traits;

use Core\Bootstrap;
use Core\Support\Path;
use Psr\Container\ContainerInterface;
use Slim\App;

trait AppTestTrait
{
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use AssertTestTrait;
    use MockTestTrait;

    protected App $app;

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        require_once __DIR__.'/../../../public_html/index.php';

        $this->app = Bootstrap::getApp();
        $container = $this->app->getContainer();

        if (!$container instanceof ContainerInterface) {
            throw new \UnexpectedValueException(
                'Container is not an instance Psr\Container\ContainerInterface.'
            );
        }

        $this->container = $container;

        if (method_exists($this, 'setUpDatabase')) {
            $path = Path::resources('/database/schema.sql');
            $this->setUpDatabase($path, true);
        }
    }
}
