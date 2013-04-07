<?php
// Fusión de archivos para LIGA 3.x
class FUSION {
   static $dir = '';
   private function __construct() {}
   // Recibe la dirección de uno o más archivos para comprimirlos
   static function archivos($files) {
      $files = is_array($files) ? $files : func_get_args();
      if (is_array($files) && count($files) > 0) {
         $llave = '';
         $ext   = substr($files[0], strrpos($files[0], '.')+1);
         ob_start();
         foreach($files as $file) {
            $llave .= $file.filemtime($file);
         }
         $archivo = self::$dir.md5($llave).'.'.$ext;
         if (!file_exists($archivo)) {
            foreach($files as $file) {
               require $file;
            }
            $cont = ob_get_clean();
            $cont = preg_replace('/(?<!\S)\/\/\s*[^\r\n]*/', '', $cont);
            $cont = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $cont);
            $cont = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $cont);
            file_put_contents($archivo, $cont);
         }
         return array($archivo);
      }
      return array();
   }
}
?>