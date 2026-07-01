<?php

declare(strict_types=1); // Enforce strict type checking

/**
 * Adds two integers and returns an integer.
 *
 * @param integer $a
 * @param integer $b
 * @return integer
 */
function add(int $a, int $b): int
{
    return $a + $b;
}

/**
 * Reverses a string using PHP's built-in `strrev()` function.
 *
 * @param string $str
 * @return string
 */
function reverse(string $str): string
{
    return strrev($str);
}

test('add() returns a sum of integers', function () {
    // Expect add(1, 2) to equal 3.
    expect(add(1, 2))->toBe(3);
    // Expect add(3, -3) to equal 0.
    expect(add(3, -3))->toBe(0);
    // Expect add(4, -5) to equal -1.
    expect(add(4, -5))->toBe(-1);
});

test('reverse() returns a reversed string', function () {
    // Expect reverse("desserts") to equal "stressed".
    expect(reverse("desserts"))->toBe("stressed");
    // Expect reverse("PHP") to equal "PHP".
    expect(reverse("PHP"))->toBe("PHP");
});
