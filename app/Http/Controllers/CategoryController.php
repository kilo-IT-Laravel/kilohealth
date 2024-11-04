<?php

namespace App\Http\Controllers;

use App\Http\Resources\Category\index;
use App\Http\Resources\Category\show;
use App\Http\Resources\CategoryResource;
use App\pagination\paginating;
use App\Repositories\Category\CategoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(CategoryInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }

    public function index(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $categories = $this->Repository->getAllCategories($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving categories',
                'data' => index::collection($categories),
                'meta' => $this->pagination->metadata($categories)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving categories', 'err' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $this->req->validate([
                'name' => 'required|max:255',
                'icon' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $category = $this->Repository->createCategory($this->req);
            return response()->json(['success' => true, 'message' => 'Successfully store category', 'data' => $category], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->Repository->getCategoryById($id);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving category',
                'data' => new show($category)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $this->req->validate([
                'name' => 'sometimes|string|max:255',
                'icon' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $this->Repository->updateCategory($id, $this->req);
            return response()->json(['success' => true, 'message' => 'Successfully updated category'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPopularCategory()
    {
        $take = $this->req->take ?? 10;
        try {
            $categories = $this->Repository->getPopularCategory($take);
            return response()->json([
                'success' => true,
                'message' => 'successfully retrieving categories',
                'data' => $categories
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $category = $this->Repository->getCategoryBySlug($slug);
            return response()->json([
                'success' => true,
                'message' => 'successfully retrieved category',
                'data' => new show($category)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->Repository->deleteCategory($id);
            return response()->json(['success' => true, 'message' => 'Successfully deleted category'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $this->Repository->restoreCategory($id);
            return response()->json(['success' => true, 'message' => 'Category restored successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->Repository->forceDeleteCategory($id);
            return response()->json(['success' => true, 'message' => 'successfully permenantly deleted category'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $trashedCategories = $this->Repository->getTrashedCategories($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving soft deleted categories',
                'data' => index::collection($trashedCategories),
                'meta' => $this->pagination->metadata($trashedCategories)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
