<?PHP

if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin_feria.php");	
	exit(0);
}

// Includes
include(DIR_BASE.'feria/evento_feria.class.php');

// Objeto
$objEF = new EventoFeria($Cnx, $xajax);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('feria/fotos_feria.htm');

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(isset($_POST["subefoto"]) && $_POST["subefoto"] == _SI && is_uploaded_file($_FILES["fotonueva"]["tmp_name"])){
		$res = $objEF->asociarNuevaFoto($_GET['COD'], $_FILES["fotonueva"]["tmp_name"], $_FILES["fotonueva"]["name"]);
		if($res != ""){
			$error = $res;	
		}
	}
	elseif(isset($_POST["orden"]) && $_POST["orden"] != ""){
		$orden = explode(",", $_POST["orden"]);
		$nuevoOrden = array();
		foreach($orden as $item){
			array_push($nuevoOrden, trim($item));
		}
		$res = $objEF->ordenarFotos($_GET['COD'], $nuevoOrden);
		if($res != ""){
			$error = $res;	
		}
		else{
			$error = "Las fotos se han ordenado correctamente.";	
		}
	}
}

$galeria = $objEF->obtenerGaleriaFotos($_GET['COD']);
while(!$galeria->EOF){
	$extension = GetExtension($galeria->fields['archivo']);
	$nomSinExt = str_replace(".{$extension}", "", $galeria->fields['archivo']); 
	$html->append('FOTOS', array('archivo'=>$galeria->fields['archivo'], 'url'=>DIR_HTTP_FOTOS_FERIA."{$_GET['COD']}/{$nomSinExt}-thu.{$extension}"));
	$galeria->MoveNext();
}

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'GALERÍA DE FOTOS');
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_POST);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin_feria.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('id_feria_evento', $_GET['COD']);
$html->assign('error', $error);
$xajax->setRequestURI(DIR_HTTP.'feria/fotos_feria_ajax.php');
$xajax->registerFunction("obtenerGaleriaFotos");
$xajax->registerFunction("eliminarFoto");

$mod_Contenido = $html->fetchHTML();

?>