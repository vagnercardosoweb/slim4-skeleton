<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/02/2021 Vagner Cardoso
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
    public function algorithm(): int | string
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
            'memory_cost' => $options['memory_cost'] ?? 1024,
            'time_cost' => $options['time_cost'] ?? 2,
            'threads' => $options['threads'] ?? 2,
        ];
    }
}
