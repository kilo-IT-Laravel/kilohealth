<?php

namespace App\Repositories\Posts;

use App\Models\post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostInterface
{
    public function getAllPosts(Request $req, int $perPage): LengthAwarePaginator;
    public function getPostById(int $id): ?post;
    public function displayPostPhotosById(int $postId, int $perPage = 10): LengthAwarePaginator;
    public function getPostByIdForPublic(int $id , ?int $userId): ?array;
    public function createPost(Request $req): post;
    public function updatePost(int $id, Request $req): bool;
    public function deletePost(int $id): bool;
    public function getPostsByCategory(int $categoryId, int $perPage): LengthAwarePaginator;
    public function getPostsByAuthor(int $authorId, int $perPage): LengthAwarePaginator;
    public function getLikesCount(int $postId): int;
    public function publishPost(int $postId): bool;
    public function unpublishPost(int $postId): bool;
    public function getPublishedPosts(Request $req, int $perPage): LengthAwarePaginator;
    public function getTrashedPosts(Request $req, int $perPage): LengthAwarePaginator;
    public function restorePost(int $postId): bool;
    public function forceDeletePost(int $postId): bool;
    public function getRelatedPosts(int $postId, int $limit = 3): Collection;
    public function getPopularPosts(int $limit = 10 , int $days = 30): Collection;
    public function toggleLike(int $postId , int $userId):bool;
}
