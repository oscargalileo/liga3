<?php
 // Cargamos LIGA3
 require_once 'LIGA3/LIGA.php';
 
 // Personalizo una conexión a la base de datos (servidor, usuario, contraseña, schema)
 BD('localhost', 'root', '', 'base');
 
 // Configuramos la entidad a usar
 $tabla = 'usuarios';
 $liga  = LIGA($tabla);

 // Controlador de acciones
 $resp  = '';
 if (isset($_GET['accion'])) {
  if ($_GET['accion'] == 'insertar') {
   $resp = $liga->insertar($_POST);
   
  } elseif ($_GET['accion'] == 'modificar') {
   $datos = array($_POST['cual']=>$_POST);
    $resp = $liga->modificar($datos);
   }
 }
 if (isset($_GET['borrar'])) {
  $resp = $liga->eliminar($_GET['borrar']);
 }

 // Si es una petición asíncrona sólo muestro la respuesta
 if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
  echo $resp;
  exit(0);
 }
  // Imprimo las etiquetas HTML iniciales
  HTML::cabeceras(array('title'      =>'Pruebas LIGA 3',
			'description'=>'Página de pruebas para LIGA 3',
			'css'        =>'util/LIGA.css',
			'style'      =>'label { width:100px; }'
			)
		  );

 // Guardo el bufer para colocarlo en el layout
 ob_start();
 // Tabla con instancias
  $cols = array('*', '-contraseña', 'acción'=>'<a href="?borrar=@[0]">Borrar</a>');
  $join = array('depende'=>$liga);
  $pie  = '<th colspan="@[numCols]">Total de instancias: @[numReg]</th>';
  HTML::tabla($liga, 'Instancias de '.$tabla, $cols, true, $join, $pie);
  
  // Formulario para crear nuevas instancias
  $props  = array('form'=>'method="POST" action="?accion=insertar"',
		  'input[puesto]'=>array('required'=>'required'));
  $campos = array('*', '-fecha');
  HTML::forma($liga, 'Registro de '.$tabla, $campos, $props, true, $_POST);
  
  // Formulario para modificar instancias
  $props  = array('form'=>array('method'=>'POST', 'action'=>'?accion=modificar'), 'prefid'=>'algo',
		  'input[puesto]'=>array('required'=>'required'));
  $cual   = !empty($_POST['cual']) ? $_POST['cual'] : '';
  $select = HTML::selector($liga, 1, array('select'=>array('name'=>'cual', 'id'=>'algocual'),
							 'option'=>array('value'=>'@[0]'),
							 "option@si('$cual'=='@[0]')"=>array('selected'=>'selected')), array('depende'=>$liga)
			   );
  $campos = array('cual'=>$select, '*', '-fecha');
  HTML::forma($liga, 'Modificar '.$tabla, $campos, $props, true);
 $cont = ob_get_clean();
 
  // Estuctura el cuerpo de la página
  HTML::cuerpo(array('cont'=>$cont));
  echo '<a href="enrutador/">Probar enrutador (RUTA)</a>';
  // Cierre de etiquetas HTML
  HTML::pie();
?>