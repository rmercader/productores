<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();

include_once('./app.config.php');
include_once('./sitio.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php');
include_once(DIR_BASE.'class/image_handler.class.php');

switch($_GET['unidad']){
	case "granos-y-concentrados":
		$template = "unidades-negocio/granos-y-concentrados.htm";
		$img = "pics/unidades-negocio/granos-y-concentrados.jpg";
		$id_unidad = 3;
		break;
	case "fertilizantes":
		$template = "unidades-negocio/fertilizantes.htm";
		$img = "pics/unidades-negocio/fertilizantes.jpg";
		$id_unidad = 4;
		break;
	case "semillas":
		$template = "unidades-negocio/semillas.htm";
		$img = "pics/unidades-negocio/semillas.jpg";
		$id_unidad = 8;
		break;
	case "agroquimicos":
		$template = "unidades-negocio/agroquimicos.htm";
		$img = "pics/unidades-negocio/agroquimicos.jpg";
		$id_unidad = 9;
		break;	
	case "insumos":
		$template = "unidades-negocio/insumos.htm";
		$img = "pics/unidades-negocio/insumos.jpg";
		$id_unidad = 10;
		break;
	case "veterinaria":
		$template = "unidades-negocio/veterinaria.htm";
		$img = "pics/unidades-negocio/veterinaria.jpg";
		$id_unidad = 5;
		break;
	case "ventas-especiales":
		$template = "unidades-negocio/ventas-especiales.htm";
		$img = "pics/unidades-negocio/ventas-especiales.jpg";
		$id_unidad = 11;
		break;
	case "nutricion-animal":
		$template = "unidades-negocio/nutricion-animal.htm";
		$img = "pics/unidades-negocio/nutricion-animal.jpg";
		$id_unidad = 104;
		break;	
}

$imgHandler = new ImageHandler();
$imgHandler->open_image_with_extension($img);
$pagina = new nyiHTML($template);
$pagina->assign("srcimg", $img);
$pagina->assign("w", $imgHandler->get_image_width());
$pagina->assign("h", $imgHandler->get_image_height());
$pagina->assign("id_unidad", $id_unidad);
$pagina->printHTML();

?>