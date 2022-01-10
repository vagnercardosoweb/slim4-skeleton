<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Tests\App\Models;

use App\Models\UserModel;
use Tests\Fixture\UserFixture;
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

    public function testShouldCreateNewUserCorrectly(): void
    {
        $this->insertFixtures([UserFixture::class]);

        $rowData = UserModel::query()->order('created_at DESC')->fetch();

        $this->assertEquals('any_name', $rowData->name);
        $this->assertEquals('any_mail@mail.com', $rowData->email);
        $this->assertEquals('any_password', $rowData->password);
    }

    public function testChecksIfThereIsOnlyOneResult(): void
    {
        $this->insertFixtures([UserFixture::class]);
        $this->assertSame(1, UserModel::query()->count());
    }
}
