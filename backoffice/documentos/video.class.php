<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'documentos/categoria_documento.class.php');

class Video extends Table {

	var $Ajax;	
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Video($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'video');
		$UploadedDoc = array();
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
		$Grid  = new nyiGridDB('VIDEOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre');
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('nombre_categoria_documento'=>'Categoria', 'id'=>'Identificador' ,'nombre'=>'Nombre'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
	
		$Campos = "v.id_video AS id, c.nombre_categoria_documento, v.nombre, v.preferencial";
		$From = "video v INNER JOIN categoria_documento c ON c.id_categoria_documento = v.id_categoria_documento";
		
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
		$id = $this->Registro['id_video'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('documentos/video_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('id_video', $id);
		$Form->assign('id_categoria_documento', $this->Registro['id_categoria_documento']);
		$Form->assign('nombre', $this->Registro['nombre']);
		$Form->assign('codigo', $this->Registro['codigo']);
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
	
	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_video'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function GetNombre($id_video){
		return $this->DB->getOne("SELECT nombre FROM video WHERE id_video = $id_video");
	}	
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		// Cargo desde el formulario
		$this->Registro['id_video'] = $_POST['id_video'];
		$this->Registro['id_categoria_documento'] = $_POST['id_categoria_documento'];
		$this->Registro['nombre'] = $_POST['nombre'];
		$this->Registro['codigo'] = $_POST['codigo'];
		$this->Registro['preferencial'] = $_POST['preferencial'];
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('documentos/video_grid.htm', 'Listado de videos',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_video) FROM video");
	}
	
	function ObtenerVideosPorCategoria($id_categoria_documento){
		return $this->DB->execute("SELECT * FROM video WHERE id_categoria_documento = $id_categoria_documento");
	}
	
	function videosComunes(){
		$docs = array();
		$rsDocs = $this->DB->execute("SELECT nombre, codigo FROM video WHERE preferencial = 0 ORDER BY nombre");
		while(!$rsDocs->EOF){
			array_push($docs, array(
	   			'nombre'=>$rsDocs->fields['nombre'],
				'url'=>"www.youtube.com/watch?v=".$rsDocs->fields['codigo']
			));
			$rsDocs->MoveNext();	
		}
		return $docs;
	}
	
	function videosComunesPorCategoria($idCat){
		$docs = array();
		$rsDocs = $this->DB->execute("SELECT nombre, codigo FROM video WHERE preferencial = 0 AND id_categoria_documento = $idCat AND nombre <> '' ORDER BY nombre");
		while(!$rsDocs->EOF){
			array_push($docs, array(
	   			'nombre'=>$rsDocs->fields['nombre'],
				'url'=>"www.youtube.com/watch?v=".$rsDocs->fields['codigo']
			));
			$rsDocs->MoveNext();
		}
		return $docs;	
	}
}
?>