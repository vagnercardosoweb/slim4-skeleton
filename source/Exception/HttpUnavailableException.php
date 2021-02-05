<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/02/2021 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Slim\Exception\HttpSpecializedException;

/**
 * Class HttpUnavailableException.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class HttpUnavailableException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE;

    /**
     * @var string
     */
    protected $message = 'Service Unavailable.';

    /**
     * @var string
     */
    protected $title = '503 Service Unavailable';

    /**
     * @var string
     */
    protected $description = 'We are currently undergoing maintenance. Come back later.';
}
