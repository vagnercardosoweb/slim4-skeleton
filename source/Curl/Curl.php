<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
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
    protected string $url = '';

    /**
     * @var string
     */
    protected string $method = 'GET';

    /**
     * @var string
     */
    protected string $body = '';

    /**
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * @var array<int, mixed>
     */
    protected array $options = [];

    /**
     * Curl constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \Exception(
                "Please, install curl extension.\n".
                'https://goo.gl/yTAeZh'
            );
        }
    }

    /**
     * @param string $url
     *
     * @return \Core\Curl\Curl
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

    /**
     * @param array<string, mixed> $params
     *
     * @return $this
     */
    public function addQueryParams(array $params): Curl
    {
        $this->url .= '?'.Common::httpBuildQuery($params);

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function addBody(array $data): Curl
    {
        $this->body = json_encode($data, JSON_PRETTY_PRINT);
        $this->addHeader('Content-Type', 'application/json');
        $this->addHeader('Content-Length', mb_strlen($this->body));

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
     * @return \Core\Curl\Response
     */
    public function send(): Response
    {
        $curl = curl_init($this->url);
        $this->method = strtoupper($this->method);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);

        if (!empty($this->headers)) {
            curl_setopt($curl, CURLOPT_HEADER, $this->parseHeaders());
        }

        if ('GET' !== $this->method) {
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

        $this->clear();

        return new Response(
            $response,
            $httpInfo,
            $error
        );
    }

    /**
     * @return array<string>
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }

        return $headers;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Curl
     */
    public function addHeader(string $key, string $value): Curl
    {
        $this->headers[trim($key)] = trim($value);

        return $this;
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

    /**
     * @return void
     */
    private function clear(): void
    {
        $this->url = '';
        $this->method = 'GET';
        $this->body = '';
        $this->headers = [];
        $this->options = [];
    }
}
