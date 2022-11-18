<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteController extends Controller
{
    public function destroy()
    {
        try {
            DB::table('sale_records')->delete();
            DB::table('sale_price_tracks')->delete();
            DB::table('buy_records')->delete();
            DB::table('buy_price_tracks')->delete();
            DB::table('damage_items')->delete();
            DB::table('expenses')->delete();

            return jsend_success(['message' => 'Successfully deleted!'], JsonResponse::HTTP_ACCEPTED);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => 'Deleting Table.']), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }
}
