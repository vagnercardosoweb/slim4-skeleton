<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 30/01/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Core\App;

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
            'version' => App::VERSION,
            'datetime' => new \DateTimeImmutable(),
        ];
    }
}
