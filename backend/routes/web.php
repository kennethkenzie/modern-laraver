<?php

use App\Http\Controllers\AttributeSetsDashboardController;
use App\Http\Controllers\BrandsDashboardController;
use App\Http\Controllers\CategoriesDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportDashboardController;
use App\Http\Controllers\ImportDashboardController;
use App\Http\Controllers\OrdersDashboardController;
use App\Http\Controllers\ProfileDashboardController;
use App\Http\Controllers\ProductsDashboardController;
use App\Http\Controllers\ShippingDashboardController;
use App\Http\Controllers\StorefrontDashboardController;
use App\Http\Controllers\UnitsDashboardController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('web.login.page');
});

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('web.login.page');
Route::post('/auth/login', [WebAuthController::class, 'login'])->name('web.login');
Route::post('/auth/register', [WebAuthController::class, 'register'])->name('web.register');
Route::post('/auth/logout', [WebAuthController::class, 'logout'])->name('web.logout');

Route::middleware('web.dashboard')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/account/profile', [ProfileDashboardController::class, 'show'])->name('dashboard.account.profile');
    Route::patch('/dashboard/account/profile', [ProfileDashboardController::class, 'update'])->name('dashboard.account.profile.update');
    Route::get('/dashboard/account/settings', [ProfileDashboardController::class, 'settings'])->name('dashboard.account.settings');
    Route::patch('/dashboard/account/settings/password', [ProfileDashboardController::class, 'updatePassword'])->name('dashboard.account.settings.password');

    Route::get('/dashboard/orders', [OrdersDashboardController::class, 'index'])->name('dashboard.orders');
    Route::patch('/dashboard/orders/{id}/status', [OrdersDashboardController::class, 'updateStatus'])->name('dashboard.orders.status');
    Route::get('/dashboard/orders/{id}', [OrdersDashboardController::class, 'show'])->name('dashboard.orders.show');

    Route::get('/dashboard/shipping/configuration', [ShippingDashboardController::class, 'configuration'])->name('dashboard.shipping.configuration');
    Route::get('/dashboard/shipping/countries', [ShippingDashboardController::class, 'countries'])->name('dashboard.shipping.countries');
    Route::get('/dashboard/shipping/states', [ShippingDashboardController::class, 'states'])->name('dashboard.shipping.states');
    Route::get('/dashboard/shipping/cities', [ShippingDashboardController::class, 'cities'])->name('dashboard.shipping.cities');
    Route::get('/dashboard/shipping/pickup-locations', [ShippingDashboardController::class, 'pickupLocations'])->name('dashboard.shipping.pickup-locations');

    Route::get('/dashboard/storefront/header', [StorefrontDashboardController::class, 'header'])->name('dashboard.storefront.header');
    Route::patch('/dashboard/storefront/header', [StorefrontDashboardController::class, 'updateHeader'])->name('dashboard.storefront.header.update');
    Route::get('/dashboard/storefront/slider', [StorefrontDashboardController::class, 'slider'])->name('dashboard.storefront.slider');
    Route::patch('/dashboard/storefront/slider', [StorefrontDashboardController::class, 'updateSlider'])->name('dashboard.storefront.slider.update');

    Route::get('/dashboard/products', [ProductsDashboardController::class, 'index'])->name('dashboard.products');
    Route::get('/dashboard/products/add', [ProductsDashboardController::class, 'add'])->name('dashboard.products.add');
    Route::get('/dashboard/products/brands', [BrandsDashboardController::class, 'index'])->name('dashboard.products.brands');
    Route::get('/dashboard/products/categories', [CategoriesDashboardController::class, 'index'])->name('dashboard.products.categories');
    Route::get('/dashboard/products/units', [UnitsDashboardController::class, 'index'])->name('dashboard.products.units');
    Route::get('/dashboard/products/attribute-sets', [AttributeSetsDashboardController::class, 'index'])->name('dashboard.products.attribute-sets');
    Route::get('/dashboard/products/import', [ImportDashboardController::class, 'index'])->name('dashboard.products.import');
    Route::get('/dashboard/products/export', [ExportDashboardController::class, 'index'])->name('dashboard.products.export');
    Route::get('/dashboard/products/{id}', [ProductsDashboardController::class, 'show'])->name('dashboard.products.show');
});
