<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class FAQ extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function FAQ($DB){
		// Conexion
		$this->Table($DB, 'faq');
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
		$Grid  = new nyiGridDB('ADMINISTRAR PREGUNTAS M&Aacute;S FRECUENTES', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'pregunta'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('numero'=>'N&uacute;mero', 'pregunta'=>'Pregunta', "IF(visible, 'Si', 'No')"=>'Visible'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
			
		$Grid->getDatos($this->DB, "id_faq AS id, pregunta, numero, respuesta, IF(visible, 'Si', 'No') AS visible_dsc, SUBSTR(respuesta, 1, 70) AS respuesta_dsc", "faq");
		
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
		$Form = new nyiHTML('feria/faq_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_faq', $this->Registro['id_faq']);
		$Form->assign('numero', $this->Registro['numero']);
		$Form->assign('pregunta', $this->Registro['pregunta']);
		$Form->assign('respuesta', $this->Registro['respuesta']);
		$Form->assign('visible', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR PREGUNTAS M&Aacute;S FRECUENTES');
		$Cab->assign('NOMACCION', getNomAccion($Accion));
		$Cab->assign('ACC', $Accion);
		
		$Parametros = $_GET;
		unset($Parametros['ACC']);
		unset($Parametros['COD']);
		// Script Salir
		$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		// Script Listado
		$Cab->assign('SCRIPT_LIS', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		$Form->assign('NAVEGADOR', $Cab->fetchHTML());
	
		// Contenido
		return($Form->fetchHTML());
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_faq'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_faq'] = $_POST['id_faq'];
		$this->Registro['pregunta'] = $_POST['pregunta'];
		$this->Registro['numero'] = $_POST['numero'];
		$this->Registro['respuesta'] = $_POST['respuesta'];
		$this->Registro['visible'] = $_POST['visible'] ? 1 : 0;
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('feria/faq_grid.htm', 'ADMINISTRAR PREGUNTAS M&Aacute;S FRECUENTES',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_faq) FROM faq");
	}

	// Retorna el combo de identificadores ordenados por nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_faq FROM faq ORDER BY numero");
		
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
		$Col = $Aux->getCol("SELECT pregunta FROM faq ORDER BY numero");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}		
	
	function datosPorId($idFaq){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM faq WHERE id_faq = $idFaq";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_faq'] = $qr->fields['id_faq'];
			$Datos['pregunta'] = $qr->fields['pregunta'];
			$Datos['numero'] = $qr->fields['numero'];
			$Datos['respuesta'] = $qr->fields['respuesta'];
			$Datos['visible'] = $qr->fields['visible'];
		}
		return $Datos;
	}
	
	function obtenerTodasVisibles(){
		return $this->DB->execute("SELECT * FROM faq WHERE visible = 1 ORDER BY numero");
	}
}
?>