<?PHP

// Includes
include('../../app.config.php');
include('../admin.config.php');

// Incluyo funcionalidades comunes
require_once("../xajax/xajax_core/xajax.inc.php");
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include(DIR_LIB.'nyiPDF.php');
include(DIR_BASE.'funciones_auxiliares.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;
$xajax = new xajax();

include_once(DIR_BASE.'seguridad/seguridad.class.php');
include(DIR_BASE.'feria/evento_feria.class.php');

function eliminarVideo($idEventoFeria, $codigo){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$evt = new EventoFeria($Cnx);
	$evt->eliminarVideo($idEventoFeria, $codigo);
	$objResponse->assign("container", "innerHTML", $evt->obtenerHtmlListaVideosEditar($idEventoFeria));
	$objResponse->call("prepararBorrados");
	return $objResponse;
}

// Ajax
$xajax->registerFunction("eliminarVideo");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);

?>