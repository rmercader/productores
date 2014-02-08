<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'novedades/categoria_novedad.class.php');
include_once(DIR_BASE.'class/image_handler.class.php');
include_once(DIR_BASE.'fckeditor/fckeditor.php');

class Novedad extends Table {

	var $TamTextoGrilla = 200;
	var $Ajax;
	var $TablaImg;
	var $ValoresImg;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Novedad($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'novedad');
		$this->TablaImg   = new Table($DB, 'novedad_foto');
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
		$Grid  = new nyiGridDB('NOVEDADES', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'titulo');
		$Grid->setPaginador('base_navegador.htm');
		$arrCriterios = array(
			'nombre_categoria_novedad'=>'Categor&iacute;a', 
			'id'=>'Identificador' ,
			'titulo'=>'T&iacute;tulo',
			'ordinal'=>'&Iacute;ndice',
			"IF(n.portada, 'Si', 'No')"=>"Visible en la home",
			"IF(n.visible, 'Si', 'No')"=>"Visible en secci&oacute;n novedades");
		$Grid->setFrmCriterio('base_criterios_buscador.htm', $arrCriterios);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
	
		$Campos = "n.id_novedad AS id, c.nombre_categoria_novedad, n.titulo, n.cabezal, n.ordinal, IF(n.portada, 'Si', 'No') AS home, IF(n.visible, 'Si', 'No') AS seccion";
		$From = "novedad n INNER JOIN categoria_novedad c ON c.id_categoria_novedad = n.id_categoria_novedad";
		
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
		$id = $this->Registro['id_novedad'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('novedades/novedad_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('ID_NOVEDAD', $id);
		$Form->assign('ID_CATEGORIA_NOVEDAD', $this->Registro['id_categoria_novedad']);
		$Form->assign('TITULO', $this->Registro['titulo']);
		
		$Form->assign('CABEZAL', $this->Registro['cabezal']);

		$editorCab = new FCKeditor('cabezal');
		$editorCab->BasePath = 'fckeditor/';
		$editorCab->Height = ALTURA_EDITOR;
		$editorCab->Config['EnterMode'] = 'br';
		$editorCab->Value = $this->Registro['cabezal'];
		$contenidoCab = $editorCab->CreateHtml();
		$Form->assign('cabezal', $contenidoCab);
		
		$editorTxt = new FCKeditor('texto');
		$editorTxt->BasePath = 'fckeditor/';
		$editorTxt->Height = ALTURA_EDITOR;
		$editorTxt->Config['EnterMode'] = 'br';
		$editorTxt->Value = $this->Registro['texto'];
		$contenidoTxt = $editorTxt->CreateHtml();
		$Form->assign('texto', $contenidoTxt);
		
		$Form->assign('VISIBLE', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		$Form->assign('PORTADA', $this->Registro['portada'] == 1 ? 'checked="checked"' : '');
		$Form->assign('NEWSLETTER', $this->Registro['newsletter'] == 1 ? 'checked="checked"' : '');
		$Form->assign('CANTIDAD_IMAGENES', 1);
		$src = $this->GetURLImagen($id).'?time='.time();
		$Form->assign('SRC_IMAGEN', $src);
		
		$TblCat = new CategoriaNovedad($Cnx);
		$Form->assign('CATEGORIA_NOVEDAD_ID', $TblCat->GetComboIds());
		$Form->assign('CATEGORIA_NOVEDAD_NOM', $TblCat->GetComboNombres());
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('CATEGORIA_NOVEDAD_TXT', $Cnx->getOne("SELECT nombre_categoria_novedad FROM categoria_novedad WHERE id_categoria_novedad = ".$this->Registro['id_categoria_novedad']));
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'NOVEDADES');
		$Cab->assign('NOMACCION', getNomAccion($Accion));
		$Cab->assign('ACC', $Accion);
		
		// Script Salir
		$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']));
		
		// Script Listado
		$Parametros = $_GET;
		unset($Parametros['ACC']);
		unset($Parametros['COD']);
		$Cab->assign('SCRIPT_LIS', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		$Form->assign('NAVEGADOR', $Cab->fetchHTML());
	
		// Contenido
		return($Form->fetchHTML());
	}
	
	function GetURLImagen($idN, $num=1){
		return DIR_HTTP_FOTOS_NOVEDADES.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenLocal($idN, $num=1){
		return DIR_FOTOS_NOVEDADES.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetExtensionImagen($id, $i){
		return $this->DB->getOne("SELECT extension_imagen FROM novedad_foto WHERE id_novedad = $id AND numero_imagen = $i");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_novedad'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function GetTexto($id_novedad){
		return $this->DB->getOne("SELECT texto FROM novedad WHERE id_novedad = $id_novedad");
	}
	
	function GetTitulo($id_novedad){
		return $this->DB->getOne("SELECT titulo FROM novedad WHERE id_novedad = $id_novedad");
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_novedad'] = $_POST['ID_NOVEDAD'];
		if(trim($this->Registro['id_novedad']) == ''){
			// Es un alta
			$this->Registro['fecha'] = $this->DB->getOne("SELECT NOW()");
		}
		$this->Registro['id_categoria_novedad'] = $_POST['ID_CATEGORIA_NOVEDAD'];
		$this->Registro['titulo'] = $_POST['TITULO'];
		$this->Registro['cabezal'] = stripslashes($_POST['cabezal']);
		$this->Registro['texto'] = stripslashes($_POST['texto']);
		$this->Registro['visible'] = $_POST['VISIBLE'] ? 1 : 0;
		$this->Registro['portada'] = $_POST['PORTADA'] ? 1 : 0;
		$this->Registro['newsletter'] = $_POST['NEWSLETTER'] ? 1 : 0;
		$this->CargarFotos();
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('TAM_TXT', $this->TamTextoGrilla);
		
		// devuelvo
		return ($Grid->fetchGrid('novedades/novedad_grid.htm', 'Listado de novedades',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_novedad) FROM novedad");
	}

	function beforeInsert(){
		
	}
	
	function afterInsert($LastID){
		$this->TablaImg->Registro['id_novedad'] = $LastID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			$extension = GetExtension($this->ValoresImg[$num_imagen]['nombre_imagen']);
			$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
			$this->TablaImg->Registro['extension_imagen'] = $extension;
			$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
			
			if($this->Error == ''){
				$this->SalvarFoto($LastID, $num_imagen, $extension);
			}
		}
	}
	
	function beforeEdit(){
		
	}

	function afterEdit(){
		$ID = $this->Registro['id_novedad'];
		// Actualizacion de fotos
		$this->TablaImg->Registro['id_novedad'] = $ID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			if($datos['subir_imagen'] === true){
				$nom = $datos['nombre_imagen'];
				$tipo = $datos['tipo_imagen'];
				$size = $datos['size_imagen'];
				$img = $datos['imagen'];
				
				$extOld = $this->DB->getOne("SELECT extension_imagen FROM novedad_foto WHERE id_novedad = $ID AND numero_imagen = $num_imagen");
				$extension = GetExtension($datos['nombre_imagen']);
				
				$this->DB->execute("DELETE FROM novedad_foto WHERE id_novedad = $ID AND numero_imagen = $num_imagen");
				
				$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
				$this->TablaImg->Registro['extension_imagen'] = $extension;
				$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
				
				if($this->Error == ''){
					$imgOld = DIR_FOTOS_NOVEDADES.$ID.'/'.$num_imagen.'.'.$extOld;
					if($extension != $extOld && file_exists($imgOld)){
						@unlink($imgOld);
					}
					$this->SalvarFoto($ID, $num_imagen, $extension);
				}
			}
		}
	}
	
	// Salva la foto de la noticia al disco
	function SalvarFoto($id, $numero, $extension='jpg'){
		// Directorio
		$nuevoDir = DIR_FOTOS_NOVEDADES.$id;
		if(!file_exists($nuevoDir)){
			mkdir($nuevoDir);
			chmod($nuevoDir, 0755);
		}
		
		$nuevoDir = $nuevoDir.'/';
		
		// Extraigo los datos de la foto :contenido, y tamano
		$size = $this->ValoresImg[$numero]['size_imagen'];
		$content = $this->ValoresImg[$numero]['imagen'];
		
		$rutaAbsImg = $nuevoDir.$numero.'.'.$extension;
		$rutaAbsImgPrv = $nuevoDir.$numero."_prv.".$extension;
		$rutaAbsImgOri = $nuevoDir.$numero."_ori.".$extension;
		
		$fp = fopen($rutaAbsImg, 'w');
		fwrite($fp, $content, $size);
		fclose($fp);
		chmod($rutaAbsImg, 0755);
		
		$ImgHandler = new ImageHandler();
		// Preview
		$largo = LARGO_THUMBNAIL_NOVEDAD;
		$ancho = ANCHO_THUMBNAIL_NOVEDAD;
										
		if ($ImgHandler->open_image($rutaAbsImg) == 0){
			$ImgHandler->resize_image($largo, $ancho);
			$ImgHandler->image_to_file($rutaAbsImgPrv);
			chmod($rutaAbsImgPrv, 0755);
		}
		
		// Normal
		$largo = LARGO_FOTO_NOVEDAD;
		$ancho = ANCHO_FOTO_NOVEDAD;
										
		if ($ImgHandler->open_image($rutaAbsImg) == 0){
			$ImgHandler->resize_image($largo, $ancho);
			$ImgHandler->image_to_file($rutaAbsImg);
			chmod($rutaAbsImg, 0755);
		}
	}
	
	function afterDelete($id){
		$dir = DIR_FOTOS_NOVEDADES.$id;
		BorrarDirectorio($dir);
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
				$this->ValoresImg[$i]['id_novedad'] = $this->Registro['id_novedad'];
				$this->ValoresImg[$i]['numero_imagen'] = $i;
				$this->ValoresImg[$i]['nombre_imagen'] = $fileName;
				$this->ValoresImg[$i]['tipo_imagen'] = $fileType;
				$this->ValoresImg[$i]['size_imagen'] = $fileSize;
				$this->ValoresImg[$i]['imagen'] = $content;
			}
		}
	}
	
	function ObtenerNovedadesPorCategoria($id_categoria_novedad, $visible=''){
		if($visible === true){
			$sqlVisibles = "visible = 1 AND";
		}
		else if($visible === false){
			$sqlVisibles = "visible = 0 AND";
		}
	
		$sql = "SELECT id_novedad, titulo, cabezal, TO_DAYS(NOW()) - TO_DAYS(fecha) AS vigencia FROM novedad WHERE $sqlVisibles id_categoria_novedad = $id_categoria_novedad ORDER BY ordinal";
		
		return $this->DB->execute($sql);
	}
	
	// Mas simple
	function ObtenerNovedadesCategoria($id_categoria_novedad){
		$sql = "SELECT id_novedad, titulo, cabezal FROM novedad WHERE id_categoria_novedad = $id_categoria_novedad ORDER BY ordinal";
		
		return $this->DB->execute($sql);
	}
	
	// Mas simple de todas
	function ObtenerNovedadesParaMostrar(){
		$sql = "SELECT id_novedad, titulo, cabezal FROM novedad WHERE visible = 1 ORDER BY id_categoria_novedad, ordinal";		
		return $this->DB->execute($sql);
	}
	
	function SetOrdinal($id, $valOrdinal){
		$sql = "UPDATE novedad SET ordinal = $valOrdinal WHERE id_novedad = $id";
		$OK = $this->DB->execute($sql);
		if($OK === false){
			$this->Error = $this->DB->ErrorMsg();
		}
	}
	
	function DatosNovedad($id){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM novedad WHERE id_novedad = $id";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_novedad'] = $qr->fields['id_novedad'];
			$Datos['titulo'] = $qr->fields['titulo'];
			$Datos['texto'] = $qr->fields['texto'];
			$Datos['src_imagen'] = $this->GetURLImagen($id);
			$Datos['src_imagen_local'] = $this->GetURLImagenLocal($id);
			
			return $Datos;
		}
	}
	
	function novedadesPortada(){
		$q = "SELECT n.id_novedad, DATE_FORMAT(n.fecha, '%e/%c/%Y') AS fechadsc, titulo, cabezal FROM novedad n WHERE portada = 1 ORDER BY ordinal";
		$novs = array();
		$qr = $this->DB->execute($q);
		while(!$qr->EOF){
			array_push($novs, array(
				'id_novedad'=>$qr->fields['id_novedad'],
				'fechadsc'=>$qr->fields['fechadsc'],
				'titulo'=>$qr->fields['titulo'],
				'cabezal'=>$qr->fields['cabezal'],
				'url_img'=>$this->GetURLImagen($qr->fields['id_novedad']),
				'url_img_local'=>$this->GetURLImagenLocal($qr->fields['id_novedad'])
			));
			$qr->MoveNext();
		}
		return $novs;
	}
}
?>