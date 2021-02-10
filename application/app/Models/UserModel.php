<?php

namespace App\Models;

/**
 * Class UserModel
 *
 * @package App\Models
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
