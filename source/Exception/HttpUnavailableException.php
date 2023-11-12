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

class HttpUnavailableException extends HttpSpecializedException
{
    protected $code = StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE;
    protected string $title = '503 Service Unavailable';
    protected string $description = 'We are currently undergoing maintenance. Come back later.';
    protected $message = 'Service Unavailable.';
}
