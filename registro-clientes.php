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
//require_once(DIR_BASE.'class/recaptchalib.php'); 
require_once('securimage/securimage.php'); 

function validarFormulario(){
	$matricula = $_POST["matricula"];
	$nombre = $_POST["nombre"];
	$apellido = $_POST["apellido"];
	$grupo = $_POST["grupo_economico"];
	$idSucursal = intval($_POST["id_sucursal"]);
	$tecnico = $_POST["tecnico"];
	$celular = $_POST["celular"];
	$telefono = $_POST["telefono"];
	$email = $_POST["email"];
	$emailConf = $_POST["emailcnf"];
	$idDpto = intval($_POST["id_departamento"]);
	$direccion = $_POST["direccion"];
	$clave = $_POST["clave"];
	$claveConf = $_POST["clavecnf"];	
	$interfaz = new Interfaz();
	
	if(trim($matricula) == ""){
		$errores .= "- Debe ingresar su matr&iacute;cula.<br>";
	}
	
	if(trim($nombre) == ""){
		$errores .= "- Debe ingresar su nombre de titular.<br>";
	}
	
	if(trim($apellido) == ""){
		$errores .= "- Debe ingresar su apellido de titular.<br>";
	}
	
	if($idSucursal == 0){
		$errores .= "- Debe ingresar su sucursal de referencia.<br>";
	}
	
	if(trim($celular) == ""){
		$errores .= "- Debe ingresar su n&uacute;mero de celular.<br>";
	}
	
	if(!$interfaz->esEmailValido($email)){
		$errores .= "- Debe ingresar una direcci&oacute;n de correo electr&oacute;nico v&aacute;lida.<br>";
	}
	
	if($email != $emailConf){
		$errores .= "- La direcci&oacute;n de correo electr&oacute;nico y su confirmaci&oacute;n no coinciden.<br>";	
	}
	
	if($idDpto == 0){
		$errores .= "- Debe ingresar su departamento.<br>";
	}
	
	if(trim($direccion) == ""){
		$errores .= "- Debe ingresar su direcci&oacute;n.<br>";
	}
	
	if($clave != $claveConf){
		$errores .= "- La contrase&ntilde;a y su confirmaci&oacute;n no coinciden.<br>";	
	}
	/*
	// Verificacion del captcha
	$privatekey = "6LcNHMsSAAAAAEN3mAzI-fv3QpVpccQO0JGVTNnN";
  	$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	if (!$resp->is_valid) {
		$errores .= "- Deber&aacute; pasar la verificaci&oacute;n del captcha para poder continuar.<br>";
	}*/
	
	$securimage = new Securimage();
	if ($securimage->check($_POST['captcha_code']) == false) {
		$errores .= "- Deber&aacute; pasar la verificaci&oacute;n de seguridad para poder continuar.<br>";
	}
	
	return $errores;
}

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('registro-clientes.htm');
$interfaz = new Interfaz();
//$publickey = "6LcNHMsSAAAAAK_Rw9dCRwJzjSIqy6GgwFFcI3Bs";

// Variables
$matricula = "";
$nombre = "";
$apellido = "";
$grupo = "";
$idSucursal = 0;
$tecnico = "";
$celular = "";
$telefono = "";
$email = "";
$emailConf = "";
$idDpto = 0;
$direccion = "";
$clave = "";
$claveConf = "";
$errores = "";
$exitos = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$errores = validarFormulario();
	
	$matricula = trim($_POST["matricula"]);
	$nombre = trim($_POST["nombre"]);
	$apellido = trim($_POST["apellido"]);
	$grupo = trim($_POST["grupo_economico"]);
	$idSucursal = intval($_POST["id_sucursal"]);
	$tecnico = trim($_POST["tecnico"]);
	$celular = trim($_POST["celular"]);
	$telefono = trim($_POST["telefono"]);
	$email = $_POST["email"];
	$emailConf = $_POST["emailcnf"];
	$idDpto = intval($_POST["id_departamento"]);
	$direccion = trim($_POST["direccion"]);
	$clave = $_POST["clave"];
	$claveConf = $_POST["clavecnf"];
	
	if($errores == ""){		
		$res = $interfaz->registrarCliente(
			$matricula, 
			$nombre, 
			$apellido, 
			$grupo, 
			$idSucursal, 
			$tecnico, 
			$celular, 
			$telefono, 
			$email, 
			$idDpto, 
			$direccion, 
			$clave
		);
		
		if($res != ""){
			$errores = $interfaz->armarMensajeError($res);
			$exitos = "";
		}
		else {
			$exitos = $interfaz->armarMensajeExito("Sus datos han quedado registrados correctamente. Su cuenta de cliente para poder ingresar ser&aacute; habilitada a la brevedad.");
			$matricula = "";
			$nombre = "";
			$apellido = "";
			$grupo = "";
			$idSucursal = 0;
			$tecnico = "";
			$celular = "";
			$telefono = "";
			$email = "";
			$emailConf = "";
			$idDpto = 0;
			$direccion = "";
			$clave = "";
			$claveConf = "";
		}
	}
	else{
		$errores = $interfaz->armarMensajeError($errores);	
	}
}

$idsDptos = $interfaz->obtenerIdsDepartamentos();
$dscDptos = $interfaz->obtenerNombresDepartamentos();
$seccion->assign('departamentos_ids', $idsDptos);
$seccion->assign('departamentos_dsc', $dscDptos);
$idsSuc = $interfaz->obtenerIdsSucursales();
$dscSuc = $interfaz->obtenerNombresSucursales();
$seccion->assign('sucursales_ids', $idsSuc);
$seccion->assign('sucursales_dsc', $dscSuc);
//$seccion->assign('captcha', recaptcha_get_html($publickey));
$seccion->assign('error', $errores);
$seccion->assign('exito', $exitos);

// Asignacion de campos en la pagina
$seccion->assign('matricula', $matricula);
$seccion->assign('nombre', $nombre);
$seccion->assign('apellido', $apellido);
$seccion->assign('grupo_economico', $grupo);
$seccion->assign('id_sucursal', $idSucursal);
$seccion->assign('tecnico', $tecnico);
$seccion->assign('celular', $celular);
$seccion->assign('telefono', $telefono);
$seccion->assign('email', $email);
$seccion->assign('emailcnf', $emailConf);
$seccion->assign('id_departamento', $idDpto);
$seccion->assign('direccion', $direccion);
$seccion->assign('clave', $clave);
$seccion->assign('clavecnf', $claveConf);

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'ingreso-clientes');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>