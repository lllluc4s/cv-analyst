<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class FileController extends Controller
{
    public function servePdf(Request $request, $filename)
    {
        $path = 'cvs/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Arquivo não encontrado');
        }
        
        $file = Storage::disk('public')->get($path);
        
        return Response::make($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=3600',
            'X-Frame-Options' => 'ALLOWALL',
            'X-Content-Type-Options' => 'nosniff',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization'
        ]);
    }
}
