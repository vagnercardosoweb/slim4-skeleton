<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonoLogger;

class Logger extends MonoLogger
{
    public function addStreamHandler(string $path, Level $level = null): Logger
    {
        $jsonFormatter = new JsonFormatter();
        $consoleHandler = new StreamHandler($path);

        $consoleHandler->setFormatter($jsonFormatter);

        if (!empty($level)) {
            $consoleHandler->setLevel($level);
        }

        $this->pushHandler($consoleHandler);

        return $this;
    }

    public function addSlackHandler(
        string $token,
        string $channel,
        string $username,
        string $icon = '',
        Level $level = Level::Error,
        array $excludeFields = []
    ): Logger {
        try {
            $slackHandler = new SlackHandler(
                token: $token,
                channel: $channel,
                username: $username,
                useAttachment: true,
                iconEmoji: $icon,
                level: $level,
                useShortAttachment: true,
                includeContextAndExtra: true,
                excludeFields: $excludeFields
            );
            $slackHandler->setFormatter(new JsonFormatter());
            $this->pushHandler($slackHandler);
        } catch (\Throwable $e) {
            $this->error('Could not create SlackHandler', ['error_message' => $e->getMessage()]);
        }

        return $this;
    }

    /**
     * @param string                                $path
     * @param array{maxFiles: int, permission: int} $settings
     *
     * @return $this
     */
    public function addFileRotationHandler(string $path, array $settings = []): Logger
    {
        $pathInfo = pathinfo($path);
        $filename = sprintf(
            '%s/%s/%s',
            $pathInfo['dirname'],
            $pathInfo['filename'],
            $pathInfo['basename']
        );

        if (!empty($pathInfo['extension'])) {
            $filename .= ".{$pathInfo['extension']}";
        } else {
            $filename .= '.log';
        }

        $settings = array_merge([
            'maxFiles' => 14,
            'bubble' => true,
        ], $settings);

        $fileHandler = new RotatingFileHandler(
            filename: $filename,
            maxFiles: $settings['maxFiles'],
            bubble: $settings['bubble'],
            filePermission: $settings['permission']
        );

        $fileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $fileHandler->setFormatter(new JsonFormatter());

        $this->pushHandler($fileHandler);

        return $this;
    }
}
