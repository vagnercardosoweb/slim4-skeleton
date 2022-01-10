<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Tests\Traits;

use Core\Bootstrap;
use Core\Support\Path;
use DI\Container;
use Slim\App;

trait AppTestTrait
{
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use AssertTestTrait;
    use MockTestTrait;

    protected App $app;

    protected ?Container $container;

    protected function setUp(): void
    {
        require_once __DIR__.'/../../../public_html/index.php';

        $this->app = Bootstrap::getApp();
        $this->container = $this->app->getContainer();

        if (is_null($this->container)) {
            throw new \UnexpectedValueException(
                'Container is not an instance DI\\Container.'
            );
        }

        if (method_exists($this, 'setUpDatabase')) {
            if (file_exists($path = Path::resources('/database/schema.sql'))) {
                $this->setUpDatabase($path, false);
            } else {
                $this->setUpDatabase(null, true);
            }
        }
    }
}
