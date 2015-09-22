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

class RUTA {
    static $LPAR = array();
    static $base  = '';
    static $error = '<p>Error 404: Dirección inválida.</p>';
    static $uri   = '';
    static $rutas = array();
    private function __construct() {}
    static function nueva($ruta, $func) {
        self::$rutas[$ruta] = $func;
    }
    static function run($ruta = null) {
        self::$LPAR = array();
        self::$uri = $ruta ? substr(self::$base, strpos(self::$base, '/', 2)).$ruta : $_SERVER['REQUEST_URI'];
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
        if (count(self::$LPAR) > 1) {
            $lpar = self::$LPAR;
            unset($lpar[0]);
            $coincide = false;
            $ruts = array_keys(self::$rutas);
            foreach ($ruts as $ruta) {
                if (!$coincide) {
                    $rut = explode('/', $ruta);
                    $coinc = 0;
                    foreach ($rut as $k => $ru) {
                        if (isset(self::$LPAR[$k+1]) && ($ru == self::$LPAR[$k+1] || preg_match('/(*UTF8)^'.$ru.'$/', self::$LPAR[$k+1]))) {
                            $coinc++;
                        } else {
                            $coinc = 0;
                            break;
                        }
                    }
                    if (count($lpar) == $coinc) {
                        $coincide = $ruta;
                    }
                }
            }
            if ($coincide) {
                $func = self::$rutas[$coincide];
                $func();
                return true;
            }
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