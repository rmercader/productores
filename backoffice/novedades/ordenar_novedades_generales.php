<?PHP

include_once('../../app.config.php');
include('./admin.config.php');
include_once(DIR_BASE.'configuracion_inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'novedades/novedad.class.php');

$html = new nyiHTML('novedades/ordenar_novedades_generales.htm');
$TblNovedad = new Novedad($Cnx, $xajax);

// Si fue postback
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$TblNovedad->SetearOrdenGenerales($_POST["MEJUNJE_ORDENADO"], $_POST["CANTIDAD_NOVEDADES"]);
	$Error .= $TblNovedad->Error;
	if($Error == "")
		$Error = "Las novedades han sido ordenadas correctamente";
}

$RegN = $TblNovedad->ObtenerNovedadesGenerales();
$i = 0;
while(!$RegN->EOF){
	$i++;
	$sel = "";
	if($i == 1){
		$sel = " selected";
	}
	$html->append("NOVEDADES", array('ordinal'=>$RegN->fields['ordinal'],
																'titulo'=>$RegN->fields['titulo'],
																'selected'=>$sel,
																'id_novedad'=>$RegN->fields['id_novedad']));
	
	$RegN->MoveNext();
}

$html->assign('CANTIDAD_NOVEDADES', $i);

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'ORDEN DE NOVEDADES GENERALES');
$Cab->assign('NOMACCION', 'Ordenacion');
$Cab->assign('ACC', ACC_POST);
$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']));

$html->assign('ERROR', $Error);
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$mod_Contenido = $html->fetchHTML();

?>