<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Password.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Password
{
    /**
     * AbstractHash constructor.
     *
     * @param bool $verifyAlgorithm
     */
    public function __construct(protected bool $verifyAlgorithm = false) {}

    /**
     * @param string $plainValue
     * @param string $hashedValue
     *
     * @return bool
     */
    public function verify(string $plainValue, string $hashedValue): bool
    {
        if ($this->verifyAlgorithm) {
            $hashedInfo = $this->info($hashedValue);
            $algoName = $hashedInfo['algoName'] ?? null;

            if ($algoName !== $this->algorithm()) {
                throw new \RuntimeException("This password does not use the {$algoName} algorithm.");
            }
        }

        if (0 === strlen($hashedValue)) {
            return false;
        }

        return password_verify($plainValue, $hashedValue);
    }

    /**
     * @param string $hashedValue
     *
     * @return array<string, mixed>
     */
    public function info(string $hashedValue): array
    {
        return password_get_info($hashedValue);
    }

    /**
     * @return int|string
     */
    abstract protected function algorithm(): int|string;

    /**
     * @param string|int $password
     * @param array      $options
     *
     * @return string
     */
    public function make(int|string $password, array $options = []): string
    {
        $options = $this->getOptions($options);

        return password_hash($password, $this->algorithm(), $options);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    abstract protected function getOptions(array $options): array;

    /**
     * @param string $hash
     * @param array  $options
     *
     * @return bool
     */
    public function needsRehash(string $hash, array $options = []): bool
    {
        $options = $this->getOptions($options);

        return password_needs_rehash($hash, $this->algorithm(), $options);
    }
}
