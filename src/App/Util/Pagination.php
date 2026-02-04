<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Util;

/**
 * Pagination
 */
final class Pagination
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 20,
    ) {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('page >= 1');
        }

        if ($this->perPage < 1) {
            throw new \InvalidArgumentException('perPage >= 1');
        }

    }

    /**
     * offset
     *
     * @return int
     */
    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
