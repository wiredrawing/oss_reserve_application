<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageRequest;
use App\Libraries\RandomToken;
use App\Models\Image;
use App\Models\OwnerImage;
use App\Models\ServiceImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

use Intervention\Image\Facades\Image as InterventionImage;
class ImageController extends Controller
{

    private $temporary_path = "public/uploads/temporary";



    /**
     * 現在アップロード済みの未削除の画像件数を取得する
     *
     * @param ImageRequest $request
     * @return void
     */
    public function count(ImageRequest $request)
    {
        try {
            $image_count = Image::select(DB::raw("count(id)"))
            ->where([
                ["is_deleted", "=", Config("const.binary_type")["off"]],
            ])
            ->count();
            // dd($image_count->toSql(), $image_count->getBindings());
            // var_dump($image_count);
            // ページネーション後のデータのみを取得する
            $response = [
                "status" => true,
                "data" => [
                    "count" => $image_count,
                ]
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
     * アップロードした画像一覧を取得する
     *
     * @param ImageRequest $request
     * @param integer $offset
     * @param integer $limit
     * @return void
     */
    public function list(ImageRequest $request, int $offset = 0, int $limit = 0)
    {
        try {
            $images = Image::where([
                ["is_deleted", "=", Config("const.binary_type")["off"]],
            ])
            ->orderBy("id", "desc")
            ->offset($offset)
            ->paginate($limit);

            // ページネーション後のデータのみを取得する
            $response = [
                "status" => true,
                "data" => $images->getCollection(),
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
     * Serviceと画像情報を紐付ける
     *
     * @param ImageRequest $request
     * @return void
     */
    public function service(ImageRequest $request)
    {
        try {
            // バリデーション結果を取得
            $post_data = $request->validated();

            $service_image = ServiceImage::create($post_data);

            logger()->info($service_image);
            $response = [
                "status" => true,
                "data" => $service_image,
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
     * 指定したServiceに紐付いた画像一覧を取得する
     *
     * @param ImageRequest $request
     * @param integer $service_id
     * @return void
     */
    public function service_images(ImageRequest $request, int $service_id)
    {
        try {
            $images = ServiceImage::where([
                ["service_id", "=", $service_id],
            ])
            ->get();

            logger()->error($e);
            $response = [
                "status" => true,
                "data" => $images,
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
     * Ownerと画像情報を紐付ける
     *
     * @param ImageRequest $request
     * @return void
     */
    public function owner(ImageRequest $request)
    {
        try {
            $post_data = $request->validated();

            $owner_image = OwnerImage::create($post_data);

            logger()->info($owner_image);
            $response = [
                "status" => true,
                "data" => $owner_image,
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
     * 指定したオーナーに紐付いた画像一覧を取得する
     *
     * @param ImageRequest $request
     * @param integer $owner_id
     * @return void
     */
    public function owner_images(ImageRequest $request, int $owner_id)
    {
        try {
            $images = OwnerImage::with([
                "owner",
            ])->where([
                ["owner_id", "=", $owner_id],
            ])
            ->orderBy("id", "desc")
            ->get();

            logger()->info($images);

            $response = [
                "status" => true,
                "data" => $images,
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
     * 指定した画像IDを表示する
     *
     * @param ImageRequest $request
     * @param integer $image_id
     * @return void
     */
    public function show(ImageRequest $request, int $image_id)
    {
        try {
            $image = Image::where([
                ["is_displayed", "=", Config("const.binary_type")["on"]],
                ["is_deleted", "=", Config("const.binary_type")["off"]],
            ])->find($image_id);

            if ($image === NULL) {
                throw new \Exception("指定した画像が見つかりません｡");
            }

            // 保存先ディレクトリを生成
            $dir_number = floor($image->id / Config("const.image.directory_max"));
            $decided_save_path = "public/uploads/images/{$dir_number}/{$image->filename}";

            $convert_image = InterventionImage::make(Storage::disk("local")->path($decided_save_path));

            return $convert_image->response("jpg");
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
            // アップロード処理を実行する
            $image = $this->__upload($request);

            return response()->json([
                "status" => true,
                "data" => $image,
            ]);
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
     * 指定した画像に対して[いいね]を付与する
     *
     * @param ImageRequest $request
     * @param integer $image_id
     * @return void
     */
    public function good(ImageRequest $request, int $image_id)
    {
        try {
            // start transaction
            DB::beginTransaction();

            $image = Image::lockForUpdate()->find($image_id);

            // Not Found
            if ($image === NULL) {
                throw new \Exception("指定した画像が見つかりません｡");
            }

            $result = $image->fill(["good_count" => $image->good_count + 1])->save();
            if ($result) {

                // completed committing
                DB::commit();
                $response = [
                    "status" => true,
                    "data" => $image,
                ];
                return response()->json($response);
            }
            throw new \Exception("指定した画像へのいいねが失敗しました｡");
        } catch (\Throwable $e) {
            // canceled committing
            DB::rollback();
            logger()->error($e);
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }


    /**
     * Delete image selected by user.
     *
     * @param ImageRequest $request
     * @param integer $image_id
     * @return void
     */
    public function delete(ImageRequest $request, int $image_id)
    {
        try {
            $image = Image::where([
                ["is_deleted", "=", Config("const.binary_type")["off"]],
                ["is_displayed", "=", Config("const.binary_type")["off"]],
            ])
            ->find($image_id);

            if ($image === NULL) {
                throw new \Exception("指定した画像が見つかりませんでした｡");
            }

            $result = $image->fill(["is_deleted" => Config("const.binary_type")["on"]])->save();

            if ($result !== true) {
                throw new \Exception("指定した画像の削除が失敗しました｡");
            }
            $response = [
                "status" => true,
                "data" => $image,
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
     * アップロード処理
     *
     * @param ImageRequest $request
     * @return void
     */
    private function __upload(ImageRequest $request)
    {
        try {
            // start transaction
            DB::beginTransaction();

            $post_data = $request->validated();

            $temporary_filename = $request->upload_image->store($this->temporary_path);

            $iv = InterventionImage::make(Storage::disk("local")->path($temporary_filename))->orientate();

            // 規定サイズより画像の横幅が大きい場合は､リサイズ処理を実行
            $regulation_width = Config("const.image.regulation_width");
            if ($iv->width() > $regulation_width) {
                $iv->resize($regulation_width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save(Storage::disk("local")->path($temporary_filename), Config("const.image.compression"));
            }

            // アップロード画像向けのユニーク名を取得
            $token = RandomToken::MakeRandomToken(512);
            $filename = RandomToken::MakeRandomToken(128);
            // ファイルの中身からハッシュを取得
            // アップロードされたファイルの拡張子を取得する
            $extension = File::extension(Storage::disk('local')->path($temporary_filename));
            $filename = $filename.".{$extension}";

            // ファイル名が重複しないかどうか
            $image = Image::where([
                ["filename", "=", $filename]
            ])
            ->get()
            ->first();

            if ($image !== NULL) {
                throw new \Exception("只今､サーバーが混み合っています｡");
            }

            // 新規挿入データをフォーマット
            $post_data["is_displayed"] = Config("const.binary_type")["on"];
            $post_data["is_deleted"] = Config("const.binary_type")["off"];
            $post_data["token"] = $token;
            $post_data["filename"] = $filename;

            // DB insert処理
            $image = Image::create($post_data);
            $last_image_id = $image->id;

            // 仮ファイルを確定ディレクトリへアップする
            $dir_number = floor($last_image_id / Config("const.image.directory_max"));
            $decided_save_path = "public/uploads/images/{$dir_number}/{$filename}";
            $result = Storage::disk("local")->exists($decided_save_path);

            // ファイルの存在チェック
            if ($result) {
                throw new \Exception("アップロードディレクトリに既に同名のファイルが存在します｡");
            }

            // 一時ディレクトリを確定ディレクトリに移動させる
            $result = Storage::move($temporary_filename, $decided_save_path);
            if ($result !== true) {
                throw new \Exception("画像のアップロードに失敗しました｡");
            }

            // Completed committing.
            DB::commit();
            return $image;
        } catch (\Throwable $e) {
            logger()->error($e);
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * オーナーに紐付いた画像を削除する
     *
     * @param ImageRequest $request
     * @return void
     */
    public function owner_delete(ImageRequest $request)
    {
        try {
            $post_data = $request->validated();

            $image = OwnerImage::where([
                ["owner_id", "=", $post_data["owner_id"]],
                ["image_id", "=", $post_data["image_id"]],
            ])
            ->get()
            ->first();
            $result = $image->delete();
            $response = [
                "status" => true,
                "data" => $image,
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
     * サービスにに紐付いた画像を削除する
     *
     * @param ImageRequest $request
     * @return void
     */
    public function service_delete(ImageRequest $request)
    {
        try {
            $post_data = $request->validated();

            $image = ServiceImage::where([
                ["service_id", "=", $post_data["service_id"]],
                ["image_id", "=", $post_data["image_id"]],
            ])
            ->get()
            ->first();
            $result = $image->delete();
            $response = [
                "status" => true,
                "data" => $image,
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
