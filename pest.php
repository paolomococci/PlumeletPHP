<?php

/**
 * Global Pest configuration.
 *
 * `uses()` returns Pest's test-case factory.
 * `->in('tests')` tells the factory that every
 * file inside the `tests` directory should automatically
 * use the default test case class (Pest\TestCase).
 * This eliminates the need to add a `uses(...)` call
 * in each individual test file.
 *
 */
uses()->in('tests');