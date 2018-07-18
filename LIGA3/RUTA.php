<?php
/**
 * Clase RUTA
 * Para LIGA 3
 * Autor: Mtro. Oscar Galileo García García
 * Agradecimiento a: http://code.tutsplus.com/tutorials/using-htaccess-files-for-pretty-urls--net-6049
 * Se recomienda en el .htaccess
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
RewriteRule ^.*$ index.php [NC,L]
 */
define('BASE', substr('//'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'], 0, strrpos('//'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'], '/')+1 ));
class RUTA {
    static $LPAR  = array();
    static $base  = BASE;
    static $error = '<p>Error 404: Dirección inválida.</p>';
    static $uri   = '';
    static $url   = '';
    static $rutas = array();
    private function __construct() {}
    static function nueva($ruta, $func) {
        $ruta = $ruta == '' ? ' ' : $ruta;
        self::$rutas[$ruta] = $func;
    }
    static function run($ruta = null) {
        self::$LPAR = array();
        self::$uri = $ruta ? $ruta : substr($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], strlen(self::$base)-2);
        self::$uri = self::$uri!==false ? self::$uri : ' ';
        self::$url = self::$base.self::$uri;
        foreach (explode('/', self::$uri) as $param) {
            if ($param) {
             $param = urldecode($param);
             if (($pos = strpos($param, '?')) !== false) {
              $gets = substr($param, $pos + 1);
              self::$LPAR[] = substr($param, 0, $pos);
             } else {
              self::$LPAR[] = $param;
             }
            }
        }
        $lpar = self::$LPAR;
        $coincide = false;
        $ruts = array_keys(self::$rutas);
        foreach ($ruts as $ruta) {
            if (!$coincide) {
                $rut = explode('/', $ruta);
                $coinc = 0;
                foreach ($rut as $k => $ru) {
                    if (isset(self::$LPAR[$k]) && ($ru == self::$LPAR[$k] || preg_match('/(*UTF8)^'.$ru.'$/', self::$LPAR[$k]))) {
                        $coinc++;
                    } else {
                        $coinc = 0;
                        break;
                    }
                }
                if (count($lpar) > 0 && count($lpar) == $coinc) {
                    $coincide = $ruta;
                }
            }
        }
        if ($coincide) {
            $func = self::$rutas[$coincide];
            $func();
            return true;
        }
        if (!headers_sent()) {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        }
        if (is_callable(self::$error)) {
            self::$error();
        } else {
            echo self::$error;
        }
        return false;
    }
}
?>
