<?php

namespace App\Http\Controllers;

use App\Http\Resources\Category\index as CategoryIndex;
use App\Http\Resources\Topics\index;
use App\Http\Resources\Topics\popularTopic;
use App\Http\Resources\Topics\show;
use App\pagination\paginating;
use App\Repositories\Topics\TopicInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TopicController extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(TopicInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }

    public function index(): JsonResponse
    {
        try {
            $topics = $this->Repository->getAllTopics();
            return response()->json([
                'success' => true,
                'message' => 'successfully retrieving topics data',
                'data' => index::collection($topics),
                'meta' => $this->pagination->metadata($topics)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|max:255',
                'category_id' => 'required|exists:categories,id',
            ]);

            $topic = $this->Repository->createTopic($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Successfully store topic data',
                'data' => $topic
            ], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $topic = $this->Repository->getTopicById($id);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved topic data',
                'data' => new show($topic)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Topic not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving topic', 'err' => $e->getMessage()], 500);
        }
    }

    public function popularTopics(): JsonResponse
    {
        try {
            $topics = $this->Repository->getPopularTopics(10, 30);
            return response()->json([
                'success' => true,
                'message' =>
                'Successfully retrieved popular topics',
                'data' => popularTopic::collection($topics),
                'meta' => $this->pagination->metadata($topics)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'sometimes|max:255',
                'categorie_id' => 'sometimes|exists:categories,id',
            ]);

            $this->Repository->updateTopic($id, $validatedData);
            $topic = $this->Repository->getTopicById($id);
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated topic data',
                'data' => $topic
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->Repository->deleteTopic($id);
            return response()->json(['success' => true, 'message' => 'successfully deleted topic data'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getByCategory(int $categoryId): AnonymousResourceCollection|JsonResponse
    {
        try {
            $search = $this->req->search;
            $perPage = $this->req->per_page ?? 1;

            $topics = $this->Repository->getTopicsByCategory($search, $perPage, $categoryId);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving topics',
                'data' => index::collection($topics),
                'meta' => $this->pagination->metadata($topics)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $this->Repository->restoreTopic($id);
            return response()->json(['success' => true, 'message' => 'Topic restored successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->forceDeleteTopic($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Topic not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'successfully permenantly deleted topic'], 204);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $trashedCategories = $this->Repository->getTrashedTopics($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving soft deleted topic',
                'data' => index::collection($trashedCategories),
                'metadata' => $this->pagination->metadata($trashedCategories)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
