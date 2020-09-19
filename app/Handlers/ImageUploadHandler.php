<?php

namespace App\Handlers;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageUploadHandler
{
    protected $allowedExt = ["png", "jpg", "gif", 'jpeg'];

    public function save($file, $folder, $filePrefix, $maxWidth = false)
    {
        // 构建存储的文件夹规则，值如：uploads/images/avatars/201709/21/
        // 文件夹切割能让查找效率更高。
        $folderName = "uploads/images/$folder/" . date("Ym/d", time());

        // 文件具体存储的物理路径，`public_path()` 获取的是 `public` 文件夹的物理路径。
        // 值如：/home/vagrant/Code/larabbs/public/uploads/images/avatars/201709/21/
        $uploadPath = base_path('public') . '/' . $folderName;

        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        // 拼接文件名，加前缀是为了增加辨析度，前缀可以是相关数据模型的 ID
        // 值如：1_1493521050_7BVc9v9ujP.png
        $filename = $filePrefix . '_' . time() . '_' . Str::random(10) . '.' . $extension;

        // 如果上传的不是图片将终止操作
        if ( ! in_array($extension, $this->allowedExt)) {
            return false;
        }

        // 将图片移动到我们的目标存储路径中
        $file->move($uploadPath, $filename);

        // 如果限制了图片宽度，就进行裁剪
        if ($maxWidth && $extension != 'gif') {

            // 此类中封装的函数，用于裁剪图片
            $this->reduceSize($uploadPath . '/' . $filename, $maxWidth);
        }

        return [
            'path' => "/$folderName/$filename"
        ];
    }

    public function reduceSize($filePath, $maxWidth)
    {
        // 先实例化，传参是文件的磁盘物理路径
        $image = Image::make($filePath);

        // 进行大小调整的操作
        $image->resize($maxWidth, null, function ($constraint) {

            // 设定宽度是 $maxWidth，高度等比例缩放
            $constraint->aspectRatio();

            // 防止裁图时图片尺寸变大
            $constraint->upsize();
        });

        // 对图片修改后进行保存
        $image->save();
    }
}
