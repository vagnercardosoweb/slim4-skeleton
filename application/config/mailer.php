<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

use Core\Support\Env;

$configClient = require __DIR__.'/client.php';
$mailFromMail = $_SERVER['HTTP_HOST'] ?? 'localhost';

if ('localhost' === $mailFromMail) {
    $mailFromMail .= '.dev';
}

return [
    'driver' => Env::get('MAIL_DRIVER', 'phpmailer'),

    'phpmailer' => [
        'exception' => true,
        'language' => [
            'code' => Env::get('MAIL_LANGUAGE_CODE'),
            'path' => Env::get('MAIL_LANGUAGE_PATH'),
        ],
    ],

    'default' => [
        'debug' => Env::get('MAIL_DEBUG', false),
        'charset' => Env::get('MAIL_CHARSET', 'utf-8'),
        'auth' => Env::get('MAIL_AUTH', true),
        'secure' => Env::get('MAIL_SECURE', 'tls'), // ssl | tls
        'host' => Env::get('MAIL_HOST'),
        'port' => Env::get('MAIL_PORT', 587),
        'username' => Env::get('MAIL_USERNAME'),
        'password' => Env::get('MAIL_PASSWORD'),
        'from' => [
            'name' => Env::get('MAIL_FROM_NAME', $configClient['name'] ?? null),
            'mail' => Env::get('MAIL_FROM_MAIL', "no-reply@{$mailFromMail}"),
        ],
    ],
];
