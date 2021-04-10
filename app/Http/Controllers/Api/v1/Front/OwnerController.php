<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OwnerRequest;
use App\Models\Owner;
use Illuminate\Http\Request;

class OwnerController extends Controller
{



    /**
     * 現在､登録中のオーナー一覧を取得する
     *
     * @param OwnerRequest $request
     * @return void
     */
    public function list(OwnerRequest $request)
    {
        try {
            $owners = Owner::get();
            $response = [
                "status" => true,
                "data" => $owners,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            logger()->error($e);
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }

    /**
     * オーナー情報を作成する
     *
     * @param OwnerRequest $request
     * @return void
     */
    public function create(OwnerRequest $request)
    {

    }
}
