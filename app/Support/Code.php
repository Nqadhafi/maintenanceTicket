<?php

namespace App\Support;

class Code
{
    public static function ticket(): string
    {
        // Dev-friendly: TCK-YYYY-RAND4
        return 'TCK-' . now()->format('Y') . '-' . random_int(1000, 9999);
    }

    public static function workOrder(): string
    {
        return 'WO-' . now()->format('Y') . '-' . random_int(1000, 9999);
    }
}
