<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace Core;

use Core\Support\Arr;
use Core\Support\Path;

/**
 * Class Translator.
 */
abstract class Translator
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @var string|null
     */
    protected static ?string $language = null;

    /**
     * @var string
     */
    protected static string $fallback = 'en';

    /**
     * @param string $fallback
     */
    public static function setFallback(string $fallback): void
    {
        self::$fallback = self::resolveLanguageName($fallback);
    }

    /**
     * @return string
     */
    public static function getFallback(): string
    {
        return self::$fallback;
    }

    /**
     * @param string $language
     */
    public static function setLanguage(string $language): void
    {
        self::$language = self::parseLanguageName($language);
    }

    /**
     * @return string
     */
    public static function getLanguage(): string
    {
        if (self::$language) {
            return self::$language;
        }

        return self::$fallback;
    }

    /**
     * @param string $message
     *
     * @return string|array
     */
    public static function get(string $message): array | string
    {
        if (count(func_get_args()) > 2) {
            throw new \UnexpectedValueException('You can only pass two parameters.');
        }

        list($file, $message) = explode('.', $message, 2) + [null, null];
        self::loadData($file);

        $args = func_get_args();
        array_shift($args);

        $message = Arr::get(self::$data[$file], $message, $message);
        $message = self::replacementsMessage($message, $args);

        return self::sprintfMessage($message, $args);
    }

    /**
     * @param string $file
     * @param string $message
     *
     * @return array|string
     */
    public static function byFile(string $file, string $message): array | string
    {
        return self::get("{$file}.{$message}", ...array_slice(func_get_args(), 2));
    }

    /**
     * @param string $file
     */
    protected static function loadData(string $file): void
    {
        if (!empty(self::$data[$file])) {
            return;
        }

        self::$data[$file] = [];

        if (!$path = self::existsFile(self::$language, $file) ?? self::existsFile(self::$fallback, $file)) {
            return;
        }

        self::$data[$file] = require "{$path}";
    }

    /**
     * @param string $language
     *
     * @return string|null
     */
    protected static function existsFolder(string $language): ?string
    {
        $path = sprintf(Path::resources('/languages/%s'), $language);

        if (!is_dir($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param string $language
     * @param string $file
     *
     * @return string|null
     */
    protected static function existsFile(string $language, string $file): ?string
    {
        $path = sprintf(Path::resources('/languages/%s/%s.php'), $language, $file);

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param string $header
     *
     * @return string
     */
    protected static function parseLanguageName(string $header): string
    {
        $splitHeader = explode(',', $header);
        $splitHeader = array_map(function (string $name) {
            list($name) = explode(';', $name);

            return trim($name);
        }, $splitHeader);

        foreach ($splitHeader as $language) {
            $language = self::resolveLanguageName($language);

            if ('*' === $language) {
                break;
            }

            if (self::existsFolder($language)) {
                return $language;
            }
        }

        return self::$fallback;
    }

    /**
     * @param string $language
     *
     * @return string
     */
    protected static function resolveLanguageName(string $language): string
    {
        return str_replace('_', '-', strtolower($language));
    }

    /**
     * @param mixed $message
     * @param array $args
     *
     * @return mixed
     */
    protected static function replacementsMessage(mixed $message, array &$args): mixed
    {
        $language = self::$language;
        $fallback = self::$fallback;

        if (!empty($args[0])) {
            $value = null;

            if (!empty($args[0][$language])) {
                $value = $args[0][$language];
                unset($args[0][$language]);
            } elseif (!empty($args[0][$fallback])) {
                $value = $args[0][$fallback];
                unset($args[0][$fallback]);
            }

            if ($value) {
                $message = str_replace('{language}', $value, $message);
            }
        }

        if (!empty($args[0]['replacements'])) {
            foreach ($args[0]['replacements'] as $key => $replacement) {
                $message = str_replace("{{$key}}", $replacement, $message);
            }

            unset($args[0]['replacements']);
        }

        return $message;
    }

    /**
     * @param mixed $message
     * @param array $args
     *
     * @return mixed
     */
    protected static function sprintfMessage(mixed $message, array &$args): mixed
    {
        if (
            !empty($args)
            && !is_array($message)
            && str_contains($message, '%')
        ) {
            try {
                $args = $args[0]['arguments'] ?? $args[0];

                return sprintf($message, ...$args);
            } catch (\Exception) {
            }
        }

        return $message;
    }
}
