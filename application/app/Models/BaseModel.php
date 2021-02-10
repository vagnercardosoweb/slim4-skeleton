<?php

namespace App\Models;

use Core\Database\Model;
use Core\Facades\Database;

/**
 * Class BaseModel
 *
 * @package App\Models
 */
class BaseModel extends Model
{
    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
        if (is_null(self::$database)) {
            self::setDatabase(Database::getResolvedInstance());
        }
    }
}
