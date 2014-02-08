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
$seccion = new nyiHTML('feria-home.htm');
$seccion->assign('subseccion_feria', 'home');
$interfaz = new Interfaz();
$evtActual = $interfaz->obtenerDetallesEventoFeriaActual();
if(is_array($evtActual)){
	$formato = "D j \d\e F \d\e Y";
	if(PHP_VERSION > "5.1.6"){
		$fechaI = traducirFechaFormateada(date_format(date_create($evtActual['fecha_inicio']), $formato));
		$fechaF = traducirFechaFormateada(date_format(date_create($evtActual['fecha_fin']), $formato));
	}
	else{
		$fechaI = traducirFechaFormateada(date($formato, strtotime($evtActual['fecha_inicio'])));
		$fechaF = traducirFechaFormateada(date($formato, strtotime($evtActual['fecha_fin'])));
	}
	$seccion->assign('lugar', $evtActual['lugar']);
	$seccion->assign('fecha_i', $fechaI);
	$seccion->assign('fecha_f', $fechaF);
	$seccion->assign('detalles', $evtActual['detalles']);
	$seccion->assign('mapa', str_replace("#0000FF", "#FFFFFF", $evtActual['como_llegar']));
}
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'feria');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));

$marco->printHTML();

?>