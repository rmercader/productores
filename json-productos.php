<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

ini_set('display_errors', 'on');
ini_set("memory_limit", "64M");
include_once('./app.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 

define('DS', DIRECTORY_SEPARATOR);

// Criterio por defecto: Nombre de la categoria
$ord = 'nombre_categoria_producto';
$productosOut = array();

if(isset($_GET['ord'])){
	// Sanitizo el parametro
	$ord = mysql_escape_string($_GET['ord']);
}

if(in_array($ord, array('id_producto', 'nombre_producto', 'codigo_sap', 'nombre_categoria_producto'))){
	if($ord == 'codigo_sap'){
		$ord = 'codigo_sapp';
	}
	// Si el parametro es valido procedo
	$interfaz = new Interfaz();
	$productos = $interfaz->obtenerTodosLosProductos($ord);
	$dirProductosBase = DIR_BASE . 'productos' . DS . 'fotos' . DS;
	$urlProductosBase = DIR_HTTP . 'productos/fotos/';

	foreach ($productos as $producto) {

		$urlImg = DIR_HTTP_PUBLICA."pics/img-suc-no-disponible.jpg";
		$urlPrv = DIR_HTTP_PUBLICA."pics/img-suc-no-disponible.jpg";
		$urlThu = DIR_HTTP_PUBLICA."pics/thu-no-disponible.jpg";
		
		// Tamanio real
		if(file_exists($dirProductosBase . $producto['id_producto'] . DS . '1.jpg')){
			$urlImg = $urlProductosBase . $producto['id_producto'] . '/' . '1.jpg';
		}

		// Tamanio preview
		if(file_exists($dirProductosBase . $producto['id_producto'] . DS . '1.jpg')){
			$urlPrv = $urlProductosBase . $producto['id_producto'] . '/' . '1.prv.jpg';
		}

		// Thumbnail
		if(file_exists($dirProductosBase . $producto['id_producto'] . DS . '1.jpg')){
			$urlThu = $urlProductosBase . $producto['id_producto'] . '/' . '1.thu.jpg';
		}


		array_push($productosOut, array(
			'id_producto' => $producto['id_producto'],
			'categoria' => $producto['nombre_categoria_producto'],
			'codigo_sap' => $producto['codigo_sapp'],
			'nombre_producto' => $producto['nombre_producto'],
			'descripcion' => $producto['descripcion'],
			'url_imagen' =>  $urlImg,
			'url_preview' => $urlPrv,
			'url_thumbnail' => $urlThu
		));
	}
}

header("Content-Type: application/json");
//print(str_replace('\\/', '/', json_encode($productosOut))); // Sin escapear '/'
print(json_encode($productosOut));

?>