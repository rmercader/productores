<?PHP

if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin_feria.php");	
	exit(0);
}

// Includes
include(DIR_BASE.'feria/evento_feria.class.php');

// Objeto
$objEF = new EventoFeria($Cnx, $xajax);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('feria/agenda_feria.htm');
$idActividad = "";
$nombre = "";
$fecha = "";
$hora = "";
$descripcion = "";
$datosInicio = $objEF->getDatosFechaInicio($_GET['COD']);

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$nombre = trim($_POST["nombre"]);
	$fecha = "{$_POST['fechaYear']}-{$_POST['fechaMonth']}-{$_POST['fechaDay']}";
	$hora = $_POST["horaHour"].":".$_POST["horaMinute"].":00";
	$descripcion = trim($_POST["descripcion"]);
	if(isset($_POST["salva_actividad"]) && $_POST["salva_actividad"] == _SI && trim($nombre) != ""){
		if($objEF->validarFechaActividad($_GET['COD'], $fecha)){
			if(intval($_POST["id_feria_actividad"]) > 0){
				$idActividad = $_POST["id_feria_actividad"];
				$res = $objEF->modificarActividad($idActividad, $_GET['COD'], $nombre, $fecha, $hora, $descripcion);
			}
			else {
				$res = $objEF->asociarNuevaActividad($_GET['COD'], $nombre, $fecha, $hora, $descripcion);
			}
			if($res != ""){
				$error = $res;	
			}
			else {
				$idActividad = "";
				$nombre = "";
				$fecha = $datosInicio->fields["anio"]."-".$datosInicio->fields["mes"]."-".$datosInicio->fields["dia"];
				$hora = "";
				$descripcion = "";
			}
		}
		else {
			$error = "La fecha ingresada cae fuera de la fecha de inicio y fecha de fin del evento.";	
		}
	}
	else {
		$error = "No se han completado los datos requeridos en el formulario.";	
	}
}
else{
	$fecha = $datosInicio->fields["anio"]."-".$datosInicio->fields["mes"]."-".$datosInicio->fields["dia"];	
}

$html->assign('ACTIVIDADES', $objEF->obtenerHtmlListaActividades($_GET['COD']));

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'AGENDA DE ACTIVIDADES');
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_VER);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin_feria.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('error', $error);

$html->assign('id_feria_evento', $_GET['COD']);
$html->assign('id_actividad', $idActividad);
$html->assign('fecha', $fecha);
$html->assign('hora', $hora);
$html->assign('nombre', $nombre);
$html->assign('descripcion', $descripcion);
$html->assign('a_inicio', $datosInicio->fields["anio"]);
$html->assign('a_fin', $objEF->getAnioFin($_GET['COD']));

$xajax->setRequestURI(DIR_HTTP.'feria/agenda_feria_ajax.php');
$xajax->registerFunction("eliminarActividad");

$mod_Contenido = $html->fetchHTML();

?>