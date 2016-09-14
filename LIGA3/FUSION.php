<?php
// Fusión de archivos para LIGA 3
class FUSION {
   static $dir = '';
   private function __construct() {}
   // Obtiene una versión comprimida de $cont
   private static function comprimir($cont) {
      $cont = preg_replace('/(?<!\S)\/\/\s*[^\r\n]*/', '', $cont);
      $cont = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $cont);
      $cont = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $cont);
      return $cont;
   }
   // Obtiene el nombre para cache del conjunto de archivos
   private static function nombre($files) {
      $llave = '';
      $ext   = substr($files[0], strrpos($files[0], '.')+1);
      foreach ($files as $file) {
         $llave .= $file.filemtime($file);
      }
      $llave = self::$dir.md5($llave).'.'.$ext;
      return $llave;
   }
   // Obtiene un arreglo con el nombre de una versión fusionada y comprimida de los archivos
   static function arreglo($files) {
      $files = is_array($files) ? $files : func_get_args();
      if (is_array($files) && count($files) > 0) {
         try {
            if (self::$dir != '' && !is_dir(self::$dir))
               throw new Exception();
            $archivo = self::nombre($files);
            if (!file_exists($archivo)) {
               ob_start();
               foreach ($files as $file) {
                  require $file;
               }
               $cont = ob_get_clean();
               file_put_contents($archivo, self::comprimir($cont));
            }
         } catch(Exception $e) {
            return $files;
         }
         return array($archivo);
      }
      return array();
   }
   // Obtiene un String con el contenido de una versión fusionada y comprimida de los archivos
   static function contenido($files) {
      $files = is_array($files) ? $files : func_get_args();
      if (is_array($files) && count($files) > 0) {
         $archivo = self::nombre($files);
         if (file_exists($archivo)) {
            return file_get_contents($archivo);
         } else {
            ob_start();
            foreach ($files as $file) {
               require $file;
            }
            $cont = ob_get_clean();
            $cont = self::comprimir($cont);
            if (is_dir(self::$dir))
             file_put_contents($archivo, $cont);
            return $cont;
         }
      }
      return '';
   }
}
?>