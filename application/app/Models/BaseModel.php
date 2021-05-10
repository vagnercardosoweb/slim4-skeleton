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

use Core\Database\Model;
use Core\Facades\Database;

/**
 * Class BaseModel.
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
