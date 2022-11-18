<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Imports\CategoryImport;
use App\Models\Category;
use App\Models\Shop;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
{
    const NAME = 'name';
    const SHOP_ID = 'shop_id';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $categories = [];

        if ($shop->categories->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->categories as $category) {
                    array_push($categories, $category);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->categories as $category) {
                        array_push($categories, $category);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => CategoryResource::collection($categories), "total" => count($categories)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $name = trim($request->get(self::NAME));

                    $category = new Category();
                    $category->name = $name;
                    $category->shop_id = $shop_id;

                    $category->save();

                    return jsend_success(new CategoryResource($category), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Category::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Category::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $category->shop_id) {
                return jsend_success(new CategoryResource($category));
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, Category $category)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $name = trim($request->get(self::NAME));

                    $category->name = $name;
                    $category->shop_id = $shop_id;

                    $category->save();

                    return jsend_success(new CategoryResource($category), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Category::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Category::class)]), [
                $ex->getCode(),
                ErrorType::UPDATE_ERROR,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $category->shop_id) {
                    $category->delete();

                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(Region::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }

    public function import()
    {
        Excel::import(new CategoryImport, request()->file('file'));

        return jsend_success(['message' => 'Successfully imported!'], JsonResponse::HTTP_ACCEPTED);
    }
}
