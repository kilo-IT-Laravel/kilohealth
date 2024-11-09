<?php

namespace App\Repositories\Category;

use App\Models\categorie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface CategoryInterface
{
    public function getAllCategories(string $search = null, int $perPage = 10): LengthAwarePaginator;
    public function getCategoryById(int $id): ?categorie;
    public function createCategory(Request $req): categorie;
    public function updateCategory(int $id, Request $req): bool;
    public function getCategoryBySlug(string $slug): ?categorie;
    public function getPopularCategory(int $limit = 10): Collection;
    public function deleteCategory(int $id): bool;
    public function restoreCategory(int $id): bool;
    public function forceDeleteCategory(int $id): bool;
    public function getTrashedCategories(string $search = null, int $perPage = 10): LengthAwarePaginator;
}
