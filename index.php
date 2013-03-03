<?php
 require_once 'LIGA.php';
 // Personaliza una conexión a la base de datos
 new BD('localhost', 'root', 'cuci12', 'base');
 
 $tabla = 'puestos';
 $liga  = LIGA($tabla);
 $resp  = '';
 // Controlador de acciones
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
 // Si es una petición asíncrona sólo muestra la respuesta
 if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
  echo $resp;
  exit(0);
 }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link href="LIGA.css" rel="stylesheet" type="text/css" />
    <title>LIGA 3</title>
    <style type="text/css">
     label {
      width:100px;
     }
    </style>
</head>
<body>
<?php
    $cols = array('*', '-contraseña', 'acción'=>'<a href="?borrar=@[0]">Borrar</a>');
    $join = array('depende'=>$liga);
    $pie  = '<th colspan="@[numCols]">Total de instancias: @[numReg]</th>';
    HTML::tabla($liga, 'Instancias de '.$tabla, $cols, true, $join, $pie);
    
    $props  = array('form'=>'method="POST" action="?accion=insertar"', 'input[nombre]'=>array('required'=>'required'), 'input[contraseña]'=>array('required'=>'required'));
    $campos = array('*', '-fecha');
    HTML::forma($liga, 'Registro de '.$tabla, $campos, $props, true, $_POST);
    
    $props  = array('form'=>array('method'=>'POST', 'action'=>'?accion=modificar'), 'prefid'=>'algo');
    $cual   = !empty($_POST['cual']) ? $_POST['cual'] : '';
    $select = HTML::selector($liga, 1, array('select'=>array('name'=>'cual', 'id'=>'algocual'),
							   'option'=>array('value'=>'@[0]'),
							   "option@si('$cual'=='@[0]')"=>array('selected'=>'selected')), array('depende'=>$liga)
			     );
    $campos = array('cual'=>$select, '*', '-fecha');
    HTML::forma($liga, 'Modificar '.$tabla, $campos, $props, true);
?>
</body>
</html>