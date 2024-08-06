<?php
class File{
    static private $path = Path::page;
    static function in($path){
        self::$path = LOCAL.ltrim($path, '/');
        return self::class;
    }
    static function exist($filename){
        $filepath = self::$path.ltrim($filename,'/');
        return is_file($filepath) ? $filepath : false;
    }
    static function existDir($dirname){
        $dirpath = self::$path.ltrim($dirname,'/');
        return is_dir($dirpath) ? $dirpath : false;
    }
    static function try($filename='', $extensions=['', '.php', '/index.php', '/index.html']){
        foreach($extensions as $extension){
            $filepath = self::exist(rtrim($filename, '/').$extension);
            if($filepath){
                $filename = rtrim($filename, '/').$extension;
                break;
            }
        }
        return $filepath === false ? false : self::$path.ltrim($filename,'/');
    }
    #
    static function getMimeType($filename){
        $idx = explode( '.', $filename );
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode-1]);
        #
        $mimet = Inc::config('mimeTypes');
        #
        if (isset( $mimet[$idx] )) { return $mimet[$idx]; }
        else { return 'application/octet-stream'; }
    }
}