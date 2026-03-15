<?php
class Router{
    static private $uri=null;       // URI of current page (not include the root path)
    static private $local='/';     // local path where is routing (not include the absolute local path)
    static private $path=null;   // the path to be processed by the router
    static private $root=null;
	static private $parmsStr=null; // "GET" parameters string
    static $args=[];

    # init the router, only using on the main router
    static function init(){
        self::$uri = is_null(self::$uri) ? substr($_SERVER['SCRIPT_NAME'], strlen(ROOT) - 1) : self::$uri;
        self::$path = ltrim(self::uri(), '/\\');
        self::$root = '';
		self::$parmsStr = $_SERVER['QUERY_STRING'];
        self::$args = [];
        // self::$local = LOCAL;
    }

    # create a new router (usually a "router" file will only call this function once)
    static function new($local){
        self::$local .= trim($local, '/').'/';
        return self::class;
    }

    # get if match then callback
    static function get($uris, $callback=null, $path=null){
        $uris = is_array($uris) ? $uris : [$uris];
        foreach($uris as $uri){
            $uri = ltrim($uri,'/');
            if (!str_starts_with(self::path(), $uri)) { continue; };
            // 
            // 預設，扣除上層路徑
            if(is_null($path) || $path===false){
                $path = substr(self::path(), strlen($uri));
            }
            // 完整路徑轉發
            else if($path === true){
                $path = self::path();
            }
            // 自訂路徑
            else if(is_string($path)){
                $vars = [
                    "{root}" => $uri,
                    "{path}" => substr(self::path(), strlen($uri)),
                ];
                $path = strtr($path, $vars);
            }
            // unexpected
            else{
                die('Router::get(): Invalid path argument.');
            }
            if(is_callable($callback)){ call_user_func($callback); }
            else if(is_string($callback)) { self::route(self::local().$callback, $path, $uri); }
        }
    }

    #
    static function equal($uris, $callback=null, $path=null){
        $uris = is_array($uris) ? $uris : [$uris];
        foreach($uris as $uri){
            $uri = ltrim($uri,'/');
            if($uri !== self::path()){ continue; }
            $path = is_null($path) ? substr(self::path(), strlen($uri)) : $path;
            if(is_callable($callback)){ call_user_func($callback); }
            else if(is_string($callback)) { self::route($callback, $path, $uri); }
        }
    }

    # it will try other possibility of path when file not found.
    static function view($filename=null){
        if(headers_sent()){ die('Router Error: Headers already been sent.'); }
        $filename = is_null($filename) ? self::path() : $filename;
        // resources with full name
        $filepath = File::in(self::local())::exist($filename);
        if($filepath !== false){
            $mimeType = File::getMimeType(LOCAL.$filepath);
            header('Content-Type: '.$mimeType);
            if($mimeType !== 'text/html'){
                readfile($filepath);
            }
            else{ return false; }
            die();
        }
        // or is php, html
        $defaultFilenames = ['', '.php', '.html', '/index.php', '/index.html'];
        $filepath = File::in(self::local())::try($filename, $defaultFilenames);
        if($filepath !== false){
            if(!str_ends_with(self::path(), '/') && self::path() !== ''){ self::redirect(self::path().'/'); }
            $normalizedPath = trim($filename, '/\\');
            self::$path = $normalizedPath === '' ? '' : $normalizedPath.'/';
            self::$args = [];
            header('Content-Type: text/html');
            require($filepath);
            die();
        }
        // forward scan to find best page prefix for uri params, and stop early when next level not exists.
        // e.g. /some_page/1/2/ => pages/some_page/ + args = ['1', '2']
        $parts = array_values(array_filter(explode('/', trim($filename, '/'))));
        $matchedDepth = null;
        $matchedFile = false;
        for($i = 1; $i <= count($parts); $i++){
            $tryPath = implode('/', array_slice($parts, 0, $i)).'/';
            $foundFile = File::in(self::local())::try($tryPath, $defaultFilenames);
            if($foundFile !== false){
                $matchedDepth = $i;
                $matchedFile = $foundFile;
            }

            // if this level is not a directory, no need to continue deeper.
            if(File::in(self::local())::existDir($tryPath) === false){
                break;
            }
        }
        if($matchedFile !== false){
            if(!str_ends_with($filename, '/') && $filename !== ''){ self::redirect($filename.'/'); }
            self::$path = implode('/', array_slice($parts, 0, $matchedDepth)).'/';
            self::$args = array_slice($parts, $matchedDepth);
            header('Content-Type: text/html');
            $isCalledargs = self::getArgsCount($matchedFile);
            if(is_null($isCalledargs)){ self::args(); }
            require($matchedFile);
            die();
        }
        return false;
    }

