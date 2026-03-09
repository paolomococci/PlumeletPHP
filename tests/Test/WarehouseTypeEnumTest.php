<?php

declare (strict_types = 1); // Enforce strict type checking

use App\Backend\Models\Enums\WarehouseTypeEnum; // Enum to be tested.

/**
 * Store the enum class name in a variable that can be shared with Pest test closures.
 * 
 * Using `WarehouseTypeEnum::class` keeps it as a class-string fully-qualified class name.
 */
$enumClass = WarehouseTypeEnum::class;

/**
 * Initialize or reset the shared variable before each test run.
 * The closure imports $enumClass by reference so modifications
 * here affect the outer variable.
 */
beforeEach(function () use (&$enumClass) {
    $enumClass = WarehouseTypeEnum::class;
});

/**
 * Basic reflection info.
 */
it('has correct enum name and is backed', function () use (&$enumClass) {
    $reflectionEnum = new ReflectionEnum($enumClass);

    expect($reflectionEnum->getName())->toBe(WarehouseTypeEnum::class);
    expect($reflectionEnum->isBacked())->toBeTrue();

    $backing = $reflectionEnum->getBackingType();
    expect($backing)->not->toBeNull();
    expect($backing->getName())->toBe('string');
});

/**
 * Cases existence and backing values.
 */
it('exposes all cases with correct backing values', function () use (&$enumClass) {
    $reflectionEnum = new ReflectionEnum($enumClass);
    $cases          = $reflectionEnum->getCases();

    // Collect names => backing values for backed cases.
    $map = [];
    foreach ($cases as $case) {
        if ($case instanceof ReflectionEnumBackedCase) {
            $map[$case->getName()] = $case->getBackingValue();
        } else {
            $map[$case->getName()] = null; // unit case
        }
    }

    expect($map)->toMatchArray([
        'OWNED'    => 'owned',
        'SUPPLIER' => 'supplier',
        'CURRIER'  => 'currier',
    ]);
});

/**
 * Methods declared on enum.
 */
it('declares expected methods', function () use (&$enumClass) {
    $reflectionEnum = new ReflectionEnum($enumClass);

    $methods = array_map(fn($method) => $method->getName(), $reflectionEnum->getMethods());
    // We expect at least isValid and label to be declared on the enum.
    expect($methods)->toContain('isValid');
    expect($methods)->toContain('label');
});

/**
 * isValid behavior.
 */
it('validates values correctly with isValid', function () {
    expect(WarehouseTypeEnum::isValid('owned'))->toBeTrue();
    expect(WarehouseTypeEnum::isValid('OWNED'))->toBeTrue(); // method lowercases input.
    expect(WarehouseTypeEnum::isValid('supplier'))->toBeTrue();
    expect(WarehouseTypeEnum::isValid('currier'))->toBeTrue();
    expect(WarehouseTypeEnum::isValid('unknown'))->toBeFalse();
});

/**
 * tryFrom and label()
 */
it('creates instances with tryFrom and returns correct label', function () {
    $case = WarehouseTypeEnum::tryFrom('owned');
    expect($case)->not->toBeNull();
    expect($case->value)->toBe('owned');
    expect($case->name)->toBe('OWNED');
    expect($case->label())->toBe('Owned Warehouse');
});

/**
 * cases() iteration and labels
 */
it('iterates all cases and each case returns its label', function () {
    $expected = [
        'OWNED'    => ['value' => 'owned', 'label' => 'Owned Warehouse'],
        'SUPPLIER' => ['value' => 'supplier', 'label' => 'Supplier Warehouse'],
        'CURRIER'  => ['value' => 'currier', 'label' => 'Courier Warehouse'],
    ];

    foreach (WarehouseTypeEnum::cases() as $case) {
        expect($expected)->toHaveKey($case->name);
        expect($case->value)->toBe($expected[$case->name]['value']);
        expect($case->label())->toBe($expected[$case->name]['label']);
    }
});

/**
 * Reflection-based invocation examples.
 */
it('invokes static and instance methods via reflection', function () use (&$enumClass) {
    $reflectionEnum = new ReflectionEnum($enumClass);

    // Static method: isValid
    $methodIsValid = $reflectionEnum->getMethod('isValid');
    expect($methodIsValid->isStatic())->toBeTrue();
    expect($methodIsValid->invokeArgs(null, ['owned']))->toBeTrue();
    expect($methodIsValid->invokeArgs(null, ['nope']))->toBeFalse();

    // Instance method: label
    $owned = WarehouseTypeEnum::tryFrom('owned');
    expect($owned)->not->toBeNull();
    $methodLabel = $reflectionEnum->getMethod('label');
    expect($methodLabel->isStatic())->toBeFalse();
    expect($methodLabel->invoke($owned))->toBe('Owned Warehouse');
});
