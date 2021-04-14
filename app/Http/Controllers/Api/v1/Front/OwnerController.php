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
        try {
            $insert_data = $request->validated();

            $owner = Owner::create($insert_data);

            // オーナーの作成に失敗した場合
            if ($owner === NULL) {
                throw new \Exception("オーナー情報の新規登録に失敗しました｡");
            }

            $response = [
                "status" => true,
                "data" => $owner,
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
     * 指定したオーナー情報を取得する
     *
     * @param OwnerRequest $request
     * @param integer $owner_id
     * @return void
     */
    public function detail(OwnerRequest $request, int $owner_id)
    {
        try {
            $update_data = $request->validated();

            $owner = Owner::find($owner_id);

            if ($owner === NULL) {
                throw new \Exception("指定したオーナー情報が見つかりませんでした｡");
            }

            $response = [
                "status" => true,
                "data" => $owner,
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
     * 指定したオーナーIDのオーナー情報を更新処理する
     *
     * @param OwnerRequest $request
     * @param integer $owner_id
     * @return void
     */
    public function update(OwnerRequest $request, int $owner_id)
    {
        try {
            $update_data = $request->validated();

            $owner = Owner::find($owner_id);

            if ($owner === NULL) {
                throw new \Exception("指定したオーナー情報が見つかりませんでした｡");
            }
            $result = $owner->fill($update_data)->save();

            if ($result !== true) {
                throw new \Exception("指定したオーナー情報の更新処理に失敗しました｡");
            }

            $response = [
                "status" => true,
                "data" => $owner,
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
}
