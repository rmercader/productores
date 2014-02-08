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
$html = new nyiHTML('feria/videos_feria.htm');

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(isset($_POST["subevideo"]) && $_POST["subevideo"] == _SI && trim($_POST["nombre"]) != "" && trim($_POST["codigo"]) != ""){
		$res = $objEF->asociarNuevoVideo($_GET['COD'], $_POST["nombre"], $_POST["codigo"]);
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
		//LogArchivo(print_r($nuevoOrden, true));
		//LogArchivo($_POST["orden"]);
		$res = $objEF->ordenarVideos($_GET['COD'], $nuevoOrden);
		if($res != ""){
			$error = $res;	
		}
		else{
			$error = "Los videos se han ordenado correctamente.";	
		}
	}
}

$html->assign('VIDEOS', $objEF->obtenerHtmlListaVideosEditar($_GET['COD']));

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'VIDEOS');
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_POST);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin_feria.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('id_feria_evento', $_GET['COD']);
$html->assign('error', $error);
$xajax->setRequestURI(DIR_HTTP.'feria/videos_feria_ajax.php');
$xajax->registerFunction("eliminarVideo");

$mod_Contenido = $html->fetchHTML();

?>