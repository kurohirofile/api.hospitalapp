<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function __construct() {
        $this->middleware(array('auth'));
    }
    
    public static function fileUpload($file,$destinationPath) {
        $extenstion = $file->getClientOriginalExtension();
        $fileName = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,10).'.'.$extenstion;
        $file->move($destinationPath, $fileName);
        return $fileName;
    }
}
