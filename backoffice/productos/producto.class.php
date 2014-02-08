<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'productos/categoria_producto.class.php');
include_once(DIR_BASE.'fckeditor/fckeditor.php');
include_once(DIR_BASE.'class/image_handler.class.php');

class Producto extends Table {

	var $TamTextoGrilla = 200;
	var $Ajax;
	var $TablaImg;
	var $ValoresImg;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Producto($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'producto');
		$this->TablaImg   = new Table($DB, 'producto_foto');
		$this->AccionesGrid = array(ACC_BAJA, ACC_MODIFICACION, ACC_CONSULTA);
		// Ajax
		$this->Ajax = $AJAX;
	}
	
	function SetSoloLectura(){
		$this->AccionesGrid = array(ACC_CONSULTA);
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('PRODUCTOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_producto');
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('nombre_categoria_producto'=>'Categoria', 'id'=>'Identificador' ,'nombre_producto'=>'Nombre'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
	
		$Campos = "n.id_producto AS id, c.nombre_categoria_producto, n.nombre_producto, n.codigo_sapp, pf.extension_imagen";
		$From = "producto n INNER JOIN categoria_producto c ON c.id_categoria_producto = n.id_categoria_producto LEFT OUTER JOIN producto_foto pf ON pf.id_producto = n.id_producto AND pf.numero_imagen = 1";
		
		$Grid->getDatos($this->DB, $Campos, $From);
		
		// Devuelvo
		return($Grid);
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		$id = $this->Registro['id_producto'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('productos/producto_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('id_producto', $id);
		$Form->assign('id_categoria_producto', $this->Registro['id_categoria_producto']);
		$Form->assign('nombre_producto', $this->Registro['nombre_producto']);
		$Form->assign('codigo_sapp', $this->Registro['codigo_sapp']);
		$Form->assign('visible', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		
		// Tengo que meterlo como caja de texto enriquecido
		$editor = new FCKeditor('DESCRIPCION') ;
		$editor->BasePath = 'fckeditor/' ;
		$editor->Height = ALTURA_EDITOR;
		$editor->Config['EnterMode'] = 'br';
		$editor->Value = $this->Registro['descripcion'];
		$contenido = $editor->CreateHtml();
		$Form->assign('DESCRIPCION', $contenido);
		$Form->assign('CANTIDAD_IMAGENES', 1);
		$src = $this->GetURLImagenPreview($id).'?time='.time();
		$Form->assign('SRC_IMAGEN', $src);
		
		$TblCat = new CategoriaProducto($Cnx);
		$Form->assign('categoria_producto_id', $TblCat->GetComboIdsParaProducto());
		$Form->assign('categoria_producto_nom', $TblCat->GetComboNombresParaProducto());
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('categoria_producto_txt', $Cnx->getOne("SELECT nombre_categoria_producto FROM categoria_producto WHERE id_categoria_producto = ".$this->Registro['id_categoria_producto']));
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'PRODUCTOS');
		$Cab->assign('NOMACCION', getNomAccion($Accion));
		$Cab->assign('ACC', $Accion);
		
		// Script Listado
		$Parametros = $_GET;
		unset($Parametros['ACC']);
		unset($Parametros['COD']);
		$Cab->assign('SCRIPT_LIS', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		// Script Salir
		$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		$Form->assign('NAVEGADOR', $Cab->fetchHTML());
	
		// Contenido
		return($Form->fetchHTML());
	}
	
	function GetURLImagen($idN, $num=1){
		return DIR_HTTP_FOTOS_PRODUCTOS.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenPreview($idN, $num=1){
		return DIR_HTTP_FOTOS_PRODUCTOS.$idN.'/'.$num.'.prv.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenThumbnail($idN, $num=1){
		return DIR_HTTP_FOTOS_PRODUCTOS.$idN.'/'.$num.'.thu.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenThumbnailLocal($idN, $num=1){
		return DIR_FOTOS_PRODUCTOS.$idN.'/'.$num.'.thu.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenLocal($idN, $num=1){
		return DIR_FOTOS_PRODUCTOS.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenPrvLocal($idN, $num=1){
		return DIR_FOTOS_PRODUCTOS.$idN.'/'.$num.'.prv.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetExtensionImagen($id, $i){
		return $this->DB->getOne("SELECT extension_imagen FROM producto_foto WHERE id_producto = $id AND numero_imagen = $i");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_producto'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function GetDescripcion($id_producto){
		return $this->DB->getOne("SELECT descripcion FROM producto WHERE id_producto = $id_producto");
	}
	
	function GetNombre($id_producto){
		return $this->DB->getOne("SELECT nombre_producto FROM producto WHERE id_producto = $id_producto");
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_producto'] = $_POST['id_producto'];
		$this->Registro['id_categoria_producto'] = $_POST['id_categoria_producto'];
		$this->Registro['nombre_producto'] = $_POST['nombre_producto'];
		$this->Registro['codigo_sapp'] = $_POST['codigo_sapp'];
		$this->Registro['descripcion'] = utf8_decode(stripslashes($_POST['DESCRIPCION']));
		$this->Registro['visible'] = $_POST['visible'] ? 1 : 0;
		$this->CargarFotos();
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('TAM_TXT', $this->TamTextoGrilla);
		$Grid->addVariable('DIR_HTTP_FOTOS_PRODUCTOS', DIR_HTTP_FOTOS_PRODUCTOS);
		
		// devuelvo
		return ($Grid->fetchGrid('productos/producto_grid.htm', 'Listado de productos',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_producto) FROM producto");
	}

	function beforeInsert(){
		
	}
	
	function afterInsert($LastID){
		$this->TablaImg->Registro['id_producto'] = $LastID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			$extension = GetExtension($this->ValoresImg[$num_imagen]['nombre_imagen']);
			$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
			$this->TablaImg->Registro['extension_imagen'] = $extension;
			$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
			
			if($this->Error == ''){
				$this->SalvarFoto($LastID, $num_imagen, $extension);
			}
		}

		// Inserto en la tabla para busqueda
		$nombre = $this->Registro['nombre_producto'];
		$dsc = $this->Registro['descripcion'];
		$idCat = $this->Registro['id_categoria_producto'];
		$datosCat = $this->DB->execute("SELECT c.nombre_categoria_producto, c.id_categoria_padre, p.nombre_categoria_producto AS unidad FROM categoria_producto c INNER JOIN categoria_producto p ON c.id_categoria_padre = p.id_categoria_producto WHERE c.id_categoria_producto = {$idCat}");
		$nomCat = $datosCat->fields['nombre_categoria_producto'];
		$idUnidad = $datosCat->fields['id_categoria_padre'];
		$nomUnidad = $datosCat->fields['unidad'];
		$this->DB->execute("INSERT INTO producto_busqueda(id_producto, nombre_producto, descripcion, id_categoria, categoria, id_unidad, unidad) VALUES ({$LastID}, '{$nombre}', '{$dsc}', {$idCat}, '{$nomCat}', {$idUnidad}, '{$nomUnidad}')");
	}
	
	function beforeEdit(){
		
	}

	function afterEdit(){
		$ID = $this->Registro['id_producto'];

		// Actualizacion de fotos
		$this->TablaImg->Registro['id_producto'] = $ID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			if($datos['subir_imagen'] === true){
				$nom = $datos['nombre_imagen'];
				$tipo = $datos['tipo_imagen'];
				$size = $datos['size_imagen'];
				$img = $datos['imagen'];
				
				$extOld = $this->DB->getOne("SELECT extension_imagen FROM producto_foto WHERE id_producto = $ID AND numero_imagen = $num_imagen");
				$extension = GetExtension($datos['nombre_imagen']);
				
				$this->DB->execute("DELETE FROM producto_foto WHERE id_producto = $ID AND numero_imagen = $num_imagen");
				
				$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
				$this->TablaImg->Registro['extension_imagen'] = $extension;
				$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
				
				if($this->Error == ''){
					$imgOld = DIR_FOTOS_PRODUCTOS.$ID.'/'.$num_imagen.'.'.$extOld;
					$imgOldThu = DIR_FOTOS_PRODUCTOS.$ID.'/'.$num_imagen.'.thu.'.$extOld;
					$imgOldPrv = DIR_FOTOS_PRODUCTOS.$ID.'/'.$num_imagen.'.prv.'.$extOld;
					if($extension != $extOld){
						if(file_exists($imgOld)){
							@unlink($imgOld);	
						}
						if(file_exists($imgOldThu)){
							@unlink($imgOldThu);	
						}
						if(file_exists($imgOldPrv)){
							@unlink($imgOldPrv);	
						}
					}
					$this->SalvarFoto($ID, $num_imagen, $extension);
				}
			}
		}

		// Actualizo en la tabla para busqueda
		$nombre = $this->Registro['nombre_producto'];
		$dsc = $this->Registro['descripcion'];
		$idCat = $this->Registro['id_categoria_producto'];
		$datosCat = $this->DB->execute("SELECT c.nombre_categoria_producto, c.id_categoria_padre, p.nombre_categoria_producto AS unidad FROM categoria_producto c INNER JOIN categoria_producto p ON c.id_categoria_padre = p.id_categoria_producto WHERE c.id_categoria_producto = {$idCat}");
		$nomCat = $datosCat->fields['nombre_categoria_producto'];
		$idUnidad = $datosCat->fields['id_categoria_padre'];
		$nomUnidad = $datosCat->fields['unidad'];
		$this->DB->execute("UPDATE producto_busqueda SET nombre_producto = '{$nombre}', descripcion = '{$dsc}', id_categoria = {$idCat}, categoria = '{$nomCat}', id_unidad = {$idUnidad}, unidad = '{$nomUnidad}' WHERE id_producto = {$ID}");
	}
	
	// Salva la foto de la noticia al disco
	function SalvarFoto($id, $numero, $extension='jpg'){
		// Directorio
		$nuevoDir = DIR_FOTOS_PRODUCTOS.$id;
		if(!file_exists($nuevoDir)){
			mkdir($nuevoDir);
			chmod($nuevoDir, 0755);
		}
		
		$nuevoDir = $nuevoDir.'/';
		
		// Extraigo los datos de la foto :contenido, y tamano
		$size = $this->ValoresImg[$numero]['size_imagen'];
		$content = $this->ValoresImg[$numero]['imagen'];
		
		$rutaImg = $nuevoDir.$numero.'.'.$extension;
		$rutaImgThu = $nuevoDir.$numero.'.thu.'.$extension;
		$rutaImgPrv = $nuevoDir.$numero.'.prv.'.$extension;
		$fp = fopen($rutaImg, 'w');
		fwrite($fp, $content, $size);
		fclose($fp);
		chmod($rutaImg, 0755);
		
		$ImgHandler = new ImageHandler();
		// Imagen Thumbnail
		if ($ImgHandler->open_image($rutaImg) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_THUMBNAIL_PRODUCTO, ANCHO_THUMBNAIL_PRODUCTO);
			// La guarda
			$ImgHandler->image_to_file($rutaImgThu);
			chmod($rutaImgThu, 0755);
		}
		
		// Imagen Preview
		if ($ImgHandler->open_image($rutaImg) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_PREVIEW_PRODUCTO, ANCHO_PREVIEW_PRODUCTO);
			// La guarda
			$ImgHandler->image_to_file($rutaImgPrv);
			chmod($rutaImgPrv, 0755);
		}
	}
	
	function afterDelete($id){
		$dir = DIR_FOTOS_PRODUCTOS.$id;
		BorrarDirectorio($dir);
		$this->DB->execute("DELETE FROM producto_busqueda WHERE id_producto = {$id}");
	}
	
	function CargarFotos(){
		$cant = $_POST['CANTIDAD_IMAGENES'];
		if(!is_array($this->ValoresImg))
			$this->ValoresImg = array();
		
		for($i=1; $i <= $cant; $i++){
			$label = "IMAGEN_$i";
			if(is_uploaded_file($_FILES[$label]['tmp_name']) && $_FILES[$label]['size'] > 0){
				// Archivo imagen
				$fileName = $_FILES[$label]['name'];
				$tmpName  = $_FILES[$label]['tmp_name'];
				$fileSize = $_FILES[$label]['size'];
				$fileType = $_FILES[$label]['type'];
				
				$fp      = fopen($tmpName, 'r');
				$content = fread($fp, filesize($tmpName));
				fclose($fp);
				
				if(!get_magic_quotes_gpc()){
						$fileName = addslashes($fileName);
				}
				
				$this->ValoresImg[$i] = array();
				$this->ValoresImg[$i]['subir_imagen'] = true;
				$this->ValoresImg[$i]['id_producto'] = $this->Registro['id_producto'];
				$this->ValoresImg[$i]['numero_imagen'] = $i;
				$this->ValoresImg[$i]['nombre_imagen'] = $fileName;
				$this->ValoresImg[$i]['tipo_imagen'] = $fileType;
				$this->ValoresImg[$i]['size_imagen'] = $fileSize;
				$this->ValoresImg[$i]['imagen'] = $content;
			}
		}
	}
	
	function ObtenerProductosPorCategoria($id_categoria_producto, $visible=''){
		if($visible === true){
			$sqlVisibles = "visible = 1 AND";
		}
		else if($visible === false){
			$sqlVisibles = "visible = 0 AND";
		}
	
		$sql = "SELECT id_producto, nombre_producto FROM producto WHERE $sqlVisibles id_categoria_producto = $id_categoria_producto ORDER BY ordinal";
		
		return $this->DB->execute($sql);
	}
	
	// Mas simple
	function ObtenerProductosCategoria($id_categoria_producto, $arrayAsociativo=false){
		$sql = "SELECT id_producto, nombre_producto FROM producto WHERE id_categoria_producto = $id_categoria_producto AND visible = 1 ORDER BY nombre_producto";
		$resultados = $this->DB->execute($sql);
		$datos = $resultados;
		if($arrayAsociativo){
			$datos = array();
			while(!$resultados->EOF){
				array_push($datos, array(
					'id_producto'=>$resultados->fields['id_producto'], 
					'nombre_producto'=>$resultados->fields['nombre_producto'],
					'src_imagen_thu'=>$this->GetURLImagenThumbnail($resultados->fields['id_producto']),
					'src_imagen_thu_local'=>$this->GetURLImagenThumbnailLocal($resultados->fields['id_producto'])
				));
				$resultados->MoveNext();
			}
		}
		return $datos;
	}
	
	function SetOrdinal($id, $valOrdinal){
		$sql = "UPDATE producto SET ordinal = $valOrdinal WHERE id_producto = $id";
		$OK = $this->DB->execute($sql);
		if($OK === false){
			$this->Error = $this->DB->ErrorMsg();
		}
	}
	
	function DatosProducto($id){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM producto WHERE id_producto = $id";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_producto'] = $qr->fields['id_producto'];
			$Datos['id_categoria_producto'] = $qr->fields['id_categoria_producto'];
			$Datos['nombre_producto'] = $qr->fields['nombre_producto'];
			$Datos['codigo_sapp'] = $qr->fields['codigo_sapp'];
			$Datos['descripcion'] = $qr->fields['descripcion'];
			$Datos['src_imagen'] = $this->GetURLImagen($id);
			$Datos['src_imagen_local'] = $this->GetURLImagenLocal($id);
			$Datos['src_imagen_prv_local'] = $this->GetURLImagenPrvLocal($id);
			$Datos['src_imagen_prv'] = $this->GetURLImagenPreview($id);
			$Datos['src_imagen_thu'] = $this->GetURLImagenThumbnail($id);
		}
		return $Datos;
	}

	function busquedaProductos($sTxtBusqueda, $pagina=0, $cantidad){
		$Cnx = $this->DB;
		$txtBusqueda = mysql_real_escape_string($sTxtBusqueda);
		$q = "";
		$q .= "SELECT p.id_producto, p.nombre_producto, p.descripcion ";
		$q .= "FROM producto_busqueda p ";
		$q .= "WHERE MATCH(p.nombre_producto, p.descripcion, p.categoria, p.unidad) AGAINST ('{$txtBusqueda}') ";
		$q .= "ORDER BY p.nombre_producto ";
		$q .= "LIMIT {$pagina}, {$cantidad} ";
		
		$qr = $Cnx->execute($q);
		return iterator_to_array($qr);
	}

	function busquedaProductosCantidadResultados($sTxtBusqueda){
		$Cnx = $this->DB;
		$txtBusqueda = mysql_real_escape_string($sTxtBusqueda);
		$q = "";
		$q .= "SELECT COUNT(p.id_producto) ";
		$q .= "FROM producto_busqueda p ";
		$q .= "WHERE MATCH(p.nombre_producto, p.descripcion, p.categoria, p.unidad) AGAINST ('{$txtBusqueda}') ";
		
		$qr = $Cnx->getOne($q);
		return$qr;
	}

	/* Retorna todos los productos ordenados por el criterio indicado en el parametro
	* @param $criterioOrden
	*/
	function obtenerTodos($criterioOrden){
		$Cnx = $this->DB;
		$q = "";
		$q .= "SELECT p.id_producto, p.nombre_producto, p.codigo_sapp, p.descripcion, c.nombre_categoria_producto ";
		$q .= "FROM producto p INNER JOIN categoria_producto c ON c.id_categoria_producto = p.id_categoria_producto ";
		$q .= "ORDER BY $criterioOrden";
		$qr = $Cnx->execute($q);

		return iterator_to_array($qr);
	}
}
?>