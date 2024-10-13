<?php

use App\Events\testing;
use App\Http\Controllers\Auth\authenticate;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PemrissionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostViewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UploadMediaController;
use App\Http\Controllers\UserManagement;
use App\TestMethod\SwitchMe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [authenticate::class, 'register']);
Route::post('/login', [authenticate::class, 'login']);

Route::get('posts', [PostController::class, 'getPublished']);
Route::get('posts/{id}', [PostController::class, 'show']);
Route::post('posts/{id}/increment-views', [PostController::class, 'incrementViews']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/update_user', [UserManagement::class, 'UpdateUserInfo']);
    Route::post('/logout', [authenticate::class, 'logout']);

    Route::get('/testView', function () {
        $test = new SwitchMe();
        $result = $test->useSwitch('testMethod');
        return response()->json([
            'msg' => $result
        ]);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagement::class, 'ShowAll'])->middleware(['role:super_admin', 'permission:view_roles']);
        Route::get('/trashed', [UserManagement::class, 'ShowTrashUsers'])->middleware(['role:super_admin', 'permission:view_roles']);
        Route::get('/{user}', [UserManagement::class, 'GetUserDetails'])->middleware(['role:super_admin', 'permission:view_roles']);
        Route::delete('/{userId}/soft-delete', [UserManagement::class, 'SoftDeleteUser'])->middleware(['role:super_admin', 'permission:delete_roles']);
        Route::post('/{userId}/restore', [UserManagement::class, 'RestoreUser'])->middleware(['role:super_admin', 'permission:create_roles']);
        Route::delete('/{userId}/force-delete', [UserManagement::class, 'ForceDeleteUser'])->middleware(['role:super_admin', 'permission:delete_roles']);

        /////////////////// todo implement a rollback deletes function too -- assign to sopheak
        Route::get('/auditlog/{userId}', [UserManagement::class, 'getAuditLogs'])->middleware(['role:super_admin', 'permission:delete_roles']);
    });

    Route::prefix('roles')->group(function(){
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::put('/{role}/permissions', [UserManagement::class, 'UpdateRolePermissions'])->middleware(['role:super_admin', 'permission:edit_roles']);
        Route::put('/users/{user}/role', [UserManagement::class, 'UpdateUserRole'])->middleware(['role:super_admin', 'permission:edit_roles']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
        Route::post('/{id}/restore', [RoleController::class, 'restore']);
        Route::delete('/{id}/force', [RoleController::class, 'forceDelete']);
        Route::get('/trashed', [RoleController::class, 'displayTrashed']);
    });
    
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PemrissionController::class, 'index']);
        Route::get('/{id}', [PemrissionController::class, 'show']);
        Route::post('/', [PemrissionController::class, 'store']);
        Route::put('/{id}', [PemrissionController::class, 'update']);
        Route::delete('/{id}', [PemrissionController::class, 'destroy']);
        Route::post('/{id}/restore', [PemrissionController::class, 'restore']);
        Route::delete('/{id}/force', [PemrissionController::class, 'forceDelete']);
        Route::get('/trashed', [PemrissionController::class, 'displayTrashed']);
    });

    ////////////// heng visal routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::get('/slug/{slug}', [CategoryController::class, 'showBySlug']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::post('/{id}/restore', [CategoryController::class, 'restore']);
        Route::delete('/{id}/force', [CategoryController::class, 'forceDelete']);
        Route::get('/trashed', [CategoryController::class, 'trashed']);
    });

    Route::prefix('topics')->group(function () {
        Route::get('/', [TopicController::class, 'index']);
        Route::post('/', [TopicController::class, 'store']);
        Route::get('/{id}', [TopicController::class, 'show']);
        Route::put('/{id}', [TopicController::class, 'update']);
        Route::delete('/{id}', [TopicController::class, 'destroy']);
        Route::post('/{id}/restore', [TopicController::class, 'restore']);
        Route::delete('/{id}/force', [TopicController::class, 'forceDelete']);
        Route::get('/trashed', [TopicController::class, 'trashed']);
        Route::get('/categories/{category}/topics', [TopicController::class, 'getByCategory']);
    });

    /////////////// jung kook routes and lay vila routes
    Route::prefix('post')->group(function () {
        Route::post('posts', [PostController::class, 'store']);
        Route::put('posts/{id}', [PostController::class, 'update']);
        Route::delete('{id}', [PostController::class, 'destroy']);
        Route::post('{id}/restore', [PostController::class, 'restore']);
        Route::delete('{id}/force', [PostController::class, 'forceDelete']);
        Route::get('trashed', [PostController::class, 'trashed']);
        Route::post('posts/{id}/publish', [PostController::class, 'publish']);
        Route::post('posts/{id}/unpublish', [PostController::class, 'unpublish']);
        Route::post('posts/{id}/like', [PostController::class, 'like']);
        Route::delete('posts/{id}/like', [PostController::class, 'unlike']);

        Route::get('admin/posts', [PostController::class, 'index']); /// middleware should be added later on after finish testing
    });

    Route::prefix('post_views')->group(function () {
        Route::post('posts/{postId}/view', [PostViewController::class, 'recordView']);
        Route::get('posts/{postId}/views', [PostViewController::class, 'getViewsByPost']);
        Route::get('users/{userId}/views', [PostViewController::class, 'getViewsByUser']);
        Route::get('posts/{postId}/check-view', [PostViewController::class, 'checkUserViewedPost']);
    });

    Route::prefix('upload_media')->group(function () {
        Route::post('posts/{postId}/media', [UploadMediaController::class, 'upload']);
        Route::get('posts/{postId}/media', [UploadMediaController::class, 'getMediaByPost']);
        Route::delete('media/{mediaId}', [UploadMediaController::class, 'deleteMedia']);
        Route::get('media/{mediaId}', [UploadMediaController::class, 'getMedia']);
    });

    Route::prefix('site_settings')->group(function () {
        Route::get('settings', [SiteSettingController::class, 'index']);
        Route::get('settings/{key}', [SiteSettingController::class, 'show']);
        Route::put('settings/{key}', [SiteSettingController::class, 'update']);
        Route::post('settings', [SiteSettingController::class, 'store']);
        Route::delete('settings/{key}', [SiteSettingController::class, 'destroy']);
    });
});


//////////////////reverb
Route::post('/msg', function (Request $req) {
    $bruh = $req->message;
    event(new testing($bruh));
    return response()->json([
        'msg' => $bruh
    ]);
});

//Route::get('/read_image',function(){

    //$url = Storage::disk('s3')->temporaryUrl('images/qDzvxaOoXGMQCcxZ1WEXOC4dDDvPO1MQtMc0gYWK.jpg',now()->addHours(5));

    //return response()->json(['url' => $url]);
//});