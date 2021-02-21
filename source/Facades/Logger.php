<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 21/02/2021 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class Logger.
 *
 * @method static \Core\Logger setName(string $name)
 * @method static \Core\Logger setLevel(int $level)
 * @method static \Core\Logger addConsoleHandler(int $level = null)
 * @method static \Core\Logger addSlackWebhookHandler(string $webhookUrl, ?string $channel = null, ?string $username = null, bool $useAttachment = true, ?string $iconEmoji = ':boom:', bool $useShortAttachment = false, bool $includeContextAndExtra = false, int $level = Logger::CRITICAL, bool $bubble = true, array $excludeFields = [])
 * @method static \Core\Logger addFileHandler(string $path, array $settings = []) [maxFiles: int, level: int, bubble: bool, permission: int]
 * @method static void emergency($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void log($level, $message, array $context = [])
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Logger extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Logger::class;
    }
}
