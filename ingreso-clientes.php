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

$interfaz = new Interfaz();
if(isset($_COOKIE[COOKIE_ID_CLIENTE])){
	$valCookie = intval($_COOKIE[COOKIE_ID_CLIENTE]);
	if($valCookie > 0){
		$credenciales = $interfaz->datosClienteParaSesionPorId($valCookie);
		if(is_array($credenciales) && $credenciales["admitido"] == 1 && $credenciales["activo"] == 1){
			$_SESSION[CREDENCIALES_CLIENTE] = $credenciales;
			header("Location: area-clientes.php");
			exit();
		}
	}
}

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('ingreso-clientes.htm');
$errores = "";
$exitos = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$matricula = trim($_POST["matricula"]);
	$clave = $_POST["clave"];
	$accion = $_POST["accion"];
	switch($accion){
		case "login":
			if($matricula != ""){
				$credenciales = $interfaz->loginCliente($matricula, $clave);
				if(is_array($credenciales)){
					if($credenciales["admitido"] == 1 && $credenciales["activo"] == 1){
						$_SESSION[CREDENCIALES_CLIENTE] = $credenciales;
						setcookie(COOKIE_ID_CLIENTE, $credenciales["id_cliente"], time()+60*60*24*15); // 15 dias
						header("Location: area-clientes.php");
						exit();
					}
					else{
						$errores = $interfaz->armarMensajeError("Nombre de usuario y/o contrase&ntilde;a incorrectos.");
					}
				}
				else{
					$errores = $interfaz->armarMensajeError("Nombre de usuario y/o contrase&ntilde;a incorrectos.");
				}
			}
			else{
				$errores = $interfaz->armarMensajeError("Debe ingresar su matr&iacute;cula.");
			}
			break;
		
		case "olvido":
			if($matricula != ""){
				if($interfaz->enviarClaveCliente($matricula)){
					$exitos = $interfaz->armarMensajeExito("La contrase&ntilde;a ha sido enviada a la casilla de correo electr&oacute;nico que ha usado para registrarse en nuestro sitio.");	
				}
				else {
					$errores = $interfaz->armarMensajeError("No tenemos registros de un cliente asociado a la matr&iacute;cula ingresada.");	
				}
			}
			else{
				$errores = $interfaz->armarMensajeError("Debe ingresar la matr&iacute;cula con la que se ha registrado.");
			}
			break;
	}
}

$seccion->assign('error', $errores);
$seccion->assign('exito', $exitos);
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'ingreso-clientes');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>