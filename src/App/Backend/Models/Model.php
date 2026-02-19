<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Model
 */
abstract class Model
{
    /**
     * Validate a serial number (SERIAL data type: BIGINT UNSIGNED).
     * Some points to consider:
     * PHP 8.0 allows 64-bit signed integers. However, on 64-bit PHP, integers are still 64-bit signed, with a maximum value of 9,223,372,036,854,775,807.
     * But we need unsigned max 18,446,744,073,709,551,615. That's larger than signed max. So cannot store as int on signed 64-bit PHP.
     * So need to treat as string and use bccomp for comparison? Use gmp or bc? Use string comparison. So check numeric only, no leading zeros? Accept maybe.
     * Also allow check that string trimmed length <= 20. Use bccomp($serial, '18446744073709551615', 0) <= 0.
     * Use BCMath. Or if not available, use gmp_cmp.
     * Provide fallback: use string compare after padding lengths.
     *
     * Recommended use:
     *
     * try {
     *      $cleanSerial = $this->checkSerialBcMath($_POST['serial']);
     * } catch (InvalidArgumentException $e) {
     *      $e->getMessage();
     * }
     *
     * @param string $serial The input value (e.g., 12345678901234567890).
     * @return string        Clean and ready-to-use value for SERIAL.
     *
     * @throws InvalidArgumentException If the value is empty, too long, or out of range.
     */
    protected static function checkSerial(string $serial): string
    {
        /* 1. Sanitization: Removes spaces, tabs, commas, etc. */
        $serial = trim($serial);                        // Removes leading/trailing spaces.
        $serial = preg_replace('/[^\d]/', '', $serial); // Only numeric characters.

        /* 2. Cannot be empty. */
        if ($serial === '') {
            throw new InvalidArgumentException('Serial cannot be empty.');
        }

        /* 3. Normalize: Removes leading zeros (optional) */
        $serial = ltrim($serial, '0');
        if ($serial === '') {
            $serial = '0'; // If the input is 0000, the value is set to 0.
        }

        /* 4.Maximum length (20 digits for BIGINT UNSIGNED). */
        if (strlen($serial) > 20) {
            throw new InvalidArgumentException('Serial too long (max 20 digits).');
        }

        /* 5. Comparison with the maximum value: 18446744073709551615. */
        $maxSerial = '18446744073709551615'; // 2^64 - 1

        // If the string is longer than $maxSerial or the same length but greater than $maxSerial.
        if (
            strlen($serial) > strlen($maxSerial) ||
            (strlen($serial) === strlen($maxSerial) && $serial > $maxSerial)
        ) {
            throw new InvalidArgumentException(
                'Serial exceeds the maximum allowed value (18446744073709551615).'
            );
        }

        /* 6. Returns the cleaned serial. */
        return $serial;
    }

    /**
     * Validates a serial number (BIGINT UNSIGNED) using BCMath.
     *
     * Recommended use:
     *
     * try {
     *      $cleanSerial = $this->checkSerialBcMath($_POST['serial']);
     * } catch (InvalidArgumentException $e) {
     *      $e->getMessage();
     * }
     *
     * These option are only available if PHP was configured with --enable-bcmath
     *
     * @param string $serial  Value to be checked:
     * @return string         Number that is clean and ready for use.
     *
     * @throws InvalidArgumentException If the value is empty, too long, or out of range.
     */
    protected static function checkSerialBcMath(string $serial): string
    {
        /* 1. Removes spaces, tabs, commas, etc., and leaves only numbers. */
        $serial = trim($serial);                        // Outer spaces
        $serial = preg_replace('/[^\d]/', '', $serial); // Only numbers

        /* 2. Cannot be empty. */
        if ($serial === '') {
            throw new InvalidArgumentException('Serial cannot be empty.');
        }

        /* 3. Avoid leading zeros (if necessary). */
        $serial = ltrim($serial, '0');
        if ($serial === '') {
            $serial = '0';
        }

        /* 4. Maximum length (20 digits). */
        if (strlen($serial) > 20) {
            throw new InvalidArgumentException('Serial too long (max 20 digits).');
        }

        /* 5. Compare with the maximum value using bccomp. */
        $maxSerial = '18446744073709551615'; // 2^64 - 1

        // bccomp return:
        //  -1  if $serial <  $maxSerial
        //   0  if $serial == $maxSerial
        //   1  if $serial >  $maxSerial
        if (bccomp($serial, $maxSerial, 0) > 0) {
            throw new InvalidArgumentException(
                'Serial exceeds the maximum allowed value (18446744073709551615).'
            );
        }

        /* 6. Returns the cleaned serial number value. */
        return $serial;
    }

