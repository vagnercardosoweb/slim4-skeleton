<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Mask.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Mask
{
    const MASK_CPF = '###.###.###-##';

    const MASK_CNPJ = '##.###.###/####-##';

    const MASK_PHONE = ['(##)####-####', '(##)#####-####'];

    const MASK_CEP = '##.###-###';

    /**
     * @param string $value
     * @param string $mask
     *
     * @return string
     */
    public static function create(string $value, string $mask): string
    {
        $normalizeValue = self::normalizeValue($value);
        $normalizeMask = preg_replace('/[^#]/m', '', $mask);

        if (strlen($normalizeValue) !== strlen($normalizeMask)) {
            return $value;
        }

        return vsprintf(
            str_replace('#', '%s', $mask),
            str_split($normalizeValue)
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function normalizeValue(string $value): string
    {
        return preg_replace(
            '/[\-\|\(\)\/\.\: ]/',
            '',
            $value
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cep(string $value): string
    {
        return self::create(Common::onlyNumber($value), self::MASK_CEP);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function phone(string $value): string
    {
        $value = Common::onlyNumber($value);
        $mask = 10 == strlen($value) ? self::MASK_PHONE[0] : self::MASK_PHONE[1];

        return self::create($value, $mask);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cpfOrCnpj(string $value): string
    {
        if (11 === strlen($value)) {
            return self::cpf($value);
        }

        return self::cnpj($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cpf(string $value): string
    {
        return self::create(Common::onlyNumber($value), self::MASK_CPF);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cnpj(string $value): string
    {
        return self::create(Common::onlyNumber($value), self::MASK_CNPJ);
    }
}
