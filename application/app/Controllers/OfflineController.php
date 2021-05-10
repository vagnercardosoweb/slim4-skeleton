<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;

/**
 * Class OfflineController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class OfflineController extends BaseController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->withTwig('offline');
    }
}
