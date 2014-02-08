<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/image_handler.class.php');

class Sucursal extends Table {

	var $Ajax;
	var $TablaImg;
	var $ValoresImg;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Sucursal($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'sucursal');
		$this->TablaImg   = new Table($DB, 'sucursal_foto');
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
		$Grid  = new nyiGridDB('SUCURSALES', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre');
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('nombre'=>'Nombre', 'id_sucursal'=>'Id.'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
	
		$Campos = "s.id_sucursal AS id, s.*";
		$From = "sucursal s";
		
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
		$id = $this->Registro['id_sucursal'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('sucursales/sucursal_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('id_sucursal', $id);
		$Form->assign('nombre', $this->Registro['nombre']);
		$Form->assign('telefonos', $this->Registro['telefonos']);
		$Form->assign('direccion', $this->Registro['direccion']);
		$Form->assign('encargados', $this->Registro['encargados']);
		$Form->assign('email', $this->Registro['email']);
		$Form->assign('CANTIDAD_IMAGENES', 1);
		$src = $this->GetURLImagen($id).'?time='.time();
		$Form->assign('SRC_IMAGEN', $src);
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'SUCURSALES');
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
		return DIR_HTTP_FOTOS_SUCURSALES.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenLocal($idN, $num=1){
		return DIR_FOTOS_SUCURSALES.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetExtensionImagen($id, $i){
		return $this->DB->getOne("SELECT extension_imagen FROM sucursal_foto WHERE id_sucursal = $id AND numero_imagen = $i");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_sucursal'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_sucursal'] = $_POST['id_sucursal'];
		$this->Registro['nombre'] = $_POST['nombre'];
		$this->Registro['telefonos'] = $_POST['telefonos'];
		$this->Registro['direccion'] = $_POST['direccion'];
		$this->Registro['encargados'] = $_POST['encargados'];
		$this->Registro['email'] = $_POST['email'];
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
		return ($Grid->fetchGrid('sucursales/sucursales_grid.htm', 'Listado de sucursales',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_sucursal) FROM sucursal");
	}

	function beforeInsert(){
		
	}
	
	function afterInsert($LastID){
		$this->TablaImg->Registro['id_sucursal'] = $LastID;
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
		$ID = $this->Registro['id_sucursal'];
		// Actualizacion de fotos
		$this->TablaImg->Registro['id_sucursal'] = $ID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			if($datos['subir_imagen'] === true){
				$nom = $datos['nombre_imagen'];
				$tipo = $datos['tipo_imagen'];
				$size = $datos['size_imagen'];
				$img = $datos['imagen'];
				
				$extOld = $this->DB->getOne("SELECT extension_imagen FROM sucursal_foto WHERE id_sucursal = $ID AND numero_imagen = $num_imagen");
				$extension = GetExtension($datos['nombre_imagen']);
				
				$this->DB->execute("DELETE FROM sucursal_foto WHERE id_sucursal = $ID AND numero_imagen = $num_imagen");
				
				$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
				$this->TablaImg->Registro['extension_imagen'] = $extension;
				$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
				
				if($this->Error == ''){
					$imgOld = DIR_FOTOS_SUCURSALES.$ID.'/'.$num_imagen.'.'.$extOld;
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
		$nuevoDir = DIR_FOTOS_SUCURSALES.$id;
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
		$largo = 80;
		$ancho = 80;
										
		if ($ImgHandler->open_image($rutaAbsImg) == 0){
			$ImgHandler->resize_image($largo, $ancho);
			$ImgHandler->image_to_file($rutaAbsImgPrv);
			chmod($rutaAbsImgPrv, 0755);
		}
		
		// Normal
		$largo = 180;
		$ancho = 150;
										
		if ($ImgHandler->open_image($rutaAbsImg) == 0){
			$ImgHandler->resize_image($largo, $ancho);
			$ImgHandler->image_to_file($rutaAbsImg);
			chmod($rutaAbsImg, 0755);
		}
	}
	
	function afterDelete($id){
		$dir = DIR_FOTOS_SUCURSALES.$id;
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
				$this->ValoresImg[$i]['id_sucursal'] = $this->Registro['id_sucursal'];
				$this->ValoresImg[$i]['numero_imagen'] = $i;
				$this->ValoresImg[$i]['nombre_imagen'] = $fileName;
				$this->ValoresImg[$i]['tipo_imagen'] = $fileType;
				$this->ValoresImg[$i]['size_imagen'] = $fileSize;
				$this->ValoresImg[$i]['imagen'] = $content;
			}
		}
	}
	
	// Retorna el combo de identificadores ordenados segun el nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_sucursal FROM sucursal ORDER BY nombre");
		
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
		$Col = $Aux->getCol("SELECT nombre FROM sucursal ORDER BY nombre");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function DatosSucursal($id){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM sucursal WHERE id_sucursal = $id";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_sucursal'] = $qr->fields['id_sucursal'];
			$Datos['nombre'] = $qr->fields['nombre'];
			$Datos['telefonos'] = $qr->fields['telefonos'];
			$Datos['direccion'] = $qr->fields['direccion'];
			$Datos['encargados'] = $qr->fields['encargados'];
			$Datos['email'] = $qr->fields['email'];
			$Datos['src_imagen'] = $this->GetURLImagen($id);
			$Datos['src_imagen_local'] = $this->GetURLImagenLocal($id);
			
			return $Datos;
		}	
	}
}
?>