<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

namespace Core\Curl;

use Core\Support\Common;

/**
 * Class Response.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Response
{
    /**
     * Response constructor.
     *
     * @param string $body
     * @param array  $info
     * @param mixed  $error
     */
    public function __construct(
        protected string $body,
        protected array $info,
        protected mixed $error
    ) {
    }

    /**
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->info['http_code'] ?? 500;
    }

    /**
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * @return object|null
     */
    public function getError(): ?object
    {
        if (empty($this->error) && (!$this->isClientError() && !$this->isServerError())) {
            return null;
        }

        $message = $this->isServerError() ? 'Server error.' : 'Client error.';

        $object = new \stdClass();
        $object->error = true;
        $object->status = $this->getStatusCode();
        $object->message = $this->error ?? $message;

        return $object;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return \SimpleXMLElement|null
     */
    public function toXml(): ?\SimpleXMLElement
    {
        return Common::parseXml($this->body);
    }

    /**
     * @return object|null
     */
    public function toJson(): ?object
    {
        $this->parseXmlToString();

        return Common::parseJson($this->body);
    }

    /**
     * @return array|null
     */
    public function toArray(): ?array
    {
        $this->parseXmlToString();

        return Common::parseJson($this->body, true);
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @return void
     */
    protected function parseXmlToString(): void
    {
        if ($toXml = $this->toXml()) {
            $this->body = json_encode($toXml);
        }
    }
}
