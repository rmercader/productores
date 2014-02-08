<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
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
$seccion = new nyiHTML('feria-galeria.htm');
$seccion->assign('subseccion_feria', 'galeria');

$interfaz = new Interfaz();
$evtActual = $interfaz->obtenerDetallesEventoFeriaActual();
if(is_array($evtActual)){
	$idEvento = $evtActual["id_feria_evento"];
	$galeria = $interfaz->obtenerListaFotosEventoFeria($idEvento);
	$pag = 0;
	foreach($galeria as $foto){
		$seccion->append('FOTOS', $foto);
		$pag++;
	}
	for($i=0; $i < $pag; $i++){
		//$paginas = array('nro'=>$i, 'estilo'=>$i < 9 ? 'style="padding-left: 11px; padding-right: 11px;"' : '');
		//$seccion->append('PAG', $paginas);
		$seccion->append('PAG', $i);
	}
}

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'feria');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));

$marco->printHTML();

?>