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

function obtenerGaleriaFotos($idEventoFeria){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$objResponse->alert("$idEventoFeria");
	return $objResponse;
}

function eliminarFoto($idEventoFeria, $nombre){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$evt = new EventoFeria($Cnx);
	$evt->eliminarFoto($idEventoFeria, $nombre);
	$galeria = $evt->obtenerGaleriaFotos($idEventoFeria);
	$html = "";
	while(!$galeria->EOF){
		$extension = GetExtension($galeria->fields['archivo']);
		$nomSinExt = str_replace(".{$extension}", "", $galeria->fields['archivo']); 
		$url = DIR_HTTP_FOTOS_FERIA."{$idEventoFeria}/{$nomSinExt}-thu.{$extension}";
		$html .= '<div class="image" id="'.$galeria->fields['archivo'].'" style="background-image:url('.$url.');">';
		$html .= '<a href="#" class="delete">';
        $html .= '<img src="templates/img/ico-lst-eliminar.gif" />';
        $html .= '</a>';
        $html .= '</div>';
		$galeria->MoveNext();
	}
	$objResponse->assign("container", "innerHTML", $html);
	$objResponse->call("prepararBorrados");
	return $objResponse;
}

// Ajax
$xajax->registerFunction("obtenerGaleriaFotos");
$xajax->registerFunction("eliminarFoto");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);

?>