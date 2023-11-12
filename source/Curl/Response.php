<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core\Curl;

use Core\Support\Common;

readonly class Response
{
    public function __construct(
        private string $body,
        private array  $httpInfo,
        private string $error
    )
    {
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function isClientError(): bool
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    public function getStatusCode(): int
    {
        return $this->httpInfo['http_code'] ?? 500;
    }

    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function toArray(): array
    {
        $body = $this->body;

        if ($toXml = Common::parseXml($this->body)) {
            $body = json_encode($toXml);
        }

        return Common::parseJson($body, true);
    }

    public function getHttpInfo(): array
    {
        return $this->httpInfo;
    }
}
