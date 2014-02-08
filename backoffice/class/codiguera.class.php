<?PHP
/*--------------------------------------------------------------------------
   Archivo:codigera.class.php
   Descripcion: Clase para tablas codigeras
  --------------------------------------------------------------------------*/
// includes
include_once('./clases/tabla.class.php');

class TCodigera extends Ttabla {
	var $Titulo;
	var $NomTbl;
	var $htmlFrm;
	var $htmlGrid;

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function TCodigera($DB,$Titulo,$NomTbl,$Frm='codigera_frm.htm',$Grid='codigera_grid.htm'){
		// Conexion
		$this->Ttabla($DB,$NomTbl);

		// Parametros
		$this->Titulo   = $Titulo;
		$this->NomTbl   = $NomTbl;
		$this->htmlFrm  = $Frm;
		$this->htmlGrid = $Grid;
	}

	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
   	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB($this->Titulo,$Regs,'base_grid.htm');

		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']),'nombre');     // Parametros de la sesion
   		$Grid->setPaginador('base_navegador.htm');
   		$Grid->setFrmCriterio('base_frmcriterio.htm',array('id'=>'Identificador','nombre'=>'Nombre'));
   		// Si viene con post
   		if ($_SERVER["REQUEST_METHOD"] == "POST"){
       		$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
       		unset($_GET['NROPAG']);
   		}
		// Numero de Pagina
   		if (isset($_GET['NROPAG']))
       		$Grid->setPaginaAct($_GET['NROPAG']);

       	// Datos
       	$Grid->getDatos($this->DB,'*',$this->NomTbl);

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
		$Form = new nyiHTML($this->htmlFrm);
		$Form->assign('ACC',$Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('ID',$this->Registro['id']);
		$Form->assign('NOMBRE',$this->Registro['nombre']);

       	// Script Post
       	$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));

       	// Cabezal
       	$Cab = new nyiHTML('base_cab_abm.htm');
       	$Cab->assign('NOMFORM',$this->Titulo);
       	$Cab->assign('NOMACCION',getNomAccion($Accion));
       	$Cab->assign('ACC',$Accion);
       	// Script Salir
       	$Cab->assign('SCRIPT_SALIR',basename($_SERVER['SCRIPT_NAME']));

       	// Script Listado
       	$Parametros = $_GET;
       	unset($Parametros['ACC']);
       	unset($Parametros['COD']);
       	$Cab->assign('SCRIPT_LIS',basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
       	$Form->assign('NAVEGADOR',$Cab->fetchHTML());

       // Contenido
       return($Form->fetchHTML());
   	}

	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id']     = $_POST['ID'];
      	$this->Registro['nombre'] = htmlentities($_POST['NOMBRE']);
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
   		// Datos
   		$Grid = $this->_Registros($Regs);
   		// devuelvo
   		$Grid->setAncho("90%");
   		return ($Grid->fetchGrid($this->htmlGrid, $this->Titulo,
        				        basename($_SERVER['SCRIPT_NAME']), // Paginador
                				basename($_SERVER['SCRIPT_NAME']), // PDF
                				basename($_SERVER['SCRIPT_NAME']), // Home
                				basename($_SERVER['SCRIPT_NAME']), // Mto
                				array(ACC_ALTA,ACC_BAJA,ACC_MODIFICACION,ACC_CONSULTA)));
	}

	// ------------------------------------------------
	// Genera archivo PDF
	// ------------------------------------------------
	function PDF(){
		// Grid
		$Datos = $this->_Registros();
   		// PDF
   	    $PDF = new nyiGridPDF();
    	$PDF->setCabezal($this->Titulo, $Datos->getFiltro(),
    					 $_SESSION["empresa"]["NOMBRE"],
    					 $_SESSION["empresa"]["DATOS"],
    					 $_SESSION["empresa"]["LOGO"],
    					 $_SESSION["cfgusu"]["nombre"]);
      	$PDF->addColumna('Id.','id',20,'C');
      	$PDF->addColumna('Nombre','nombre',70,'L');
      	$PDF->Inicio();
      	$PDF->genListado($Datos->Datos);
      	$PDF->fetchPDF();
	}

	// ------------------------------------------------
	// Devuelvo nombre
	// ------------------------------------------------
	function getNombre($Cod){
		$Aux = $this->DB;
		return($Aux->getOne("SELECT nombre FROM ".$this->NomTbl." WHERE id = $Cod"));
	}

	// ------------------------------------------------
	// Devuelvo array de ids para combo
	// ------------------------------------------------
	function getCombo_ids($Todos=false,$IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id FROM ".$this->NomTbl." ORDER BY nombre");

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
	function getCombo_des($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre FROM ".$this->NomTbl." ORDER BY nombre");

		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
}
?>