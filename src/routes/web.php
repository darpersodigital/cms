<?php

use Illuminate\Support\Facades\Route;
use Darpersodigital\Cms\Controllers\CmsController;
use Darpersodigital\Cms\Controllers\PostTypesController;
use Darpersodigital\Cms\Controllers\PostTypeController;
use Darpersodigital\Cms\Controllers\LanguagesController;
use Darpersodigital\Cms\Controllers\seo\SitemapsController;
use Darpersodigital\Cms\Controllers\seo\RobotsTXTController;
use Darpersodigital\Cms\Controllers\seo\SeoAIController;
use Darpersodigital\Cms\Controllers\seo\GoogleAnalyticsController;
use Darpersodigital\Cms\Controllers\admin\AdminRolesController;
use Darpersodigital\Cms\Controllers\admin\AdminsController;
use Darpersodigital\Cms\Controllers\FormsController;
use Darpersodigital\Cms\Models\PostType;


Route::prefix(config('cms_config.route_path_prefix'))->middleware(['web', 'admin'])->group(function () {
    Route::get('/', [CmsController::class,'redirectToLoginForm']);
    Route::get('login', [CmsController::class,'showLoginForm'])->name('admin-login');
});


Route::prefix(config('cms_config.route_path_prefix'))->middleware(['web'])->group(function () {
    Route::post('login', [CmsController::class,'login']);
});

Route::get('/asset', [CmsController::class,'getAssets']);



Route::prefix(config('cms_config.route_path_prefix'))->middleware(['web', 'admin'])->group(function () {
    Route::get('dashboard', [CmsController::class,'showHome'])->name('admin-dashboard');


    Route::post('/analyze-seo', [SeoAIController::class, 'analyze'])->name('analyze-seo');

    // Admin Routes
    Route::resource('admins', AdminsController::class);
    Route::resource('admin-roles', AdminRolesController::class);

    // Auth
    Route::get('logout', [CmsController::class,'logout'])->name('admin-logout');

    // Profile Routes
    Route::get('profile', [AdminsController::class,'showProfile'])->name('admin-profile');
    Route::get('profile/edit', [AdminsController::class,'showEditProfile'])->name('admin-profile-edit');
    Route::post('profile/edit', [AdminsController::class,'editProfile']);


    // site configurations 
    Route::resource('languages', LanguagesController::class);
    Route::resource('sitemaps', SitemapsController::class);
    Route::resource('robots-txts', RobotsTXTController::class);
    Route::resource('google-analytics', GoogleAnalyticsController::class);
    Route::get('google-analytics/guide-assets/{filename}', [GoogleAnalyticsController::class, 'guideAsset'])
        ->where('filename', '[^/]+')
        ->name('google-analytics.guide-asset');


    // CMS Pages
    Route::get('/post-types', [PostTypesController::class, 'index']);
    Route::get('/post-types/icons', [PostTypesController::class,'icons']);



    Route::get('/post-types/icons', [PostTypesController::class,'icons']);
    Route::get('/post-types/order', [PostTypesController::class,'orderIndex']);
    Route::get('/post-types', [PostTypesController::class,'index'])->name('post-types');
    Route::get('/post-types/order', [PostTypesController::class,'order']);
    Route::get('/post-types/create', [PostTypesController::class,'create']);
    Route::get('/post-types/create/custom', [PostTypesController::class,'createCustom']);
    Route::get('/post-types/{id}/edit', [PostTypesController::class,'edit']);
    Route::get('/post-types/custom/{id}/edit', [PostTypesController::class,'editCustom']);
    Route::post('/post-types/order', [PostTypesController::class,'orderSubmit']);
    Route::post('/post-types', [PostTypesController::class,'store']);
    Route::post('/post-types/custom', [PostTypesController::class,'storeCustom']);
    Route::post('/post-types/order', [PostTypesController::class,'saveOrder']);
    Route::put('/post-types/{id}', [PostTypesController::class,'update']);
    Route::put('/post-types/custom/{id}', [PostTypesController::class,'updateCustom']);
    Route::delete('/post-types/{id}', [PostTypesController::class,'destroy']);


    foreach (PostType::where('custom_page', 0)->where('custom_crud',0)->get() as $postType) {
       
        Route::get('/' . $postType->route, [PostTypeController::class,'index'])->defaults('route', $postType->route);

        Route::get('/' . $postType->route . '/order', [PostTypeController::class,'order'])->defaults('route', $postType->route);
        Route::get('/' . $postType->route . '/create', [PostTypeController::class,'create'])->defaults('route', $postType->route);
        Route::get('/' . $postType->route . '/{id}', [PostTypeController::class,'show'])->defaults('route', $postType->route);
        Route::get('/' . $postType->route . '/{id}/edit', [PostTypeController::class,'edit'])->defaults('route', $postType->route);
        Route::post('/' . $postType->route, [PostTypeController::class,'store'])->defaults('route', $postType->route);
        Route::put('/' . $postType->route . '/order', [PostTypeController::class,'changeOrder'])->defaults('route', $postType->route);
        Route::put('/' . $postType->route . '/{id}', [PostTypeController::class,'update'])->defaults('route', $postType->route);
        // Both routes are the same but have different method for roles purposes
        Route::delete('/' . $postType->route , [PostTypeController::class,'destroy'])->defaults('route',$postType->route)->defaults('id','');
        Route::delete('/' . $postType->route . '/{id}', [PostTypeController::class,'destroy'])->defaults('route',$postType->route)->defaults('id','');
        if($postType->is_form){
            Route::get('/formMessages' .'/'. $postType->route . '/star/{id}' , [FormsController::class,'star'])->defaults('route', $postType->route);
            Route::get('/formMessages' .'/'. $postType->route . '/read/{id}' , [FormsController::class,'read'])->defaults('route', $postType->route);
        }

    }

});
