<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BuyController;
use App\Http\Controllers\Api\V1\BuyCreditController;
use App\Http\Controllers\Api\V1\BuyPriceTrackController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CreditController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DamageItemController;
use App\Http\Controllers\Api\V1\DeleteController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\PriceController;
use App\Http\Controllers\Api\V1\ProfitController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\SalePriceTrackController;
use App\Http\Controllers\Api\V1\ShopController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::namespace('Api\V1')->group(function () {
    Route::prefix('v1')->group(function () {

        Route::post('io-register', [AuthController::class, 'register']);
        Route::post('io-login', [AuthController::class, 'login']);

        Route::middleware(['auth:api'])->group(function () {
            // Users
            Route::get('users/{shop}', [AuthController::class, 'userByShop']);
            Route::get('user', [AuthController::class, 'user']);

            // Shops
            Route::get('shops', [ShopController::class, 'index']);
            Route::post('shops', [ShopController::class, 'store']);
            Route::get('shops/{shop}', [ShopController::class, 'show']);
            Route::put('shops/{shop}', [ShopController::class, 'update']);
            Route::delete('shops/{shop}', [ShopController::class, 'destroy']);

            // Regions
            Route::get('regions/{shop?}', [RegionController::class, 'index']);
            Route::post('regions', [RegionController::class, 'store']);
            Route::get('region/{region}', [RegionController::class, 'show']);
            Route::put('regions/{region}', [RegionController::class, 'update']);
            Route::delete('regions/{region}', [RegionController::class, 'destroy']);

            // Regions
            Route::get('categories/{shop?}', [CategoryController::class, 'index']);
            Route::post('categories', [CategoryController::class, 'store']);
            Route::get('category/{category}', [CategoryController::class, 'show']);
            Route::put('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

            // Items
            Route::get('items/{shop?}', [ItemController::class, 'index']);
            Route::post('items', [ItemController::class, 'store']);
            Route::get('item/{item}', [ItemController::class, 'show']);
            Route::put('items/{item}', [ItemController::class, 'update']);
            Route::delete('items/{item}', [ItemController::class, 'destroy']);
            Route::get('low-items/{shop?}', [ItemController::class, 'lowItems']);

            // Prices
            Route::get('prices/{shop}/{region}', [PriceController::class, 'index']);
            Route::post('prices', [PriceController::class, 'store']);
            Route::get('prices/{price}', [PriceController::class, 'show']);
            Route::put('prices/{price}', [PriceController::class, 'update']);

            // Sale Price Tracks
            Route::get('sale-price-tracks/{shop}/{region}', [SalePriceTrackController::class, 'index']);

            // Buy Price Tracks
            Route::get('buy-price-tracks/{shop?}', [BuyPriceTrackController::class, 'index']);

            // Customers
            Route::get('customers/{shop?}', [CustomerController::class, 'index']);
            Route::get('customer/{customer}', [CustomerController::class, 'show']);
            Route::post('customers', [CustomerController::class, 'store']);
            Route::put('customers/{customer}', [CustomerController::class, 'update']);
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

            // Sales
            Route::get('sales/{shop?}', [SaleController::class, 'index']);
            Route::get('sale/{sale_record}', [SaleController::class, 'show']);
            Route::post('sales', [SaleController::class, 'store']);
            Route::delete('sales/{sale_record}', [SaleController::class, 'destroy']);
            Route::get('more-sales/{shop_id}', [SaleController::class, 'moreSaleItems']);

            // Credits
            Route::post('credits', [CreditController::class, 'store']);
            Route::delete('credits/{credit}', [CreditController::class, 'destroy']);

            // Merchants
            Route::get('merchants/{shop?}', [MerchantController::class, 'index']);
            Route::get('merchant/{merchant}', [MerchantController::class, 'show']);
            Route::post('merchants', [MerchantController::class, 'store']);
            Route::put('merchants/{merchant}', [MerchantController::class, 'update']);
            Route::delete('merchants/{merchant}', [MerchantController::class, 'destroy']);

            // Buys
            Route::get('buys/{shop?}', [BuyController::class, 'index']);
            Route::get('buy/{buy_record}', [BuyController::class, 'show']);
            Route::post('buys', [BuyController::class, 'store']);
            Route::delete('buys/{buy_record}', [BuyController::class, 'destroy']);

            //Buy Credits
            Route::post('buy-credits', [BuyCreditController::class, 'store']);
            Route::delete('buy-credits/{buy_credit}', [BuyCreditController::class, 'destroy']);

            // Expenses
            Route::get('expenses/{shop?}', [ExpenseController::class, 'index']);
            Route::get('expense/{expense}', [ExpenseController::class, 'show']);
            Route::post('expenses', [ExpenseController::class, 'store']);
            Route::put('expenses/{expense}', [ExpenseController::class, 'update']);
            Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy']);

            // Damage Items
            Route::get('damage-items/{shop?}', [DamageItemController::class, 'index']);
            Route::get('damage-item/{damage_item}', [DamageItemController::class, 'show']);
            Route::post('damage-items', [DamageItemController::class, 'store']);
            Route::delete('damage-items/{damage_item}', [DamageItemController::class, 'destroy']);
            Route::get('change-damage-items/{damage_item}', [DamageItemController::class, 'changeStatus']);

            // Summy
            Route::get('daily/{shop_id}', [SaleController::class, 'daily']);
            Route::get('monthly/{shop_id}', [SaleController::class, 'monthly']);
            Route::get('yearly/{shop_id}', [SaleController::class, 'yearly']);

            // Profit
            Route::get('profit/{shop_id}', [ProfitController::class, 'profit']);

            // Excel
            Route::post('items-import', [ItemController::class, 'import']);
            Route::post('categories-import', [CategoryController::class, 'import']);
            Route::post('prices-import', [PriceController::class, 'import']);

            // Delete
            Route::get('deletes', [DeleteController::class, 'destroy']);
        });

        if (App::environment('local')) {
            Route::get('routes', function () {
                $routes = [];

                foreach (Route::getRoutes()->getIterator() as $route) {
                    if (strpos($route->uri, 'api') !== false) {
                        $routes[] = $route->uri;
                    }
                }

                return response()->json($routes);
            });
        }
    });
});


Route::fallback(function () {
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact www.rcs-mm.com'
    ], 404);
});
