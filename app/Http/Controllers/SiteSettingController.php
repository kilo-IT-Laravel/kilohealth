<?php

namespace App\Http\Controllers;

use App\Http\Resources\SiteSettings\index;
use App\Http\Resources\SiteSettings\show;
use App\pagination\paginating;
use App\Repositories\SiteSettings\SiteSettingInterface;
use App\Services\SiteSettingsService;
use App\Traits\ValidationErrorFormatter;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SiteSettingController extends Controller
{
    use ValidationErrorFormatter;

    private Request $req;

    protected $Repository;

    protected $siteSettingsService;

    protected $pagination;

    public function __construct(SiteSettingInterface $repository, Request $req, SiteSettingsService $siteSettingsService)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->siteSettingsService = $siteSettingsService;
        $this->pagination = new paginating();
    }

    public function index(): JsonResponse
    {
        try {

            $search = $this->req->search;
            $key = $this->req->key;
            $perPage = $this->req->per_page ?? 10;

            $settings = $this->Repository->getAllSettings($search , $key, $perPage);
            return response()->json([
                'success' => true, 
                'message' => 'Successfully', 
                'data' => index::collection($settings),
                'meta' => $this->pagination->metadata($settings)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function show(string $key): JsonResponse
    {
        try {
            $setting = $this->Repository->getSetting($key);
            if (!$setting) {
                return response()->json(['message' => 'Setting not found'], 404);
            }
            return response()->json([
                'success' => true, 
                'message' => 'Successfully', 
                'data' => new show($setting)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Setting not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function update(string $key): JsonResponse
    {
        try {
            $this->req->validate([
                'name' => 'sometimes|string',
                'value' => 'sometimes|string',
                'input_type' => 'sometimes|in:text,number,date,boolean,image',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $this->Repository->updateSetting($key, $this->req);

            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch(ValidationException $e){
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false , 'message' => 'Unsuccessfully' , 'errors' => $formattedErrors] , 422); 
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Setting not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $this->req->validate([
                'key' => 'required|string|unique:site_settings,key',
                'name' => 'required|string',
                'value' => 'required_without:image|string',
                'input_type' => 'required|in:text,number,date,boolean,image',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $setting = $this->Repository->createSetting($this->req);
            return response()->json(['success' => true, 'message' => 'Successfully', 'data' => $setting], 201);
        } catch(ValidationException $e){
            $formattedErrors = $this->formatValidationError($e->errors());
            return response()->json(['success' => false , 'message' => 'Unsuccessfully' , 'errors' => $formattedErrors] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function destroy(string $key): JsonResponse
    {
        try {
            $deleted = $this->Repository->deleteSetting($key);
            if (!$deleted) {
                return response()->json(['message' => 'Setting not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function homepageSettings(): JsonResponse
    {
        try {
            $settings = $this->siteSettingsService->getAllSettings();
            return response()->json(['success' => true, 'message' => 'Successfully', 'data' => $settings]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }

    public function homepageSetting(string $key): JsonResponse
    {
        try {
            $value = $this->siteSettingsService->getSetting($key);
            if ($value === null) {
                return response()->json(['message' => 'Setting not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully' , 'data' => [$key => $value]] , 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server errors'], 500);
        }
    }
}
