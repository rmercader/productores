<?PHP

ini_set('display_errors', 1);
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();

include_once('./app.config.php');
include_once('./sitio.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('novedades.htm');
$interfaz = new Interfaz();
$novs = $interfaz->obtenerNovedadesParaMostrar();
while(!$novs->EOF){
	$seccion->append('NOVEDADES', array(
		'id_novedad'=>$novs->fields['id_novedad'], 
		'titulo'=>$novs->fields['titulo'],
		'cabezal'=>$novs->fields['cabezal']));	
	$novs->MoveNext();
}
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'novedades');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();