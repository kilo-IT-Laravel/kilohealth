<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostPhotos\index;
use App\pagination\paginating;
use App\Repositories\postPhotos\postPhotosInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class postPhotosController extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(postPhotosInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }

    public function index(int $id): JsonResponse
    {
        try {
            $perPage = $this->req->per_page ?? 5;
            $medias = $this->Repository->getMediaByPost($id, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully get media',
                'data' => index::collection($medias),
                'meta' => $this->pagination->metadata($medias)
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $media = $this->Repository->getMediaById($id);
            return response()->json(['success' => true, 'message' => 'Successfully get media', 'media' => new index($media)], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(int $id): JsonResponse
    {
        try {
            $this->req->validate([
                'file.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $arr = $this->Repository->uploadMedia($this->req, $id);
            return response()->json(['success' => true, 'message' => 'Successfully uploaded' , 'data'=>$arr], 201);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => $e->getMessage() , 'errors' => $e->errors()] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $this->req->validate([
                'file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'post_id' => 'sometimes|int|exists:posts,id'
            ]);

            $this->Repository->updateMedia($this->req, $id);

            return response()->json(['success' => true, 'message' => 'Successfully updated'], 200);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => $e->getMessage() , 'errors' => $e->errors()] , 422); 
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->Repository->deleteMedia($id);
            return response()->json(['success' => true, 'message' => 'Successfully deleted media'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id)
    {
        try {
            $this->Repository->restoreMedia($id);
            return response()->json(['success' => true, 'message' => 'Successfully restored media'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id) {
        try{
            $this->Repository->forceDeleteMedia($id);
            return response()->json(['success' => true, 'message' => 'Successfully permenantly deleted media'], 204);
        }catch(ModelNotFoundException $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }catch(Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function trashed()
    {
        try {
            $perPage = $this->req->per_page ?? 10;
            $search = $this->req->search;

            $medias = $this->Repository->getTrashedMedia($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully get trashed media',
                'data' => index::collection($medias),
                'meta' => $this->pagination->metadata($medias)
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
