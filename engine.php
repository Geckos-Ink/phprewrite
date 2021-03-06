<?php

// Utility functions
function startsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    return substr( $haystack, 0, $length ) === $needle;
}

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}

function checked($var){
    if(!isset($GLOBALS[$var]))
        return false;

    return $GLOBALS[$var];
}

if(!checked('phpRewriteInstalled') && file_exists('../index.php') && !file_exists('../.htaccess')){
    copy('.htaccess', '../.htaccess');
}

class EngineClass {
    public $path;
    public $paths;
    public $cPaths;
    public $pathPos;

    function __construct(){
        $this->baseUrl = '/'; 
        $this->_constructPath();
    }

    function _constructPath(){
        $this->path = substr($_SERVER['REQUEST_URI'], strlen($this->baseUrl));

        if(endsWith($this->path, '/'))
            $this->path = substr($this->path, 0, strlen($this->path)-1);

        $this->paths = explode('/', $this->path);
        $this->cPaths = count($this->paths);
        $this->pathPos = 1;
    }

    function setBaseUrl($baseUrl){
        $this->baseUrl = $baseUrl;
        $this->_constructPath();
    }

    function getCurPath(){
        return $this->paths[$this->pathPos];
    }

    function nextPath($by = 1){
        $this->pathPos += $by;
    }
    
    function check($path, $autoIncrement=true){    
        if(!is_array($path)){
            $path = explode('/', $path);
            $autoIncrement = true;
        }
    
        $cp = count($path);
    
        $ignoreFirst = false;
        $basePos = $this->pathPos;

        if($path[0]==''){
            $cp-=1; // be elastic for route/
            $ignoreFirst = true;
        }

        if ($cp > $this->cPaths-$basePos || $cp < 1)
            return false;  


        for($i=($ignoreFirst?1:0); $i+$basePos<$this->cPaths && $i < $cp; $i++){            
            $ib = $i+$basePos;
            $pv = $path[$i];    
    
            echo "check $pv vs ".$this->paths[$ib];
            if($pv != $this->paths[$ib]){
                return false;
            }
        }
    
        if($autoIncrement)
            $this->nextPath($cp);

        return true;
    }
    
    function get($path, $to){
        $pp = explode('/', $path);
    
        if(!$this->check($pp))
            return false;
    
        if(is_callable($to)){
            $to();
        }
        else {
            switch(gettype($to)){
                case 'string':
                    if(str_ends_with($to, '.php')){
                        include($to);
                        return true;
                    }
                    break;
            }
        }
    }

