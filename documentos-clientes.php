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

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('documentos-clientes.htm');
$interfaz = new Interfaz();
$errores = "";
$exitos = "";

if(isset($_COOKIE[COOKIE_ID_CLIENTE]) && isset($_SESSION[CREDENCIALES_CLIENTE]) && is_array($_SESSION[CREDENCIALES_CLIENTE]) && isset($_GET['id_categoria_documento'])){
	$credenciales = $_SESSION[CREDENCIALES_CLIENTE];
	$seccion->assign('nombre_usuario', $credenciales["nombre"]." ".$credenciales["apellido"]);
	$documentos = $interfaz->obtenerDocumentosComunesPorCategoria(intval($_GET['id_categoria_documento']));
	foreach($documentos as $doc){
		$seccion->append('documentos', $doc);
	}
	$datosCat = $interfaz->obtenerDatosCategoriaDocumento(intval($_GET['id_categoria_documento']));
	$seccion->assign('categoria', $datosCat['nombre_categoria_documento']);
}
else{
	header("Location: ingreso-clientes.php");
	exit();
}

$seccion->assign('error', $errores);
$seccion->assign('exito', $exitos);
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'ingreso-clientes');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>