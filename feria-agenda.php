<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
//ini_set("display_errors", true);
//ini_set("error_reporting", E_ALL);
include_once('./app.config.php');
include_once('./sitio.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('feria-agenda.htm');
$seccion->assign('subseccion_feria', 'agenda');
$interfaz = new Interfaz();
$evtActual = $interfaz->obtenerDetallesEventoFeriaActual();
if(is_array($evtActual)){
	$idEvento = $evtActual["id_feria_evento"];
	$agenda = $interfaz->obtenerAgendaActividadesEventoFeria($idEvento);
	$fechaCtrl = "";
	$hashAgenda = array(); // Armo una tabla para ir a buscar por fecha
	foreach($agenda as $actividad){
		// Si existe la lista de actividades en esa fecha
		if(!array_key_exists($actividad['fecha'], $hashAgenda)){
			$hashAgenda[$actividad['fecha']] = array();		
		}
		array_push($hashAgenda[$actividad['fecha']], $actividad);
	}
	while(list($fecha, $item) = each($hashAgenda)){	
		$formato = "D j \d\e F";
		if(PHP_VERSION > "5.1.6"){
			$fechaMostrar = traducirFechaFormateada(date_format(date_create($fecha), $formato));
		}
		else{
			$fechaMostrar = traducirFechaFormateada(date($formato, strtotime($fecha)));
		}
		$seccion->append('AGENDA', array('fecha_mostrar'=>$fechaMostrar, 'actividades'=>$item));
	}
}
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'feria');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));

$marco->printHTML();

?>