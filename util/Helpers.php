<?php

namespace Util;

class Helpers
{
    public static function saveFile($filename, $filecontent, $folderPath)
    {
        if (strlen($filename) > 0) {
            $file = @fopen($folderPath, "w+");
            if ($file != false) {
                fwrite($file, $filecontent);
                fclose($file);
                return 1;
            }
            return -2;
        }
        return -1;
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
