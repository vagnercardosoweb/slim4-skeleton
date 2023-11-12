<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Slim\Exception\HttpSpecializedException;

class HttpUnprocessableEntityException extends HttpSpecializedException
{
    protected $code = StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY;
    protected string $title = '422 Unprocessable Entity';
    protected string $description = 'The request was well-formed but was unable to be followed due to semantic errors.';
    protected $message = 'Unprocessable Entity';
}
