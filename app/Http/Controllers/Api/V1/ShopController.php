<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    const NAME = 'name';
    const ADDRESS = 'address';
    const EMPLOYEES = 'employees';
    const PHONE_NO_ONE = 'phone_no_one';
    const PHONE_NO_TWO = 'phone_no_two';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $shops = $user->shops;
        return response()->json(["status" => "success", "data" => ShopResource::collection($shops), "total" => count($shops)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\ShopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopRequest $request)
    {
        try {
            $name = trim($request->get(self::NAME));
            $address = trim($request->get(self::ADDRESS));
            $employees = trim($request->get(self::EMPLOYEES));
            $phone_no_one = trim($request->get(self::PHONE_NO_ONE));
            $phone_no_two = trim($request->get(self::PHONE_NO_TWO));

            $shop = new Shop();
            $shop->name = $name;
            $shop->address = $address;
            $shop->employees = $employees;
            $shop->phone_no_one = $phone_no_one;

            if ($request->has(self::PHONE_NO_TWO)) {
                $shop->phone_no_two = $phone_no_two;
            }

            $shop->save();

            $user = Auth::user();

            $user->shops()->attach($shop->id);

            return jsend_success(new ShopResource($shop), JsonResponse::HTTP_CREATED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Shop::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Shop::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $singleShop) {
            if ($singleShop->id == $shop->id) {
                return jsend_success(new ShopResource($shop));
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ShopRequest  $request
     * @param  Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(ShopRequest $request, Shop $shop)
    {

        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop->id) {
                    $name = trim($request->get(self::NAME));
                    $address = trim($request->get(self::ADDRESS));
                    $employees = trim($request->get(self::EMPLOYEES));
                    $phone_no_one = trim($request->get(self::PHONE_NO_ONE));
                    $phone_no_two = trim($request->get(self::PHONE_NO_TWO));

                    $shop->name = $name;
                    $shop->address = $address;
                    $shop->employees = $employees;
                    $shop->phone_no_one = $phone_no_one;

                    if ($request->has(self::PHONE_NO_TWO)) {
                        $shop->phone_no_two = $phone_no_two;
                    }

                    $shop->save();

                    return jsend_success(new ShopResource($shop), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Shop::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Shop::class)]), [
                $ex->getCode(),
                ErrorType::UPDATE_ERROR,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {

        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop->id) {
                    $shop->delete();

                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (ModelNotFoundException $exception) {
            return jsend_error(["error" => 'Data Not Found.'], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
