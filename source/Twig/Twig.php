<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 16/03/2021 Vagner Cardoso
 */

namespace Core\Twig;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class View.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Twig
{
    /**
     * @var \Twig\Loader\FilesystemLoader
     */
    protected FilesystemLoader $loader;

    /**
     * @var \Twig\Environment
     */
    protected Environment $environment;

    /**
     * @param string|array $path
     * @param array        $options
     *
     * @throws \Exception
     */
    public function __construct(array | string $path, array $options = [])
    {
        $this->loader = $this->createLoader($path);
        $this->environment = new Environment($this->loader, $options);
    }

    /**
     * @param ResponseInterface $response
     * @param string            $template
     * @param array             $context
     * @param int               $status
     *
     * @return ResponseInterface
     */
    public function render(
        ResponseInterface $response,
        string $template,
        array $context = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        // $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write($this->fetch($template, $context));

        return $response->withStatus($status);
    }

    /**
     * @param string $template
     * @param array  $context
     *
     * @return string
     */
    public function fetch(string $template, array $context = []): string
    {
        $removedExtension = $this->removeExtension($template);

        if ($this->exists("{$removedExtension}/index.twig")) {
            $template = sprintf('%s/index', $removedExtension);
        }

        $template = $this->normalizeExtension($template);

        return $this->environment->render($template, $context);
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function exists(string $template): bool
    {
        return $this->loader->exists($this->normalizeExtension($template));
    }

    /**
     * @param \Twig\Extension\ExtensionInterface $extension
     *
     * @return $this
     */
    public function addExtension(ExtensionInterface $extension): Twig
    {
        $this->environment->addExtension($extension);

        return $this;
    }

    /**
     * @param \Twig\RuntimeLoader\RuntimeLoaderInterface $runtimeLoader
     *
     * @return $this
     */
    public function addRuntimeLoader(RuntimeLoaderInterface $runtimeLoader): Twig
    {
        $this->environment->addRuntimeLoader($runtimeLoader);

        return $this;
    }

    /**
     * @param string          $name
     * @param callable|string $callable
     * @param array           $options
     *
     * @return $this
     */
    public function addFunction(string $name, callable | string $callable, array $options = ['is_safe' => ['all']]): Twig
    {
        $this->environment->addFunction(new TwigFunction($name, $callable, $options));

        return $this;
    }

    /**
     * @param string          $name
     * @param callable|string $callable
     * @param array           $options
     *
     * @return $this
     */
    public function addFilter(string $name, callable | string $callable, array $options = ['is_safe' => ['all']]): Twig
    {
        $this->environment->addFilter(new TwigFilter($name, $callable, $options));

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addGlobal(string $name, mixed $value): Twig
    {
        $this->environment->addGlobal($name, $value);

        return $this;
    }

    /**
     * @return \Twig\Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return \Twig\Loader\FilesystemLoader
     */
    public function getLoader(): FilesystemLoader
    {
        return $this->loader;
    }

    /**
     * @param string      $path
     * @param string|null $namespace
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addPath(string $path, ?string $namespace = null): Twig
    {
        $namespace = $namespace ?? FilesystemLoader::MAIN_NAMESPACE;

        $this->loader->addPath($path, $namespace);

        return $this;
    }

    /**
     * @param string $template
     *
     * @return string
     */
    private function normalizeExtension(string $template): string
    {
        $template = $this->removeExtension($template);
        $template = str_replace('.', '/', $template);

        return sprintf('%s.twig', $template);
    }

    /**
     * @param string $template
     *
     * @return string
     */
    private function removeExtension(string $template): string
    {
        return preg_replace('/\.twig$/', '', $template);
    }

    /**
     * @param string|array $path
     *
     * @throws \Exception
     *
     * @return \Twig\Loader\FilesystemLoader
     */
    private function createLoader(array | string $path): FilesystemLoader
    {
        $paths = is_string($path) ? [$path] : $path;
        $loader = new FilesystemLoader();

        foreach ($paths as $namespace => $location) {
            if (!is_string($namespace) || empty($namespace)) {
                $namespace = FilesystemLoader::MAIN_NAMESPACE;
            }

            $loader->addPath($location, $namespace);
        }

        return $loader;
    }
}
