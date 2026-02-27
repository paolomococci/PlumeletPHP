<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models\Enums;

/**
 * Enumeration that presents a partial collection of 
 * currencies used for testing purposes only.
 * 
 */
enum CurrencyEnum: string
{
    case ALL = 'ALL';
    case AOA = 'AOA';
    case ARS = 'ARS';
    case AUD = 'AUD';
    case AWG = 'AWG';
    case BAM = 'BAM';
    case BBD = 'BBD';
    case BDT = 'BDT';
    case BIF = 'BIF';
    case BMD = 'BMD';
    case BOB = 'BOB';
    case BRL = 'BRL';
    case BSD = 'BSD';
    case BTN = 'BTN';
    case BWP = 'BWP';
    case BZD = 'BZD';
    case CAD = 'CAD';
    case CDF = 'CDF';
    case CHF = 'CHF';
    case CLP = 'CLP';
    case COP = 'COP';
    case CRC = 'CRC';
    case CVE = 'CVE';
    case CZK = 'CZK';
    case DJF = 'DJF';
    case DKK = 'DKK';
    case DOP = 'DOP';
    case EUR = 'EUR';
    case FJD = 'FJD';
    case FKP = 'FKP';
    case GBP = 'GBP';
    case GEL = 'GEL';
    case GIP = 'GIP';
    case GMD = 'GMD';
    case GNF = 'GNF';
    case GTQ = 'GTQ';
    case GYD = 'GYD';
    case HKD = 'HKD';
    case HNL = 'HNL';
    case HTG = 'HTG';
    case IDR = 'IDR';
    case ILS = 'ILS';
    case ISK = 'ISK';
    case JMD = 'JMD';
    case JOD = 'JOD';
    case JPY = 'JPY';
    case KMF = 'KMF';
    case KRW = 'KRW';
    case KWD = 'KWD';
    case KYD = 'KYD';
    case LRD = 'LRD';
    case LSL = 'LSL';
    case MAD = 'MAD';
    case MDL = 'MDL';
    case MKD = 'MKD';
    case MVR = 'MVR';
    case MWK = 'MWK';
    case MXN = 'MXN';
    case MYR = 'MYR';
    case NAD = 'NAD';
    case NOK = 'NOK';
    case NZD = 'NZD';
    case PAB = 'PAB';
    case PEN = 'PEN';
    case PGK = 'PGK';
    case PHP = 'PHP';
    case PLN = 'PLN';
    case PYG = 'PYG';
    case RON = 'RON';
    case SBD = 'SBD';
    case SCR = 'SCR';
    case SEK = 'SEK';
    case SGD = 'SGD';
    case SHP = 'SHP';
    case STN = 'STN';
    case THB = 'THB';
    case TOP = 'TOP';
    case TRY = 'TRY';
    case TTD = 'TTD';
    case TWD = 'TWD';
    case TZS = 'TZS';
    case UAH = 'UAH';
    case USD = 'USD';
    case UYU = 'UYU';
    case VES = 'VES';
    case VUV = 'VUV';
    case WST = 'WST';
    case XCD = 'XCD';
    case XPF = 'XPF';

    public static function isValid(string $code): bool
    {
        return self::tryFrom(strtoupper($code)) !== null;
    }

    public function country(): string
    {
        return match($this) {
            self::ALL => 'Albania',
            self::AOA => 'Angola',
            self::ARS => 'Argentina',
            self::AUD => 'Australia',
            self::AWG => 'Aruba',
            self::BAM => 'Bosnia and Herzegovina',
            self::BBD => 'Barbados',
            self::BDT => 'Bangladesh',
            self::BIF => 'Burundi',
            self::BMD => 'Bermuda',
            self::BOB => 'Bolivia',
            self::BRL => 'Brazil',
            self::BSD => 'Bahamas',
            self::BTN => 'Bhutan',
            self::BWP => 'Botswana',
            self::BZD => 'Belize',
            self::CAD => 'Canada',
            self::CDF => 'Democratic Republic of the Congo',
            self::CHF => 'Switzerland',
            self::CLP => 'Chile',
            self::COP => 'Colombia',
            self::CRC => 'Costa Rica',
            self::CVE => 'Cabo Verde',
            self::CZK => 'Czech Republic',
            self::DJF => 'Djibouti',
            self::DKK => 'Denmark',
            self::DOP => 'Dominican Republic',
            self::EUR => 'Eurozone',
            self::FJD => 'Fiji',
            self::FKP => 'Falkland Islands',
            self::GBP => 'United Kingdom',
            self::GEL => 'Georgia',
            self::GIP => 'Gibraltar',
            self::GMD => 'Gambia',
            self::GNF => 'Guinea',
            self::GTQ => 'Guatemala',
            self::GYD => 'Guyana',
            self::HKD => 'Hong Kong',
            self::HNL => 'Honduras',
            self::HTG => 'Haiti',
            self::IDR => 'Indonesia',
            self::ILS => 'Israel',
            self::ISK => 'Iceland',
            self::JMD => 'Jamaica',
            self::JOD => 'Jordan',
            self::JPY => 'Japan',
            self::KMF => 'Comoros',
            self::KRW => 'South Korea',
            self::KWD => 'Kuwait',
            self::KYD => 'Cayman Islands',
            self::LRD => 'Liberia',
            self::LSL => 'Lesotho',
            self::MAD => 'Morocco',
            self::MDL => 'Moldova',
            self::MKD => 'North Macedonia',
            self::MVR => 'Maldives',
            self::MWK => 'Malawi',
            self::MXN => 'Mexico',
            self::MYR => 'Malaysia',
            self::NAD => 'Namibia',
            self::NOK => 'Norway',
            self::NZD => 'New Zealand',
            self::PAB => 'Panama',
            self::PEN => 'Peru',
            self::PGK => 'Papua New Guinea',
            self::PHP => 'Philippines',
            self::PLN => 'Poland',
            self::PYG => 'Paraguay',
            self::RON => 'Romania',
            self::SBD => 'Solomon Islands',
            self::SCR => 'Seychelles',
            self::SEK => 'Sweden',
            self::SGD => 'Singapore',
            self::SHP => 'Saint Helena',
            self::STN => 'São Tomé and Príncipe',
            self::THB => 'Thailand',
            self::TOP => 'Tonga',
            self::TRY => 'Turkey',
            self::TTD => 'Trinidad and Tobago',
            self::TWD => 'Taiwan Republic of China',
            self::TZS => 'Tanzania',
            self::UAH => 'Ukraine',
            self::USD => 'United States',
            self::UYU => 'Uruguay',
            self::VES => 'Venezuela',
            self::VUV => 'Vanuatu',
            self::WST => 'Samoa',
            self::XCD => 'East Caribbean',
            self::XPF => 'CFP franc'
        };
    }
}