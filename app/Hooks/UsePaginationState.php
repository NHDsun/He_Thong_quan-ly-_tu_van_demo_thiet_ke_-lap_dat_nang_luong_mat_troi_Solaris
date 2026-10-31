<?php
declare(strict_types=1);

namespace App\Hooks;

final class UsePaginationState
{
    public static function make(int $total, int $page, int $perPage = 20): array
    {
        $perPage = max(1, min(20, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($pages, $page));
        return ['total' => $total, 'page' => $page, 'pages' => $pages,
            'limit' => $perPage, 'offset' => ($page - 1) * $perPage];
    }
}
