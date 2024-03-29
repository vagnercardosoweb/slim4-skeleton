<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
use Core\Mailer\Mailer;
use Core\Mailer\PHPMailer;
use DI\Container;

/**
 * Class MailerProvider.
 */
class MailerProvider implements ServiceProvider
{
    /**
     * @param Container $container
     *
     * @return Mailer
     */
    public function __invoke(Container $container): Mailer
    {
        Facade::setAliases(['Mailer' => Mailer::class]);

        $configMailer = Config::get('mailer');
        $driverMailer = $configMailer['driver'] ?? 'phpmailer';
        $mergedConfigMailer = array_merge(
            $configMailer['default'] ?? [],
            $configMailer[$driverMailer] ?? []
        );

        $phpMailer = new PHPMailer($mergedConfigMailer);

        return new Mailer($phpMailer);
    }
}
