<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

namespace App\Modules\Api\Controllers;

use Core\Controller;
use Core\Support\Path;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SwaggerController.
 */
class SwaggerController extends Controller
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $docs = file_get_contents(Path::resources('/swagger/docs.json'));
        $docs = json_decode($docs);

        return $this->withTwig(
            '@swagger',
            compact('docs')
        );
    }
}
