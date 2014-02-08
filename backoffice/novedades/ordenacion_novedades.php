<?PHP

include_once(DIR_BASE.'novedades/novedad.class.php');

function CargarNovedadesCategoria($id_categoria){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$nov = new Novedad($Cnx);
	$Data = $nov->ObtenerNovedadesCategoria($id_categoria);
	$arrDatos = array();
	
	while(!$Data->EOF){
		$arrDatos[$Data->fields['id_novedad']] = $Data->fields['titulo'];
		$Data->MoveNext();
	}
	
	$cant = count($arrDatos);
	$objResponse->assign("CANTIDAD", "value", $cant);
	$objResponse->assign("ORDENACION", "size", $cant);
	$objResponse->assign("ORDENACION", "innerHTML", GenerarOpcionesCombo($arrDatos));
	
	return $objResponse;
}

$html = new nyiHTML('novedades/ordenacion_novedades.htm');
$tblCat = new CategoriaNovedad($Cnx);
$tblNov = new Novedad($Cnx);
$Error = '';
$cant = 0;

// Si fue postback
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$items = explode("|", $_POST["MEJUNJE_ORDENADO"], $_POST["CANTIDAD"] + 1);
	$cant = count($items);
	
	for($x = 0; $x < $cant-1; $x++){
		$arrVal = explode("#", $items[$x]);
		$tblNov->SetOrdinal($arrVal[0], $arrVal[1]);
		$Error .= $tblCat->Error;
	}
	
	if($Error == "")
		$Error = "Las novedades se han ordenado correctamente.";
	else{
		$Error = "Ha ocurrido un error al ordenar las novedades. Contactar administrador.";
		LogError($Error, "novedad.class.php", "SetOrdinal");
	}
}

$html->assign('CANTIDAD', $cant);
$html->assign('CATEGORIA_NOVEDAD_ID', $tblCat->GetComboIds(true));
$html->assign('CATEGORIA_NOVEDAD_NOM', $tblCat->GetComboNombres(true, 'Seleccionar categoría'));

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'ORDEN DE APARICION DE NOVEDADES');
$Cab->assign('NOMACCION', 'Ordenacion');
$Cab->assign('ACC', ACC_POST);
$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']));

// Ajax
$xajax->registerFunction("CargarNovedadesCategoria");
$xajax->processRequest();

$html->assign('ERROR', $Error);
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$mod_Contenido = $html->fetchHTML();

?>