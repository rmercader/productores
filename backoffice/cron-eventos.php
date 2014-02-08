<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');
include_once(DIR_BASE.'eventos/evento.class.php');

LogArchivo("Comenzando ejecucion del proceso de notificacion de eventos...");

$evt = new Evento($Cnx);
$clObj = new Cliente($Cnx);
$evtsParaNotificar = $evt->eventosParaNotificar(DIAS_ANTES_NOTIFICAR_EVENTOS);
foreach($evtsParaNotificar as $evento){
	$lstMails = array();
	switch($evento["alcance"]){
		case ALCANCE_CLIENTES:
			$lstMails = $clObj->mailsClientesHabilitados();
			break;
			
		case ALCANCE_SUCURSAL:
			$sucursal = $evento['id_sucursal'];
			$lstMails = $clObj->mailsClientesHabilitadosDeSucursal($sucursal);
			break;					
	}
	
	if(count($lstMails) > 0){
		$mailEvt = new PHPMailer();
		// Armado del mail
		$mailEvt->FromName = "Prolesa";		
		$mailEvt->Subject = "Prolesa - Recordatorio de evento";
		$mailEvt->IsHTML(true);
		$cont = "Estimado cliente, le recordamos la realización del evento ".$evento["nombre_evento"]." el dia ".$evento["fecha_disp"].", en ".$evento['lugar'].".<br>No responda este correo.";
		$cont = $mailEvt->WrapText($cont, 72);
		$mailEvt->Body = utf8_decode($cont);
		// Configuracion
		$mailEvt->IsSMTP();
		$mailEvt->Username = USUARIO_SMTP;
		$mailEvt->Password = CLAVE_USUARIO_SMTP;
		$mailEvt->Host = MAIL_HOST;
		
		foreach($lstMails as $mailCliente){	
			/* El mismo mail para cada destinatario */
			$mailEvt->ClearAllRecipients();
			$mailEvt->AddAddress($mailCliente);
			$mailEvt->From = CASILLA_NO_REPLY;
			$success = $mailEvt->Send();
			LogArchivo("Notificacion del evento ".$evento["id_evento"]." enviada a $mailCliente.");
		}
		
		$mailEvt->ClearAllRecipients();
		$mailEvt->AddAddress("rmercader@fira.com.uy");
		$mailEvt->Subject = utf8_decode("Prolesa - Notificación de evento enviada");
		$msg = "Se envió la notificación del evento ".$evento["id_evento"]." a ".count($lstMails)." clientes.";
		$mailEvt->Body = utf8_decode($msg);
		$mailEvt->From = CASILLA_NO_REPLY;
		$success = $mailEvt->Send();
		LogArchivo(utf8_decode($msg));
	}
}
LogArchivo("Finalizando ejecucion del proceso de notificacion de eventos...");

?>