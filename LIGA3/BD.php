<?php
 /**
  * Manejador con MySQLi
  * Para LIGA 3
  * Autor: Mtro. Oscar Galileo García García
  */
 class BD {
    static  $conn;
    private $base;
    // Crea una nueva instancia de conexión a MySQL a partir de los parámetros dados
    function __construct($s='127.0.0.1', $u='root', $p='', $b='') {
        if (empty(self::$conn)) {
            self::$conn = new mysqli($s, $u, $p);
	    self::$conn->query("SET NAMES 'utf8'");
        }
        if (!empty($b)) {
            $this->base = (self::$conn->select_db($b)) ? $b : null;
        } else {
            $resp = $this->consulta('select database()');
            $resp = $resp->fetch_row();
            $this->base = $resp[0];
        }
    }
    // Ejecuta una consulta SQL y obtiene el resultado o la cantidad de filas afectadas
    static function SQL($sql) {
        $resp = self::$conn->query($sql);
        if(self::$conn->error) {
	    return '[<strong style="color:#2B8C45"><span style="color:#DECC0B">LI</span>GA</strong>] <strong style="color:#ED5818">Error de SQL:</strong> <em>'.self::$conn->error."</em> con [<strong>$sql</strong>]<br />\n";
        }
        if (strpos(strtolower($sql), 'select ') !== false || strpos(strtolower($sql), 'show ') !== false) {
            return $resp;
        }
        return self::$conn->affected_rows;
    }
    // Obtiene la base de datos seleccionada o selecciona alguna
    function base($b='') {
        if (empty($b)) {
            return (!empty($this->base)) ? $this->base : false;
        } else {
            if (self::$conn->select_db($b)) {
                $this->base = $b;
                return $b;
            }
            return false;
        }
    }
    // Obtiene el resultado a partir de la consulta SQL dada o la cantidad de filas afectadas
    function consulta($sql) {
        $resp = self::$conn->query($sql);
        if(self::$conn->error) {
	    return '[<strong style="color:#2B8C45"><span style="color:#DECC0B">LI</span>GA</strong>] <strong style="color:#ED5818">Error de SQL:</strong> <em>'.self::$conn->error."</em> con [<strong>$sql</strong>]<br />\n";
        }
        if (strpos(strtolower($sql), 'select ') !== false || strpos(strtolower($sql), 'show ') !== false) {
            return $resp;
        }
        return self::$conn->affected_rows;
    }
    // Obtiene un arreglo con la base y tabla a partir de la consulta
    function base_tabla($sql) {
        $sq = strtolower($sql);
        if (strpos($sq, 'from ') === false && strpos($sq, 'into ') === false && strpos($sq, 'update ') === false) {
            return $this->extrae($sql);
        } elseif (($pos = strpos($sq, 'from ')) !== false || ($pos = strpos($sq, 'into ')) !== false) {
            $queda = substr($sql, $pos+5);
            return $this->extrae($queda);
        } elseif (($pos = strpos($sq, 'update ')) !== false) {
            $queda = substr($sql, $pos+7);
            return $this->extrae($queda);
        }
        return array(false, false);
    }
    // Extrae el nombre de la base y tabla de una cadena
    private function extrae($s) {
        if(($s = trim($s)) === '') {
            return false;
        }
        $cad1 = '';
        $cad2 = '';
        if (($pos = strpos($s, '`')) !== false) {
            while ($s[++$pos] !== '`') {
                $cad1 .= $s[$pos];
            }
            if ($s[++$pos] === '.') {
                if ($s[++$pos] === '`') {
                    while ($s[++$pos] !== '`') {
                        $cad2 .= $s[$pos];
                    }
                }
            }
        } else {
            $ar = preg_split('/[. ]/', $s, 3);
            $cad1 = $ar[0];
            if(strpos($s, '.') !== false) {
                $cad2 = $ar[1];
            }
        }
        $cad1 = trim($cad1);
        if ($cad2 === '') {
            return ($this->base()) ? array($this->base(), $cad1) : array($cad1);
        }
        return array($cad1, trim($cad2));
    }
    // Obtenemos la meta información a partir de la consulta
    function meta($s) {
        $bt = $this->base_tabla($s);
        if (strpos(strtolower($s), 'select ') !== false) {
            if (($pos = strpos(strtolower($s), 'limit')) !== false) {
                $s = substr($s,0,$pos).' LIMIT 0';
            } else {
                $s .= ' LIMIT 0';
            }
        } else {
            $s = (count($bt) === 2) ? "select * from `$bt[0]`.`$bt[1]` LIMIT 0" : "select * from `$bt[0]` LIMIT 0";
        }
        $res = $this->consulta($s);
	$cols = array();
	if (is_string($res)) {
	    echo $res;
	} else {
	    while ($col = $res->fetch_field()) {
	       $nom  = $col->name;
	       $null = ($col->flags & 1) ? false : true;
	       $num  = ($col->flags & 32768) ? true : false;
	       $blob = ($col->flags & 16) ? true : false;
	       $tipo = 'integer';
	       $tipo = ($col->flags & 4 || $col->flags & 5 || $col->flags & 246) ? 'decimal' : $tipo;
	       $tipo = ($col->flags & 10) ? 'date' : $tipo;
	       $tipo = ($col->flags & 12) ? 'datetime' : $tipo;
	       $tipo = ($col->flags & 7) ? 'timestamp' : $tipo;
	       $tipo = ($col->flags & 11) ? 'time' : $tipo;
	       $tipo = ($col->flags & 252 || $col->flags & 253 || $col->flags & 254) ? 'string' : $tipo;
	       $cols[$nom] = array('tabla'=>$col->table,'null'=>$null,'num'=>$num,'blob'=>$blob,'tipo'=>$tipo,'pri'=>false,'ai'=>false,'codif'=>false,'com'=>false,'max'=>0);
	       if (count($bt) === 2) {
		   $resp = $this->consulta("show full columns from `$bt[0]`.`$bt[1]` like '$nom'");
		   if (!is_string($resp)) {
		       $resp = $resp->fetch_assoc();
		       $cols[$nom]['codif'] = $resp['Collation'];
		       $cols[$nom]['pri']   = ($resp['Key']==='PRI') ? true : false;
		       $cols[$nom]['ai']    = ($resp['Extra']==='auto_increment') ? true : false;
		       $cols[$nom]['com']   = $resp['Comment'];
		       $cols[$nom]['nulo']  = $resp['Null']==='NO' && $resp['Default']===null && !$cols[$nom]['ai'] ? false : true;
		       $tipo = $resp['Type'];
		       $cols[$nom]['type']  = $tipo;
		       $cols[$nom]['num']   = $tipo==='timestamp' ? false : $cols[$nom]['num'];
		       if (strpos($tipo,'(') !== false) {
			   $ini = strpos($tipo,'(')+1;
			   $fin = strpos($tipo,')');
			   $max = substr($tipo,$ini,$fin-$ini);
			   $cols[$nom]['max'] = $max;
		       }
		       $resp = $this->consulta("SELECT CONCAT(REFERENCED_TABLE_SCHEMA,'.',referenced_table_name,'::',referenced_column_name) AS foranea FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '$bt[0]' AND table_name = '$bt[1]' AND column_name = '$nom' AND REFERENCED_TABLE_NAME is not null");
		       if ($resp->num_rows === 1) {
			   $resp = $resp->fetch_assoc();
			   $cols[$nom]['referencia'] = $resp['foranea'];
		       }
		   }
	       }
	   }
	}
        return $cols;
    }
    // Obtenemos los registros a partir de la consulta
    function info($s, $q='', $l='') {
        $bt = $this->base_tabla($s);
        if (strpos(strtolower($s), 'select ') === false) {
            $s = (count($bt) === 2) ? "select * from `$bt[0]`.`$bt[1]`" : "select * from `$bt[0]`";
        }
        if (is_array($q) && count($q) > 0) {
            $sq   = '';
	    $meta = $this->meta($s);
            foreach ($q as $k => $v) {
                if (array_key_exists($k, $meta)) {
                    $k  = self::$conn->real_escape_string($k);
                    $v  = self::$conn->real_escape_string($v);
		    if ($meta[$k]['pri'])
		     $sq .= ($sq) ? " and `$k` = '$v'" : " where `$k` = '$v' ";
		    else $sq .= ($sq) ? " and `$k` like '%$v%'" : " where `$k` like '%$v%' ";
                }
            }
            $s .= $sq.$l;
        } else {
            $s .= $q.' '.$l;
        }
        $res = $this->consulta($s);
        $regs = array();
	if (!is_string($res)) {
	 while ($reg = $res->fetch_row()) {
	     $regs[] = $reg;
	 }
	 $res->free();
	} else {
	 $regs = false;
	}
        return $regs;
    }
    /**
     * Inserta registros en la tabla a partir del arreglo y
     * obtiene el número de inserciones o algún mensaje de error
     */
    function insertar($datos, $s) {
    	$tb = $this->base_tabla($s);
        if ($tb[0] && $tb[1]) {
            $cols = '(`'.implode('`,`', array_keys($datos)).'`)';
            foreach($datos as $k => $v) {
                $datos[$k] = self::$conn->real_escape_string($v);
            }
            $vals = "('".implode("','", $datos)."')";
            $sql  = "insert into `$tb[0]`.`$tb[1]` $cols value $vals";
            return $this->consulta($sql);
        }
        return 0;
    }
    /**
     * Modifica los registros indicados a partir del id proporcionado
     * y obtiene el número de modificaciones o algún mensaje de error
     */
    function modificar($datos, $s) {
    	$tb = $this->base_tabla($s);
    	if ($tb[0] && $tb[1]) {
    	    $base  = $tb[0];
    	    $tabla = $tb[1];
            $cual  = array_keys($datos);
            $cual  = $cual[0];
            $datos = $datos[$cual];
            if(strpos($cual, 'where ') === false) {
                $meta = $this->meta($base.'.'.$tabla);
                $cols = array_keys($meta);
                $llave = $cols[0];
                $cual = "where `$llave` = '$cual' ";
            }
            $sets = '';
            foreach($datos as $k => $v) {
            	$k = self::$conn->real_escape_string($k);
		$v = self::$conn->real_escape_string($v);
                $v = ($v == '[null]') ? 'null' : "'$v'";
                $sets .= ($sets==='') ? " `$k` = $v " : ", `$k` = $v ";
            }
            $sql = "update `$base`.`$tabla` set $sets $cual";
            return $this->consulta($sql);
        }
        return 0;
    }
    /**
     * Elimina los registros indicados a partir del id proporcionado
     * y obtiene el número de eliminaciones o algún mensaje de error
     */
    function eliminar($datos, $s) {
    	$tb = $this->base_tabla($s);
        if ($tb[0] && $tb[1]) {
            $base  = $tb[0];
            $tabla = $tb[1];
            $datos = is_array($datos) ? "'".implode("','", $datos)."'" : "'$datos'";
            if (strpos($datos, 'where ') === false) {
                $meta = $this->meta($base.'.'.$tabla);
                $cols = array_keys($meta);
                $llave = $cols[0];
		$datos = "where `$llave` in ($datos) ";
            }
            $sql = "delete from `$base`.`$tabla` $datos";
            return $this->consulta($sql);
        }
        return 0;
    }
 }
?>