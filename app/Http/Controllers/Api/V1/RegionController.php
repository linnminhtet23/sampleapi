<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use App\Models\Shop;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
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

        $regions = [];

        if ($shop->regions->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->regions as $region) {
                    array_push($regions, $region);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->regions as $region) {
                        array_push($regions, $region);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => RegionResource::collection($regions), "total" => count($regions)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegionRequest $request)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $name = trim($request->get(self::NAME));

                    $region = new Region();
                    $region->name = $name;
                    $region->shop_id = $shop_id;

                    $region->save();

                    return jsend_success(new RegionResource($region), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Region::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Region::class)]), [
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
    public function show(Region $region)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $region->shop_id) {
                return jsend_success(new RegionResource($region));
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
    public function update(RegionRequest $request, Region $region)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $name = trim($request->get(self::NAME));

                    $region->name = $name;
                    $region->shop_id = $shop_id;

                    $region->save();

                    return jsend_success(new RegionResource($region), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Region::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Region::class)]), [
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
    public function destroy(Region $region)
    {
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $region->shop_id) {
                    $region->delete();

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
}
