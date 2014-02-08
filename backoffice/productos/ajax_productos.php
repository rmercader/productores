<?PHP

include('../../app.config.php');
include('../admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');
include_once(DIR_BASE.'productos/producto.class.php');

function eliminarArchivoCatalogo($idCategoria, $nombreArchivo){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$cat = new CategoriaProducto($Cnx, $xajax);
	$cat->eliminarCatalogo($idCategoria, $nombreArchivo);
	$objResponse->assign("archivos", "innerHTML", $cat->obtenerHtmlCatalogosParaABM($idCategoria));
	return $objResponse;
}

function eliminarCatalogos($idCategoria){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$cat = new CategoriaProducto($Cnx, $xajax);
	$cat->eliminarCatalogos($idCategoria);
	$objResponse->assign("contArchivos", "innerHTML", "");
	return $objResponse;
}

// Ajax
$xajax->registerFunction("eliminarArchivoCatalogo");
$xajax->registerFunction("eliminarCatalogos");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);

?>