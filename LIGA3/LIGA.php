<?php
 /**
  * LIGA 3
  * Autor: Mtro. Oscar Galileo García García
  */
 function __autoload($clase) {
    require_once "$clase.php";
 }
 // Permite crear el objeto LIGA sin new
 function LIGA($s, $q='', $l='') {
    return new LIGA($s, $q, $l);
 }
 function LIGAC($s, $q='', $l='') {
    return new LIGAC($s, $q, $l);
 }
 // Personaliza una conexión a la base de datos sin new
 function BD($s='127.0.0.1', $u='root', $p='', $b='') {
    return new BD($s, $u, $p, $b);
 }
 
 class LIGA {
    private $s, $q, $l;
    private $bd;
    public  $meta = array();
    public  $info = array();
    private $idx = 0;
    // Crea una nueva instancia del objeto LIGA a partir de los parámetros
    function __construct($s1, $s2='', $s3='') {
        if (is_array($s1) && count($s1) > 0) {
            $s3 = isset($s1[2]) ? $s1[2] : $s3;
            $s2 = isset($s1[1]) ? $s1[1] : $s2;
            $s1 = isset($s1[0]) ? $s1[0] : '';
        }
        if (is_string($s1) && !empty($s1)) {
            $this->bd = BD();
            $this->s  = $s1;
            $this->q  = $s2;
            $this->l  = $s3;
        } else {
            throw new Exception("`[LIGA] Parámetros incorrectos LIGA($s1, $s2, $s3)´");
        }
    }
    // Obtiene y/o actualiza la meta información a partir de la consulta o tabla
    function meta($f=false) {
        if (count($this->meta) === 0 || $f) {
            return ($this->meta = $this->bd->meta($this->s));
        }
        return $this->meta;
    }
    //Obtiene y/o actualiza los registros a partir de la consulta completa
    function info($f=false) {
        if (count($this->info) === 0 || $f) {
            return ($this->info = $this->bd->info($this->s, $this->q, $this->l));
        }
        return $this->info;
    }
    // Limpia la caché del objeto para atraer registros actualizados
    function actualizar() {
        $this->info = array();
        $this->idx = 0;
    }
    // Obtiene el nombre de una columna a partir de un índice numérico si existe
    function num2col($num) {
        if (is_numeric($num)) {
            $num = intval($num);
            $cols = array_keys($this->meta());
            if (isset($cols[$num])) {
                return $cols[$num];
            }
        }
        return $num;
    }
    // Obtiene el número de una columna a partir del nombre si existe
    function col2num($col) {
        if (is_string($col) && $col != '') {
            $cols = array_keys($this->meta());
            $pos = array_search($col, $cols);
            if ($pos !== false) {
                return $pos;
            }
        }
        return $col;
    }
    // Obtiene true si la columna existe sino false
    function existe($col) {
        $col = trim($this->num2col($col));
        return (is_string($col) && array_key_exists($col, $this->meta()));
    }
    // Obtiene el valor de la propiedad para la columna dada o null si no existe
    function prop($col, $prop) {
        $col = trim($this->num2col($col));
        $prop = trim($prop);
        if ($this->existe($col)) {
            $meta = $this->meta();
            if (array_key_exists($prop, $meta[$col])) {
                return $meta[$col][$prop];
            }
        }
        return null;
    }
    // Alias de prop
    function p($col, $prop) {
      return $this->prop($col, $prop);
    }
    // Obtiene el número de registros en la tabla o consulta vinculada
    function numReg() {
        return count($this->info());
    }
    // Obtiene el número de columnas en la tabla o consulta vinculada
    function numCol() {
        return count($this->meta());
    }
    // Obtiene una fila (arreglo simple o asociativo) a partir del índice dado
    function fila($ind, $cols=true) {
        $info = $this->info();
        if (isset($info[$ind])) {
            if ($cols) {
                $llaves = array_keys($this->meta());
                return array_combine($llaves, array_slice($info[$ind], 0, count($llaves)));
            }
            return $info[$ind];
        }
        return null;
    }
    // Obtiene algún dato a partir del índice y el nombre de la columna
    function dato($ind, $col) {
        $fila = (is_numeric($col)) ? $this->fila($ind, false) : $this->fila($ind);
        return (isset($fila[trim($col)])) ? $fila[trim($col)] : null;
    }
    // Alias de dato, se puede omitir el índice si este es cero
    function d($ind, $col='') {
      return is_int($ind) ? $this->dato($ind, $col) : $this->dato(0, $ind);
    }
    // Obtiene todas las filas (arreglos simples o asociativos) de la tabla o consulta
    function filas($cols=true) {
        if ($fila = $this->fila($this->idx++, $cols)) {
            return $fila;
        } else {
            $this->idx = 0;
        }
        return null;
    }
    // Obtiene los registros que coinciden con los parámetros de búsqueda
    function buscar($q, $cols=true) {
        if (count($q) === 0) return array();
        $r = array();
        $c = count($q);
        while ($f = $this->filas($cols)) {
            $i = (array_intersect_assoc($q, $f));
            if (count($i) == $c) $r[] = $f;
        }
        return $r;
    }
    // Obtiene un arreglo simple con todos los valores de la columna especificada
    function columna($col) {
        $datos = array();
        while ($this->filas()) {
            if (($d = $this->d($this->idx-1, $col))) {
                $datos[] = $d;
            }
        }
        return $datos;
    }
    // Obtiene un arreglo asociativo a partir de 2 columnas del objeto LIGA
    function arreglo($col1=0, $col2=1) {
        if ($this->existe($col1) && $this->existe($col2)) {
            return array_combine($this->columna($col1), $this->columna($col2));
        }
        return array();
    }
    // Cambia algún dato en el objeto LIGA a partir del índice y la columna dada
    function cambiar($ind, $col, $dato) {
        $col  = $this->col2num($col);
        $info = $this->info();
        if (isset($info[$ind][$col])) {
            return ($this->info[$ind][$col] = $dato);
        }
        return null;
    }
    // Obtiene una cadena procesada con las variables especiales @[col,prop]
    function vars($ind, $cad, $com=false) {
        if ($this->fila($ind) && ($pos1 = strpos($cad, '@[')) !== false && ($pos2 = strpos($cad, ']', $pos1+2)) !== false) {
            $i = $pos1;
            $f = $pos2;
            $cont = substr($cad, $i, $f-$i+1);
            $pars = explode(',', substr($cad, $i+2, $f-$i-2), 2);
            $pars[0] = html_entity_decode($pars[0], ENT_QUOTES, 'UTF-8');
            $res = (isset($pars[1])) ? $this->p($pars[0], $pars[1]) : $this->d($ind, $pars[0]);
            $res = htmlentities($res, ENT_NOQUOTES, 'UTF-8');
            if (!$this->existe($pars[0])) {
                $pars = trim($pars[0]);
                if ($pars === 'numReg') {
                    $res = $this->numReg();
                } elseif ($pars === 'numCol') {
                    $res = $this->numCol();
                } else {
                    $res = "`[LIGA]: Columna '$pars' no encontrada´";
                }
            }
            $res = ($com) ? "'$res'" : $res;
            $cad = str_replace($cont, $res, $cad);
            $cad = $this->vars($ind, $cad, $com);
        }
        return $cad;
    }
    // Obtiene la cadena después de ejecutar con eval lo que está entre @{ }@
    function ejec($cad, $comillas=false) {
        if (($pos1 = strpos($cad, '@{')) !== false && ($pos2 = strpos($cad, '}@', $pos1+2)) !== false) {
            $i = $pos1;
            $f = $pos2;
            $cont = substr($cad, $i, $f-$i+2);
            $cod  = substr($cad, $i+2, $f-$i-2);
            $cod  = (strpos($cod, 'echo') === false && strpos($cod, 'return') === false) ? "return $cod" : $cod;
            ob_start();
            $ret  = @eval($cod.';');
            $res  = ob_get_clean();
            $res .= $ret;
            $res  = htmlentities($res, ENT_NOQUOTES, 'UTF-8');
            $res  = ($comillas) ? "'$res'" : $res;
            $cad  = str_replace($cont, $res, $cad);
            $cad  = $this->ejec($cad, $comillas);
        }
        return $cad;
    }
    // Obtiene true si $cad pasa la validación a partir del registro indicado
    function cond($ind, $cad) {
        if ($this->fila($ind)) {
            $cad = $this->vars($ind, $cad, true);
            $res = $this->ejec('@{return (('.$cad.')===true)}@');
            return ($res==1);
        }
        return false;
    }
    // Muestra en pantalla todos los registros a partir del formato indicado en $cad
    function registros($cad) {
        if (is_string($cad) && !empty($cad) && strpos($cad, '@[') !== false && $this->numReg() && $this->numCol()) {
            $info = $this->info();
            foreach($info as $k => $v) {
                echo $this->ejec($this->vars($k, $cad));
            }
            return true;
        }
        return false;
    }
    // Inserta un registro nuevo a partir del arreglo filtrando los valores
    function insertar($datos) {
        if (is_array($datos) && count($datos) > 0) {
            $dats = array();
            foreach ($datos as $k => $v) {
                if ($this->existe($k) && trim($v) !== '') {
                    $dats[$k] = $v;
                }
            }
            if (count($dats) > 0) {
                $res = $this->bd->insertar($dats, $this->s);
                if (is_numeric($res) && $res > 0) {
                    $this->actualizar();
                }
                return $res;
            }
        }
        return 0;
    }
    // Modifica uno o más registros a partir del valor de la llave primaria o condición where
    function modificar($datos) {
        if (is_array($datos) && count($datos) > 0) {
            $llaves = array_keys($datos);
            $llave = $llaves[0];
            $datos = $datos[$llave];
            $dats  = array();
            foreach ($datos as $k => $v) {
                if ($this->existe($k) && trim($v) !== '') {
                    $dats[$k] = $v;
                }
            }
            if (count($dats) > 0) {
                $res = $this->bd->modificar(array($llave => $dats), $this->s);
                if (is_numeric($res) && $res > 0) {
                    $this->actualizar();
                }
                return $res;
            }
        }
        return 0;
    }
    // Elimina registros a partir de los valores de su llave primaria
    function eliminar($datos) {
        if (!empty($datos)) {
            $res = $this->bd->eliminar($datos, $this->s);
            if (is_numeric($res) && $res > 0) {
                $this->actualizar();
            }
            return $res;
        }
        return 0;
    }
 }
?>
