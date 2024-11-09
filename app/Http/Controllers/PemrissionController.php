<?php

namespace App\Http\Controllers;

use App\Http\Resources\permissions;
use App\pagination\paginating;
use App\Repositories\Permissions\PermissionInterface;
use App\Traits\ValidationErrorFormatter;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PemrissionController extends Controller
{
    use ValidationErrorFormatter;

    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(PermissionInterface $repository, Request $req)
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
            $permissions = $this->Repository->getPermissions($search, $perPage);
            return response()->json([
                'success' => true, 
                'message' => 'Successfully', 
                'data' => permissions::collection($permissions),
                'meta'=>$this->pagination->metadata($permissions)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $permission = $this->Repository->getPermissionsId($id);
            return response()->json(['success' => true, 'message' => 'Successfully', 'data' => $permission], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|string|max:255',
            ]);
            $permission = $this->Repository->createPermission($validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully', 'data' => $permission], 201);
        } catch(ValidationException $e){
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false , 'message' => 'Unsuccessfully' , 'errors' => $formattedErrors] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'sometimes|string|max:255'
            ]);
            $this->Repository->updatePermission($id, $validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch(ValidationException $e){
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false , 'message' => 'Unsuccessfully' , 'errors' => $formattedErrors] , 422); 
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->Repository->deletePermission($id);
            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $this->Repository->restorePermission($id);
            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->Repository->forceDeletePermission($id);
            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function displayTrashed()
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $trashedPermissions = $this->Repository->getTrashedPermissions($search, $perPage);
            return response()->json([
                'success' => true, 
                'message' => 'Successfuly', 
                'data' => permissions::collection($trashedPermissions),
                'meta'=> $this->pagination->metadata($trashedPermissions)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }
}
