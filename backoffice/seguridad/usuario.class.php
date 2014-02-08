<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'class/constantes_si_no.class.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

class Usuario extends Table {
	var $Ajax;
	var $id_perfil_anterior;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Usuario($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'usuario_admin');
		$this->AccionesGrid = array(ACC_BAJA,ACC_MODIFICACION,ACC_CONSULTA);
		
		// Ajax
		$this->Ajax = $AJAX;
	}
	
	function SetSoloLectura(){
		$this->AccionesGrid = array(ACC_CONSULTA);
	}
	
	function ExisteLogin($username){
		$cant = $this->DB->getOne("SELECT COUNT(id_usuario_admin) FROM usuario_admin WHERE login = '$username'");
		return ($cant > 0);
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('ADMINISTRAR USUARIOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('nombre_usuario_admin'=>'Nombre', 'nombre_perfil'=>'Perfil', 'login'=>'Login'));
	
		$Where = "";
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		$Grid->_CampoBus = 'nombre_usuario_admin';
		
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
		
		$campos = 'id_usuario_admin AS id, nombre_usuario_admin, nombre_perfil, perfil.id_perfil, login, password, activo';
		
		$from = 'usuario_admin INNER JOIN perfil ON usuario_admin.id_perfil = perfil.id_perfil';
		
		$where = 'usuario_admin.id_usuario_admin <> '.$_SESSION["cfgusu"]["id_usuario"];
		
		// Datos
		$Grid->getDatos($this->DB, $campos, $from, $where);
		
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
		$Form = new nyiHTML('usuarios/usuario_frm.htm');
		$Security = new Seguridad($Cnx);
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('ID_USUARIO', $this->Registro['id_usuario_admin']);
		$Form->assign('LOGIN', $this->Registro['login']);
		$Form->assign('NOMBRE_USUARIO', $this->Registro['nombre_usuario_admin']);
		$Form->assign('ID_PERFIL', $this->Registro['id_perfil']);
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('PERFIL', $Cnx->getOne("SELECT nombre_perfil FROM perfil WHERE id_perfil = ".$this->Registro['id_perfil']));
			$Activo = $this->Registro['activo'] == 1 ? _SIN : _NON;
		}
		else{
			$Activo = $this->Registro['activo'] == 1 ? 'checked' : '';
			$Form->assign('PERFIL_ID', $Security->GetComboIdsPerfiles());
			$Form->assign('PERFIL_NOM', $Security->GetComboNombresPerfiles());
		}
		
		$Form->assign('ACTIVO', $Activo);
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRACION DE USUARIOS');
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
		
		// Ajax
		$this->Ajax->setRequestURI(DIR_HTTP.'ajax_usuario.php');
		$this->Ajax->registerFunction("ResetClave");
		$this->Ajax->registerFunction("CambiarClaveManual");
	
		// Contenido
		return($Form->fetchHTML());
	}
		
	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_usuario_admin'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
		$this->id_perfil_anterior = $this->Registro['id_perfil'];
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_usuario_admin'] = $_POST['ID_USUARIO'];
		$this->Registro['nombre_usuario_admin'] = $_POST['NOMBRE_USUARIO'];
		$this->Registro['id_perfil'] = $_POST['ID_PERFIL'];
		$this->Registro['activo'] = isset($_POST['ACTIVO']) ? 1 : 0;
		//$this->Registro['login'] = $_POST['LOGIN'];
	}
	
	function afterInsert($LastID){
		// MANDAR MAIL al cliente
	}
	
	function afterEdit(){
		$ID = $this->Registro['id_cliente'];
		// MANDAR MAIL al cliente
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		if($Grid->CantidadRegistros() == 0){
			$Grid->addVariable("MENSAJE", "No se encontraron resultados para los criterios ingresados.");
		}
		// devuelvo
		return ($Grid->fetchGrid('usuarios/usuario_grid.htm', 'ADMINISTRACION DE USUARIOS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								basename($_SERVER['SCRIPT_NAME']), // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_usuario_admin) FROM usuario_admin");
	}
	
	function Login($user, $pass){
		$Security = new Seguridad($this->DB);
		$encPass = $Security->Encriptar($pass);
		$user = $this->DB->execute("SELECT * FROM usuario_admin WHERE login = '$user' AND password = '$encPass'");
		
		if($user->EOF){
			//print_r($UserLogged, false);
			return false;
		}
		
		$user->MoveFirst();
		
		if($user->fields['activo'] == 0){
			return false;
		}
		
		$this->Registro['id_usuario_admin'] = $user->fields['id_usuario_admin'];
		$this->Registro['nombre_usuario_admin'] = $user->fields['nombre_usuario_admin'];
		$this->Registro['id_perfil'] = $user->fields['id_perfil'];
		$this->Registro['login'] = $user;
		$this->Registro['password'] = $pass;
		
		return $this->Registro;
	}
	
	function CambiarClave($user, $pass, $nueva_pass){
		$Security = new Seguridad($this->DB);
		$encPass = $Security->Encriptar($pass);
		$id_usuario_admin = $this->DB->execute("SELECT id_usuario_admin FROM usuario_admin WHERE login = '$user' AND password = '$encPass'");
		
		if($id_usuario->EOF){
			return "La clave actual es incorrecta.";
		}
		
		$encNuevaPass = $Security->Encriptar($nueva_pass);
		$OK = $this->DB->execute("UPDATE usuario_admin SET password = '$encNuevaPass' WHERE id_usuario_admin = ".$id_usuario_admin->fields['id_usuario_admin']);
		
		if($OK === false){
			return $this->DB->ErrorMsg();
		}
		
		return "";
	}
	
	// ------------------------------------------------
	// Genera archivo PDF
	// ------------------------------------------------
	function PDF(){
		// Grid
		$Datos = $this->_Registros(0);
		// PDF
		$PDF = new nyiGridPDF('L');
		$PDF->setCabezal($Tit, $Datos->getFiltro(),
			$_SESSION["empresa"]["NOMBRE"],
			$_SESSION["empresa"]["DATOS"],
			$_SESSION["empresa"]["LOGO"],
			$_SESSION["cfgusu"]["nombre"]);
		$PDF->addColumna('Id.', 'id', 20, 'C');
		$PDF->addColumna('Login', 'login', 70, 'L');
		$PDF->addColumna('Nombre', 'nombre_usuario_admin', 70, 'L');
		$PDF->addColumna('Perfil', 'nombre_perfil', 70, 'L');
		$PDF->addColumna('Activo', 'activo', 20, 'L');
		$PDF->Inicio();
		$PDF->genListado($Datos->Datos);
		$PDF->fetchPDF();
	}
	
	function EstaLoginDisponible($login){
		$cant = $this->DB->getOne("SELECT COUNT(id_usuario_admin) FROM usuario_admin WHERE login = '$login'");
		return $cant == 0;
	}
}
?>