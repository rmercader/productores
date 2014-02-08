<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'documentos/categoria_documento.class.php');

class Documento extends Table {

	var $TamTextoGrilla = 200;
	var $Ajax;
	var $UploadedDoc;
	var $RegPrevio;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Documento($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'documento');
		$UploadedDoc = array();
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
		$Grid  = new nyiGridDB('DOCUMENTOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_documento');
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('nombre_categoria_documento'=>'Categoria', 'id'=>'Identificador' ,'nombre_documento'=>'Nombre', 'titulo'=>'Título', 'extension'=>'Extension'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
	
		$Campos = "d.id_documento AS id, c.nombre_categoria_documento, d.nombre_documento, d.titulo, d.extension, d.preferencial";
		$From = "documento d INNER JOIN categoria_documento c ON c.id_categoria_documento = d.id_categoria_documento";
		
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
		$id = $this->Registro['id_documento'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('documentos/documento_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('ID_DOCUMENTO', $id);
		$Form->assign('ID_CATEGORIA_DOCUMENTO', $this->Registro['id_categoria_documento']);
		$Form->assign('NOMBRE_DOCUMENTO', $this->Registro['nombre_documento']);
		$Form->assign('TITULO', $this->Registro['titulo']);
		$Form->assign('preferencial', $this->Registro['preferencial']);
		$Form->assign('pref_ids', array(0, 1));
		$Form->assign('pref_dsc', array('Com&uacute;n', 'Preferencial'));
		
		$TblCat = new CategoriaDocumento($Cnx);
		$Form->assign('CATEGORIA_DOCUMENTO_ID', $TblCat->GetComboIds());
		$Form->assign('CATEGORIA_DOCUMENTO_NOM', $TblCat->GetComboNombres());
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('NOMBRE_CATEGORIA_DOCUMENTO', $Cnx->getOne("SELECT nombre_categoria_documento FROM categoria_documento WHERE id_categoria_documento = ".$this->Registro['id_categoria_documento']));
			$src = $this->GetUrlDocumento($id);
			$ext = $this->GetUrlExtensionDocumento($id);
			$Form->assign('SRC_DOCUMENTO', $src);
			$Form->assign('SRC_EXTENSION', $ext);
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'DOCUMENTOS');
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
	
	function GetUrlDocumento($idDoc){
		$url = "";
		if(is_numeric($idDoc)){
			$Datos = $this->DB->execute("SELECT id_categoria_documento, nombre_documento, extension FROM documento WHERE id_documento = $idDoc");
			if(!$Datos->EOF){
				// Si existe
				$cat = $Datos->fields['id_categoria_documento'];
				$nom = $Datos->fields['nombre_documento'];
				$ext = $Datos->fields['extension'];
				$nombre = "$nom.$ext";
				$url = DIR_HTTP_DOCUMENTOS.$cat."/$nombre";
			}
		}
		
		return $url;
	}
	
	function GetUrlExtensionDocumento($idDoc){
		$url = "";
		if(is_numeric($idDoc)){
			$Dato = $this->DB->getOne("SELECT extension FROM documento WHERE id_documento = $idDoc");
			if($Dato != ""){
				// Si existe
				$tipo = strtolower($Dato);
				$url = DIR_HTTP_EXTENSIONES_DOCUMENTOS."$tipo.gif";
			}
		}
		
		return $url;
	}
	
	function ControlArchivo($id=''){
		$cat = $this->Registro['id_categoria_documento'];
		$nom = $this->Registro['nombre_documento'];
		$ext = $this->Registro['extension'];
		$sql = "SELECT COUNT(id_documento) FROM documento WHERE nombre_documento = '$nom' AND id_categoria_documento = $cat AND extension = '$ext'";
		if($id != ''){
			$sql .= " AND id_documento <> $id";
		}
		
		$cnt = $this->DB->getOne($sql);
		
		if($cnt > 0){
			$this->Error .= "\nYa existe un archivo de igual nombre y extension, almacenado dentro de esa categoria.\n";
		}
	}
	
	function GetExtensionDocumento($id){
		return $this->DB->getOne("SELECT extension FROM documento WHERE id_documento = $id");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_documento'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function GetNombre($id_documento){
		return $this->DB->getOne("SELECT nombre_documento FROM documento WHERE id_documento = $id_documento");
	}	
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		// Cargo desde el formulario
		$this->Registro['id_documento'] = $_POST['ID_DOCUMENTO'];
		$this->Registro['id_categoria_documento'] = $_POST['ID_CATEGORIA_DOCUMENTO'];
		$this->Registro['titulo'] = $_POST['TITULO'];
		$this->Registro['preferencial'] = $_POST['preferencial'];
		$this->CargarUpload();
		if($this->UploadedDoc['subir_documento'] === true){
			$this->Registro['extension'] = GetExtension($this->UploadedDoc['nombre']);
			$nom = str_replace(".".$this->Registro['extension'], "", $this->UploadedDoc['nombre']);
			$this->Registro['nombre_documento'] = $nom;
		}
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('documentos/documento_grid.htm', 'Listado de documentos',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_documento) FROM documento");
	}

	function beforeInsert(){
		$this->ControlArchivo();
	}
	
	function afterInsert($LastID){
		$this->SalvarDocumento($LastID);
	}
	
	function beforeEdit(){
		$id = $this->Registro['id_documento'];
		$this->ControlArchivo($id);
		if($this->Error == ''){
			$DatosPrv = $this->DB->execute("SELECT * FROM documento WHERE id_documento = ".$this->Registro['id_documento']);
			// Genero estructura
			$this->RegPrevio = $this->Registro;
			$this->RegPrevio['nombre_documento'] = $DatosPrv->fields['nombre_documento'];
			$this->RegPrevio['id_categoria_documento'] = $DatosPrv->fields['id_categoria_documento'];
			$this->RegPrevio['extension'] = $DatosPrv->fields['extension'];
		}
	}

	function afterEdit(){
		// Si se subio archivo
		if($this->UploadedDoc['subir_documento'] === true){
			$this->BorrarDocumentoFileSystem($this->RegPrevio['id_categoria_documento'], $this->RegPrevio['nombre_documento'], $this->RegPrevio['extension']); // Borro el anterior
			$this->SalvarDocumento(); // Salvo el nuevo
		}
		else{
			if($this->Registro['id_categoria_documento'] != $this->RegPrevio['id_categoria_documento']){
				// Si se cambio la categoria, pero no se subio archivo, entonces hay que mover el archivo
				// Recupero la categoria
				$cat = $this->Registro['id_categoria_documento'];
				// Directorio
				$nuevoDir = DIR_DOCUMENTOS.$cat;
				if(!file_exists($nuevoDir)){
					mkdir($nuevoDir);
					chmod($nuevoDir, 0755);
				}
				
				$nuevaUrl = $nuevoDir.'/'.$this->Registro['nombre_documento'].'.'.$this->Registro['extension'];
				$viejaUrl = DIR_DOCUMENTOS.$this->RegPrevio['id_categoria_documento'].'/'.$this->Registro['nombre_documento'].'.'.$this->Registro['extension'];
				
				rename($viejaUrl, $nuevaUrl);
				chmod($nuevaUrl, 0755);
			}
		}
	}
	
	// Salva documento al disco
	function SalvarDocumento(){
		// Recupero la categoria
		$cat = $this->Registro['id_categoria_documento'];
		// Directorio
		$nuevoDir = DIR_DOCUMENTOS.$cat;
		if(!file_exists($nuevoDir)){
			mkdir($nuevoDir);
			chmod($nuevoDir, 0755);
		}
		
		$nuevoDir = $nuevoDir.'/';
		
		// Extraigo los datos de la foto :contenido, y tamano
		$size = $this->UploadedDoc['size'];
		$content = $this->UploadedDoc['documento'];
		$nombre = $this->Registro['nombre_documento'];
		$extension = $this->Registro['extension'];
		
		$archivo = $nuevoDir."$nombre.$extension";
		$fp = fopen($archivo, 'w');
		fwrite($fp, $content, $size);
		fclose($fp);
		chmod($archivo, 0755);
	}
	
	function BorrarDocumentoFileSystem($id_cat, $nom, $ext){
		$dir = DIR_DOCUMENTOS."$id_cat/$nom.$ext";
		@unlink($dir);
	}
	
	function afterDelete($id){
		$this->BorrarDocumentoFileSystem($this->Registro['id_categoria_documento'], $this->Registro['nombre_documento'], $this->Registro['extension']);
	}
	
	function CargarUpload(){
		$label = "ARCHIVO_DOCUMENTO";
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
			
			if(!is_array($this->UploadedDoc)){
				$this->UploadedDoc = array();
			}
			
			$this->UploadedDoc['subir_documento'] = true;
			$this->UploadedDoc['nombre'] = $fileName;
			$this->UploadedDoc['tipo'] = $fileType;
			$this->UploadedDoc['size'] = $fileSize;
			$this->UploadedDoc['documento'] = $content;
		}
	}
	
