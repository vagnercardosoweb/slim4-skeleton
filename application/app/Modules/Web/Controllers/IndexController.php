<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace App\Modules\Web\Controllers;

use Core\Controller;
use Psr\Http\Message\ResponseInterface;

class IndexController extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->withTwig('home/index');
    }
}
