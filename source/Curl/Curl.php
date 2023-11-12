<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core\Curl;

use Core\Support\Common;

/**
 * Class Curl.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Curl
{
    /**
     * @var string
     */
    private string $url = '';

    /**
     * @var string
     */
    private string $method = 'GET';

    /**
     * @var string
     */
    private string $body = '';

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * @var array<string, string>
     */
    private array $queryParams = [];

    /**
     * @var array<int, mixed>
     */
    private array $options = [];

    /**
     * @var bool
     */
    private bool $verifyPeer = false;

    /**
     * @param string $url
     *
     * @return Curl
     */
    public static function get(string $url): Curl
    {
        $http = new self();
        $http->url = $url;
        $http->method = 'GET';

        return $http;
    }

    public static function post(string $url): Curl
    {
        $http = new self();
        $http->url = $url;
        $http->method = 'POST';

        return $http;
    }

    public static function put(string $url): Curl
    {
        $http = new self();
        $http->url = $url;
        $http->method = 'PUT';

        return $http;
    }

    public static function delete(string $url): Curl
    {
        $http = new self();
        $http->url = $url;
        $http->method = 'DELETE';

        return $http;
    }

    public static function patch(string $url): Curl
    {
        $http = new self();
        $http->url = $url;
        $http->method = 'PATCH';

        return $http;
    }

    public function addQueryParam(string $name, int|string $value): Curl
    {
        $this->queryParams[trim($name)] = filter_var($value);

        return $this;
    }

    public function verifyPeer(): Curl
    {
        $this->verifyPeer = true;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function addBody(array $data): Curl
    {
        $this->body = json_encode($data);
        $this->addHeader('Content-Type', 'application/json; charset=utf-8');
        $this->addHeader('Content-Length', mb_strlen($this->body));

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Curl
     */
    public function addHeader(string $key, string $value): Curl
    {
        $this->headers[trim($key)] = filter_var(trim($value));

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function addFormUrlencoded(array $data): Curl
    {
        $this->body = Common::httpBuildQuery($data);
        $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->addHeader('Content-Length', mb_strlen($this->body));

        return $this;
    }

    /**
     * @return Response
     */
    public function send(): Response
    {
        try {
            $curl = curl_init($this->makeUrl());
            $this->method = strtoupper($this->method);

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

            if ($this->verifyPeer) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            }

            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);

            if (!empty($this->headers)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $this->makeHeaders());
            }

            if ('GET' !== $this->method && !empty($this->body)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $this->body);
            }

            if ('POST' === $this->method) {
                curl_setopt($curl, CURLOPT_POST, true);
            }

            if ('PUT' === $this->method) {
                curl_setopt($curl, CURLOPT_PUT, true);
            }

            foreach ($this->options as $optKey => $optValue) {
                curl_setopt($curl, $optKey, $optValue);
            }

            $response = curl_exec($curl);
            $httpInfo = curl_getinfo($curl);
            $error = curl_error($curl);
            curl_close($curl);

            return new Response(
                $response,
                $httpInfo,
                $error
            );
        } finally {
            $this->clear();
        }
    }

    private function makeUrl(): string
    {
        if (empty($this->url)) {
            throw new \InvalidArgumentException('Url is empty');
        }
        $queryParams = '';
        if (!empty($this->queryParams)) {
            $queryParams = '?'.Common::httpBuildQuery($this->queryParams);
        }

        return $this->url.$queryParams;
    }

    /**
     * @return array<string>
     */
    private function makeHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }

        return $headers;
    }

    /**
     * @return void
     */
    private function clear(): void
    {
        $this->url = '';
        $this->method = 'GET';
        $this->body = '';
        $this->headers = [];
        $this->queryParams = [];
        $this->options = [];
        $this->verifyPeer = false;
    }

    /**
     * @param int   $option
     * @param mixed $value
     *
     * @return Curl
     */
    public function addOption(int $option, mixed $value): Curl
    {
        $this->options[$option] = $value;

        return $this;
    }
}
