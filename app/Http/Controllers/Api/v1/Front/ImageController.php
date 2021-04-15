<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageRequest;
use App\Libraries\RandomToken;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

use Intervention\Image\Facades\Image as InterventionImage;
class ImageController extends Controller
{

    private $temporary_path = "public/uploads/temporary";


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
            $temporary_filename = $request->upload_image->store($this->temporary_path);

            $iv = InterventionImage::make(Storage::disk("local")->path($temporary_filename))->orientate();

            // 規定サイズより画像の横幅が大きい場合は､リサイズ処理を実行
            if ($iv->width() > Config("const.image.regulation_width")) {
                $iv->resize(Config("const.image.regulation_width"), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save(Storage::disk("local")->path($temporary_filename), Config("const.image.compression"));
            }

            // アップロード画像向けのユニーク名を取得
            $token = RandomToken::MakeRandomToken(256);
            // ファイルの中身からハッシュを取得
            $filename = hash_file("sha3-512", Storage::disk("local")->path($temporary_filename));

            // ハッシュ化したファイルが衝突しないかどうか
            $image = Image::where([
                ["filename", "=", $filename]
            ])
            ->get()
            ->first();

            // 仮ディレクトリパスから確定ディレクトリに移動させる
            // 一時ディレクトリ内のファイルを確定ディレクトリに移動させる
            $dir_number = floor($last_image_id / Config("const.image.directory_max"));
            $decided_save_path = "public/uploads/images/{$dir_number}/{$filename}";
            $result = Storage::disk("local")->exists($decided_save_path);

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
