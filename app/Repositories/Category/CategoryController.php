<?php

namespace App\Repositories\Category;

use App\Events\Categories\CategoryCreated;
use App\Events\Categories\CategoryDeleted;
use App\Events\Categories\CategoryUpdated;
use App\Models\categorie;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController implements CategoryInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function getAllCategories(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return categorie::withCount(['topics'])
                ->when(
                    $search ?? null,
                    fn($query, $search) =>
                    $query->where(
                        fn($q) =>
                        $q->where('name', 'LIKE', "%{$search}%")
                    )
                )
                ->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving categories');
        }
    }

    public function getCategoryById(int $id): ?categorie
    {
        try {
            return categorie::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving category');
        }
    }

    public function getPopularCategory(int $limit = 10): Collection
    {
        try {
            return categorie::select('id', 'name', 'slug', 'icon')->withCount([
                'posts as total_views' => function ($query) {
                    $query->select(DB::raw('SUM(views)'));
                },
                'posts as total_likes' => function ($query) {
                    $query->select(DB::raw('SUM(likes)'));
                },
            ])
            ->orderByRaw('total_views + total_likes DESC')
            ->take($limit)
            ->get();
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving popular category');
        }
    }

    public function createCategory(Request $req): categorie
    {
        try {
            $data = [
                'name' => $req->name,
                'slug' => Str::slug($req->name),
                'icon' => $req->hasFile('icon') ? $req->file('icon')->store('categories', 's3') : null
            ];

            $category = categorie::create($data);
            $this->logService->log(Auth::id(), 'created_category', categorie::class, $category->id, json_encode($data));
            event(new CategoryCreated($category->id));
            return $category;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error creating category');
        }
    }

    public function updateCategory(int $id, Request $req): bool
    {
        try {


            $category = categorie::findOrFail($id);

            $data = [
                'name' => $req->name,
                'slug' => Str::slug($req->name),
            ];

            if ($req->hasFile('icon')) {
                if ($category->icon) {
                    Storage::disk('s3')->delete($category->icon);
                }
                $data['icon'] = $req->file('icon')->store('categories', 's3');
            }

            $data = array_filter($data);

            $updated = $category->update($data);
            if ($updated) {
                $this->logService->log(Auth::id(), 'updated_category', categorie::class, $id, json_encode($data));
                event(new CategoryUpdated($category));
            }
            return $updated;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error updating category');
        }
    }

    public function getCategoryBySlug(string $slug): ?categorie
    {
        try {
            return categorie::where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving category by slug');
        }
    }

    public function deleteCategory(int $id): bool
    {
        try {
            $category = categorie::findOrFail($id);
            $deleted = $category->delete();
            if ($deleted) {
                $this->logService->log(Auth::id(), 'deleted_category', categorie::class, $id, null);
                event(new CategoryDeleted($id));
            }
            return $deleted;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error deleting category');
        }
    }

    public function restoreCategory(int $id): bool
    {
        try {
            $restored = categorie::withTrashed()->findOrFail($id)->restore();
            if ($restored) {
                $this->logService->log(Auth::id(), 'restored_category', categorie::class, $id, null);
            }
            return $restored;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Error restoring category: ' . $e->getMessage());
            throw new Exception('Error restoring category');
        }
    }

    public function forceDeleteCategory(int $id): bool
    {
        try {
            $category = categorie::withTrashed()->findOrFail($id);

            $dataToDelete = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'slug' => $category->slug
            ];

            $forceDeleted = $category->forceDelete();

            if ($forceDeleted) {

                $this->logService->log(Auth::id(), 'force_deleted_category', categorie::class, $id, json_encode([
                    'model' => get_class($category),
                    'data' => $dataToDelete
                ]));
            }
            return $forceDeleted;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Error force deleting category: ' . $e->getMessage());
            throw new Exception('Error force deleting category');
        }
    }

    public function getTrashedCategories(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return categorie::withCount(['topics'])->onlyTrashed()->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('name', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving trashed categories: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed categories');
        }
    }
}
