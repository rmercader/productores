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

$pagina = new nyiHTML('evento-detalle.htm'); 
$pagina->assign('img_w', LARGO_PREVIEW_EVENTO);
$pagina->assign('img_h', ANCHO_PREVIEW_EVENTO);
$interfaz = new Interfaz();

if(isset($_GET['dia']) && isset($_GET['mes']) && isset($_GET['anio'])){
	$dia = $_GET['dia'];
	$mes = $_GET['mes'];
	
	if(!isset($_GET['actual'])){
		$eventos = $interfaz->obtenerEventosPorFecha($dia, $mes, $_GET['anio']);
		$_SESSION["eventos"] = $eventos;
		$actual = 0;
	}
	else {
		$actual = intval($_GET['actual']);
	}
	
	$diaDsc = $_GET['dia'];
	switch($_GET['mes']){
		case 1:
			$mesDsc .= "ENE";
			break;
		case 2:
			$mesDsc .= "FEB";
			break;
		case 3:
			$mesDsc .= "MAR";
			break;
		case 4:
			$mesDsc .= "ABR";
			break;
		case 5:
			$mesDsc .= "MAY";
			break;
		case 6:
			$mesDsc .= "JUN";
			break;	
		case 7:
			$mesDsc .= "JUL";
			break;
		case 8:
			$mesDsc .= "AGO";
			break;
		case 9:
			$mesDsc .= "SET";
			break;
		case 10:
			$mesDsc .= "OCT";
			break;
		case 11:
			$mesDsc .= "NOV";
			break;
		case 12:
			$mesDsc .= "DIC";
			break;
	}
	$pagina->assign('dia', $diaDsc);
	$pagina->assign('mes', $mesDsc);
	$evento = $_SESSION["eventos"][$actual];
	if(is_array($evento)){
		// Mostrar siguiente?
		if(isset($_SESSION["eventos"][$actual + 1])){
			$pagina->assign('mostrar_siguiente', _SI);
			$pagina->assign('url_siguiente', "evento-detalle.php?dia=$dia&mes=$mes&anio=".$_GET['anio']."&actual=".($actual + 1));
		}
		// Mostrar anterior?
		if(isset($_SESSION["eventos"][$actual - 1])){
			$pagina->assign('mostrar_anterior', _SI);
			$pagina->assign('url_anterior', "evento-detalle.php?dia=$dia&mes=$mes&anio=".$_GET['anio']."&actual=".($actual - 1));
		}
		
		$pagina->assign('nombre_evento', $evento['nombre_evento']);
		$pagina->assign('hora', $evento['hora']);
		$pagina->assign('sucursal', $evento['sucursal']);
		$pagina->assign('lugar', $evento['lugar']);
		$pagina->assign('descripcion', $evento['descripcion']);
		$img = DIR_HTTP_PUBLICA."pics/img-suc-no-disponible.jpg";
		//LogArchivo($evento['img_src_local']);
		if(file_exists($evento['img_src_local'])){
			$img = $evento['img_src'];
		}
		$pagina->assign('img_src', $img);
	}
}
$pagina->printHTML();