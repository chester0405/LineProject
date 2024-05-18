<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => '照片不能大於1MB'], 400);
        }

        // 使用 Storage facade 來儲存圖片
        $imagePath = $request->file('image')->store('public/images');

        $publicPath = str_replace('public/', '', $imagePath);

        return response()->json([
            'path' => $publicPath,
        ]);
    }
}
