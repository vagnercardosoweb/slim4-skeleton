<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 15/02/2021 Vagner Cardoso
 */

namespace Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;

/**
 * Class Logger.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Logger extends MonoLogger
{
    /**
     * @var int
     */
    protected int $level = MonoLogger::DEBUG;

    /**
     * Logger constructor.
     *
     * @param string|null $name
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name ?? 'slim4-skeleton');

        $this->extendsProcessor();
        $this->addConsoleHandler();
    }

    /**
     * @param string $name
     *
     * @return Logger
     */
    public function setName(string $name): Logger
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param int $level
     *
     * @return Logger
     */
    public function setLevel(int $level): Logger
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param int|null $level
     *
     * @return Logger
     */
    public function addConsoleHandler(?int $level = null): Logger
    {
        $consoleHandler = new StreamHandler('php://stdout', $level ?? $this->level);
        $consoleHandler->setFormatter($this->getLineFormatter());

        $this->pushHandler($consoleHandler);

        return $this;
    }

    /**
     * @param string      $webhookUrl
     * @param string|null $channel
     * @param string|null $username
     * @param bool        $useAttachment
     * @param string|null $iconEmoji
     * @param bool        $useShortAttachment
     * @param bool        $includeContextAndExtra
     * @param mixed       $level
     * @param bool        $bubble
     * @param array       $excludeFields
     *
     * @return Logger
     */
    public function addSlackWebhookHandler(
        string $webhookUrl,
        ?string $channel = null,
        ?string $username = null,
        bool $useAttachment = true,
        ?string $iconEmoji = ':boom:',
        bool $useShortAttachment = false,
        bool $includeContextAndExtra = false,
        int $level = Logger::CRITICAL,
        bool $bubble = true,
        array $excludeFields = []
    ): Logger {
        $this->pushHandler(new SlackWebhookHandler(
            $webhookUrl,
            $channel,
            $username,
            $useAttachment,
            $iconEmoji,
            $useShortAttachment,
            $includeContextAndExtra,
            $level,
            $bubble,
            $excludeFields
        ));

        return $this;
    }

    /**
     * @param string               $path
     * @param array<string, mixed> $settings [maxFiles: int, level: int, bubble: bool, permission: int]
     *
     * @return Logger
     */
    public function addFileHandler(string $path, array $settings = []): Logger
    {
        $pathInfo = pathinfo($path);
        $filename = sprintf(
            '%s/%s/%s.%s',
            $pathInfo['dirname'],
            $pathInfo['filename'],
            $pathInfo['basename'],
            $pathInfo['extension'] ?? 'log'
        );

        $settings = array_merge([
            'maxFiles' => 14,
            'level' => $this->level,
            'bubble' => true,
            'permission' => null,
        ], $settings);

        $fileHandler = new RotatingFileHandler(
            filename: $filename,
            maxFiles: $settings['maxFiles'],
            level: $settings['level'],
            bubble: $settings['bubble'],
            filePermission: $settings['permission']
        );

        $fileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $fileHandler->setFormatter($this->getLineFormatter());

        $this->pushHandler($fileHandler);

        return $this;
    }

    /**
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getLineFormatter(): LineFormatter
    {
        return new LineFormatter(
            format: null,
            dateFormat: 'Y-m-d H:i:s',
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true
        );
    }

    /**
     * @return void
     */
    protected function extendsProcessor(): void
    {
        $this->pushProcessor(new UidProcessor());
        $this->pushProcessor(new MemoryUsageProcessor());
        $this->pushProcessor(new WebProcessor());
    }
}
