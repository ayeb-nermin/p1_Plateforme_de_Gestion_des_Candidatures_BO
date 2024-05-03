<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('menu', 'MenuCrudController');
    Route::post('menu/{id}/enable/{state}', 'MenuCrudController@enable')->name('menu.enable');
    Route::get('menu_by_module/{id}', 'MenuCrudController@getMenuByModule')->name('menu_by_module');

    Route::post('update-locale', 'MyAccountController@updateLocale')->name('admin.update_locale');

    Route::crud('widget', 'WidgetCrudController');
    Route::post('widget/{id}/enable/{state}', 'WidgetCrudController@enable')->name('widget.enable');
    Route::get('getWidgetColumnsByModuleReference', 'WidgetCrudController@getWidgetColumnsByModuleReference')->name('getWidgetColumnsByModuleReference');

    Route::crud('banner', 'BannerCrudController');






























    
    Route::crud('email', 'EmailCrudController');
});