<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core\Twig;

use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteParserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * TwigExtension constructor.
     *
     * @param RouteParserInterface    $routeParser
     * @param UriInterface            $uri
     * @param array<string, callable> $filters
     * @param array<string, callable> $functions
     * @param string|null             $basePath
     */
    public function __construct(
        protected RouteParserInterface $routeParser,
        protected UriInterface $uri,
        protected array $filters = [],
        protected array $functions = [],
        protected ?string $basePath = '',
    ) {
        $this->functions['url_for'] = [$this, 'getUrlFor'];
        $this->functions['full_url_for'] = [$this, 'getFullUrlFor'];
        $this->functions['relative_url_for'] = [$this, 'getRelativeUrlFor'];
        $this->functions['is_current_url'] = [$this, 'isCurrentUrl'];
        $this->functions['current_url'] = [$this, 'getCurrentUrl'];
        $this->functions['get_uri'] = [$this, 'getUri'];
        $this->functions['base_path'] = [$this, 'getBasePath'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'twig';
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        $twigFunctions = [];

        foreach ($this->functions as $name => $callable) {
            $twigFunctions[] = new TwigFunction($name, $callable, ['is_safe' => ['all']]);
        }

        return $twigFunctions;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        $twigFilters = [];

        foreach ($this->filters as $name => $callable) {
            $twigFilters[] = new TwigFilter($name, $callable, ['is_safe' => ['all']]);
        }

        return $twigFilters;
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public function getUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public function getFullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->fullUrlFor($this->uri, $routeName, $data, $queryParams);
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public function getRelativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->relativeUrlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param bool                 $withQueryString
     *
     * @return bool
     */
    public function isCurrentUrl(string $routeName, array $data = [], bool $withQueryString = false): bool
    {
        $currentUrl = $this->getCurrentUrl($withQueryString);
        $result = $this->routeParser->urlFor($routeName, $data);

        return $result === $currentUrl;
    }

    /**
     * @param bool $withQueryString
     *
     * @return string
     */
    public function getCurrentUrl(bool $withQueryString = false): string
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $query = $this->uri->getQuery();

        if ($withQueryString && !empty($query)) {
            $currentUrl .= '?'.$query;
        }

        return $currentUrl;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
