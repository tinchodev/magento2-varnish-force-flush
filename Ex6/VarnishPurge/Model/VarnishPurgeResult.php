<?php
declare(strict_types=1);

namespace Ex6\VarnishPurge\Model;

final class VarnishPurgeResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
    ) {
    }
}
