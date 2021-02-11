<?php

namespace App\Models;

use Core\Database\Model;
use Core\Facades\Database;

/**
 * Class BaseModel
 *
 * @package App\Models
 */
abstract class BaseModel extends Model
{
    /**
     * BaseModel constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (is_null(self::$database)) {
            $database = Database::getResolvedInstance();

            self::setDatabase($database->driver($this->driver));
        }
    }
}
