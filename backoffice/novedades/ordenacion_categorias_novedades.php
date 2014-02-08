<?PHP

include_once(DIR_BASE.'novedades/categoria_novedad.class.php');

$html = new nyiHTML('novedades/ordenacion_categorias_novedades.htm');
$tblCat = new CategoriaNovedad($Cnx);
$Error = '';

// Si fue postback
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$items = explode("|", $_POST["MEJUNJE_ORDENADO"], $_POST["CANTIDAD_CATEGORIAS"] + 1);
	$cant = count($items);
	
	for($x = 0; $x < $cant-1; $x++){
		$arrVal = explode("#", $items[$x]);
		$tblCat->SetOrdinal($arrVal[0], $arrVal[1]);
		$Error .= $tblCat->Error;
	}
	
	if($Error == "")
		$Error = "Las categorias se han ordenado correctamente.";
}

$RegP = $tblCat->ObtenerCategorias();
$i = 0;
while(!$RegP->EOF){
	$i++;
	$sel = "";
	if($i == 1){
		$sel = " selected";
	}
	$html->append("CATEGORIAS", array('ordinal'=>$RegP->fields['ordinal'],
																'nombre'=>$RegP->fields['nombre_categoria_novedad'],
																'selected'=>$sel,
																'id_categoria_novedad'=>$RegP->fields['id_categoria_novedad']));
	
	$RegP->MoveNext();
}
$html->assign('CANTIDAD_CATEGORIAS', $i);

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'ORDEN DE APARICION DE CATEGORIAS DE NOVEDAD');
$Cab->assign('NOMACCION', 'Ordenacion');
$Cab->assign('ACC', ACC_POST);
$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']));

$html->assign('ERROR', $Error);
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$mod_Contenido = $html->fetchHTML();

?>