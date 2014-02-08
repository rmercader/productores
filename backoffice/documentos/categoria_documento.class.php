<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class CategoriaDocumento extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function CategoriaDocumento($DB){
		// Conexion
		$this->Table($DB, 'categoria_documento');
		$this->AccionesGrid = array(ACC_BAJA,ACC_MODIFICACION,ACC_CONSULTA);
	}
	
	function SetSoloLectura(){
		$this->AccionesGrid = array(ACC_CONSULTA);
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('ADMINISTRAR CATEGORIAS DE DOCUMENTOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_categoria_documento'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('id_categoria_documento'=>'Identificador', 'nombre_categoria_documento'=>'Nombre'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
			
		$Grid->getDatos($this->DB, "id_categoria_documento AS id, nombre_categoria_documento", "categoria_documento");
		
		// Devuelvo
		return($Grid);
	}

	

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('documentos/categoria_documento_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('ID_CATEGORIA_DOCUMENTO', $this->Registro['id_categoria_documento']);
		$Form->assign('NOMBRE_CATEGORIA_DOCUMENTO', $this->Registro['nombre_categoria_documento']);
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR CATEGORIAS DE DOCUMENTOS');
		$Cab->assign('NOMACCION', getNomAccion($Accion));
		$Cab->assign('ACC', $Accion);
		// Script Salir
		$Cab->assign('SCRIPT_SALIR',basename($_SERVER['SCRIPT_NAME']));
		
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
	function _GetDB($Cod=-1, $Campo='id_categoria_documento'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_categoria_documento'] = $_POST['ID_CATEGORIA_DOCUMENTO'];
		$this->Registro['nombre_categoria_documento'] = $_POST['NOMBRE_CATEGORIA_DOCUMENTO'];
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('documentos/categoria_documento_grid.htm', 'ADMINISTRAR CATEGORIAS DE DOCUMENTOS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_categoria_documento) FROM categoria_documento");
	}

	// Retorna el combo de identificadores ordenados por nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_categoria_documento FROM categoria_documento ORDER BY nombre_categoria_documento");
		
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
		$Col = $Aux->getCol("SELECT nombre_categoria_documento FROM categoria_documento ORDER BY nombre_categoria_documento");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function beforeDelete($Cod){
		$cnt = $this->DB->getOne("SELECT COUNT(id_documento) FROM documento WHERE id_categoria_documento = $Cod");
		
		if($cnt > 0){
			$this->Error .= "Existen $cnt documentos asociados a esta categoria\n";
		}
	}
	
	function ObtenerCategoriasConDocumentos(){
		return $this->DB->execute("SELECT * FROM categoria_documento c WHERE EXISTS (SELECT id_documento FROM documento d WHERE d.id_categoria_documento = c.id_categoria_documento) ORDER BY nombre_categoria_documento");
	}
	
	function categoriasConDocumentosComunes(){
		return $this->DB->execute("SELECT * FROM categoria_documento c WHERE EXISTS (SELECT id_documento FROM documento d WHERE d.id_categoria_documento = c.id_categoria_documento AND d.preferencial = 0 AND d.nombre_documento <> '' AND d.extension <> '') ORDER BY nombre_categoria_documento");
	}
	
	function datosPorId($idCategoria){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM categoria_documento WHERE id_categoria_documento = $idCategoria";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_categoria_documento'] = $qr->fields['id_categoria_documento'];
			$Datos['nombre_categoria_documento'] = $qr->fields['nombre_categoria_documento'];
			$Datos['visible'] = $qr->fields['visible'];
		}
		return $Datos;
	}
}
?>