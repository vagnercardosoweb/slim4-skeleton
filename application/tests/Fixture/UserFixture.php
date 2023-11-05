<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace Tests\Fixture;

use Core\Support\Str;

class UserFixture implements Fixture
{
    /**
     * Returns the name of the table.
     *
     * @return string
     */
    public function getTable(): string
    {
        return 'users';
    }

    /**
     * Returns collection with data for insertion into the bank.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecords(): array
    {
        return [
            [
                'id' => Str::uuid(),
                'name' => 'any_name',
                'email' => 'any_mail@any.com',
                'password' => 'any_password',
            ],
        ];
    }
}
