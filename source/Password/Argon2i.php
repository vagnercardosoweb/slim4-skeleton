<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Argon2i.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Argon2i extends Password
{
    /**
     * @return int|string
     */
    public function algorithm(): int|string
    {
        return PASSWORD_ARGON2I;
    }

    /**
     * @param array $options
     *
     * @return array<string, mixed>
     */
    protected function getOptions(array $options = []): array
    {
        return [
            'threads' => $options['threads'] ?? PASSWORD_ARGON2_DEFAULT_THREADS,
            'memory_cost' => $options['memory_cost'] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => $options['time_cost'] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST,
        ];
    }
}
