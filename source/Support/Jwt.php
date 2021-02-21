<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 21/02/2021 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Jwt.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Jwt
{
    /**
     * @var string
     */
    protected string $key;

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = (string)$key;

        if (empty($this->key)) {
            throw new \InvalidArgumentException('Jwt empty key.');
        }
    }

    /**
     * @param array $payload
     * @param array $header
     *
     * @return string
     */
    public function encode(array $payload, array $header = []): string
    {
        $array = [];
        $header = array_merge($header, ['typ' => 'JWT', 'alg' => 'HS512']);
        $array[] = base64_encode(json_encode($header));
        $array[] = base64_encode(json_encode($payload));
        $signature = $this->signature(implode('.', $array));
        $array[] = base64_encode($signature);

        return implode('.', $array);
    }

    /**
     * @param string $token
     *
     * @throws \Exception
     *
     * @return array
     */
    public function decode(string $token): array
    {
        $split = explode('.', $token);

        if (3 != count($split)) {
            throw new \InvalidArgumentException('The token does not contain a valid format.');
        }

        list($header64, $payload64, $signature) = $split;

        if (!$header = json_decode(base64_decode($header64), true, 512, JSON_BIGINT_AS_STRING)) {
            throw new \UnexpectedValueException('Invalid header encoding.');
        }

        if (!$payload = json_decode(base64_decode($payload64), true, 512, JSON_BIGINT_AS_STRING)) {
            throw new \UnexpectedValueException('Invalid payload encoding.');
        }

        if (!$signature = base64_decode($signature)) {
            throw new \UnexpectedValueException('Invalid signature encoding.');
        }

        if (empty($header['alg'])) {
            throw new \UnexpectedValueException('Empty algorithm.');
        }

        if ('HS512' !== $header['alg']) {
            throw new \UnexpectedValueException("Algorithm {$header['alg']} is not supported.");
        }

        if (!$this->validate("{$header64}.{$payload64}", $signature)) {
            throw new \Exception('Signature verification failed.');
        }

        return array_merge($header, $payload);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function signature(string $value): string
    {
        return hash_hmac('sha512', $value, $this->key, true);
    }

    /**
     * @param string $value
     * @param string $signature
     *
     * @return bool
     */
    private function validate(string $value, string $signature): bool
    {
        $hashed = hash_hmac('sha512', $value, $this->key, true);

        if (function_exists('hash_equals')) {
            return hash_equals($signature, $hashed);
        }

        return $signature === $hashed;
    }
}
