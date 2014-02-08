<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class Link extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Link($DB){
		// Conexion
		$this->Table($DB, 'link');
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
		$Grid  = new nyiGridDB('ADMINISTRAR LINKS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'titulo'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('id'=>'Identificador', 'titulo'=>'Titulo'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
			
		$Grid->getDatos($this->DB, "id_link AS id, titulo, url", "link");
		
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
		$Form = new nyiHTML('documentos/link_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('ID_LINK', $this->Registro['id_link']);
		$Form->assign('TITULO', $this->Registro['titulo']);
		$Form->assign('URL', $this->Registro['url']);
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR LINKS');
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
	function _GetDB($Cod=-1, $Campo='id_link'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_link'] = $_POST['ID_LINK'];
		$this->Registro['titulo'] = $_POST['TITULO'];
		$this->Registro['url'] = trim($_POST['URL']);
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('documentos/link_grid.htm', 'ADMINISTRAR LINKS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_link) FROM link");
	}

	// Retorna el combo de identificadores ordenados segun el idioma
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_link FROM link ORDER BY titulo");
		
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
		$Col = $Aux->getCol("SELECT titulo FROM link ORDER BY titulo");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function ObtenerLinks(){
		return $this->DB->execute("SELECT * FROM link ORDER BY titulo");
	}
}
?>