<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

namespace Core\Exception;

use Fig\Http\Message\StatusCodeInterface;

/**
 * Class InvalidParamException.
 */
class InvalidParamException extends \InvalidArgumentException
{
    /**
     * InvalidParamException constructor.
     *
     * @param string $param
     */
    public function __construct(string $param)
    {
        parent::__construct(
            "Invalid parameter on request: {$param}",
            StatusCodeInterface::STATUS_BAD_REQUEST,
            $this->getPrevious()
        );
    }
}
