<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function serve($mediaId)
    {
        $media = Media::find($mediaId);
        if (!$media) abort(404);
        $path = $media->getPath();
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    }

    public function file(Request $request)
    {
        $path = $request->query('p');
        if (!$path) abort(404);
        $fullPath = base_path('../uploads/' . $path);
        if (!file_exists($fullPath)) abort(404);
        return response()->file($fullPath);
    }
}
