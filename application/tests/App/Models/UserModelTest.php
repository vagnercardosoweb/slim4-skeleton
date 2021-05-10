<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

namespace Tests\App\Models;

use App\Models\UserModel;
use Tests\TestCase;
use Tests\Traits\DatabaseTestTrait;

/**
 * Class UserModelTest.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class UserModelTest extends TestCase
{
    use DatabaseTestTrait;

    /**
     * @throws \Exception
     */
    public function testShouldCreateNewUserCorrectly(): void
    {
        $user = UserModel::query()->create([
            'name' => 'any_name',
            'email' => 'any_email@mail.com',
            'password' => 'any_password',
        ]);

        $this->assertIsString($user->id);
        $this->assertEquals('any_name', $user->name);
        $this->assertEquals('any_email@mail.com', $user->email);
        $this->assertEquals('any_password', $user->password);
    }
}