    # route to another router
    static function route($router, $path, $root){
        self::$path = $path;
        self::$root .= $root;
        Inc::router($router);
        die();
    }

    # redirect page
    static function redirect($path, $withPost=true, $withGet=true){
        if(headers_sent()){ die('Router Error: Headers already been sent.'); }
        if($withPost) header('HTTP/1.1 307 Temporary Redirect');
        header('Location: '.ROOT.self::root().ltrim($path,'/').($withGet && empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING']));
        die();
    }
    static function jump($url, $withPost=false, $withGet=false){
        if(headers_sent()){ die('Router Error: Headers already been sent.'); }
        if($withPost) header('HTTP/1.1 307 Temporary Redirect');
        header('Location: '.$url.($withGet && !empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : ''));
        die();
    }

    # opt-in args resolver. example: Router::args($a, $b)
    # - allows /x/, /x/a/, /x/a/b/
    # - rejects /x/a/b/c/
    # return true when valid, false when invalid
    static function args(&...$vars){
        $args = self::$args;

        if(count($args) > count($vars)){
            http_response_code(400);
            die();
            return false;
        }

        foreach($vars as $i => &$var){
            $var = isset($args[$i]) ? $args[$i] : null;
        }
        return true;
    }

    # magic function for check if file calls Router::args()
    static function getArgsCount($file) {
        $code = file_get_contents($file);
        $tokens = token_get_all($code);
        $count = null;
        for ($i = 0; $i < count($tokens); $i++) {
            // 找到 Router::args(
            if (
                is_array($tokens[$i]) && $tokens[$i][0] === T_STRING && $tokens[$i][1] === 'Router' &&
                isset($tokens[$i+1]) && $tokens[$i+1][0] === T_DOUBLE_COLON &&
                isset($tokens[$i+2]) && $tokens[$i+2][0] === T_STRING && $tokens[$i+2][1] === 'args' &&
                isset($tokens[$i+3]) && $tokens[$i+3] === '('
            ) {
                // 計算括號內的參數數量
                $j = $i + 4;
                $args = 0;
                $parenLevel = 1;
                while ($parenLevel > 0 && isset($tokens[$j])) {
                    if ($tokens[$j] === '(') $parenLevel++;
                    elseif ($tokens[$j] === ')') $parenLevel--;
                    elseif ($parenLevel === 1 && $tokens[$j] === ',') $args++;
                    $j++;
                }
                // 有參數才算
                if ($j > $i + 4) $count = $args + ($j - $i - 4 > 1 ? 1 : 0);
                break;
            }
        }
        return $count;
    }

    # get info of router
    # e.g. /project-root/router-root/path/
    static function uri(){ return self::$uri; } // /router-root/path/
    static function local(){ return self::$local; }  // /router-local/
    static function path(){ return self::$path; }  // path/
    static function root(){ return self::$root; }  // /project-root/router-root/
	static function parmsStr($questionSigh=true){ return self::$parmsStr!=='' && $questionSigh ? '?'.self::$parmsStr : self::$parmsStr; } // ?a=123&b=456
}