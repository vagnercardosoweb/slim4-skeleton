<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

namespace App\Models;

/**
 * Class UserModel.
 */
class UserModel extends BaseModel
{
    /**
     * @var string
     */
    protected string $table = 'users';

    /**
     * @var string|null
     */
    protected ?string $primaryKey = 'id';
}
