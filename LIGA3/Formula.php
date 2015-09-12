<?php
 // Formulas para LIGA 3
 class Formula {
    // No se puede instanciar, quite () y use :: para usar sus funciones
    private function __construct() {}
    // Obtiene la sumatoria de los valores numéricos de una columna
    private static function operacion($tipo, $liga, $col, $cond=false) {
        if ($liga->existe($col)) {
            if ($cond && is_string($cond) && strpos($cond, '#[') !== false) {
                $cond = str_replace('#[', '@[', $cond);
                $num  = 0;
                $con  = 0;
                $pasa = 0;
                while ($liga->filas()) {
                    if ($liga->cond(++$con, $cond) && ($dato = strval($liga->dato($con, $col)))) {
                        if ($tipo == 'prod') {
                            ($num) ? ($num *= $dato) : ($num = $dato*1);
                        } else {
                            $num += $dato;
                        }
                        $pasa++;
                    }
                }
                return ($tipo == 'prom') ? $num/$pasa : $num;
            }
            $col = $liga->columna($col);
            if ($tipo == 'sum') {
                return array_sum($col);
            } elseif ($tipo == 'prod') {
                return array_product($col);
            }
            return array_sum($col)/$liga->numReg();
        }
        return "No existe la columna '$col'";
    }
    // Obtiene la sumatoria de una columna numérica
    static function sum($liga, $col, $cond=false) {
        return self::operacion('sum', $liga, $col, $cond);
    }
    // Obtiene el producto de una columna numérica
    static function prod($liga, $col, $cond=false) {
        return self::operacion('prod', $liga, $col, $cond);
    }
    // Obtiene el promedio de una columna numérica
    static function prom($liga, $col, $cond=false) {
        return self::operacion('prom', $liga, $col, $cond);
    }
    // Obtiene el mínimo o máximo de una columna numérica
    private static function min_max($tipo, $liga, $col) {
        if ($liga->existe($col)) {
            $col = $liga->columna($col);
            return ($tipo == 'min') ? min($col) : max($col);
        }
        return "No existe la columna '$col'";
    }
    // Obtiene el máximo valor de una columna numérica
    static function max($liga, $col) {
        return self::min_max('max', $liga, $col);
    }
    // Obtiene el mínimo valor de una columna numérica
    static function min($liga, $col) {
        return self::min_max('min', $liga, $col);
    }
 }
?>