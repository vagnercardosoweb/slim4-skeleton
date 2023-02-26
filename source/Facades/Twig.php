<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Core\Facades;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * Class Twig.
 *
 * @method static \Core\Twig\Twig   addPath(string $path, ?string $namespace)
 * @method static ResponseInterface render(ResponseInterface $response, string $template, array $context = [], int $status = StatusCodeInterface::STATUS_OK)
 * @method static string            fetch(string $template, array $context = [])
 * @method static bool              exists(string $template)
 * @method static \Core\Twig\Twig   addExtension(ExtensionInterface $extension)
 * @method static \Core\Twig\Twig   addRuntimeLoader(RuntimeLoaderInterface $runtimeLoader)
 * @method static \Core\Twig\Twig   addFunction(string $name, callable | string $callable, array $options = ['is_safe' => ['all']])
 * @method static \Core\Twig\Twig   addFilter(string $name, callable | string $callable, array $options = ['is_safe' => ['all']])
 * @method static \Core\Twig\Twig   addGlobal(string $name, mixed $value)
 * @method static Environment getEnvironment()
 * @method static Environment getLoader()
 */
class Twig extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Twig\Twig::class;
    }
}
