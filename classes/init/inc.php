<?php
class Inc{
    static function clas($name){
        if(class_exists(ucfirst($name))) return;
        $filename = File::in(Path::clas)::exist(lcfirst($name).'.php');
        if($filename === false){ die('Inc::clas(): Error - Can not include class "'.$name.'".'); }
        return require($filename);
    }
    static function router($name){
        foreach(['', '.php'] as $exName){
            $filename = File::in(Path::router)::exist($name.$exName);
            if($filename) break;
        }
        if($filename === false){ die('Inc::router(): Error - Can not include router "'.$name.'".'); }
        return require($filename);
    }

    static function config($name){
        foreach(['', '.php', '.ini', '.json'] as $exName){
            $filename = File::in(Path::config)::exist($name.$exName);
            if($filename) break;
        }
        if(!$filename){ die("Inc::config() - Not found file. ({$name})"); };
        return include($filename);
    }
    
    static function component($name){
        foreach(['', '.php'] as $exName){
            $filename = File::in(Path::component)::exist($name.$exName);
            if($filename) break;
        }
        if($filename === false){ die('Inc::component(): Error - Can not include subpage "'.$name.'".'); }
        return require($filename);
    }

    static function page($name){
        foreach(['', '.php'] as $exName){
            $filename = File::in(Path::page)::exist($name.$exName);
            if($filename) break;
        }
        if($filename === false){ die('Inc::page(): Error - Can not include page "'.$name.'".'); }
        return require($filename);
    }

    static function asset($name){
        $filename = File::in(Path::asset)::exist($name);
        if($filename === false){ die('Inc::asset(): Error - Can not include asset "'.$name.'".'); }
        return require($filename);
    }

    static function lib($name){
        foreach(['', '.php'] as $exName){
            $filename = File::in(Path::lib)::exist($name.$exName);
            if($filename) break;
        }
        if($filename === false){ die('Inc::lib(): Error - Can not include lib "'.$name.'".'); }
        return require($filename);
    }
}