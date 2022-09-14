<?php
/**
 * @copyright Copyright © 2022 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Iban\Helper;

use BeastBytes\Iban\Formats\IbanFormatInterface;
use \InvalidArgumentException;

/**
 * Static helper methods for {@link https://www.iban.com/ International Bank Account Number (IBAN)}
 */
class Iban
{
    private static array $ibanFormats;

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
        IbanFormatInterface $ibanFormats
    ): string
    {
        $country = strtoupper($country);

        if (!self::usesIban($country, $ibanFormats)) {
            throw new InvalidArgumentException(strtr(
                'Country {country} does not use IBAN',
                ['{country}' => $country]
            ));
        }

        if (is_array($data)) {
            $data = implode($data);
        }

        $data = str_replace(' ', '', strtoupper($data));

        $iban = $country . '00' . $data;
        if (preg_match($ibanFormats->getPattern($country), $iban, $matches) === 0) {
            throw new InvalidArgumentException(strtr(
                'Data not the correct format for {country}',
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
    public static function getFields(string $iban, IbanFormatInterface $ibanFormats): array
    {
        $iban = str_replace(' ', '', $iban);
        $country = substr($iban, 0, 2);

        if (!self::usesIban($country, $ibanFormats)) {
            throw new InvalidArgumentException(strtr(
                'Country {country} does not use IBAN',
                ['{country}' => $country]
            ));
        }

        $matches = [];
        preg_match($ibanFormats->getPattern($country), $iban, $matches);
        return array_combine($ibanFormats->getFields($country), array_slice($matches, 1));
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

    public static function usesIban(string $country, IbanFormatInterface $ibanFormats): bool
    {
        return $ibanFormats->hasCountry($country);
    }
}
