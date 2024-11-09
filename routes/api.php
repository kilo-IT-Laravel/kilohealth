<?php

use App\Events\testing;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\authenticate;
use App\Http\Controllers\Auth\passwordResetController;
use App\Http\Controllers\Auth\verificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PemrissionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\postPhotosController;
use App\Http\Controllers\PostViewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UploadMediaController;
use App\Http\Controllers\UserManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [authenticate::class, 'register']);
Route::post('/login', [authenticate::class, 'login']);
Route::post('/verify-email', [verificationController::class, 'verifyEmail']);
Route::post('/resend-verification-otp', [verificationController::class, 'resendVerificationOTP']);
Route::post('/forgot-password', [passwordResetController::class, 'forgotPassword']);
Route::post('/resend-reset-password-otp', [passwordResetController::class, 'resendResetPasswordOTP']);
Route::post('/reset-password', [passwordResetController::class, 'resetPassword']);
/*
|--------------------------------------------------------------------------
| Frontend Public Routes
|--------------------------------------------------------------------------
*/

// Public Content Routes
Route::prefix('posts')->group(function () {
    // Public viewing routes - no auth required
    Route::get('/', [PostController::class, 'getPublished']);
    Route::get('/popular', [PostController::class, 'popularPosts']);
    Route::get('/{postId}', [PostController::class, 'publicShow'])->middleware(['setUserId']);
    Route::get('/related/{postId}', [PostController::class, 'getRelatedPosts']);
    Route::get('/{postId}/view-count', [PostViewController::class, 'getViewCount']);

    // Auth required actions
    Route::post('/{id}/like', [PostController::class, 'toggleLikes'])->middleware('auth:sanctum');
});

// Public Categories & Topics
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/popular', [CategoryController::class, 'getPopularCategory']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/slug/{slug}', [CategoryController::class, 'showBySlug']);
});

Route::prefix('topics')->group(function () {
    Route::get('/', [TopicController::class, 'index']);
    Route::get('/popular', [TopicController::class, 'popularTopics']);
    Route::get('/{id}', [TopicController::class, 'show']);
    Route::get('/categories/{category}/topics', [TopicController::class, 'getByCategory']);
});

// Public Settings
Route::get('/all-settings', [SiteSettingController::class, 'homepageSettings']);
Route::get('/setting/{key}', [SiteSettingController::class, 'homepageSetting']);

