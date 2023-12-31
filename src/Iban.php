<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\IBAN\Helper;

use BeastBytes\IBAN\IbanDataInterface;
use \InvalidArgumentException;

/**
 * Static helper methods for {@link https://www.iban.com/ ISO 13616-1:2007 International Bank Account Numbers (IBAN)}
 */
class Iban
{
    public const DATA_NOT_CORRECT_FORMAT_EXCEPTION_MESSAGE = 'Data not the correct format for {country}';
    public const INVALID_COUNTRY_EXCEPTION_MESSAGE = 'Invalid country "{country}"';
    public const INVALID_IBAN_EXCEPTION_MESSAGE = 'Invalid IBAN "{iban}"';

    private const COUNTRY_REGEX = '/^([A-Z]+)\d/';

    /**
     * Generates a valid IBAN
     *
     * The account data varies by country - see {@link IbanHandler.php} for country IBAN definitions
     *
     * @param string $country ISO-3166 Alpha-2 code of the country
     * @param string|string[] $data Data for the IBAN according to the country,
     * e.g. bank identifier, account number, ..., in the correct order, e.g. for GB,
     * either [Bank identifier, Branch identifier, Account number]
     * or Bank identifier . Branch identifier . Account number
     * @return string The IBAN
     * @example IbanHelper::generateIban('GB', ['NWBK', '601613', '31926819']) returns 'GB29NWBK60161331926819'
     * @example IbanHelper::generateIban('GB', 'NWBK60161331926819') returns 'GB29NWBK60161331926819'
     */
    public static function generateIban(
        string $country,
        array|string $data,
        IbanDataInterface $ibanData
    ): string
    {
        $country = strtoupper($country);

        if (!self::usesIban($country, $ibanData)) {
            throw new InvalidArgumentException(strtr(
                self::INVALID_COUNTRY_EXCEPTION_MESSAGE,
                ['{country}' => $country]
            ));
        }

        if (is_array($data)) {
            $data = implode($data);
        }

        $data = str_replace(' ', '', strtoupper($data));

        $iban = $country . '00' . $data;
        if (preg_match($ibanData->getPattern($country), $iban, $matches) === 0) {
            throw new InvalidArgumentException(strtr(
                self::DATA_NOT_CORRECT_FORMAT_EXCEPTION_MESSAGE,
                ['{country}' => $country]
            ));
        }

        return $country . self::checkDigits($iban) . $data;
    }

    /**
     * Returns an array of fields and their values
     *
     * @param string $iban The IBAN to get the fields of
     * @return array The IBAN fields
     */
    public static function getFields(string $iban, IbanDataInterface $ibanData): array
    {
        $iban = str_replace(' ', '', $iban);

        $matches = [];
        $result = preg_match(self::COUNTRY_REGEX, $iban, $matches);

        if ($result !== 1) {
            throw new InvalidArgumentException(strtr(
               self::INVALID_IBAN_EXCEPTION_MESSAGE,
               ['{iban}' => $iban]
            ));
        }

        $country = $matches[1];

        if (!self::usesIban($country, $ibanData)) {
            throw new InvalidArgumentException(strtr(
                self::INVALID_COUNTRY_EXCEPTION_MESSAGE,
                ['{country}' => $country]
            ));
        }

        preg_match($ibanData->getPattern($country), $iban, $matches);
        return array_combine($ibanData->getFields($country), array_slice($matches, 1));
    }

    public static function checkDigits(string $iban): string
    {
        return str_pad((string)(98 - self::mod97($iban)), 2, '0', STR_PAD_LEFT);
    }

    public static function mod97(string $iban): int
    {
        $iban = substr($iban, 4) . substr($iban, 0, 4);

        $ary = [];

        for ($i = 0, $l = strlen($iban); $i < $l; $i++) {
            if (is_numeric($iban[$i])) {
                $ary[] = $iban[$i];
            } else {
                $ary[] = (string)(ord($iban[$i]) - 55);
            }
        }

        $iban = implode('', $ary);

        $mod97 = (int)substr($iban, 0, 9) % 97;
        $i = 0;

        while (($n = substr($iban, 9 + $i * 7, 7)) !== '') {
            $mod97 = (int)($mod97 . $n) % 97;
            $i++;
        }

        return $mod97;
    }

    public static function usesIban(string $country, IbanDataInterface $ibanData): bool
    {
        return $ibanData->hasCountry($country);
    }
}
