<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Tests\Fixture;

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
     * @return array<string, mixed>
     */
    public function getRecords(): array
    {
        return [
            [
                'name' => 'any_name',
                'email' => 'any_mail@mail.com',
                'password' => 'any_password',
            ],
        ];
    }
}
