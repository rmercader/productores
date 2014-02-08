<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();

// Includes
include('../app.config.php');
include('./admin.config.php');
include('./funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;
$TablaUser = new Usuario($Cnx);

// Si hay Sesion
$Error = '';

if (!isset($_SESSION["activa"])) {
	// Si hay que procesar
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		
		$username = $_POST['LOGIN'];
		$pass = $_POST['PASS'];
		
		$UserLogged = $TablaUser->Login($username, $pass);
		
		if($UserLogged === false){
			$Error = "No se pudo iniciar sesion. Verifique que su usuario y clave sean correctos, o que su cuenta no haya sido desactivada por el administrador del sistema.";
		}
		
		// Si no hay errores
		if ($Error == "") {
			// Cargo datos de la Sesion
			$_SESSION["activa"]   = 'S';
			$_SESSION["cfgusu"]["nombre"] = $UserLogged['nombre_usuario_admin'];
			$_SESSION["cfgusu"]["id_usuario"] = $UserLogged['id_usuario_admin'];
			$_SESSION["cfgusu"]["id_perfil"] = $UserLogged['id_perfil'];

			// Configuro empresa
			//include('./ ');
			
			// Redirecciono
			header("Location: inicio.php");
			exit();
		}
	}
}
else{
     // Elimino sesion (no tendria que pasar)
     header("Location: logout.php");
     exit();
}

// -------------------------------------------------------------------------------
//                                Genero Pagina
// -------------------------------------------------------------------------------
// Pagina
$Pagina = new nyiHTML('login.htm');
$Pagina->assign('SCRIPTFRM',basename($_SERVER['SCRIPT_NAME']).$Pagina->fetchParamURL($_GET));
$Pagina->assign('ERROR', $Error);
// Genero html
$Pagina->printHTML();
?>
