<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/07/2021 Vagner Cardoso
 */

namespace Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessorInterface;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;

/**
 * Class Logger.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
final class Logger
{
    /**
     * Logger constructor.
     *
     * @param int                                          $level
     * @param array<\Monolog\Handler\HandlerInterface>     $handlers
     * @param array<\Monolog\Processor\ProcessorInterface> $processors
     */
    public function __construct(
        protected int $level = MonoLogger::DEBUG,
        protected array $handlers = [],
        protected array $processors = []
    ) {
    }

    /**
     * @param string|null $name
     *
     * @return \Monolog\Logger
     */
    public function createLogger(string | null $name = null): MonoLogger
    {
        $logger = new MonoLogger($name ?? 'app');

        foreach ($this->processors as $processor) {
            $logger->pushProcessor($processor);
        }

        foreach ($this->handlers as $handler) {
            $logger->pushHandler($handler);
        }

        $this->handlers = [];
        $this->processors = [];

        return $logger;
    }

    /**
     * @param int|null $level
     *
     * @return $this
     */
    public function addConsoleHandler(?int $level = null): Logger
    {
        $consoleHandler = new StreamHandler('php://stdout', $level ?? $this->level);
        $consoleHandler->setFormatter($this->getLineFormatter());

        $this->addHandler($consoleHandler);

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
     * @param \Monolog\Handler\HandlerInterface $handler
     *
     * @return $this
     */
    public function addHandler(HandlerInterface $handler): Logger
    {
        array_unshift($this->handlers, $handler);

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
     * @return $this
     */
    public function addSlackWebhookHandler(
        string $webhookUrl,
        ?string $channel = null,
        ?string $username = null,
        bool $useAttachment = true,
        ?string $iconEmoji = ':boom:',
        bool $useShortAttachment = false,
        bool $includeContextAndExtra = false,
        int $level = MonoLogger::DEBUG,
        bool $bubble = true,
        array $excludeFields = []
    ): Logger {
        $this->addHandler(new SlackWebhookHandler(
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
     * @return $this
     */
    public function addRotationFileHandler(string $path, array $settings = []): Logger
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

        $this->addHandler($fileHandler);

        return $this;
    }

    /**
     * @param string               $path
     * @param array<string, mixed> $settings [level: int, bubble: bool, filePermission: int, useLocking: boolean]
     *
     * @return $this
     */
    public function addFileHandler(string $path, array $settings = []): Logger
    {
        $settings = array_merge([
            'level' => $this->level,
            'bubble' => true,
            'filePermission' => null,
            'useLocking' => false,
        ], $settings);

        $handler = new StreamHandler(
            stream: $path,
            level: $settings['level'],
            bubble: $settings['bubble'],
            filePermission: $settings['filePermission'],
            useLocking: $settings['useLocking']
        );

        $handler->setFormatter($this->getLineFormatter());

        $this->addHandler($handler);

        return $this;
    }

    /**
     * @return $this
     */
    public function addDefaultProcessors(): Logger
    {
        $this->addProcessor(new UidProcessor());
        $this->addProcessor(new MemoryUsageProcessor());
        $this->addProcessor(new WebProcessor());

        return $this;
    }

    /**
     * @param \Monolog\Processor\ProcessorInterface $processor
     *
     * @return $this
     */
    public function addProcessor(ProcessorInterface $processor): Logger
    {
        array_unshift($this->processors, $processor);

        return $this;
    }
}
