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

$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('productos-y-servicios.htm');
$abrirGenerico = false;
$interfaz = new Interfaz();
$url_volver = "productos-y-servicios.php";

if(isset($_GET['id_unidad'])){
	if(intval($_GET['id_unidad'])){
		$unidad = $interfaz->obtenerDatosUnidadNegocio($_GET['id_unidad']);
		$urlIconoUnidad = $interfaz->obtenerUrlIconoCategoriaProducto($_GET['id_unidad']);
		if(count($unidad) > 0){
			$nombreUnidad = $unidad['nombre_categoria_producto'];
			$categorias = $interfaz->obtenerCategoriasHijas($_GET['id_unidad']);
			foreach($categorias as $cat){
				$seccion->append('categorias', $cat);
			}
			$seccion->assign('mostrarVolver', 'S');
			$seccion->assign('muestraCategorias', 'S');
		}
		else{
			$abrirGenerico = true;
		}
	}
	else {
		$abrirGenerico = true;
	}
}
elseif(isset($_GET['id_categoria'])){
	if(intval($_GET['id_categoria'])){
		$cat = $interfaz->obtenerDatosCategoriaProducto($_GET['id_categoria']);
		$catPadre = $interfaz->obtenerDatosCategoriaProducto($cat['id_categoria_padre']);
		$param = "id_categoria";
		if($catPadre['id_categoria_padre'] == ''){
			$param = "id_unidad";	
		}
		$nombreUnidad = $cat['nombre_categoria_producto'];
		$catsHijas = $interfaz->obtenerCategoriasHijas($_GET['id_categoria']);
		if(count($catsHijas) > 0){
			foreach($catsHijas as $catHija){
				$seccion->append('categorias', $catHija);
			}
			$seccion->assign('mostrarVolver', 'S');
			$seccion->assign('muestraCategorias', 'S');
			$url_volver = "productos-y-servicios.php?$param=".$cat['id_categoria_padre'];
		}
		else {
			$urlIconoUnidad = $interfaz->obtenerUrlIconoCategoriaProducto($_GET['id_categoria']);
			$productos = $interfaz->obtenerProductosCategoria($_GET['id_categoria']);
			foreach($productos as $prod){
				if(!file_exists($prod['src_imagen_thu_local'])){
					$prod['src_imagen_thu']	= DIR_HTTP_PUBLICA."pics/thu-no-disponible.jpg";
				}
				$seccion->append('productos', $prod);
			}
			$url_volver = "productos-y-servicios.php?$param=".$cat['id_categoria_padre'];
			$seccion->assign('mostrarVolver', 'S');
			$seccion->assign('muestraProductos', 'S');
			$catalogos = $interfaz->obtenerCatalogosCategoria($_GET['id_categoria'], true);
			foreach($catalogos as $docum){
				$arch = $docum['archivo'];
				if(file_exists(DIR_CATALOGOS.$_GET['id_categoria']."/$arch")){
					$extension = str_replace(".", "", strtolower(strrchr($arch, '.')));
					$dirImgExt = DIR_HTTP_EXTENSIONES_DOCUMENTOS."$extension.gif";
					$dirSrc = DIR_HTTP_CATALOGOS.$_GET['id_categoria']."/$arch";
					$seccion->append('catalogos', array('url_archivo'=>$dirSrc, 'nombre'=>$arch, 'url_ext'=>$dirImgExt));
				}
			}
		}
	}
	else {
		$abrirGenerico = true;		
	}
}
elseif(isset($_GET['id_producto'])){
	if(intval($_GET['id_producto'])){
		$prod = $interfaz->obtenerDatosProducto($_GET['id_producto']);
		$img = $prod['src_imagen_prv']; 
		if(!file_exists($prod['src_imagen_prv_local'])){
			$img = DIR_HTTP_PUBLICA."pics/img-suc-no-disponible.jpg";
		}
		$seccion->assign('src_img_producto', $img);
		$nombreUnidad = $prod['nombre_producto'];
		$seccion->assign('codigo_sapp', $prod['codigo_sapp']);
		$seccion->assign('descripcion', $prod['descripcion']);
		$seccion->assign('mostrarProducto', 'S');
		$seccion->assign('mostrarVolver', 'S');
		if(isset($_GET['q']) && isset($_GET['s']) && isset($_GET['cursor'])){
			$q = $_GET['q'];
			$s = $_GET['s'];
			$cursor = $_GET['cursor'];
			$url_volver = "busqueda.php?q={$q}&s={$s}&cursor={$cursor}";
		}
		else{
			$url_volver = "productos-y-servicios.php?id_categoria=".$prod['id_categoria_producto'];
		}
	}
	else {
		$abrirGenerico = true;		
	}
}
else {
	$abrirGenerico = true;	
	$nombreUnidad = "Productos y Servicios";
}

if($abrirGenerico){
	$seccion->assign('abrirGenerico', 'S');	
}

$seccion->assign('url_volver', $url_volver);
$seccion->assign('nombreUnidad', $nombreUnidad);
$seccion->assign('url_icono_unidad', $urlIconoUnidad);
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'productos-y-servicios');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>