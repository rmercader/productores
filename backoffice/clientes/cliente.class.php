<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'sucursales/sucursal.class.php');
include_once(DIR_BASE.'class/interfaz.class.php');
include_once(DIR_BASE.'class/class.phpmailer.php');

class Cliente extends Table {

	var $Ajax;
	var $claveGenerada;
	var $enviarMailPorAdmision = false;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Cliente($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'cliente');
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
		$Grid  = new nyiGridDB('CLIENTES', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'matricula');
		$Grid->setPaginador('base_navegador.htm');
		$arrCriterios = array(
			'nombre'=>'Nombre', 
			'apellido'=>'Apellido',
			'matricula'=>'Matr&iacute;cula',
			's.nombre'=>'Sucursal',
			"IF(c.admitido, 'Si', 'No')"=>'Admitido',
			"IF(c.activo, 'Si', 'No')"=>'Activo'
		);
		$Grid->setFrmCriterio('clientes/base_criterios_buscador_clientes.htm', $arrCriterios);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
	
		$Campos = "c.id_cliente AS id, c.*, IF(c.admitido, 'Si', 'No') AS dsc_admitido, IF(c.activo, 'Si', 'No') AS dsc_activo, s.nombre AS dsc_sucursal";
		$From = "cliente c INNER JOIN sucursal s ON s.id_sucursal = c.id_sucursal";
		
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
		$id = $this->Registro['id_cliente'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('clientes/cliente_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('id_cliente', $id);
		$Form->assign('nombre', $this->Registro['nombre']);
		$Form->assign('apellido', $this->Registro['apellido']);
		$Form->assign('matricula', $this->Registro['matricula']);
		$Form->assign('id_sucursal', $this->Registro['id_sucursal']);
		$Form->assign('email', $this->Registro['email']);
		$Form->assign('celular', $this->Registro['celular']);
		$Form->assign('telefono', $this->Registro['telefono']);
		$Form->assign('grupo_economico', $this->Registro['grupo_economico']);
		$Form->assign('tecnico', $this->Registro['tecnico']);
		$Form->assign('direccion', $this->Registro['direccion']);
		$Form->assign('id_departamento', $this->Registro['id_departamento']);
		$Form->assign('suscripto', $this->Registro['suscripto_newsletter'] ? 'checked="checked"' : '');
		$Form->assign('activo', $this->Registro['activo'] ? 'checked="checked"' : '');
		$Form->assign('admitido', $this->Registro['admitido'] ? 'checked="checked"' : '');
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		if($Accion != ACC_ALTA){
			if($this->Registro['fecha_registro'] != '' && $this->Registro['fecha_registro'] != "0000-00-00 00:00:00"){
				$Form->assign("mostrar_fecha_registro", _SI);
				$Form->assign("fecha_registro", FormatDateLong($this->Registro['fecha_registro']));
			}			
			if($this->Registro['fecha_admision'] != '' && $this->Registro['fecha_admision'] != "0000-00-00 00:00:00"){
				$Form->assign("mostrar_fecha_admision", _SI);
				$Form->assign("fecha_admision", FormatDateLong($this->Registro['fecha_admision']));
			}
			$Form->assign("mostrar_cambiar_clave", _SI);
		}
		
		$objSuc = new Sucursal($Cnx);
		$Form->assign('ids_suc', $objSuc->GetComboIds());
		$Form->assign('dsc_suc', $objSuc->GetComboNombres());
		
		$interfaz = new Interfaz($Cnx);
		$Form->assign('ids_depto', $interfaz->obtenerIdsDepartamentos());
		$Form->assign('dsc_depto', $interfaz->obtenerNombresDepartamentos());
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'CLIENTES');
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
	function _GetDB($Cod=-1,$Campo='id_cliente'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_cliente'] = $_POST['id_cliente'];
		$this->Registro['nombre'] = $_POST['nombre'];
		$this->Registro['apellido'] = $_POST['apellido'];
		$this->Registro['matricula'] = $_POST['matricula'];
		$this->Registro['id_sucursal'] = $_POST['id_sucursal'];
		$this->Registro['tecnico'] = $_POST['tecnico'];
		$this->Registro['grupo_economico'] = $_POST['grupo_economico'];
		$this->Registro['email'] = $_POST['email'];
		$this->Registro['celular'] = $_POST['celular'];
		$this->Registro['telefono'] = $_POST['telefono'];
		$this->Registro['direccion'] = $_POST['direccion'];
		$this->Registro['id_departamento'] = $_POST['id_departamento'];
		$this->Registro['suscripto_newsletter'] = isset($_POST['suscripto_newsletter']) ? 1 : 0;
		$this->Registro['admitido'] = isset($_POST['admitido']) ? 1 : 0;
		$this->Registro['activo'] = isset($_POST['activo']) ? 1 : 0;
		
		$security = new Seguridad($this->DB);
		if(isset($_POST['generar_clave'])){
			$clave = $security->GenerarPassword();
			$this->claveGenerada = $clave;
			$claveEnc = $security->Encriptar($clave);
			$this->Registro['clave'] = $claveEnc;
		}
		else if($this->Registro['id_cliente'] != ""){
			// Modificacion, ver si cambia clave
			if(isset($_POST['cambiar_clave'])){
				$this->claveGenerada = $_POST['clave'];
				$this->Registro['clave'] = $security->Encriptar($_POST['clave']);	
			}
		}
		else {
			$this->claveGenerada = $_POST['clave'];
			$this->Registro['clave'] = $security->Encriptar($_POST['clave']);
		}
		
		if($this->Registro['id_cliente'] == ""){
			$this->Registro['fecha_registro'] = date('Y')."-".date('m')."-".date('d')." ".date('H').":".date('i').":00";
			if(isset($_POST['admitido'])){
				$this->Registro['fecha_admision'] = $this->Registro['fecha_registro'];	
				$this->enviarMailPorAdmision = true; // Se va a dar de alta admitido
			}
		}
		else {
			$fechaAdm = $this->fechaAdmision($this->Registro['id_cliente']);
			if(isset($_POST['admitido']) && (trim($fechaAdm) == "" || $fechaAdm == "0000-00-00 00:00:00")){
				$this->Registro['fecha_admision'] = date('Y')."-".date('m')."-".date('d')." ".date('H').":".date('i').":00";
				$this->enviarMailPorAdmision = true; // Se admite por primera vez
			}
		}
	}
	
	function fechaAdmision($idCliente){
		return $this->DB->getOne("SELECT fecha_admision FROM cliente WHERE id_cliente = $idCliente");
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('TAM_TXT', $this->TamTextoGrilla);
		
		// devuelvo
		return ($Grid->fetchGrid('clientes/clientes_grid.htm', 'Listado de clientes',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_cliente) FROM cliente");
	}				
	
	function DatosCliente($id){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM cliente WHERE id_cliente = $id";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_cliente'] = $qr->fields['id_cliente'];
			$Datos['nombre'] = $qr->fields['nombre'];			
			$Datos['telefonos'] = $qr->fields['telefonos'];
			$Datos['direccion'] = $qr->fields['direccion'];
			$Datos['encargados'] = $qr->fields['encargados'];
			$Datos['src_imagen'] = $this->GetURLImagen($id);
			
			return $Datos;
		}
	}
	
	// Retorna el combo de identificadores ordenados segun el nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_cliente FROM cliente ORDER BY nombre");
		
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
		$Col = $Aux->getCol("SELECT nombre FROM cliente ORDER BY nombre");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function existeMatricula($matricula){
		$cnt = $this->DB->getOne("SELECT COUNT(id_cliente) FROM cliente WHERE UPPER(matricula) = '".strtoupper($matricula)."'");
		return $cnt > 0;
	}
	
	function registro($matricula, $nombre, $apellido, $grupo, $idSucursal, $tecnico, $celular, $telefono, $email, $idDpto, $direccion, $clave){
		$security = new Seguridad($this->DB);
		$claveEnc = $security->Encriptar($clave);
		$sqlInsert = "
		INSERT INTO cliente (
			nombre,
			apellido,
			matricula,
			id_sucursal,
			email,
			celular,
			telefono,
			grupo_economico,
			tecnico,
			direccion,
			id_departamento,
			clave,
			fecha_registro,
			suscripto_newsletter,
			admitido,
			activo
			)
		VALUES (
			'$nombre', 
			'$apellido', 
			'$matricula', 
			$idSucursal, 
			'$email', 
			'$celular', 
			'$telefono', 
			'$grupo', 
			'$tecnico', 
			'$direccion', 
			$idDpto, 
			'$claveEnc', 
			'".date('Y')."-".date('m')."-".date('d')." ".date('H').":".date('i').":00',
			1, 
			0, 
			1
		)";
		$ok = $this->DB->execute($sqlInsert);
		if($ok === FALSE){
			$datoLog = "Intento fallido de registro de cliente, matricula: $matricula y departamento $idDpto.\n";
			$datoLog .= "Detalle del error: ".$this->DB->ErrorMsg()."\n".$sqlInsert;
			LogError($datoLog, DIR_BASE.'clientes/cliente.class.php', "registro");
			$this->Error .= $datoLog;
		}
		return $ok;
	}
	
	function obtenerMailPorMatricula($matricula){
		$mail = $this->DB->getOne("SELECT DISTINCT(email) FROM cliente WHERE matricula = '$matricula'");
		return $mail;
	}
	
	function cambiarClavePorMatricula($matricula, $claveEnc){
		$this->DB->execute("UPDATE cliente SET clave = '$claveEnc' WHERE matricula = '$matricula'");	
	}
	
	function autenticar($matricula, $clave){
		$datos = $this->DB->execute("SELECT id_cliente, nombre, apellido, admitido, activo FROM cliente WHERE matricula = '$matricula' AND clave = '$clave'");		
		if(!$datos->EOF){
			$cliente = array(
				'id_cliente'=>$datos->fields['id_cliente'],
				'nombre'=>$datos->fields['nombre'],
				'apellido'=>$datos->fields['apellido'],
				'admitido'=>$datos->fields['admitido'],
				'activo'=>$datos->fields['activo']
			);
			return $cliente;
		}
		return "";
	}
	
	function datosParaSesionPorId($idCliente){
		$datos = $this->DB->execute("SELECT nombre, apellido, admitido, activo FROM cliente WHERE id_cliente = $idCliente");		
		if(!$datos->EOF){
			$cliente = array(
				'id_cliente'=>$idCliente,
				'nombre'=>$datos->fields['nombre'],
				'apellido'=>$datos->fields['apellido'],
				'admitido'=>$datos->fields['admitido'],
				'activo'=>$datos->fields['activo']
			);
			return $cliente;
		}
		return "";
	}
	
	function afterInsert($id){
		$this->mailPorClaveGenerada();
		$this->mailPorAdmision();
	}
	
	function afterEdit($id){
		$this->mailPorClaveGenerada();
		$this->mailPorAdmision();
	}
	
	function mailPorClaveGenerada(){
		if($this->claveGenerada != ""){
			$clave = $this->claveGenerada;
			$mailReg = new PHPMailer();
			$mailReg->IsHTML(true);
			$cont = "Su clave para ingresar al sitio web de Prolesa es: $clave<br>No responda este correo.";
			$cont = $mailReg->WrapText($cont, 72);
			$mailReg->IsSMTP();
			$mailReg->Username = USUARIO_SMTP;
			$mailReg->Password = CLAVE_USUARIO_SMTP;
			$mailReg->Host = MAIL_HOST;
			$mailReg->FromName = "Prolesa";		
			$mailReg->Subject = "Prolesa - Envío de nueva contraseña";
			
			/* Destinatario */
			$mailReg->AddAddress($this->Registro['email']);
			$mailReg->Body = utf8_encode($cont);
			$mailReg->From = CASILLA_NO_REPLY;
			$success = $mailReg->Send();
			if($success)
				LogArchivo("Mando mail por clave generada $clave a cliente ".$this->Registro['id_cliente']." de mail ".$this->Registro['email']);
		}
	}
	
	function mailPorAdmision(){
		if($this->enviarMailPorAdmision){
			$mailReg = new PHPMailer();
			$mailReg->IsHTML(true);
			$cont = "Estimado(a), su cuenta de cliente se encuentra habilitada para ingresar al sitio web de Prolesa.<br>No responda este correo.";
			$cont = $mailReg->WrapText($cont, 72);
			$mailReg->IsSMTP();
			$mailReg->Username = USUARIO_SMTP;
			$mailReg->Password = CLAVE_USUARIO_SMTP;
			$mailReg->Host = MAIL_HOST;
			$mailReg->FromName = "Prolesa";		
			$mailReg->Subject = "Prolesa - Su cuenta de cliente fue habilitada";
			
			/* Destinatario */
			$mailReg->AddAddress($this->Registro['email']);
			$mailReg->Body = utf8_encode($cont);
			$mailReg->From = CASILLA_NO_REPLY;
			$success = $mailReg->Send();
			if($success)
				LogArchivo("Mando mail de admision a cliente ".$this->Registro['id_cliente']." de mail ".$this->Registro['email']);
		}	
	}
	
	function sucursal($idCliente){
		$recordSet = $this->DB->execute("SELECT s.id_sucursal, s.nombre FROM sucursal s INNER JOIN cliente c ON c.id_sucursal = s.id_sucursal AND c.id_cliente = $idCliente");
		return $recordSet->GetRows();
	}
	
	function mailsClientesHabilitados(){
		return $this->DB->getCol("SELECT DISTINCT email FROM cliente WHERE activo = 1 AND admitido = 1");
	}
	
	function mailsClientesHabilitadosDeSucursal($sucursal){
		return $this->DB->getCol("SELECT DISTINCT email FROM cliente WHERE id_sucursal = $sucursal AND activo = 1 AND admitido = 1");	
	}
	
	function esClaveValida($clave, $idCliente){
		$cnt = $this->DB->getOne("SELECT COUNT(*) FROM cliente WHERE id_cliente = $idCliente AND clave = '$clave'");		
		return $cnt > 0;
	}
	
	function cambiarClave($idCliente, $clave){
		$ok = $this->DB->execute("UPDATE cliente SET clave = '$clave' WHERE id_cliente = $idCliente");
		if($ok === FALSE){
			LogError("Error cambiando clave de cliente $idCliente: ".$this->DB->ErrorMsg(), "cliente.class.php", "cambiarClave");
			return "Ha ocurrido un error y no se pudo cambiar la clave.";
		}
		return "";
	}

	function obtenerParaExcel(){
		$sqlQuery = "SELECT c.nombre, c.apellido, c.matricula, s.nombre AS nombre_sucursal, c.email, c.celular, c.telefono, c.grupo_economico, c.tecnico, c.direccion, d.nombre_departamento, c.fecha_registro, c.fecha_admision, IF(c.admitido, 'Si', 'No') AS admitido, IF(c.activo, 'Si', 'No') AS activo ";
		$sqlQuery .="FROM cliente c INNER JOIN sucursal s ON c.id_sucursal = s.id_sucursal INNER JOIN departamento d ON c.id_departamento = d.id_departamento ";
		$sqlQuery .="ORDER BY c.apellido";
		$recordSet = $this->DB->execute($sqlQuery);
		return iterator_to_array($recordSet);
	}
}
?>