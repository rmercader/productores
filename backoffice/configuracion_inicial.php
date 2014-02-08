<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
if (!isset($_SESSION["activa"])){
   header("Location: index.php");
   exit();
}

// Incluyo funcionalidades comunes
require_once("xajax/xajax_core/xajax.inc.php");
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include(DIR_LIB.'nyiPDF.php');
include(DIR_BASE.'funciones_auxiliares.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;
$xajax = new xajax();
//$xajax->setFlag("debug", true);

?>