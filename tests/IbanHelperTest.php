<?php
/**
 * @copyright Copyright © 2022 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace Tests;

use BeastBytes\Iban\PHP\IbanStorage;
use BeastBytes\Iban\Helper\Iban;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IbanHelperTest extends TestCase
{
    /**
     * @dataProvider ibanProvider
     */
    public function test_generate_iban($country, $checkDigits, $data): void
    {
        $iban = $country . $checkDigits . implode($data);
        $this->assertSame($iban, Iban::generateIban($country, $data, new IbanStorage()));
    }

    /**
     * @dataProvider badIbanProvider
     */
    public function test_bad_ibans(string $country, string $data, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        Iban::generateIban($country, $data, new IbanStorage());
    }

    /**
     * @dataProvider ibanProvider
     */
    public function test_get_fields($country, $checkDigits, $data)
    {
        $ibans = new IbanStorage();
        $iban = $country . $checkDigits . implode($data);
        array_unshift($data, $checkDigits);
        $fields = Iban::getFields($iban, $ibans);
        $this->assertSame(array_combine($ibans->getFields($country), $data), $fields);
    }

    /**
     * @dataProvider countryProvider
     */
    public function test_uses_iban(string $country)
    {
        $this->assertTrue(Iban::usesIban($country, new IbanStorage()));
    }

    /**
     * @dataProvider badCountryProvider
     */
    public function test_does_not_use_iban(string $country)
    {
        $this->assertFalse(Iban::usesIban($country, new IbanStorage()));
    }

    public function badIbanProvider()
    {

        return [ // country, data, message
            [
                'XX', 'BARC20201630093459', 'Country XX does not use IBAN'
            ],
            [
                'GB', 'BARC2020163003459', 'Data not the correct format for GB'
            ],
            [
                'GB', 'BARC20201530093A59', 'Data not the correct format for GB'
            ],
            [
                'GB', 'BARCO0201530093459', 'Data not the correct format for GB'
            ],
        ];
    }

    public function ibanProvider(): array
    {
        return [
            'AL' => ['AL', '47', ['212', '1100', '9', '0000000235698741']],
            'AD' => ['AD', '12', ['0001', '2030', '200359100100']],
            'AT' => ['AT', '61', ['19043', '00234573201']],
            'AZ' => ['AZ', '21', ['NABZ', '00000000137010001944']],
            'BH' => ['BH', '67', ['BMAG', '00001299123456']],
            'BE' => ['BE', '68', ['539', '0075470', '34']],
            'BA' => ['BA', '39', ['129', '007', '94010284', '94']],
            'BR' => ['BR', '97', ['00360305', '00001', '0009795493', 'P', '1']],
            'BG' => ['BG', '80', ['BNBG', '9661', '10', '20345678']],
            'CR' => ['CR', '05', ['0152', '02001026284066']],
            'HR' => ['HR', '12', ['1001005', '1863000160']],
            'CY' => ['CY', '17', ['002', '00128', '0000001200527600']],
            'CZ' => ['CZ', '65', ['0800', '000019', '2000145399']],
            'DK' => ['DK', '50', ['0040', '044011624', '3']],
            'DO' => ['DO', '28', ['BAGR', '00000001212453611324']],
            'EE' => ['EE', '38', ['22', '00', '22102014568', '5']],
            'FO' => ['FO', '62', ['6460', '000163163', '4']],
            'FI' => ['FI', '21', ['123456', '0000078', '5']],
            'FR' => ['FR', '14', ['20041', '01005', '0500013M026', '06']],
            'GE' => ['GE', '29', ['NB', '0000000101904917']],
            'DE' => ['DE', '89', ['37040044', '0532013000']],
            'GI' => ['GI', '75', ['NWBK', '000000007099453']],
            'GR' => ['GR', '16', ['011', '0125', '0000000012300695']],
            'GL' => ['GL', '89', ['6471', '000100020', '6']],
            'GT' => ['GT', '82', ['TRAJ', '01', '02', '0000001210029690']],
            'HU' => ['HU', '42', ['117', '7301', '6', '111110180000000', '0']],
            'IS' => ['IS', '14', ['01', '59', '26', '007654', '5510730339']],
            'IE' => ['IE', '29', ['AIBK', '931152', '12345678']],
            'IL' => ['IL', '62', ['010', '800', '0000099999999']],
            'IT' => ['IT', '60', ['X', '05428', '11101', '000000123456']],
            'JO' => ['JO', '94', ['CBJO', '0010', '000000000131000302']],
            'KZ' => ['KZ', '86', ['125', 'KZT5004100100']],
            'XK' => ['XK', '05', ['12', '12', '0123456789', '06']],
            'KW' => ['KW', '81', ['CBKU', '0000000000001234560101']],
            'LV' => ['LV', '80', ['BANK', '0000435195001']],
            'LB' => ['LB', '62', ['0999', '00000001001901229114']],
            'LI' => ['LI', '21', ['08810', '0002324013AA']],
            'LT' => ['LT', '12', ['10000', '11101001000']],
            'LU' => ['LU', '28', ['001', '9400644750000']],
            'MK' => ['MK', '07', ['250', '1200000589', '84']],
            'MT' => ['MT', '84', ['MALT', '01100', '0012345MTLCAST001S']],
            'MR' => ['MR', '13', ['00020', '00101', '00001234567', '53']],
            'MU' => ['MU', '17', ['BOMM01', '01', '101030300200000', 'MUR']],
            'MD' => ['MD', '24', ['AG', '000225100013104168']],
            'MC' => ['MC', '58', ['11222', '00001', '01234567890', '30']],
            'ME' => ['ME', '25', ['505', '0000123456789', '51']],
            'NO' => ['NO', '93', ['8601', '111794', '7']],
            'PK' => ['PK', '36', ['SCBL', '0000001123456702']],
            'PS' => ['PS', '92', ['PALS', '000000000400123456702']],
            'PL' => ['PL', '61', ['109', '0101', '4', '0000071219812874']],
            'PT' => ['PT', '50', ['0002', '0123', '12345678901', '54']],
            'QA' => ['QA', '58', ['DOHB', '00001234567890ABCDEFG']],
            'RO' => ['RO', '49', ['AAAA', '1B31007593840000']],
            'LC' => ['LC', '55', ['HEMM', '000100010012001200023015']],
            'SM' => ['SM', '86', ['U', '03225', '09800', '000000270100']],
            'ST' => ['ST', '68', ['0001', '0001', '00518453101', '12']],
            'SA' => ['SA', '03', ['80', '000000608010167519']],
            'RS' => ['RS', '35', ['260', '0056010016113', '79']],
            'SK' => ['SK', '31', ['1200', '0000198742637541']],
            'SI' => ['SI', '56', ['26', '330', '00120390', '86']],
            'ES' => ['ES', '91', ['2100', '0418', '45', '0200051332']],
            'SE' => ['SE', '45', ['500', '0000005839825746', '6']],
            'CH' => ['CH', '93', ['00762', '011623852957']],
            'NL' => ['NL', '91', ['ABNA', '0417164300']],
            'TL' => ['TL', '38', ['008', '00123456789101', '57']],
            'TN' => ['TN', '59', ['10', '006', '0351835984788', '31']],
            'TR' => ['TR', '33', ['00061', '0', '0519786457841326']],
            'AE' => ['AE', '07', ['033', '1234567890123456']],
            'GB' => ['GB', '29', ['NWBK', '601613', '31926819']],
            'VG' => ['VG', '96', ['VPVG', '0000012345678901']],
        ];
        
    }

    public function countryProvider(): array
    {
        $provider = [];
        foreach (array_keys($this->ibanProvider()) as $country) {
            $provider[] = [$country];
        }
        return $provider;
    }

    public function badCountryProvider(): array
    {
        return [
            'non-existent code' => ['XX'],
            'alpha-3 code' => ['GBR'],
            'too short' => ['G'],
            'too long' => ['GBRT'],
            'number string' => ['12']
        ];
    }
}
