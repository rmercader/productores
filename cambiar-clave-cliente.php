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

function validarFormulario($credencialesCliente){
	$interfaz = new Interfaz();
	$claveant = $_POST["claveant"];
	$clave = $_POST["clave"];
	$claveConf = $_POST["clavecnf"];
	
	if($clave != $claveConf){
		$errores .= "- La contrase&ntilde;a y su confirmaci&oacute;n no coinciden.<br>";	
	}
	
	if(!$interfaz->esClaveDeCliente($claveant, $credencialesCliente['id_cliente'])){
		$errores .= "- La contrase&ntilde;a anterior es incorrecta.";
	}
	
	return $errores;
}

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('cambiar-clave-cliente.htm');
$interfaz = new Interfaz();

$clave = "";
$claveConf = "";
$errores = "";
$exitos = "";

if(isset($_COOKIE[COOKIE_ID_CLIENTE]) && isset($_SESSION[CREDENCIALES_CLIENTE]) && is_array($_SESSION[CREDENCIALES_CLIENTE])){
	$credenciales = $_SESSION[CREDENCIALES_CLIENTE];
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		$errores = validarFormulario($credenciales);
		$clave = $_POST["clave"];
		
		if($errores == ""){		
			$res = $interfaz->cambiarClaveCliente($credenciales['id_cliente'], $clave);
			if($res != ""){
				$errores = $interfaz->armarMensajeError($res);
				$exitos = "";
			}
			else {
				$exitos = $interfaz->armarMensajeExito("Su clave ha sido cambiada correctamente.");
				$claveant = "";
				$clave = "";
				$claveConf = "";
			}
		}
		else{
			$errores = $interfaz->armarMensajeError($errores);	
		}
	}
}	
else{
	header("Location: ingreso-clientes.php");
	exit();
}

// Asignacion de campos en la pagina
$seccion->assign('error', $errores);
$seccion->assign('exito', $exitos);
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'ingreso-clientes');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>