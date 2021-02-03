<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/02/2021 Vagner Cardoso
 */

use App\Providers\JwtProvider;
use App\Providers\LoggerProvider;
use App\Providers\SymfonyConsoleProvider;
use App\Providers\TwigProvider;
use Core\Helpers\Jwt;
use Core\Logger;
use Core\Twig\Twig;
use Symfony\Component\Console\Application;
use function DI\factory;

return [
    Twig::class => factory(TwigProvider::class),
    Application::class => factory(SymfonyConsoleProvider::class),
    Logger::class => factory(LoggerProvider::class),
    Jwt::class => factory(JwtProvider::class),
];
