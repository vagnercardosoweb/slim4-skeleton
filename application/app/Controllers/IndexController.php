<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 02/07/2020 Vagner Cardoso
 */

namespace App\Controllers;

use Core\App;
use Psr\Http\Message\ResponseInterface;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends BaseController
{
    public function __invoke($request, ResponseInterface $response): ResponseInterface
    {
        return $this->withJson($response, [
            'version' => App::VERSION,
            'datetime' => new \DateTimeImmutable(),
        ]);
    }
}
