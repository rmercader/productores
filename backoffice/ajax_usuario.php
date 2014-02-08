<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include_once('./seguridad/seguridad.class.php');
include_once('./clientes/cliente.class.php');

function ResetClave($id_usuario, $mandar_mail='0'){
	$objResponse = new xajaxResponse();
	
	$Cnx = nyiCNX();
	$Sec = new Seguridad($Cnx);
	
	$newPass = $Sec->GenerarPassword();
	LogArchivo($newPass);
	$password = $Sec->Encriptar($newPass);
	$Cnx->execute("UPDATE usuario SET password = '$password' WHERE id_usuario = $id_usuario");
	if($mandar_mail == 1 && $Sec->GetIdPerfilUsuario($id_usuario) == PERFIL_CLIENTE){
		// Mandar mail al cliente
		LogArchivo("[Reset-Password] Enviando mail de cambio de clave a usuario $id_usuario...");
		EnviarMailNotificacionCambioClave($id_usuario, $newPass, $Sec, $Cnx);
	}
	
	$Cnx->Close();
	
	$objResponse->assign("SPINNER", "innerHTML", "");
	return $objResponse;
}

function CambiarClaveManual($id_usuario, $newPass, $mandar_mail='0'){
	$objResponse = new xajaxResponse();
	
	$Cnx = nyiCNX();
	$Sec = new Seguridad($Cnx);
	
	$password = $Sec->Encriptar($newPass);
	$Cnx->execute("UPDATE usuario SET password = '$password' WHERE id_usuario = $id_usuario");
	if($mandar_mail == 1 && $Sec->GetIdPerfilUsuario($id_usuario) == PERFIL_CLIENTE){
		// Mandar mail al cliente
		LogArchivo("[Reset-Password] Enviando mail de cambio de clave a usuario $id_usuario...");
		EnviarMailNotificacionCambioClave($id_usuario, $newPass, $Sec, $Cnx);
	}
	
	$Cnx->Close();
	
	$objResponse->assign("SPINNER", "innerHTML", "");
	$objResponse->assign("CLAVE_MANUAL", "style.class", "ocultar");
	$objResponse->assign("NUEVA_CLAVE", "value", "");
	$objResponse->assign("NUEVA_CLAVE_CONFIRMACION", "value", "");
	$objResponse->Alert("La clave ha sido cambiada correctamente.");
	return $objResponse;
}

function EnviarMailNotificacionCambioClave($id_usuario, $newPass, $Sec, $Cnx){
	$smtp = new smtp();
	$smtp->addSecurityData(true);
	/* Seteamos el subject... */
	$smtp->setSubject(SUBJECT_RESET_PASSWORD_CLIENTE);

	if(EMAIL_ADMINISTRADOR != ""){
		/* Agregamos un Blind Carbon Copy... (opcional) */
		$smtp->addBCC(EMAIL_ADMINISTRADOR);
	}
	
	$id_cliente = $Sec->GetIdClienteUsuario($id_usuario);
	$DatosCliente = $Cnx->execute("SELECT nombre_cliente, email FROM cliente WHERE id_cliente = $id_cliente");
	$login = $Cnx->getOne("SELECT login FROM usuario WHERE id_usuario = $id_usuario");
	
	/* Ponemos el destinatario (primero la descripcion y despues el mail) */
	$smtp->setRcptTo($DatosCliente->fields['nombre_cliente'], $DatosCliente->fields['email']);

	/* Ponemos el remitente (idem destinatario) */
	$smtp->setFrom(FROM_TITULO_RESET_PASSWORD_CLIENTE, FROM_RESET_PASSWORD_CLIENTE);

	/* Ponemos el cuerpo (no se hacen chequeos sobre el contenido... no hace falta) */
	$body = "Estimado: ".$DatosCliente->fields['nombre_cliente']."\n\n";
	$body .= "Sus credenciales han cambiado en el sistema Transportes Acosta, a continuacion le enviamos el usuario y contraseña para poder acceder al mismo:\n";
	$body .= "Usuario: $login\n";
	$body .= "Contraseña: $newPass\n";
	$body .= "Para acceder: ".DIR_HTTP;
	$smtp->setBody($body);

	/* Tratamos de enviar el mensaje */
	if ($smtp->send() === false) {
		LogError($smtp->error, 'ajax_usuario.php', 'Envio de e-mail por cambio de password de  usuario cliente');
	}
}

function BusquedaClientes($nombre){
	$Cnx = nyiCNX(); // Creo la conexion
	$Cliente = new Cliente($Cnx, $xajax);	// Hago uso del objeto global $xajax
	return $Cliente->AjaxBusquedaCliente($nombre); // Invoco la funcionalidad
}

function ChequearLoginDisponibilidad($login){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$User = new Usuario($Cnx, $xajax);
	$txt_disp = $User->EstaLoginDisponible($login) ? '<font class="LetraExito">Disponible</font>' : '<font class="LetraError">No disponible, elegir otro</font>';
	$objResponse->assign('SPINNER', 'innerHTML', $txt_disp);
	return $objResponse;
}

// Ajax
$xajax->registerFunction("BusquedaClientes");
$xajax->registerFunction("ChequearLoginDisponibilidad");
$xajax->registerFunction("ResetClave");
$xajax->registerFunction("CambiarClaveManual");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX);

?>