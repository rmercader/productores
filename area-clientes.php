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
$seccion = new nyiHTML('area-clientes.htm');
$interfaz = new Interfaz();
$errores = "";
$exitos = "";

if(isset($_COOKIE[COOKIE_ID_CLIENTE]) && isset($_SESSION[CREDENCIALES_CLIENTE]) && is_array($_SESSION[CREDENCIALES_CLIENTE])){
	$credenciales = $_SESSION[CREDENCIALES_CLIENTE];
	$seccion->assign('nombre_usuario', $credenciales["nombre"]." ".$credenciales["apellido"]);
		
	// Eventos para todos los clientes
	$evParaTodos = $interfaz->obtenerEventosParaTodosLosClientes();
	if(count($evParaTodos) > 0)
	{
		$seccion->assign('mostrar_eventos_generales', _SI);
		foreach($evParaTodos as $evtGen){
			$evtGen['url'] = 'evento-detalle-por-id.php?id_evento='.$evtGen["id_evento"];
			$seccion->append('EVTGEN', $evtGen);
		}
	}
	
	// Eventos para clientes de la sucursal del cliente
	$evParaSucursal = $interfaz->obtenerEventosSucursalCliente($credenciales["id_cliente"]);
	if(count($evParaSucursal) > 0)
	{
		$seccion->assign('mostrar_eventos_sucursal', _SI);	
		$seccion->assign('sucursal', $interfaz->obtenerSucursalCliente($credenciales["id_cliente"]));
		foreach($evParaSucursal as $evtSuc){
			$evtSuc['url'] = 'evento-detalle-por-id.php?id_evento='.$evtSuc["id_evento"];
			$seccion->append('EVTSUC', $evtSuc);
		}
	}
	
	// Documentos de interes
	$cats = $interfaz->obtenerCategoriasConDocumentosComunes();
	if(count($cats) > 0)
	{
		$seccion->assign('mostrar_documentos', _SI);
		foreach($cats as $cat){
			$docsPorCat = array('nom_cat'=>$cat['nombre_categoria_documento'], 'id_categoria_documento'=>$cat['id_categoria_documento']);
			$seccion->append('CATS', $docsPorCat);
		}
	}
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