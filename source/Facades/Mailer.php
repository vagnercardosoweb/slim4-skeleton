<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class Mailer.
 *
 * @method static \Core\Mailer\Mailer from(string $address, ?string $name = null)
 * @method static \Core\Mailer\Mailer reply(string $address, ?string $name = null)
 * @method static \Core\Mailer\Mailer addCC(string $address, ?string $name = null)
 * @method static \Core\Mailer\Mailer addBCC(string $address, ?string $name = null)
 * @method static \Core\Mailer\Mailer to(string $address, ?string $name = null)
 * @method static \Core\Mailer\Mailer subject(string $subject)
 * @method static \Core\Mailer\Mailer addFile(string $path, ?string $name = null)
 * @method static \Core\Mailer\Mailer body(string $message)
 * @method static \Core\Mailer\Mailer altBody(string $message)
 * @method static mixed send()
 */
class Mailer extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Mailer\Mailer::class;
    }
}
