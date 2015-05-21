<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
ini_set('display_errors', 1);
include_once('./app.config.php');
include_once('./sitio.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 

$interfaz = new Interfaz();
$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('home.htm');

$novsHome = $interfaz->obtenerNovedadesPortada();
if(isset($novsHome[0])){
	$seccion->assign('mostrar_1', _SI);
	$seccion->assign('id_novedad_1', $novsHome[0]["id_novedad"]);
	$seccion->assign('titulo_novedad_1', $novsHome[0]["titulo"]);
	$seccion->assign('cab_novedad_1', $novsHome[0]["cabezal"]);
	if(file_exists($novsHome[0]["url_img_local"])){
		$seccion->assign('url_img_1', $novsHome[0]["url_img"]);	
	}
	else{
		$seccion->assign('url_img_1', "pics/img-suc-no-disponible.jpg");		
	}
}
if(isset($novsHome[1])){
	$seccion->assign('mostrar_2', _SI);
	$seccion->assign('id_novedad_2', $novsHome[1]["id_novedad"]);
	$seccion->assign('titulo_novedad_2', $novsHome[1]["titulo"]);
	$seccion->assign('cab_novedad_2', $novsHome[1]["cabezal"]);
	if(file_exists($novsHome[1]["url_img_local"])){
		$seccion->assign('url_img_2', $novsHome[1]["url_img"]);	
	}
	else{
		$seccion->assign('url_img_2', "pics/img-suc-no-disponible.jpg");		
	}
}
if(isset($novsHome[2])){
	$seccion->assign('mostrar_3', _SI);
	$seccion->assign('id_novedad_3', $novsHome[2]["id_novedad"]);
	$seccion->assign('titulo_novedad_3', $novsHome[2]["titulo"]);
	$seccion->assign('cab_novedad_3', $novsHome[2]["cabezal"]);
	if(file_exists($novsHome[2]["url_img_local"])){
		$seccion->assign('url_img_3', $novsHome[2]["url_img"]);	
	}
	else{
		$seccion->assign('url_img_3', "pics/img-suc-no-disponible.jpg");		
	}
}
if(isset($novsHome[3])){
	$seccion->assign('mostrar_4', _SI);
	$seccion->assign('id_novedad_4', $novsHome[3]["id_novedad"]);
	$seccion->assign('titulo_novedad_4', $novsHome[3]["titulo"]);
	$seccion->assign('cab_novedad_4', $novsHome[3]["cabezal"]);
	if(file_exists($novsHome[3]["url_img_local"])){
		$seccion->assign('url_img_4', $novsHome[3]["url_img"]);	
	}
	else{
		$seccion->assign('url_img_4', "pics/img-suc-no-disponible.jpg");		
	}
}

for($i = 4; $i < count($novsHome); $i++){
	$seccion->append('NOVS', array('id_novedad'=>$novsHome[$i]['id_novedad'], 'titulo'=>$novsHome[$i]['titulo']));	
}

// Popup inicial
if(!isset($_COOKIE["PopupInicio"])){
	setcookie("PopupInicio", 1, time() + 30); // Que expire en una hora
	$seccion->assign("popup_inicio", _SI);
}
else {
	$seccion->assign("popup_inicio", _NO);
}

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'home');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>