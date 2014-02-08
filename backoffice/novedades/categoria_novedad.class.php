<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class CategoriaNovedad extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function CategoriaNovedad($DB){
		// Conexion
		$this->Table($DB, 'categoria_novedad');
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
		$Grid  = new nyiGridDB('ADMINISTRAR CATEGORIAS DE NOVEDADES', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_categoria_novedad'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('id_categoria_novedad'=>'Identificador', 'nombre_categoria_novedad'=>'Nombre'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
			
		$Grid->getDatos($this->DB, "id_categoria_novedad AS id, nombre_categoria_novedad", "categoria_novedad");
		
		// Devuelvo
		return($Grid);
	}

	function ObtenerCategoriasConNovedades($visibles=''){
		if($visibles === true){
			$sqlVisibles = "visible = 1 AND";
		}
		else if($visibles === false){
			$sqlVisibles = "visible = 0 AND";
		}
	
		return $this->DB->execute("SELECT * FROM categoria_novedad c WHERE $sqlVisibles EXISTS (SELECT id_novedad FROM novedad n WHERE n.id_categoria_novedad = c.id_categoria_novedad) ORDER BY ordinal");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('novedades/categoria_novedad_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('ID_CATEGORIA_NOVEDAD', $this->Registro['id_categoria_novedad']);
		$Form->assign('NOMBRE_CATEGORIA_NOVEDAD', $this->Registro['nombre_categoria_novedad']);
		$Form->assign('VISIBLE', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR CATEGORIAS DE NOVEDADES');
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
	function _GetDB($Cod=-1, $Campo='id_categoria_novedad'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_categoria_novedad'] = $_POST['ID_CATEGORIA_NOVEDAD'];
		$this->Registro['nombre_categoria_novedad'] = $_POST['NOMBRE_CATEGORIA_NOVEDAD'];
		$this->Registro['visible'] = $_POST['VISIBLE'] ? 1 : 0;
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('novedades/categoria_novedad_grid.htm', 'ADMINISTRAR CATEGORIAS DE NOVEDADES',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_categoria_novedad) FROM categoria_novedad");
	}

	// Retorna el combo de identificadores ordenados segun el idioma
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_categoria_novedad FROM categoria_novedad ORDER BY nombre_categoria_novedad");
		
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
		$Col = $Aux->getCol("SELECT nombre_categoria_novedad FROM categoria_novedad ORDER BY nombre_categoria_novedad");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function ObtenerCategorias(){
		return $this->DB->execute("SELECT * FROM categoria_novedad ORDER BY ordinal");
	}
	
	function SetOrdinal($id, $valOrdinal){
		$sql = "UPDATE categoria_novedad SET ordinal = $valOrdinal WHERE id_categoria_novedad = $id";
		$OK = $this->DB->execute($sql);
		if($OK === false){
			$this->Error = $this->DB->ErrorMsg();
		}
	}
}
?>