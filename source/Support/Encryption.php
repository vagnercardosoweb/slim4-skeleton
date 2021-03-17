<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 17/03/2021 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Encryption.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Encryption
{
    /**
     * @param string $key
     * @param string $cipher
     */
    public function __construct(
        protected string $key,
        protected string $cipher = 'AES-256-CBC'
    ) {
        if (!static::supported($key, $cipher)) {
            throw new \RuntimeException(
                'The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.'
            );
        }
    }

    /**
     * @param string $key
     *
     * @return Encryption
     */
    public function setKey(string $key): Encryption
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $cipher
     *
     * @return Encryption
     */
    public function setCipher(string $cipher): Encryption
    {
        $this->cipher = $cipher;

        return $this;
    }

    /**
     * @return string
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }

    /**
     * @param string $key
     * @param string $cipher
     *
     * @return bool
     */
    public static function supported(string $key, string $cipher): bool
    {
        $length = mb_strlen($key, '8bit');

        return ('AES-128-CBC' === $cipher && 16 === $length)
            || ('AES-256-CBC' === $cipher && 32 === $length);
    }

    /**
     * @param string $cipher
     *
     * @return string
     */
    public static function generateKey(string $cipher): string
    {
        return random_bytes('AES-128-CBC' === $cipher ? 16 : 32);
    }

    /**
     * @param mixed $payload
     * @param bool  $serialize
     *
     * @throws \Exception
     *
     * @return string
     */
    public function encrypt(mixed $payload, bool $serialize = true): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));

        $payload = \openssl_encrypt(
            $serialize ? serialize($payload) : $payload,
            $this->cipher, $this->key, 0, $iv
        );

        if (false === $payload) {
            throw new \RuntimeException('Could not encrypt the data.');
        }

        $mac = $this->hash($iv = base64_encode($iv), $payload);
        $json = json_encode(compact('iv', 'payload', 'mac'), JSON_UNESCAPED_SLASHES);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * @param string $encrypted
     * @param bool   $unserialize
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function decrypt(string $encrypted, bool $unserialize = true): mixed
    {
        $encrypted = $this->getJsonPayload($encrypted);
        $iv = base64_decode($encrypted['iv']);

        $decrypted = \openssl_decrypt(
            $encrypted['payload'],
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        if (false === $decrypted) {
            throw new \RuntimeException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * @param string $iv
     * @param mixed  $payload
     *
     * @return string
     */
    protected function hash(string $iv, string $payload): string
    {
        return hash_hmac('sha256', $iv.$payload, $this->key);
    }

    /**
     * @param string $payload
     *
     * @return array|null
     */
    protected function getJsonPayload(string $payload): ?array
    {
        $payload = json_decode(base64_decode($payload), true);

        if (!$this->validPayload($payload)) {
            throw new \UnexpectedValueException('The payload is invalid.');
        }

        if (!$this->validMac($payload)) {
            throw new \UnexpectedValueException('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * @param array|null $payload
     *
     * @return bool
     */
    protected function validPayload(?array $payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['payload'], $payload['mac'])
            && strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }

    /**
     * @param array $payload
     *
     * @return bool
     */
    protected function validMac(array $payload): bool
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['payload']),
            $payload['mac']
        );
    }
}
