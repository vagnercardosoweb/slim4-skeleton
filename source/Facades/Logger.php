<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/07/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Monolog\Handler\HandlerInterface;
use Monolog\Processor\ProcessorInterface;

/**
 * Class Logger.
 *
 * @method static \Core\Logger addConsoleHandler(int $level = null)
 * @method static \Core\Logger addSlackWebhookHandler(string $webhookUrl, ?string $channel = null, ?string $username = null, bool $useAttachment = true, ?string $iconEmoji = ':boom:', bool $useShortAttachment = false, bool $includeContextAndExtra = false, int $level = Logger::CRITICAL, bool $bubble = true, array $excludeFields = [])
 * @method static \Core\Logger addRotationFileHandler(string $path, array $settings = []) [maxFiles: int, level: int, bubble: bool, permission: int]
 * @method static \Core\Logger addFileHandler(string $path, array $settings = []) [level: int, bubble: bool, filePermission: int, useLocking: boolean]
 * @method static \Core\Logger addDefaultProcessors()
 * @method static \Core\Logger addProcessor(ProcessorInterface $processor)
 * @method static \Core\Logger addHandler(HandlerInterface $handler)
 * @method static \Monolog\Logger createLogger(string | null $name = null)
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
