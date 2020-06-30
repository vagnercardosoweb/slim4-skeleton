<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 30/06/2020 Vagner Cardoso
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;

/**
 * Class BaseController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class BaseController
{
    public function withJson(
        ResponseInterface $response,
        $data = [],
        int $options = JSON_PRETTY_PRINT
    ): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, $options));

        return $response;
    }
}
