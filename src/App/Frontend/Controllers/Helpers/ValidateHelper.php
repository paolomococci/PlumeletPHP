<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Frontend\Controllers\Helpers;

use Egulias\EmailValidator\Validation\RFCValidation;
use Throwable;

trait ValidateHelper
{
    /**
     * toTrimmedString
     *
     * @param  mixed $value
     * @return string
     */
    final public static function toTrimmedString(mixed $value): string
    {
        // Reject arrays and resources.
        if (is_array($value) || is_resource($value)) {
            return '';
        }

        // Safe cast.
        $str = (string) $value;

        if ($str === '') {
            return '';
        }

        // Remove NUL bytes early helps some malformations.
        $str = str_replace("\0", '', $str);

        // Prefer mbstring: normalize and replace invalid sequences with U+FFFD.
        if (function_exists('mb_convert_encoding') && function_exists('normalizer_normalize')) {
            $str  = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            $norm = normalizer_normalize($str, \NormalizER::FORM_C) ?? $str;
            $str  = $norm;
        } elseif (function_exists('mb_convert_encoding')) {
            $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        } else {
            if (function_exists('iconv')) {
                $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
                if ($converted !== false) {
                    $str = $converted;
                } else {
                    $str = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\x80-\xFF]+/u', '', $str) ?? '';
                }
            } else {
                $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/', '', $str) ?? '';
            }
        }

        // Remove remaining Unicode control characters except common whitespace (tab, LF, CR).
        $str = preg_replace('/\p{Cc}+/u', '', $str) ?? $str;

        // Trim leading/trailing whitespace.
        return trim($str);
    }

    /**
     * escapeHtmlForXss
     *
     * @param  string $str
     * @return string
     */
    final public static function escapeHtmlForPreventXss(string $str): string
    {
        // Use ENT_QUOTES to escape both " and ', ENT_SUBSTITUTE to replace invalid UTF-8.
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * isNameSafe
     *
     * @param  string $name
     * @return bool
     */
    final protected static function isNameSafe(string $name): bool
    {
        // Reject control characters and BOM, (byte-order mark).
        if (preg_match('/[\x00-\x1F\x7F\x{FEFF}]/u', $name)) {
            return false;
        }

        // Max length 32 characters.
        if (mb_strlen($name) > 32) {
            return false;
        }

        // Basic charset, allow letters, combining marks and spaces, plus ' - . and allow only single spaces between tokens.
        if (! preg_match('/\A[\p{L}\p{M}\'\-.]+(?: [\p{L}\p{M}\'\-.]+)*\z/u', $name)) {
            return false;
        }

        // Each of apostrophe, hyphen and dot allowed at most once.
        if (substr_count($name, "'") > 1 || substr_count($name, "-") > 1 || substr_count($name, ".") > 1) {
            return false;
        }

        // Disallow symbols adjacent to each other.
        if (preg_match('/[\'\-.]{2,}/u', $name)) {
            return false;
        }

        // Disallow symbols at start or end, and disallow symbol adjacent to space.
        if (preg_match('/^(?:[\'\-.])|(?:[\'\-.])$|[ \t][\'\-.]|[\'\-.][ \t]/u', $name)) {
            return false;
        }

        return true;
    }

    /**
     * isPasswordSafe
     *
     * @param  string $password
     * @return bool
     */
    final protected static function isPasswordSafe(?string $password): bool
    {
        // Not null or not empty.
        if ($password === null || $password === '') {
            return false;
        }

        // Length checks 8 to 32 characters, use mb_strlen for multi byte safety.
        $passwordLength = mb_strlen($password);
        if ($passwordLength < 8 || $passwordLength > 32) {
            return false;
        }

        // Reject control characters (C0/C1) including DEL.
        if (preg_match('/[\x00-\x1F\x7F]/', $password)) {
            return false;
        }

        // At least one uppercase letter.
        if (! preg_match('/\p{Lu}/u', $password)) {
            return false;
        }

        // At least one lowercase letter.
        if (! preg_match('/\p{Ll}/u', $password)) {
            return false;
        }

        // At least one digit.
        if (! preg_match('/\d/', $password)) {
            return false;
        }

        // At least one allowed non-dangerous symbol.
        $allowedSymbols = '!@#%&*()_+-=:.;';
        $escaped        = preg_quote($allowedSymbols, '/');
        if (! preg_match('/[' . $escaped . ']/u', $password)) {
            return false;
        }

        return true;
    }

    /**
     * isPassphraseValid
     *
     * @param  string $passphrase
     * @return bool
     */
    final protected static function isPassphraseValid(string $passphrase): bool
    {
        // Make sure your passphrase contains only lowercase hexadecimal characters
        // and is made up of exactly 32 of these characters.
        return (bool) preg_match('/\A[0-9a-f]{32}\z/', $passphrase);
    }

    /**
     * isValidIp
     *
     * @param  string $ip
     * @return bool
     */
    final protected static function isValidIp(string $ip): bool
    {
        // Verify that $ip is a valid IPv4 or IPv6 address.
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * throttleKey
     *
     * @param  string $ip
     * @param  string $email
     * @return string
     */
    final protected static function throttleKey(string $ip, string $email): string
    {
        return sha1($ip . '|' . $email);
    }

    /**
     * throttleKeyMatches
     *
     * @param  string $ip
     * @param  string $email
     * @param  string $expectedSha1
     * @return bool
     */
    final protected static function throttleKeyMatches(string $ip, string $email, string $expectedSha1): bool
    {
        // Normalize emails and IPs consistently.
        $normalizedIp    = trim($ip);
        $normalizedEmail = mb_strtolower(trim($email));

        if (! static::isValidIp($normalizedIp)) {
            return false;
        }

        // Calculate the key and we compare it securely.
        $computed = sha1($normalizedIp . '|' . $normalizedEmail);

        // Use hash_equals to avoid timing attacks.
        return hash_equals($computed, $expectedSha1);
    }

    /**
     * validateEmail
     *
     * @param  string $email
     * @return bool|string
     */
    private function validateEmail(string $email): bool | string
    {
        try {
            $email = $this->trimString($email);

            // Empty email.
            if ($email === '') {
                return 'Email cannot be empty!';
            }

            // Regex check.
            $pattern = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';

            if (! preg_match($pattern, $email)) {
                return 'Email contains forbidden characters!';
            }

            // Malformed email.
            if (! $this->emailValidator->isValid($email, new RFCValidation())) {
                return 'Email is not RFC compliant!';
            }
        } catch (Throwable $e) {
            // Log or re-throw if you want.
            return 'Unexpected error during email validation.';
        }

        return false;
    }

    /**
     * trimString
     *
     * @param  mixed $value
     * @return string
     */
    private function trimString(mixed $value): string
    {
        return (string) trim($value);
    }
}
