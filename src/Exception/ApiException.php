<?php
declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        string $detail,
        public readonly int $status,
        public readonly ?string $errorCode = null,
    ) {
        parent::__construct($detail);
    }
}
