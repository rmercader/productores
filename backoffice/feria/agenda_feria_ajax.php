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

function eliminarActividad($idEventoFeria, $idActividad){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$evt = new EventoFeria($Cnx);
	$res = $evt->eliminarActividad($idActividad);
	$objResponse->assign("container", "innerHTML", $evt->obtenerHtmlListaActividades($idEventoFeria));
	if($res != ""){
		$objResponse->alert($res);	
	}
	return $objResponse;
}

// Ajax
$xajax->registerFunction("eliminarActividad");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);

?>