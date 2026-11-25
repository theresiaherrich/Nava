<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis (bukan kesalahan format input).
 * Dirender sebagai { message, code, errors? } dengan status default 422.
 */
class AturanBisnisException extends RuntimeException
{
    /** @param array<string, string|list<string>> $errors */
    public function __construct(
        string $message,
        public readonly string $kode = 'BUSINESS_RULE',
        public readonly int $status = 422,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public static function terkunci(string $message): self
    {
        return new self($message, 'LOCKED', 422);
    }

    public static function dilarang(string $message, string $kode = 'FORBIDDEN'): self
    {
        return new self($message, $kode, 403);
    }
}
