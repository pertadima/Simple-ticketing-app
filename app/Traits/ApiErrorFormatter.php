<?php

namespace App\Traits;

trait ApiErrorFormatter
{
    function formatError(
        int $status,
        string $title,
        string $detail,
        array $errors = [],
    ): array {
        return [
            'status' => $status,
            'title' => $title,
            'detail' => $detail,
            'errors' => $errors
        ];
    }
}