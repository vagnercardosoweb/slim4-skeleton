<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace App\Controllers;

use Core\Controller;
use Core\Support\Common;
use Core\Support\Path;
use Psr\Http\Message\ResponseInterface;

class DocController extends Controller
{
    public function index(): ResponseInterface
    {
        $docs = file_get_contents(Path::resources('/swagger/docs.json'));
        $docs = Common::parseJson($docs);

        return $this->withTwig(
            '@swagger',
            compact('docs')
        );
    }
}
