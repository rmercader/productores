<?PHP

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
include_once(DIR_BASE.'class/image_handler.class.php');

$pagina = new nyiHTML('novedad-detalle.htm');
$interfaz = new Interfaz();
if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$nov = $interfaz->obtenerDetallesNovedad($_GET['id']);
	$pagina->assign('titulo', $nov['titulo']);
	$pagina->assign('texto', $nov['texto']);
	$pagina->assign('src_imagen', $nov['src_imagen']);
	$imgHandler = new ImageHandler();
	$imgHandler->open_image_with_extension($nov['src_imagen_local']);
	$pagina->assign("w", $imgHandler->get_image_width());
	$pagina->assign("h", $imgHandler->get_image_height());
}
$pagina->printHTML();