    /**
     * checkVarchar
     *
     * @param  mixed $text
     * @param  mixed $length
     * @return string
     */
    protected static function checkVarchar(string $text, int $length): string
    {
        // Sanitizes and limits the length to the specified value of $length.
        $text = static::sanitize($text, ['max_length' => $length]);

        // Validation: Must be non-empty, with a maximum length of ($length).
        if ($text === '') {
            throw new InvalidArgumentException('Name cannot be empty.');
        }
        if (mb_strlen($text) > $length) {
            throw new InvalidArgumentException('Name too long (max $length chars).');
        }

        // Removes control characters that are not printable.
        return preg_replace('/[[:cntrl:]]+/', '', $text);
    }

    /**
     * checkPrice
     *
     * @param  mixed $price
     * @param  mixed $digits
     * @return float
     */
    protected static function checkPrice(float $price, int $digits): float
    {
        // Validation: Value must be non-negative and have reasonable precision.
        if (! is_finite($price) || $price < 0.0) {
            throw new InvalidArgumentException('Price must be a non-negative finite number.');
        }

        // To limit the number of decimal places to n (for monetary values).
        return round($price, $digits);
    }

    /**
     * Sanitizes and validates an email address.
     *
     * @param string $email   Address to check.
     * @param int    $length  Maximum permitted length (default: 255).
     *
     * @return string Normalized address (lowercase, no spaces, etc.).
     *
     * @throws InvalidArgumentException If the address is null, too long, or invalid.
     */
    protected static function checkEmail(string $email, int $length = 255): string
    {
        /* 1. Removes unwanted spaces and extraneous characters. */
        $email = trim($email);
        $email = str_replace(' ', '', $email);

        /* 2. Basic check. */
        if ($email === '') {
            throw new InvalidArgumentException('Email cannot be empty.');
        }

        if (mb_strlen($email) > $length) {
            throw new InvalidArgumentException(
                "Email too long (max {$length} chars)."
            );
        }

        /* 3. Formal verification. */
        // `filter_var` Returns the address if valid, otherwise returns `false`.
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        /* 4. Stripping out control characters. */
        $email = preg_replace('/[[:cntrl:]]+/', '', $email);

        /* 5. Normalization (lowercase). */
        $email = strtolower($email);

        return $email;
    }

    /**
     * normalize
     *
     * @param  mixed $text
     * @return string
     */
    protected static function normalize(string $text): string
    {
        // Trimming leading/trailing spaces.
        $text = trim($text);

        // Replaces any sequence of spaces (including tabs/newlines) with a single space.
        $text = preg_replace('/\s+/u', ' ', $text);

        // Replaces sequences of three or more dots with Unicode ellipses.
        $text = preg_replace('/\.{3,}/u', '…', $text);

        // Collapses consecutive sequences of the same punctuation mark to a single instance.
        // es. "!!!" => "!", "..." => ".", "???" => "?"
        $text = preg_replace('/([!?.,:;\"\'\-\—\(\)\[\]\{\}])\1+/u', '$1', $text);

        // Optional: Removes spaces before common punctuation, if present.
        $text = preg_replace('/\s+([!?.,:;)\]\}])/u', '$1', $text);

        // Optional: Inserts a space after punctuation if one is not already present.
        $text = preg_replace('/([!?.:;,])(?=[^\s0-9\p{P}])/u', '$1 ', $text);

        return $text;
    }

