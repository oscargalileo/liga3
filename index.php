<?php
 require_once 'LIGA.php';
 $ptos = LIGA('base.puestos');
 if (isset($_GET['accion'])) {
    if ($_GET['accion'] == 'insertar') {
        $ptos->insertar($_POST);
    } elseif ($_GET['accion'] == 'modificar') {
    	$datos = array($_POST['cual']=>$_POST);
 		$ptos->modificar($datos);
 	}
 }
 if (isset($_GET['borrar'])) {
 	$ptos->eliminar($_GET['borrar']);
 }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link href="LIGA.css" rel="stylesheet" type="text/css" />
    <title>LIGA 3</title>
</head>
<body>
<?php
	$cols  = array('*','acción'=>'<a href="?borrar=@[id]">Borrar</a>');
	$join = array('depende'=>$ptos);
    HTML::tabla($ptos, 'Puestos registrados', $cols, true, $join);
    
    $props = array('form'=>'method="POST" action="?accion=insertar"');
    HTML::forma($ptos, 'Nuevo puesto', false, $props, true, $_POST);
    
    $props  = array('form'=>array('method'=>'POST', 'action'=>'?accion=modificar'), 'prefid'=>'algo');
    $campos = array('cual'=>HTML::selector($ptos, 1, array('select'=>array('name'=>'cual', 'id'=>'algocual'), 'option'=>'value="@[0]"', 'option@si("'.(isset($_POST['cual']) ? $_POST['cual'] : '').'"=="@[0]")'=>'selected="selected"')), '*');
    HTML::forma($ptos, 'Modificar puesto', $campos, $props, true, $_POST);
?>
</body>
</html>