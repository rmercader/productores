<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class CategoriaProducto extends Table {

	var $TablaCatalogos;

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function CategoriaProducto($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'categoria_producto');
		$this->TablaCatalogos = new Table($DB, 'categoria_producto_catalogo');
		$this->AccionesGrid = array(ACC_BAJA,ACC_MODIFICACION,ACC_CONSULTA);
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
		$Grid  = new nyiGridDB('ADMINISTRAR CATEGORIAS DE PRODUCTOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'c.nombre_categoria_producto'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('c.nombre_categoria_producto'=>'Nombre', 'p.nombre_categoria_producto'=>'Categoria padre'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "c.id_categoria_producto AS id, c.nombre_categoria_producto, p.nombre_categoria_producto AS nombre_categoria_padre";
		$from = "categoria_producto c LEFT OUTER JOIN categoria_producto p ON c.id_categoria_padre = p.id_categoria_producto";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}

	function ObtenerCategoriasConProductos($visibles=''){
		if($visibles === true){
			$sqlVisibles = "n.visible = 1 AND";
		}
		else if($visibles === false){
			$sqlVisibles = "n.visible = 0 AND";
		}
	
		return $this->DB->execute("SELECT * FROM categoria_producto c WHERE EXISTS (SELECT id_producto FROM producto n WHERE $sqlVisibles n.id_categoria_producto = c.id_categoria_producto)");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('productos/categoria_producto_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_categoria_producto', $this->Registro['id_categoria_producto']);
		$Form->assign('id_categoria_padre', $this->Registro['id_categoria_padre'] == '' ? 0 : $this->Registro['id_categoria_padre']);
		$Form->assign('nombre_categoria_producto', $this->Registro['nombre_categoria_producto']);
		
		if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		else{
			// Combo de categorias padre
			$Form->assign('categoria_producto_id', $this->GetComboIdsParaPadre($this->Registro['id_categoria_producto'], true, 0));
			$Form->assign('categoria_producto_nom', $this->GetComboNombresParaPadre($this->Registro['id_categoria_producto'], true, ''));
		}
		
		// Archivos ya subidos
		if($this->Registro['id_categoria_producto'] != ""){
			$Form->assign('archs', $this->obtenerHtmlCatalogosParaABM($this->Registro['id_categoria_producto']));
			if($this->Registro['id_categoria_padre'] != 0){
				$panelHtml = '<input type="file" name="pdf_1" id="pdf_1" onchange="setBlock();" size="60" />';
				$panelHtml .='<div id="moreUploads"></div>';
				$panelHtml .='<div id="moreLink" style="display:none;"><a href="javascript:agregarNuevoUploader();">Agregar otro archivo</a></div>';
				$Form->assign('contDivPdfs', $panelHtml);
				$Form->assign('cant_pdfs', 1);
			}
			$src = $this->getUrlIcono($this->Registro['id_categoria_producto']).'?time='.time();
			$Form->assign('SRC_ICONO', $src);
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR CATEGORIAS DE PRODUCTOS');
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
		
		$this->Ajax->setRequestURI(DIR_HTTP.'productos/ajax_productos.php');
		$this->Ajax->registerFunction("eliminarArchivoCatalogo");
		$this->Ajax->registerFunction("eliminarCatalogos");
	
		// Contenido
		return($Form->fetchHTML());
	}
	
	function getUrlIcono($id){
		return DIR_HTTP_ICONOS_CATEGORIAS_PRODUCTOS.$id.".jpg";
	}
	
	function obtenerHtmlCatalogosParaABM($idCategoria){
		$html = new nyiHTML('productos/catalogos_abm.htm');
		$archs = $this->obtenerDatosCatalogos($idCategoria);
		while(!$archs->EOF){
			if(file_exists(DIR_CATALOGOS.$idCategoria."/".$archs->fields['archivo'])){
				$html->append('archs', array(
					'archivo'=>$archs->fields['archivo'],
					'url'=>DIR_HTTP_CATALOGOS.$idCategoria.'/'.$archs->fields['archivo']
				));
			}
			$archs->MoveNext();
		}
		return $html->fetchHTML();
	}
	
	function obtenerDatosCatalogos($idCategoria, $arrayAsociativo=false){
		$resultados = $this->DB->execute("SELECT * FROM categoria_producto_catalogo WHERE id_categoria_producto = $idCategoria ORDER BY archivo");
		$datos = $resultados;
		if($arrayAsociativo){
			$datos = array();
			while(!$resultados->EOF){
				array_push($datos, array(
					'id_categoria_producto'=>$resultados->fields['id_categoria_producto'], 
					'archivo'=>$resultados->fields['archivo']
				));
				$resultados->MoveNext();
			}
		}
		return $datos;
	}
	
	function eliminarCatalogo($idCategoria, $nombreArchivo){
		$OK = $this->DB->execute("DELETE FROM categoria_producto_catalogo WHERE id_categoria_producto = $idCategoria AND archivo = '$nombreArchivo'");
		if($OK){
			if(file_exists(DIR_CATALOGOS.$idCategoria."/".$nombreArchivo)){
				@unlink(DIR_CATALOGOS.$idCategoria."/".$nombreArchivo);
			}
		}
	}
	
	function eliminarCatalogos($idCategoria){
		$cats = $this->DB->execute("SELECT archivo FROM categoria_producto_catalogo WHERE id_categoria_producto = $idCategoria");
		while(!$cats->EOF){
			$this->eliminarCatalogo($idCategoria, $cats->fields['archivo']);
			$cats->MoveNext();
		}
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_categoria_producto'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_categoria_producto'];
		$this->Registro['id_categoria_producto'] = $id;
		$this->Registro['nombre_categoria_producto'] = $_POST['nombre_categoria_producto'];
		$this->Registro['id_categoria_padre'] = $_POST['id_categoria_padre'] == 0 ? NULL : $_POST['id_categoria_padre'];
	}
	
	function afterInsert($id){
		$this->salvarArchivosCatalogo($id);
		$this->salvarIcono($id);
	}
	
	function afterEdit(){
		$idCat = $this->Registro['id_categoria_producto'];
		$this->salvarArchivosCatalogo($idCat);
		$this->salvarIcono($idCat);

		// Actualizo en la tabla para busqueda de productos, campo categoria
		$nomCat = $this->Registro['nombre_categoria_producto'];
		$this->DB->execute("UPDATE producto_busqueda SET categoria = '{$nomCat}' WHERE id_categoria = {$idCat}");
		// Actualizo en la tabla para busqueda de productos, campo unidad
		$idUnidad = $this->Registro['id_categoria_padre'];
		
		if(!empty($idUnidad)){
			$nomUnidad = $this->DB->getOne("SELECT nombre_categoria_producto AS unidad FROM categoria_producto WHERE id_categoria_producto = {$idUnidad}");
			$this->DB->execute("UPDATE producto_busqueda SET unidad = '{$nomUnidad}', id_unidad = {$idUnidad} WHERE id_categoria = {$idCat}");
		}
	}
	
	function salvarIcono($id){
		if(is_uploaded_file($_FILES["ICONO"]['tmp_name']) && $_FILES["ICONO"]['size'] > 0){
			$archivo = DIR_ICONOS_CATEGORIAS_PRODUCTOS.$id.".jpg";
			move_uploaded_file($_FILES["ICONO"]['tmp_name'], $archivo);
			chmod($archivo, 0755);
		}
	}
	
	function salvarArchivosCatalogo($id){
		if(!file_exists(DIR_CATALOGOS.$id)){
			mkdir(DIR_CATALOGOS.$id);
			chmod(DIR_CATALOGOS.$id, 0755);
		}
		$cantPdfs = $_POST['cant_pdfs'];
		
		for($i = 1; $i <= $cantPdfs; $i++){
			$label = "pdf_$i";
			if(is_uploaded_file($_FILES[$label]['tmp_name']) && $_FILES[$label]['size'] > 0 && $this->Error == ""){
				$archivo = DIR_CATALOGOS.$id."/".$_FILES[$label]['name'];
				move_uploaded_file($_FILES[$label]['tmp_name'], $archivo);
				chmod($archivo, 0755);
				$this->TablaCatalogos->Registro['id_categoria_producto'] = $id;
				$this->TablaCatalogos->Registro['archivo'] = $_FILES[$label]['name'];
				$this->Error .= $this->TablaCatalogos->TablaDB->addRegistro($this->TablaCatalogos->Registro);
			}
		}
	}
	
	function afterDelete($id){
		$fileIcono = DIR_ICONOS_CATEGORIAS_PRODUCTOS.$id.".jpg";
		if(file_exists($fileIcono)){
			@unlink($fileIcono);
		}
		$dir = DIR_CATALOGOS.$id;
		BorrarDirectorio($dir);
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('productos/categoria_producto_grid.htm', 'ADMINISTRAR CATEGORIAS DE PRODUCTOS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_categoria_producto) FROM categoria_producto");
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_categoria_producto FROM categoria_producto ORDER BY nombre_categoria_producto");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de detalles para combo
	// ------------------------------------------------
	function GetComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre_categoria_producto FROM categoria_producto ORDER BY nombre_categoria_producto");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	// Retorna el combo de identificadores ordenados segun nombre
	function GetComboIdsParaProducto($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT c.id_categoria_producto FROM categoria_producto c WHERE NOT EXISTS(SELECT * FROM categoria_producto h WHERE h.id_categoria_padre = c.id_categoria_producto) ORDER BY c.nombre_categoria_producto");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de detalles para combo
	// ------------------------------------------------
	function GetComboNombresParaProducto($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT c.nombre_categoria_producto FROM categoria_producto c WHERE NOT EXISTS(SELECT * FROM categoria_producto h WHERE h.id_categoria_padre = c.id_categoria_producto) ORDER BY c.nombre_categoria_producto");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	// Retorna el combo de identificadores ordenados por nombre
	// menos excluyendo el de id. igual al parametro y que no tenga
	// ni catalogos ni productos asociados
	function GetComboIdsParaPadre($id_excluir, $Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT c.id_categoria_producto FROM categoria_producto c WHERE c.id_categoria_producto <> '$id_excluir' AND NOT EXISTS(SELECT * FROM producto p WHERE p.id_categoria_producto = c.id_categoria_producto) AND NOT EXISTS(SELECT * FROM categoria_producto_catalogo cpc WHERE cpc.id_categoria_producto = c.id_categoria_producto) ORDER BY nombre_categoria_producto");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ----------------------------------------------------------
	// Devuelvo array de detalles para combo ordenados por nombre
	// excluyendo el de id. igual al parametro
	// ----------------------------------------------------------
	function GetComboNombresParaPadre($id_excluir, $Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$sql = "SELECT c.nombre_categoria_producto FROM categoria_producto c WHERE c.id_categoria_producto <> '$id_excluir' AND NOT EXISTS(SELECT * FROM producto p WHERE p.id_categoria_producto = c.id_categoria_producto) AND NOT EXISTS(SELECT * FROM categoria_producto_catalogo cpc WHERE cpc.id_categoria_producto = c.id_categoria_producto) ORDER BY nombre_categoria_producto";
		$Col = $Aux->getCol($sql);
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function ObtenerCategorias(){
		return $this->DB->execute("SELECT * FROM categoria_producto");
	}
	
	function beforeDelete($id){
		$cntHijos = $this->DB->getOne("SELECT COUNT(*) FROM categoria_producto WHERE id_categoria_padre = $id");
		if($cntHijos > 0){
			$this->Error .= "La categoria tiene $cntHijos subcategorias asociadas. ";
		}
		$cntProds = $this->DB->getOne("SELECT COUNT(*) FROM producto WHERE id_categoria_producto = $id");
		if($cntProds > 0){
			$this->Error .= "La categoria tiene $cntProds productos asociados. ";
		}
	}
	
	function obtenerDatosUnidadNegocio($idUnidad){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM categoria_producto WHERE id_categoria_producto = $idUnidad AND id_categoria_padre IS NULL";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_categoria_producto'] = $qr->fields['id_categoria_producto'];
			$Datos['nombre_categoria_producto'] = $qr->fields['nombre_categoria_producto'];			
		}
		return $Datos;
	}
	
	function obtenerDatosCategoria($idCategoria){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM categoria_producto WHERE id_categoria_producto = $idCategoria";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_categoria_producto'] = $qr->fields['id_categoria_producto'];
			$Datos['id_categoria_padre'] = $qr->fields['id_categoria_padre'];
			$Datos['nombre_categoria_producto'] = $qr->fields['nombre_categoria_producto'];			
		}
		return $Datos;
	}
	
	function obtenerDatosCategoriasHijas($idCategoria){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM categoria_producto WHERE id_categoria_padre = $idCategoria ORDER BY nombre_categoria_producto";
		$qr = $Cnx->execute($q);
		while(!$qr->EOF){
			array_push($Datos, array(
				'id_categoria_producto'=>$qr->fields['id_categoria_producto'],
				'nombre_categoria_producto'=>$qr->fields['nombre_categoria_producto']
			));
			$qr->MoveNext();
		}
		return $Datos;
	}
}
?>