    /**
     * sanitize
     *
     * @param  mixed $text
     * @param  mixed $options
     * @return string
     */
    public static function sanitize(string $text, array $options = []): string
    {
        // Default options.
        $opts = array_merge([
            'max_length'         => 1000,
            'allow_tabs'         => false,
            'allow_newlines'     => false,
            'use_whitelist'      => false,                          // If true, it keeps only characters in $whitelist_chars.
            'whitelist_chars'    => "[:print:]\u00A0\u00C0-\u024F", // Example: printable characters + Latin extended characters.
            'blacklist_patterns' => [                               // Removes these classes/symbols.
                '/[\p{Cc}\p{Cf}]/u',                                    // Control characters + formatting characters.
                '/[\x{202E}\x{202D}\x{202A}-\x{202E}]/u',               // Direction overrides.
                '/[<>]/',                                               // Smaller/Greater.
                '/[`~^|\\\\]/',                                         // Backtick, tilde, caret, pipe, backslash characters.
            ],
        ], $options);

        // Trim and normalize whitespace and punctuation (using the previous function).
        $text = static::normalize($text);

        // Removes unwanted control characters/formatting characters.
        foreach ($opts['blacklist_patterns'] as $pat) {
            $text = preg_replace($pat, '', $text) ?? $text;
        }

        // Optional: Removes tabs and newlines unless explicitly allowed.
        if (! $opts['allow_tabs']) {
            $text = preg_replace("/\t+/u", ' ', $text);
        }
        if (! $opts['allow_newlines']) {
            $text = preg_replace("/\R+/u", ' ', $text);
        }

        // Optional: Applies a whitelist (keeps only allowed characters).
        if ($opts['use_whitelist']) {
            // Creates a regex using whitelist_chars (assuming POSIX/Unicode character classes).
            $class = $opts['whitelist_chars'];
            $text  = preg_replace("/(?![$class])./u", '', $text) ?? $text;
        }

        // Truncate to maximum length.
        if ($opts['max_length'] !== null) {
            if (mb_strlen($text) > $opts['max_length']) {
                $text = mb_substr($text, 0, $opts['max_length']);
            }
        }

        return $text;
    }

    /**
     * toDateTimeImmutable
     *
     * @param  mixed $value
     * @param  mixed $tz
     * @return DateTimeImmutable
     */
    public static function toDateTimeImmutable(
        string $value,
        DateTimeZone $tz = new DateTimeZone('UTC')
    ): DateTimeImmutable {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, $tz);

        if ($dt === false) {
            $errors = DateTimeImmutable::getLastErrors();
            throw new InvalidArgumentException(
                sprintf('Invalid datetime format: %s', implode('; ', $errors['errors']))
            );
        }

        return $dt;
    }

    /**
     * fetchFromData
     *
     * Generic method to map data from database to object.
     *
     * @param  mixed $data Data fetched from the database.
     * @return static Instance of the mapped entity.
     */
    public static function fetchFromData(array $data): static
    {
        $reflection        = new ReflectionClass(static::class);
        $constructorParams = $reflection->getConstructor()->getParameters();

        $mappedParams = [];
        foreach ($constructorParams as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            $value = $data[$paramName] ?? null;

            $value = $paramType && $value !== null
                /**
                 * Match Benefits:
                 * - more Concise;
                 * - more performing than switches;
                 * - returns a value directly;
                 * - type-safe.
                 * 
                 */
                ? match ($paramType->getName()) {
                    'int'    => is_numeric($value) ? (int) $value : null,
                    'float'  => is_numeric($value) ? (float) $value : null,
                    'bool'   => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                    'string' => (string) $value,
                    'array'  => is_array($value) ? $value : null,
                    default  => $value
                }
                : $value;

            $mappedParams[$paramName] = $value;
        }

        return new static(...$mappedParams);
    }

    /**
     * ellipsisPreserveWords (UTF-8 safe)
     * 
     * If the string is longer than $limit, an ellipsis (…) is appended.
     *
     * @param  string $description
     * @param  int    $limit
     * @return string
     */
    public static function ellipsisPreserveWords(string $description, int $limit = 24): string
    {
        // Check if the description length is within the limit.
        // If it is, return the original string unchanged.
        if (mb_strlen($description) <= $limit) return $description;

        // Take a substring of the description up to the specified limit.
        $substr = mb_substr($description, 0, $limit);

        // Find the position of the last space character within that substring.
        // This helps us avoid cutting a word in half.
        $lastSpace = mb_strrpos($substr, ' ');

        // If a space was found, trim the substring at that position so it ends at a word boundary.
        if ($lastSpace !== false) {
            $substr = mb_substr($substr, 0, $lastSpace);
        }

        // Remove any trailing whitespace and append an ellipsis character.
        return rtrim($substr) . '…';
    }
}
