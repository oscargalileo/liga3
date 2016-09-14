<?php
/**
 * Clase HTML
 * Para LIGA 3
 * Autor: Mtro. Oscar Galileo García García
 */
class HTML {
	// No se puede instanciar, quite () y use :: para usar sus funciones
	private function __construct() {
	}
	// Obtiene una cadena mejorada para mostrar en los títulos de columna
	private static function mejorar($cad) {
		$cad = preg_replace('/([a-z])([A-Z])/', '$1 $2', $cad);
		return utf8_encode(ucwords(strtolower(str_replace('_',' ',utf8_decode($cad)))));
	}
	// Obtiene una cadena a partir de un arreglo al estilo de propiedades HTML
	private static function array2props($prop) {
		$ret = '';
		if (is_array($prop) && count($prop) > 0) {
			foreach ($prop as $k => $v) {
				$v = htmlentities($v, ENT_QUOTES, 'UTF-8');
				$ret .= " $k=\"$v\"";
			}
		} else {
			$ret = " $prop";
		}
		return $ret;
	}
	// Obtiene una etiqueta con las propiedades correspondientes procesadas
	private static function etiq_props($liga, $etiq, $props, $procesar=true, $agregar=false) {
		$ret = $etiq;
		if (is_array($props) && count($props) > 0) {
			if (array_key_exists($etiq, $props)) {
				$ret .= self::array2props($props[$etiq]);
			}
			$ret .= self::prop_cond($etiq, $props);
		}
		$ret .= (is_string($agregar) || is_array($agregar)) ? self::array2props($agregar) : '';
		$ret = ($procesar) ? htmlentities(self::procesar($liga, $ret), ENT_NOQUOTES, 'UTF-8') : $ret;
		return "<$ret>";
	}
	// Obtiene el código necesario para aplicar propiedades condicionadas
	private static function prop_cond($col, $props) {
		if (!is_array($props)) return '';
		$ret = '';
		foreach ($props as $k => $v) {
			if (strpos($k, "$col@si(") !== false) {
				$cond = substr($k, strpos($k, '('));
				$v = str_replace('"', '\"', self::array2props($v));
				$ret .= '@{if '.$cond.' echo "'.$v.'"}@';
			}
		}
		return $ret;
	}
	// Obtiene las columnas expandiendo * por todas
	private static function todos($liga, $cols) {
		$todas = array_keys($liga->meta());
		if (in_array('*', $cols)) {
			$pri  = array();
			$seg  = array();
			$otro = true;
			foreach ($cols as $k => $v) {
				if ($v == '*' && $otro) {
					unset($cols[$k]);
					$otro = false;
					continue;
				} elseif (strpos($v, '-') === 0) {
					$idx = array_search($liga->num2col(substr($v, 1)), $todas);
					if ($idx !== false)
						unset($todas[$idx]);
				}
				($otro) ? ($pri[$k] = $v) : ($seg[$k] = $v);
			}
			$cols  = array_merge($pri, $todas, $seg);
			$cols  = self::todos($liga, $cols);
		} else {
			// Recorro las columnas y si contiene -columna la descartamos
			foreach ($cols as $k => $v) {
				if (strpos($v, '-') === 0) {
					unset($cols[$k]);
				}
			}
		}
		return $cols;
	}
	// Obtiene una cadena procesada con vars y ejec con el índice y objeto LIGA
	private static function procesar($liga, $cad, $ind=0, $comillas=false) {
		return $liga->ejec($liga->vars($ind, $cad, $comillas));
	}
	// Genera los encabezado HTML5 de la página (http://html5boilerplate.com)
	static function cabeceras($config) {
		echo '<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>'.$config['title'].'</title>
		<meta name="description" content="'.$config['description'].'">
		<meta name="viewport" content="width=device-width">';
		if (!empty($config['meta'])) {
			$config['meta'] = is_array($config['meta']) ? $config['meta'] : array($config['meta']);
			foreach ($config['meta'] as $name => $content) {
				echo "\n\t\t<meta name=\"$name\" content=\"$content\">";
			}
		}
		if (!empty($config['css'])) {
			//$config['css'] = is_array($config['css']) ? $config['css'] : array($config['css']);
			$config['css'] = is_array($config['css']) ? $config['css'] : explode(',', $config['css']);
			foreach ($config['css'] as $css) {
				echo "\n\t\t",'<link rel="stylesheet" href="'.trim($css).'">';
			}
		}
		echo !empty($config['style']) ? "\n\t\t<style>$config[style]</style>" : '';
		if (!empty($config['js'])) {
			$config['js'] = is_array($config['js']) ? $config['js'] : explode(',', $config['js']);
			foreach ($config['js'] as $js) {
				echo "\n\t\t",'<script src="'.trim($js).'"></script>';
			}
		}
		echo !empty($config['script']) ? "\n\t\t<script>$config[script]</script>" : '';
		flush();
		echo "\n\t",'</head>
	<body>
	 <!--[if lt IE 7]>
	 <p class="chromeframe">Está usando un navegador <strong>desactualizado</strong>. Favor de <a href="http://browsehappy.com/">actualizarlo</a> o <a href="http://www.google.com/chromeframe/?redirect=true">active Google Chrome Frame</a> para mejorar su experiencia.</p>
	 <![endif]-->'."\n";
		flush();
	}
	// Genera el cuerpo del documento a partir del array asociativo
	static function cuerpo($config) {
		foreach ($config as $id => $cont) {
			echo "<div id=\"$id\">";
			echo is_string($cont) ? $cont : self::cuerpo($cont);
			echo '</div>';
			flush();
		}
	}
	// Genera los etiquetas de cierre del HTML5 (http://html5boilerplate.com)
	static function pie($config = array()) {
		if (!empty($config['js'])) {
			$config['js'] = is_array($config['js']) ? $config['js'] : array($config['js']);
			foreach ($config['js'] as $i => $js) {
				echo "\t\t<script src=\"$js\"></script>\n";
			}
		}
		echo !empty($config['script']) ? "\t\t    <script>$config[script]</script>\n" : '';
		flush();
		echo !empty($config['UA']) ? "\t\t<script>var _gaq=[['_setAccount','$config[UA]'],['_trackPageview']];
			(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
			g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
			s.parentNode.insertBefore(g,s)}(document,'script'));</script>\n" : '';
		echo "\n\t</body>\n</html>";
	}
	// Genera una tabla HTML a partir del objeto LIGA y los parámetros indicados
	static function tabla($liga, $caption=false, $cols=false, $props=false, $joins=false, $pie=false) {
		echo self::etiq_props($liga, 'table', $props);
		if ($caption && is_string($caption) && !empty($caption)) {
			echo self::etiq_props($liga, 'caption', $props);
			echo htmlentities($caption, ENT_NOQUOTES, 'UTF-8');
			echo "</caption>\n";
		}
		if ($joins && is_array($joins) && count($joins) > 0) {
			foreach ($joins as $k => $v) {
				if ($liga->existe($k)) {
					$v = (is_array($v)) ? $v : $v->arreglo();
					if (is_array($v) && count($v) > 0) {
						$con = 0;
						while (($dato = $liga->d($con, $k)) !== null) {
							(isset($v[$dato])) ? $liga->cambiar($con, $k, $v[$dato]) : '';
							$con++;
						}
					}
				}
			}
		}
		$ths = '';
		$tds = '';
		if (is_bool($cols) || $cols == '*') {
			$cols = array_keys($liga->meta());
		}
		$cols = is_string($cols) ? array_map('trim',explode(',', $cols)) : $cols;
		$cols = self::todos($liga, $cols);
		if (is_array($cols) && count($cols) > 0) {
			foreach($cols as $k => $v) {
				$col  = is_string($k) ? $k : $v;
				$num  = $liga->col2num($col);
				$prop = (isset($props["th[$col]"])) ? htmlentities(self::procesar($liga, self::array2props($props["th[$col]"])), ENT_NOQUOTES, 'UTF-8') : '';
				$prop = (isset($props["th[$num]"])) ? htmlentities(self::procesar($liga, self::array2props($props["th[$num]"])), ENT_NOQUOTES, 'UTF-8') : '';
				$prop .= (isset($props['th'])) ? htmlentities(self::procesar($liga, self::array2props($props['th'])), ENT_NOQUOTES, 'UTF-8') : '';
				$prop .= self::prop_cond("th[$col]", $props);
				$prop .= self::prop_cond("th[$num]", $props);
				$prop .= self::prop_cond('th', $props);
				$ths  .= is_string($k) ? "<th$prop>".$liga->num2col($col).'</th>' : "<th$prop>".self::mejorar($liga->num2col($col)).'</th>';
				$prop = (isset($props["td[$col]"])) ? self::array2props($props["td[$col]"]) : '';
				$prop = (isset($props["td[$num]"])) ? self::array2props($props["td[$num]"]) : '';
				$prop .= (isset($props[$col])) ? self::array2props($props[$col]) : '';
				$prop .= (isset($props[$num])) ? self::array2props($props[$num]) : '';
				$prop .= (isset($props['td'])) ? self::array2props($props['td']) : '';
				$prop .= self::prop_cond("td[$col]", $props);
				$prop .= self::prop_cond($col, $props);
				$prop .= self::prop_cond("td[$num]", $props);
				$prop .= self::prop_cond($num, $props);
				$prop .= self::prop_cond('td', $props);
				$tds  .= (is_string($k) && is_string($v) && $v != '') ? "<td$prop>$v</td>" : "<td$prop>@[$col]</td>";
			}
		} else {
			$ths = '<th>[LIGA] Error en el parámetro de columnas</th>';
		}
		echo self::etiq_props($liga, 'thead', $props);
		echo "<tr>$ths</tr>\n</thead>\n";
		if ($pie && is_string($pie) && !empty($pie)) {
			echo self::etiq_props($liga, 'tfoot', $props);
			$pie = str_replace('@[numCols]', count($cols), $pie);
			$pie = self::procesar($liga, $pie);
			echo (strpos($pie, '<tr') === false) ? "<tr>$pie</tr>" : $pie;
			echo '</tfoot>';
		}
		echo self::etiq_props($liga, 'tbody', $props);
		$tr = self::etiq_props($liga, 'tr', $props, false);
		if ($liga->numReg() > 0) {
			$liga->registros("$tr$tds</tr>");
		} else {
			echo "<tr><th colspan='".count($cols)."'>Sin registros</th></tr>";
		}
		echo "</tbody>\n";
		echo "</table>\n";
		$liga->actualizar();
	}
	// Show a HTML table from a LIGA object and parameters
	static function table($liga, $caption=false, $cols=false, $props=false, $joins=false, $foot=false) {
		self::tabla($liga, $caption, $cols, $props, $joins, $foot);
	}
	// Genera un formulario HTML a partir de la tabla o consulta vinculada
	static function forma($liga, $legend=false, $cols=false, $props=false, $completo=true, $vals=false) {
		if ($liga->numCol() > 0 || $cols) {
			echo $completo ? self::etiq_props($liga, 'form', $props).self::etiq_props($liga, 'fieldset', $props) : '';
			if ($legend && is_string($legend) && !empty($legend)) {
				echo self::etiq_props($liga, 'legend', $props);
				echo htmlentities($legend, ENT_NOQUOTES, 'UTF-8');
				echo "</legend>\n";
			}
			$cols = (is_array($cols) && count($cols) > 0) ? $cols : ((is_string($cols)) ? array_map('trim',explode(',', $cols)) : array_keys($liga->meta()));
			$cols = self::todos($liga, $cols);
			if (count($cols) > 0) {
				foreach ($cols as $k => $v) {
					$col = is_string($k) ? trim($k) : trim($v);
					if (!$liga->p($col, 'ai')) {
						echo self::etiq_props($liga, 'div', $props);
						$label = (is_string($k) && is_string($v) && $v != '' && strpos($v, '<') === false) ? self::procesar($liga, $v) : self::mejorar($col);
						$pref = (isset($props['prefid'])) ? self::procesar($liga, $props['prefid']) : '';
						if (strpos($label, '<label for') === false && $label != '') {
							$prop = (isset($props["label[$col]"])) ? htmlentities(self::procesar($liga, self::array2props($props["label[$col]"])),ENT_NOQUOTES,'UTF-8') : '';
							$prop .= self::procesar($liga, self::prop_cond("label[$col]", $props));
							$prop .= (isset($props['label'])) ? htmlentities(self::procesar($liga, self::array2props($props['label'])),ENT_NOQUOTES,'UTF-8') : '';
							$prop .= self::procesar($liga, self::prop_cond('label', $props));
							$req = ($liga->p($col, 'nulo') === false) ? ' *' : '';
							$label = "<label for=\"$pref$col\"$prop>$label$req</label> ";
						}
						echo $label;
						if (strpos($v, '<') !== false) {
							echo $v;
						} elseif ($liga->existe($col)) {
							$prop  = (isset($props["input[$col]"])) ? htmlentities(self::procesar($liga, self::array2props($props["input[$col]"])),ENT_NOQUOTES,'UTF-8') : '';
							$prop .= self::procesar($liga, self::prop_cond("input[$col]", $props));
							$prop .= (isset($props['input'])) ? htmlentities(self::procesar($liga, self::array2props($props['input'])),ENT_NOQUOTES,'UTF-8') : '';
							$prop .= (isset($props[$col])) ? htmlentities(self::procesar($liga, self::array2props($props[$col])),ENT_NOQUOTES,'UTF-8') : '';
							$prop .= self::procesar($liga, self::prop_cond('input', $props));
							$max = $liga->p($col, 'max') ? ' maxlength="'.$liga->p($col, 'max').'"' : '';
							$com = $liga->p($col, 'com') ? ' title="'.$liga->p($col, 'com').'"' : '';
							if (($ref = $liga->p($col, 'referencia'))) {
								$objRef = LIGA(substr($ref, 0, strpos($ref, '::')));
								$colRef = substr($ref, strpos($ref, '::')+2);
								$value = (isset($vals[$col])) ? self::procesar($liga, $vals[$col]) : '';
								$propRef = array('select'=>"id='$pref$col' name='$col'$com$prop", 'option'=>"value='@[$colRef]'", 'option@si("@['.$colRef.']"=="'.$value.'")'=>'selected="selected"');
								echo self::selector($objRef, '1', $propRef);
							} elseif ($liga->p($col, 'blob')) {
								$value = (isset($vals[$col])) ? self::procesar($liga, $vals[$col]) : '';
								echo "<textarea id='$pref$col' name='$col'$max$com$prop>$value</textarea>";
							} else {
								$value = (isset($vals[$col])) ? ' value="'.self::procesar($liga, $vals[$col]).'"' : '';
								$fecha = strpos($liga->p($col, 'tipo'), 'date') === false ? '' : ' class="fecha"';
								$fecha = strpos($liga->p($col, 'tipo'), 'stamp') === false ? $fecha : ' class="fecha"';
								echo "<input id='$pref$col' name='$col'$max$com$fecha$prop$value />";
							}
						} else {
							$value = (isset($vals[$col])) ? ' value="'.self::procesar($liga, $vals[$col]).'"' : '';
							echo "<input id='$pref$col' name='$col'$value />";
						}
						echo "</div>\n";
					}
				}
			}
			echo '<div class="botones" style="clear:both">'."\n";
			echo (isset($props['submit']) && is_string($props['submit'])) ? $props['submit'] : '<input type="submit" value="Enviar" />';
			echo (isset($props['reset']) && is_string($props['reset'])) ? $props['reset'] : '<input type="reset" value="Limpiar" />';
			echo "</div>\n";
			echo $completo ? "</fieldset>\n</form>\n" : '';
		} else {
			echo '[LIGA] Parámetros incorrectos';
		}
	}
	// Show a HTML form from a LIGA object and parameters
	static function form($liga, $legend=false, $cols=false, $props=false, $complete=true, $values=false) {
		self::forma($liga, $legend, $cols, $props, $complete, $values);
	}
	// Genera un selector (select) HTML a partir de la tabla o consulta vinculada
	static function selector($liga, $cols='1', $props=false, $optgroup=false, $completo=true, $car=0) {
		ob_start();
		echo ($completo) ? self::etiq_props($liga, 'select', $props) : '';
		if ($liga->numCol() > 0 && $liga->numReg() > 0) {
			$cont = ($liga->existe($cols)) ? "@[$cols]" : $cols;
			$cont = (is_integer($car) && $car > 0) ? "@{if($car<strlen(utf8_decode(html_entity_decode('$cont', ENT_QUOTES, 'UTF-8')))-1)return substr(html_entity_decode('$cont', ENT_QUOTES, 'UTF-8'),0,$car).'...';else return html_entity_decode('$cont', ENT_QUOTES, 'UTF-8')}@" : $cont;
			$option = self::etiq_props($liga, 'option', $props, false);
			$option = "$option$cont</option>";
			$llaves = is_array($optgroup) ? array_keys($optgroup) : array();
			$llave  = current($llaves);
			if (is_array($optgroup) && count($optgroup) > 0 && $liga->existe($llave)) {
				$grupos = array_values($optgroup);
				$grupos = current($grupos);
				if (get_class($grupos) == 'LIGA') {
					$grupos = $grupos->arreglo();
				}
				foreach ($grupos as $k => $v) {
					$v = htmlentities($v, ENT_QUOTES, 'UTF-8');
					$prop = (isset($props['optgroup'])) ? self::procesar($liga, $props['optgroup']) : '';
					$prop .= self::prop_cond('optgroup', $props);
					echo "<optgroup label='$v'$prop>";
					$info = $liga->info();
					foreach ($info as $idx => $dat) {
						if ($liga->cond($idx, "@[$llave]=='$k'")) {
							echo self::procesar($liga, $option, $idx, false);
						}
					}
					echo '</optgroup>';
				}
			} else {
				$liga->registros($option, false);
			}
		} else {
			echo '<option>Sin registros</option>';
		}
		echo ($completo) ? '</select>' : '';
		return ob_get_clean();
	}
	// Show a HTML select (combobox) from a LIGA object and parameters
	static function select($liga, $cols='1', $props=false, $optgroup=false, $complete=true, $car=0) {
		self::selector($liga, $cols, $props, $optgroup, $complete, $car);
	}
	// Genera una lista (ol o ul) HTML a partir del objeto LIGA indicado
	static function lista($liga, $cols='1', $numeros=false, $props=false, $completa=true) {
		$tipo = ($numeros) ? 'ol' : 'ul';
		echo ($completa) ? self::etiq_props($liga, $tipo, $props) : '';
		if ($liga->numCol() > 0 && $liga->numReg() > 0) {
			$cont = ($liga->existe($cols)) ? "@[$cols]" : $cols;
			$li = self::etiq_props($liga, 'li', $props, false);
			$liga->registros("$li$cont</li>");
		}
		echo ($completa) ? "</$tipo>" : '';
	}
	// Show a OL HTML list from a LIGA object and parameters
	static function ol($liga, $cols='1', $props=false, $complete=true) {
		self::lista($liga, $cols, true, $props, $complete);
	}
	// Show a UL HTML list from a LIGA object and parameters
	static function ul($liga, $cols='1', $props=false, $complete=true) {
		self::lista($liga, $cols, false, $props, $complete);
	}
	// Genera estructuras tipo botones radio o checkbox a partir de un arreglo u objeto LIGA
	private static function opciones($tipo, $obj, $nombre, $props=false, $derecha=false) {
		if (is_array($obj) && count($obj)) {
			foreach ($obj as $k => $v) {
				$propsL = isset($props['label']) ? self::array2props($props['label']) : '';
				$propsL.= isset($props["label[$v]"]) ? self::array2props($props["label[$v]"]) : '';
				$propsI = isset($props['input']) ? self::array2props($props['input']) : '';
				$propsI.= isset($props["input[$k]"]) ? self::array2props($props["input[$k]"]) : '';
				echo ($derecha) ? "<label$propsL><input type='$tipo' name='$nombre' value='$k'$propsI>$v</label> " : "<label$propsL>$v<input type='$tipo' name='$nombre' value='$k'$propsI></label> ";
			}
		} elseif (get_class($obj) == 'LIGA' && $obj->numCol() > 1) {
			$etiq  = self::etiq_props($obj, 'input', $props, false, "type='$tipo' name='$nombre' value='@[0]'");
			$label = self::etiq_props($obj, 'label', $props, false);
			$opcion  = ($derecha) ? "$label$etiq</input>@[1]</label> " : "$label@[1]$etiq</input></label> ";
			$obj->registros($opcion);
		}
	}
	// Genera un grupo de botones radio a partir del arreglo u objeto LIGA dado
	static function radio($obj, $nombre, $props=false, $derecha=false) {
		ob_start();
		self::opciones('radio', $obj, $nombre, $props, $derecha);
		return ob_get_clean();
	}
	// Genera un grupo de botones checkbox a partir del arreglo u objeto LIGA dado
	static function checkbox($obj, $nombre, $props=false, $derecha=false) {
		ob_start();
		self::opciones('checkbox', $obj, $nombre, $props, $derecha);
		return ob_get_clean();
	}
}
?>