	function ObtenerDocumentosPorCategoria($id_categoria_documento){
		return $this->DB->execute("SELECT * FROM documento WHERE id_categoria_documento = $id_categoria_documento");
	}
	
	function BorrarDocumentosPorCategoria($id_categoria_documento){
		$dir = DIR_DOCUMENTOS.$id_categoria_documento;
		BorrarDirectorio($dir);
	}
	
	function documentosComunes(){
		$docs = array();
		$rsDocs = $this->DB->execute("SELECT * FROM documento WHERE preferencial = 0 ORDER BY titulo");
		while(!$rsDocs->EOF){
			array_push($docs, array(
	   			'titulo'=>$rsDocs->fields['titulo'],
				'url_thu'=>$this->GetUrlExtensionDocumento($rsDocs->fields['id_documento']),
				'url_doc'=>$this->GetUrlDocumento($rsDocs->fields['id_documento'])
			));
			$rsDocs->MoveNext();	
		}
		return $docs;
	}
	
	function documentosComunesPorCategoria($idCat){
		$docs = array();
		$rsDocs = $this->DB->execute("SELECT * FROM documento WHERE preferencial = 0 AND id_categoria_documento = $idCat AND nombre_documento <> '' AND extension <> '' ORDER BY titulo");
		while(!$rsDocs->EOF){
			array_push($docs, array(
	   			'titulo'=>$rsDocs->fields['titulo'],
				'url_thu'=>$this->GetUrlExtensionDocumento($rsDocs->fields['id_documento']),
				'url_doc'=>$this->GetUrlDocumento($rsDocs->fields['id_documento'])
			));
			$rsDocs->MoveNext();	
		}
		return $docs;	
	}
}
?>