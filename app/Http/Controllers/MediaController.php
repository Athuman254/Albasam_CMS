<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function show($id)
    {
        $media = Media::findOrFail($id);
        $filePath = storage_path('app/public/' . $media->id . '/' . $media->file_name);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return Response::file($filePath);
    }
}
