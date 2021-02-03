<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/02/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Core\Bootstrap;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Http\Message\ResponseInterface;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends BaseController
{
    #[ArrayShape([
        'path' => 'string',
        'version' => 'string',
        'datetime' => '\\DateTimeImmutable',
    ])]
    public function index(): array
    {
        return [
            'path' => $this->request->getUri()->getPath(),
            'version' => Bootstrap::VERSION,
            'datetime' => new \DateTimeImmutable(),
        ];
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function template(): ResponseInterface
    {
        return $this->withTwig('index', [
            'version' => Bootstrap::VERSION,
        ]);
    }
}
