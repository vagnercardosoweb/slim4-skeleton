<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
use Core\Support\Env;
use Core\Twig\Twig;
use Core\Twig\TwigExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Twig\Extension\DebugExtension;

class TwigProvider implements ServiceProvider
{
    public function __invoke(ContainerInterface $container): Twig
    {
        Facade::setAliases(['Twig' => Twig::class]);

        $config = Config::get('twig');
        $twig = new Twig($config['templates'], $config['options']);

        $filters = $config['filters'] ?? [];
        $functions = $config['functions'] ?? [];
        $globals = $config['globals'] ?? [];

        $app = $container->get(App::class);
        $routeParser = $container->get(RouteParserInterface::class);
        $serverRequest = $container->get(ServerRequestInterface::class);
        $resolveCallable = fn ($value) => is_string($value) && class_exists($value) ? $container->get($value) : $value;

        $globals['requestQueryParams'] = $serverRequest->getQueryParams();
        $globals['requestServerParams'] = $serverRequest->getServerParams();

        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new TwigExtension(
            routeParser: $routeParser,
            uri: $serverRequest->getUri(),
            filters: $filters,
            functions: $functions,
            basePath: $app->getBasePath()
        ));

        foreach ($globals as $name => $value) {
            $twig->addGlobal($name, $resolveCallable($value));
        }

        if ('development' === Env::get('APP_ENV')) {
            $twig->getEnvironment()->enableDebug();
        }

        return $twig;
    }
}
