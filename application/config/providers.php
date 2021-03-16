<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 16/03/2021 Vagner Cardoso
 */

use App\Providers\CurlProvider;
use App\Providers\DatabaseProvider;
use App\Providers\EncryptionProvider;
use App\Providers\JwtProvider;
use App\Providers\LoggerProvider;
use App\Providers\MailerProvider;
use App\Providers\PasswordProvider;
use App\Providers\PDOProvider;
use App\Providers\SymfonyConsoleProvider;
use App\Providers\TwigProvider;
use Core\Curl\Curl;
use Core\Database\Database;
use Core\Logger;
use Core\Mailer\Mailer;
use Core\Password\Password;
use Core\Support\Encryption;
use Core\Support\Jwt;
use Core\Twig\Twig;
use Symfony\Component\Console\Application;
use function DI\factory;

return [
    Jwt::class => factory(JwtProvider::class),
    Twig::class => factory(TwigProvider::class),
    Logger::class => factory(LoggerProvider::class),
    Application::class => factory(SymfonyConsoleProvider::class),
    Encryption::class => factory(EncryptionProvider::class),
    Password::class => factory(PasswordProvider::class),
    Database::class => factory(DatabaseProvider::class),
    PDO::class => factory(PDOProvider::class),
    Curl::class => factory(CurlProvider::class),
    Mailer::class => factory(MailerProvider::class),
];