    function getMimeType($filename) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mime;
    }
    
    function justServeFile($file){
        $mime_type = $this->mime_type($file) ?: 'application/octet-stream';
        $file_size = filesize($file);
        
        header("Content-Type: $mime_type");
        header("Content-Length: $file_size");
        $fh = fopen($file, 'rb');
        fpassthru($fh);
        fclose($fh);
        exit;
    }
    
    function serve($path, $url=-1){
        if($url==-1){
            $pp = explode('/',$path);
            $cpp = count($pp);
            if($cpp>0) 
                $url = $pp[$cpp-1];
        }
    
        if($this->check($url, false)){   
     
            for($i=$this->pathPos; $i<$this->cPaths;$i++){
                $path .= '/'.$this->paths[$i];
                if(!file_exists($path))
                    return false;
            }
    
            $this->justServeFile($path);
            return true;
        }
    
        return false;
    }
    
    function currentPath(){
        $i = $this->pathPos;
        if($i >= $this->cPaths)
            return '';
    
        return $this->paths[$i];
    }
    
    ///
    /// General functions
    ///
    function mime_type($filename) {
    
        $mime_types = array(
           'txt' => 'text/plain',
           'htm' => 'text/html',
           'html' => 'text/html',
           'css' => 'text/css',
           'json' => array('application/json', 'text/json'),
           'xml' => 'application/xml',
           'swf' => 'application/x-shockwave-flash',
           'flv' => 'video/x-flv',
      
           'hqx' => 'application/mac-binhex40',
           'cpt' => 'application/mac-compactpro',
           'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
           'bin' => 'application/macbinary',
           'dms' => 'application/octet-stream',
           'lha' => 'application/octet-stream',
           'lzh' => 'application/octet-stream',
           'exe' => array('application/octet-stream', 'application/x-msdownload'),
           'class' => 'application/octet-stream',
           'so' => 'application/octet-stream',
           'sea' => 'application/octet-stream',
           'dll' => 'application/octet-stream',
           'oda' => 'application/oda',
           'ps' => 'application/postscript',
           'smi' => 'application/smil',
           'smil' => 'application/smil',
           'mif' => 'application/vnd.mif',
           'wbxml' => 'application/wbxml',
           'wmlc' => 'application/wmlc',
           'dcr' => 'application/x-director',
           'dir' => 'application/x-director',
           'dxr' => 'application/x-director',
           'dvi' => 'application/x-dvi',
           'gtar' => 'application/x-gtar',
           'gz' => 'application/x-gzip',
           'php' => 'application/x-httpd-php',
           'php4' => 'application/x-httpd-php',
           'php3' => 'application/x-httpd-php',
           'phtml' => 'application/x-httpd-php',
           'phps' => 'application/x-httpd-php-source',
           'js' => array('application/javascript', 'application/x-javascript'),
           'sit' => 'application/x-stuffit',
           'tar' => 'application/x-tar',
           'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
           'xhtml' => 'application/xhtml+xml',
           'xht' => 'application/xhtml+xml',             
           'bmp' => array('image/bmp', 'image/x-windows-bmp'),
           'gif' => 'image/gif',
           'jpeg' => array('image/jpeg', 'image/pjpeg'),
           'jpg' => array('image/jpeg', 'image/pjpeg'),
           'jpe' => array('image/jpeg', 'image/pjpeg'),
           'png' => array('image/png', 'image/x-png'),
           'tiff' => 'image/tiff',
           'tif' => 'image/tiff',
           'shtml' => 'text/html',
           'text' => 'text/plain',
           'log' => array('text/plain', 'text/x-log'),
           'rtx' => 'text/richtext',
           'rtf' => 'text/rtf',
           'xsl' => 'text/xml',
           'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
           'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
           'word' => array('application/msword', 'application/octet-stream'),
           'xl' => 'application/excel',
           'eml' => 'message/rfc822',
      
           // images
           'png' => 'image/png',
           'jpe' => 'image/jpeg',
           'jpeg' => 'image/jpeg',
           'jpg' => 'image/jpeg',
           'gif' => 'image/gif',
           'bmp' => 'image/bmp',
           'ico' => 'image/vnd.microsoft.icon',
           'tiff' => 'image/tiff',
           'tif' => 'image/tiff',
           'svg' => 'image/svg+xml',
           'svgz' => 'image/svg+xml',
      
           // archives
           'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
           'rar' => 'application/x-rar-compressed',
           'msi' => 'application/x-msdownload',
           'cab' => 'application/vnd.ms-cab-compressed',
      
           // audio/video
           'mid' => 'audio/midi',
           'midi' => 'audio/midi',
           'mpga' => 'audio/mpeg',
          'mp2' => 'audio/mpeg',
           'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
           'aif' => 'audio/x-aiff',
           'aiff' => 'audio/x-aiff',
           'aifc' => 'audio/x-aiff',
           'ram' => 'audio/x-pn-realaudio',
           'rm' => 'audio/x-pn-realaudio',
           'rpm' => 'audio/x-pn-realaudio-plugin',
           'ra' => 'audio/x-realaudio',
           'rv' => 'video/vnd.rn-realvideo',
           'wav' => array('audio/x-wav', 'audio/wave', 'audio/wav'),
           'mpeg' => 'video/mpeg',
           'mpg' => 'video/mpeg',
           'mpe' => 'video/mpeg',
           'qt' => 'video/quicktime',
           'mov' => 'video/quicktime',
           'avi' => 'video/x-msvideo',
           'movie' => 'video/x-sgi-movie',
      
           // adobe
           'pdf' => 'application/pdf',
           'psd' => array('image/vnd.adobe.photoshop', 'application/x-photoshop'),
           'ai' => 'application/postscript',
           'eps' => 'application/postscript',
           'ps' => 'application/postscript',
      
           // ms office
           'doc' => 'application/msword',
           'rtf' => 'application/rtf',
           'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
           'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
      
           // open office
           'odt' => 'application/vnd.oasis.opendocument.text',
           'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
      
        //todo: improve extension extraction
        $ext = explode('.', $filename);
        $ext = strtolower(end($ext));
       
        if (array_key_exists($ext, $mime_types)) {
          return (is_array($mime_types[$ext])) ? $mime_types[$ext][0] : $mime_types[$ext];
        } else if (function_exists('finfo_open')) {
           if(file_exists($filename)) {
             $finfo = finfo_open(FILEINFO_MIME);
             $mimetype = finfo_file($finfo, $filename);
             finfo_close($finfo);
             return $mimetype;
           }
        }
       
        return 'application/octet-stream';
      }
}

$GLOBAL['Engine'] = $Engine = new EngineClass();