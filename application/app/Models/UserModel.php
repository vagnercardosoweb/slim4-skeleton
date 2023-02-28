<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace App\Models;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    protected string $primaryKey = 'id';

    protected function onEachRow(UserModel $row)
    {
    }

    protected function onBeforeQuery()
    {
    }

    protected function onBeforeData(array &$data, bool $validate)
    {
    }
}