/*
|--------------------------------------------------------------------------
| Frontend Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // User Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/user', [authenticate::class, 'getUser']);
        Route::put('/update', [authenticate::class, 'UpdateUserInfo']);
        Route::put('/update-password', [authenticate::class, 'changePassword']);
        Route::post('/logout', [authenticate::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Backend Routes (Admin Panel)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Dashboard Analytics
    Route::get('/dashboard', [AnalyticsController::class, 'getDashboardAnalytics'])
        ->middleware('role:super_admin|admin');

    // User Management Routes
    Route::prefix('users')->middleware(['role:super_admin|admin'])->group(function () {
        Route::get('/', [UserManagement::class, 'ShowAll'])->middleware('permission:view_users');
        Route::get('/trashed', [UserManagement::class, 'ShowTrashUsers'])->middleware(['role:super_admin', 'permission:view_delete_users']);
        Route::get('/{user}', [UserManagement::class, 'GetUserDetails'])->middleware('permission:view_users');
        Route::delete('/{userId}/soft-delete', [UserManagement::class, 'SoftDeleteUser'])->middleware(['role:super_admin', 'permission:delete_users']);
        Route::post('/{userId}/restore', [UserManagement::class, 'RestoreUser'])->middleware(['role:super_admin', 'permission:restore_users']);
        Route::delete('/{userId}/force-delete', [UserManagement::class, 'ForceDeleteUser'])->middleware(['role:super_admin', 'permission:force_delete_users']);
        Route::put('/admin-update-user', [UserManagement::class, 'adminUpdateUser'])->middleware('role:super_admin|admin');
        Route::get('/auditlog/{userId}', [UserManagement::class, 'getAuditLog'])->middleware(['role:super_admin', 'permission:view_log']);
        Route::post('/{userId}/rollback-data', [UserManagement::class, 'rollbackDelete'])->middleware('role:super_admin');
    });

    // Role Management Routes
    Route::prefix('roles')->middleware(['role:super_admin|admin'])->group(function () {
        Route::get('/trashed', [RoleController::class, 'displayTrashed'])->middleware(['role:super_admin', 'permission:view_delete_roles']);
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:view_roles');
        Route::get('/{id}', [RoleController::class, 'show'])->middleware('permission:view_roles');
        Route::post('/', [RoleController::class, 'store'])->middleware(['role:super_admin', 'permission:create_roles']);
        Route::put('/{id}', [RoleController::class, 'update'])->middleware(['role:super_admin', 'permission:update_roles']);
        Route::put('/{id}/permissions', [UserManagement::class, 'UpdateRolePermissions'])->middleware(['role:super_admin', 'permission:edit_roles']);
        Route::put('/users/{id}/role', [UserManagement::class, 'UpdateUserRole'])->middleware(['role:super_admin', 'permission:edit_users']);
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware(['role:super_admin', 'permission:delete_roles']);
        Route::post('/{id}/restore', [RoleController::class, 'restore'])->middleware(['role:super_admin', 'permission:restore_roles']);
        Route::delete('/{id}/force', [RoleController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_roles']);
    });

    // Permission Management Routes
    Route::prefix('permissions')->middleware('role:super_admin')->group(function () {
        Route::get('/trashed', [PemrissionController::class, 'displayTrashed'])->middleware('permission:view_delete_permissions');
        Route::get('/', [PemrissionController::class, 'index'])->middleware('permission:view_permissions');
        Route::get('/{id}', [PemrissionController::class, 'show'])->middleware('permission:view_permissions');
        Route::post('/', [PemrissionController::class, 'store'])->middleware('permission:create_permissions');
        Route::put('/{id}', [PemrissionController::class, 'update'])->middleware('permission:update_permissions');
        Route::delete('/{id}', [PemrissionController::class, 'destroy'])->middleware('permission:delete_permissions');
        Route::post('/{id}/restore', [PemrissionController::class, 'restore'])->middleware('permission:restore_permisssions');
        Route::delete('/{id}/force', [PemrissionController::class, 'forceDelete'])->middleware('permission:force_delete_permissions');
    });

    // Content Management Routes

    // Categories
    Route::prefix('categorie')->group(function () {
        Route::get('/trashed', [CategoryController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:view_items');
        Route::post('/', [CategoryController::class, 'store'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::get('/{id}', [CategoryController::class, 'show'])->middleware('permission:view_items');
        Route::put('/{id}', [CategoryController::class, 'update'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::get('/slug/{slug}', [CategoryController::class, 'showBySlug'])->middleware('permission:view_items');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore', [CategoryController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [CategoryController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
    });

    // Topics
    Route::prefix('topic')->group(function () {
        Route::get('/trashed', [TopicController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/', [TopicController::class, 'index'])->middleware('permission:view_items');
        Route::post('/', [TopicController::class, 'store'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::get('/{id}', [TopicController::class, 'show'])->middleware('permission:view_items');
        Route::put('/{id}', [TopicController::class, 'update'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::delete('/{id}', [TopicController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore', [TopicController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [TopicController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
        Route::get('/categories/{category}/topics', [TopicController::class, 'getByCategory'])->middleware('permission:view_items');
    });

    // Posts
    Route::prefix('post')->group(function () {
        Route::get('/trashed', [PostController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/', [PostController::class, 'index'])->middleware(['role:super_admin|admin', 'permission:view_items']);
        Route::post('/', [PostController::class, 'store'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::get('/{id}', [PostController::class, 'show'])->middleware('permission:view_items');
        Route::get('/{id}/post-photos', [PostController::class, 'getPostPhotosById'])->middleware('role:super_admin|admin|arthur');
        Route::put('/{id}', [PostController::class, 'update'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::delete('/{id}', [PostController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore', [PostController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [PostController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
        Route::post('/{id}/publish', [PostController::class, 'publish'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::post('/{id}/unpublish', [PostController::class, 'unpublish'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
    });

    // Post Views
    Route::prefix('post-views')->group(function () {
        Route::get('/posts/{postId}/views', [PostViewController::class, 'getViewsByPost'])->middleware(['role:super_admin|admin|arthur', 'permission:view_items']);
        Route::get('/users/{userId}/views', [PostViewController::class, 'getViewsByUser'])->middleware(['role:super_admin|admin', 'permission:view_users']);
        Route::get('/posts/{postId}/check-view', [PostViewController::class, 'checkUserViewedPost'])->middleware('permission:view_items');
        Route::get('/posts/{postId}/view-count', [PostViewController::class, 'getViewCount'])->middleware('permission:view_items');
    });

    // Media Management
    Route::prefix('upload-media')->group(function () {
        Route::get('/', [UploadMediaController::class, 'index'])->middleware(['role:super_admin|admin', 'permission:view_items']);
        Route::get('/trashed', [UploadMediaController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::post('/', [UploadMediaController::class, 'upload'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::delete('/{id}', [UploadMediaController::class, 'deleteMedia'])->middleware(['role:super_admin|admin|arthur', 'permission:delete_items']);
        Route::get('/{id}', [UploadMediaController::class, 'getMedia'])->middleware('permission:view_items');
        Route::get('/{id}/posts', [UploadMediaController::class, 'getPostByMediaId'])->middleware(['role:super_admin|admin|arthur']);
        Route::post('/{id}/restore', [UploadMediaController::class, 'restored'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [UploadMediaController::class, 'forceDeleted'])->middleware(['role:super_admin', 'permission:force_delete_items']);
    });

    // Post Photos
    Route::prefix('post-photos')->group(function () {
        Route::get('/trashed', [postPhotosController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/{id}/photos', [postPhotosController::class, 'index'])->middleware('permission:view_items');
        Route::post('/{id}', [postPhotosController::class, 'store'])->middleware(['role:super_admin|admin', 'permission:create_items']);
        Route::get('/{id}', [postPhotosController::class, 'show'])->middleware('permission:view_items');
        Route::put('/{id}', [postPhotosController::class, 'update'])->middleware(['role:super_admin|admin', 'permission:update_items']);
        Route::delete('/{id}', [postPhotosController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore', [postPhotosController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [postPhotosController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
    });
    // Site Settings
    Route::prefix('site-settings')->group(function () {
        Route::get('/', [SiteSettingController::class, 'index'])->middleware(['role:super_admin', 'permission:view_items']);
        Route::get('/{key}', [SiteSettingController::class, 'show'])->middleware(['role:super_admin', 'permission:view_items']);
        Route::put('/{key}', [SiteSettingController::class, 'update'])->middleware(['role:super_admin', 'permission:update_items']);
        Route::post('/', [SiteSettingController::class, 'store'])->middleware(['role:super_admin', 'permission:create_items']);
        Route::delete('/{key}', [SiteSettingController::class, 'destroy'])->middleware(['role:super_admin', 'permission:delete_items']);
    });
});


// Route::get('/testView', function () {
//     $test = new SwitchMe();
//     $result = $test->useSwitch('testMethod');
//     return response()->json([
//         'msg' => $result
//     ]);
// });


//////////////////reverb
// Route::post('/msg', function (Request $req) {
//     $bruh = $req->message;
//     event(new testing($bruh));
//     return response()->json([
//         'msg' => $bruh
//     ]);
// });

//Route::get('/read_image',function(){

//$url = Storage::disk('s3')->temporaryUrl('images/qDzvxaOoXGMQCcxZ1WEXOC4dDDvPO1MQtMc0gYWK.jpg',now()->addHours(5));

//return response()->json(['url' => $url]);
//});

// Route::get('/test', function () {
//     return [
//         'data' => Categorie::withCount(['posts as total_likes' => function ($query) {
//             $query->select(DB::raw('sum(likes)'));
//         }])->withSum('posts', 'views')
//             ->with(['posts' => function ($query) {
//                 $query->select('id', 'title', 'category_id', DB::raw('sum(views) as total_views'))
//                     ->where('title', 'chainsaw man is awsome <3')
//                     ->with('views')->groupBy('id', 'title', 'category_id');
//             }])->select(DB::raw('min(id) as id'), 'name')->groupBy('name')->get()
//     ];
// });