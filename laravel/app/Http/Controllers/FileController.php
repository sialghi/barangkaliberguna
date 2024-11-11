<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function showImage($filename)
    {
        // Determine the file path in the storage
        $path = 'public/images/ttd/' . $filename;

        // Check if the file exists
        if (!Storage::exists($path)) {
            abort(404);
        }

        // Get the file's content
        $file = Storage::get($path);
        
        // Get the file's mime type
        $mimeType = Storage::mimeType($path);

        // Return the image as a response
        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
