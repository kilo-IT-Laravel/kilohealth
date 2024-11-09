<?php

namespace App\Repositories\PostViews;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostViewInterface
{
    public function getViewCount(int $postId): int;
    public function getViewsByPost(int $postId , string $search = null , int $perPage = 10): LengthAwarePaginator;
    public function getViewsByUser(int $userId , string $search = null , int $perPage = 10): LengthAwarePaginator;
    public function hasUserViewedPost(int $postId, int $userId): bool;
}
