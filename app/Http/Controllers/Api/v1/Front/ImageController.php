<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageRequest;
use Illuminate\Http\Request;

class ImageController extends Controller
{



    /**
     * サービス基準の画像一覧を取得する
     *
     * @param ImageRequest $request
     * @param integer $service_id
     * @return void
     */
    public function service(ImageRequest $request, int $service_id)
    {
        try {

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
     * オーナー基準の画像一覧を取得する
     *
     * @param ImageRequest $request
     * @param integer $owner_id
     * @return void
     */
    public function owner(ImageRequest $request, int $owner_id)
    {
        try {

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
     * 指定した画像IDを表示する
     *
     * @param ImageRequest $request
     * @param integer $image_id
     * @return void
     */
    public function show(ImageRequest $request, int $image_id)
    {
        try {

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
     * 任意の画像をアップロードする
     *
     * @param ImageRequest $request
     * @return void
     */
    public function upload(ImageRequest $request)
    {
        try {
            $post_data = $request->validated();
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
