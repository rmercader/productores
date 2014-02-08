<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

//ini_set("display_errors", 1);

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

function validarFormulario(){
	$nombre = $_POST["nombre"];
	$apellido = $_POST["apellido"];
	$destino = $_POST["destino"];
	$celular = $_POST["celular"];
	$consulta = $_POST["consulta"];
	$email = $_POST["email"];
	$interfaz = new Interfaz();
	
	if(trim($nombre) == ""){
		$errores .= "- Debe ingresar su nombre.<br>";
	}
	
	if(trim($apellido) == ""){
		$errores .= "- Debe ingresar su apellido.<br>";
	}
	
	if(trim($celular) == ""){
		$errores .= "- Debe ingresar su n&uacute;mero de tel&eacute;fono o celular.<br>";
	}
	
	if(!$interfaz->esEmailValido($email)){
		$errores .= "- Debe ingresar una direcci&oacute;n de correo electr&oacute;nico v&aacute;lida.<br>";
	}
	
	if(trim($consulta) == ""){
		$errores .= "- Debe ingresar la consulta.<br>";
	}
	
	return $errores;
}

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('contacto.htm');
$interfaz = new Interfaz();

// Variables
$matricula = "";
$idDepartamento = "";
$destino = "";
$nombre = "";
$apellido = "";
$celular = "";
$email = "";
$consulta = "";
$errores = "";
$exitos = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$errores = validarFormulario();
	
	$destino = $_POST["destino"];
	$matricula = trim($_POST["matricula"]);
	$idDepartamento = $_POST["id_departamento"];
	$nombre = trim($_POST["nombre"]);
	$apellido = trim($_POST["apellido"]);
	$celular = trim($_POST["celular"]);
	$email = $_POST["email"];
	$consulta = $_POST["consulta"];
	
	if($errores == ""){		
		$res = $interfaz->registrarContacto(
			$destino,
			$matricula, 
			$nombre, 
			$apellido, 
			$celular,  
			$email,
			$consulta,
			$idDepartamento
		);
		
		if($res != ""){
			$errores = $interfaz->armarMensajeError($res);
			$exitos = "";
		}
		else {
			$exitos = $interfaz->armarMensajeExito("Su consulta ha sido enviada correctamente. Nos comunicaremos con usted a la brevedad.");
			$matricula = "";
			$idDepartamento = "";
			$destino = "";
			$nombre = "";
			$apellido = "";
			$celular = "";
			$email = "";
			$consulta = "";
			$errores = "";
		}
	}
	else{
		$errores = $interfaz->armarMensajeError($errores);	
	}
}

// Asignacion de campos en la pagina
$seccion->assign('matricula', $matricula);
$seccion->assign('nombre', $nombre);
$seccion->assign('apellido', $apellido);
$seccion->assign('destino', $destino);
$seccion->assign('id_sucursal', $idSucursal);
$seccion->assign('consulta', $consulta);
$seccion->assign('celular', $celular);
$seccion->assign('email', $email);
$seccion->assign('id_departamento', $idDepartamento);
$idsDptos = $interfaz->obtenerIdsDepartamentos();
$dscDptos = $interfaz->obtenerNombresDepartamentos();
$seccion->assign('departamentos_ids', $idsDptos);
$seccion->assign('departamentos_dsc', $dscDptos);
$seccion->assign('error', $errores);
$seccion->assign('exito', $exitos);

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'contacto');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>