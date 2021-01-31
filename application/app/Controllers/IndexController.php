<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 31/01/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Core\Bootstrap;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends BaseController
{
    /**
     * @throws \Exception
     *
     * @return array
     */
    public function index(): array
    {
        return [
            'path' => $this->request->getUri()->getPath(),
            'version' => Bootstrap::VERSION,
            'datetime' => new \DateTimeImmutable(),
        ];
    }
}
