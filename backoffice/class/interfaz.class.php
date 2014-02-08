<?PHP 

include_once(DIR_BASE.'novedades/novedad.class.php');
include_once(DIR_BASE.'sucursales/sucursal.class.php');
include_once(DIR_BASE.'productos/producto.class.php');
include_once(DIR_BASE.'clientes/cliente.class.php');
include_once(DIR_BASE.'class/class.phpmailer.php');
include_once(DIR_BASE.'eventos/evento.class.php');
include_once(DIR_BASE.'feria/evento_feria.class.php');
include_once(DIR_BASE.'feria/faq.class.php');
include_once(DIR_BASE.'documentos/documento.class.php');

/* Clase de interfaz de la logica de negocio */
class Interfaz {
	
	private $Cnx; // AdoDBConnection
	
	// Constructor
	function Interfaz(){
		$this->Cnx = nyiCNX();
		$this->Cnx->debug = false;
	}
	
	function obtenerVideosEventoFeria($idEventoFeria){
		$obj = new EventoFeria($this->Cnx);
		$agenda = $obj->obtenerVideos($idEventoFeria);
		return iterator_to_array($agenda);
	}
	
	function obtenerFaqs(){
		$obj = new FAQ($this->Cnx);
		$faqs = $obj->obtenerTodasVisibles();
		return iterator_to_array($faqs);
	}
	
	function obtenerAgendaActividadesEventoFeria($idEventoFeria){
		$obj = new EventoFeria($this->Cnx);
		$agenda = $obj->obtenerActividades($idEventoFeria);
		return iterator_to_array($agenda);
	}
	
	function obtenerListaFotosEventoFeria($idEventoFeria){
		$obj = new EventoFeria($this->Cnx);
		$galeria = $obj->obtenerGaleriaFotos($idEventoFeria);
		$fotos = array();
		
		while(!$galeria->EOF){
			$extension = GetExtension($galeria->fields['archivo']);
			$nomSinExt = str_replace(".{$extension}", "", $galeria->fields['archivo']); 
			$arrFoto = array('archivo'=>$galeria->fields['archivo'], 'url'=>DIR_HTTP_FOTOS_FERIA."{$idEventoFeria}/{$galeria->fields['archivo']}");
			array_push($fotos, $arrFoto);
			$galeria->MoveNext();
		}
		
		return $fotos;
	}
	
	function obtenerDetallesEventoFeriaActual(){
		$obj = new EventoFeria($this->Cnx);
		return $obj->obtenerDetallesEventoActual();		
	}

	function obtenerTodosLosProductos($criterioOrden){
		$obj = new Producto($this->Cnx);
		return $obj->obtenerTodos($criterioOrden);
	}
	
	function obtenerDatosProducto($idProducto){
		$obj = new Producto($this->Cnx);
		return $obj->DatosProducto($idProducto);	
	}
	
	function obtenerDatosCategoriaProducto($idCategoria){
		$obj = new CategoriaProducto($this->Cnx);
		return $obj->obtenerDatosCategoria($idCategoria);
	}
	
	function obtenerCatalogosCategoria($idCategoria){
		$obj = new CategoriaProducto($this->Cnx);
		return $obj->obtenerDatosCatalogos($idCategoria);
	}
	
	function obtenerProductosCategoria($idCategoria){
		$obj = new Producto($this->Cnx);
		return $obj->ObtenerProductosCategoria($idCategoria, true);
	}
	
	function obtenerCategoriasHijas($idCategoria){
		$obj = new CategoriaProducto($this->Cnx);
		return $obj->obtenerDatosCategoriasHijas($idCategoria);
	}
	
	function obtenerDatosUnidadNegocio($idUnidad){
		$obj = new CategoriaProducto($this->Cnx);
		return $obj->obtenerDatosUnidadNegocio($idUnidad);
	}

	function busquedaProductos($txtBusqueda, $pagina, $cantidad){
		$obj = new Producto($this->Cnx);
		return $obj->busquedaProductos($txtBusqueda, $pagina, $cantidad);
	}

	function busquedaProductosCantidadResultados($txtBusqueda){
		$obj = new Producto($this->Cnx);
		return $obj->busquedaProductosCantidadResultados($txtBusqueda);
	}
	
	function obtenerNovedadesParaMostrar(){
		$obj = new Novedad($this->Cnx);
		return $obj->ObtenerNovedadesParaMostrar();
	}
	
	function obtenerNovedadesPortada(){
		$obj = new Novedad($this->Cnx);
		return $obj->novedadesPortada();
	}
	
	function obtenerDetallesNovedad($id){
		$obj = new Novedad($this->Cnx);
		return $obj->DatosNovedad($id);
	}
	
	function obtenerDetallesSucursal($id){
		$obj = new Sucursal($this->Cnx);
		return $obj->DatosSucursal($id);
	}
	
	function obtenerIdsSucursales(){
		$obj = new Sucursal($this->Cnx);
		return $obj->GetComboIds();
	}
	
	function obtenerNombresSucursales(){
		$obj = new Sucursal($this->Cnx);
		return $obj->GetComboNombres();
	}
	
	function esEmailValido($email){
		if(eregi('^[_\x20-\x2D\x2F-\x7E-]+(\.[_\x20-\x2D\x2F-\x7E-]+)*@(([_a-z0-9-]([_a-z0-9-]*[_a-z0-9-]+)?){1,63}\.)+[a-z0-9]{2,6}$', $email)){
			return TRUE;
		}
		return FALSE;
	}
	
	function obtenerIdsDepartamentos($indiferente=false, $id_indiferente=0){
		$sqlSel = "SELECT id_departamento FROM departamento ORDER BY id_departamento";
		$col = $this->Cnx->getCol($sqlSel);
		
		// Si hay que agregar el valor Indiferente
		if ($indiferente){
			if (is_array($col))
				$col = array_merge(array($id_indiferente),$col);
		}
		return($col);
	}
	
	function obtenerNombresDepartamentos($indiferente=false,$nombre_indiferente='Indiferente'){
		$sqlSel = "SELECT nombre_departamento FROM departamento ORDER BY id_departamento";
		$col = $this->Cnx->getCol($sqlSel);
		
		// Si hay que agregar el valor Indiferente
		if ($indiferente){
			if (is_array($col))
				$col = array_merge(array($nombre_indiferente),$col);
		}
		return(array_map('utf8_encode', $col));
	}			
	
	function existeDepartamento($id_departamento){
		$sqlSel = "SELECT COUNT(*) FROM departamento WHERE id_departamento = $id_departamento";
		return (intval($this->Cnx->getOne($sqlSel)) > 0);
	}
	
	function nombreDepartamento($id_departamento){
		$sqlSel = "SELECT nombre_departamento FROM departamento WHERE id_departamento = $id_departamento";
		return $this->Cnx->getOne($sqlSel);
	}
	
	function registrarContacto($destino, $matricula, $nombre, $apellido, $celular, $email, $consulta, $idDepartamento){
		$sqlIns = "INSERT INTO registro_contacto(fecha, matricula, nombre, apellido, celular, email, destino, id_departamento, consulta) ";
		$sqlIns .= "VALUES (NOW(), '$matricula', '$nombre', '$apellido', '$celular', '$email', '$destino', $idDepartamento, '$consulta')";
		$ok = $this->Cnx->execute($sqlIns);
		if($ok === FALSE){
			$mailError = new PHPMailer();
			$mailError->IsHTML(true);
			$cont = "Ha ocurrido un error inesperado en el formulario de contacto <br> con datos matricula $matricula nombre $nombre apellido $apellido.";
			$cont = $mailError->WrapText($cont, 72);
			$mailError->IsSMTP();
			$mailError->Username = USUARIO_SMTP;
			$mailError->Password = CLAVE_USUARIO_SMTP;
			$mailError->Host = MAIL_HOST;
			$mailError->FromName = "Prolesa";		
			$mailError->Subject = "Prolesa - Error en formulario de contacto";
			
			/* Destinatario */
			$mailError->AddAddress("rmercader@fira.com.uy");
			$mailError->Body = utf8_encode($cont);
			$mailError->From = CASILLA_NO_REPLY;
			$success = $mailError->Send();
			return "Ha ocurrido un error inesperado al intentar enviar su consulta. Se ha generado una notificaci&oacute;n al equipo t&eacute;cnico con el detalle del problema. Disculpe las molestias.";
		}
		else {
			switch($destino){
				case "finanzas":
					$destinoDsc = "Finanzas";
					break;
				case "logistica":
					$destinoDsc = "Logística";
					break;
				case "insumos":
					$destinoDsc = "Insumos";
					break;
				case "veterinaria":
					$destinoDsc = "Veterinaria";
					break;
				case "semillas":
					$destinoDsc = "Semillas";
					break;
				case "fertilizantes":
					$destinoDsc = "Fertilizantes";
					break;
				case "agroquimicos":
					$destinoDsc = "Agroquímicos";
					break;
				case "granos":
					$destinoDsc = "Granos y Concentrados";
					break;
				case "ventas":
					$destinoDsc = "Ventas especiales";
					break;
				case "rrhh":
					$destinoDsc = "RRHH";
					break;
				case "atencion":
					$destinoDsc = "Atención al Cliente";
					break;
				case "general":
					$destinoDsc = "General";
					break;
			}
			
			$mailReg = new PHPMailer();
			$mailReg->IsHTML(true);
			$cont = "Se ha recibido una consulta desde el sitio con los siguientes datos: <br>";
			$cont .= "<b>Fecha y hora</b>: ".date('d')."-".date('m')."-".date('Y')." ".date('H').":".date('i')."<br>";
			$cont .= "<b>Matr&iacute;cula</b>: $matricula<br>";
			$cont .= "<b>Departamento</b>: ".htmlentities($this->Cnx->getOne("SELECT nombre_departamento FROM departamento WHERE id_departamento = $idDepartamento"))."<br>";
			$cont .= "<b>Nombre</b>: $nombre $apellido<br>";
			$cont .= "<b>Tel&eacute;fono</b>: $celular<br>";
			$cont .= "<b>Email</b>: $email<br>";
			$cont .= "<b>Destino</b>: $destino<br>";
			$cont .= "<b>Consulta</b>:<br>$consulta";
			$cont = $mailReg->WrapText($cont, 72);
			$mailReg->IsSMTP();
			$mailReg->SMTPAuth = true;
			$mailReg->SMTPDebug = 1;
			$mailReg->Username = USUARIO_SMTP;
			$mailReg->Password = CLAVE_USUARIO_SMTP;
			$mailReg->Host = MAIL_HOST;
			$mailReg->From = CASILLA_NO_REPLY;
			$mailReg->FromName = "Prolesa";	
			$mailReg->Subject = "$destinoDsc - Consulta desde el sitio web";
			
			// Destinatario 
			$mailReg->AddAddress(CASILLA_CONTACTO);
			$mailReg->Body = utf8_decode($cont);
			$success = $mailReg->Send();
			if($success === FALSE){
				LogArchivo("No se pudo enviar mail por contacto de $nombre $apellido.");	
			}
		}
		
		return "";
	}
	
	function registrarNewsletter($arrDatosContacto){
		if(is_array($arrDatosContacto)){
			$nombre   = htmlentities($arrDatosContacto["nombre"]);
			$id_departamento = $arrDatosContacto["id_departamento"];
			$email  = $arrDatosContacto["email"];
			$fecha = $arrDatosContacto["fecha"];
			
			$sqlIns = "INSERT INTO registro_newsletter(fecha_registro, fecha_nacimiento, nombre, id_departamento, email) ";
			$sqlIns .= "VALUES (NOW(), '$fecha', '$nombre', $id_departamento, '$email')";
			
			$resExec = $this->Cnx->execute($sqlIns);
			if($resExec === FALSE){
				$msg = $this->Cnx->ErrorMsg();
				$this->loguearError($msg, "registrarNewsletter()", $sqlIns);
				return FALSE;
			}
			else{
				return TRUE;	
			}
		}
	}
	
	function loguearError($mensaje, $operacion='', $adicional=''){
		$logFilePtr = fopen(LOG_ERRORES, "a+");
		$log = sprintf("ERROR: %s\nFECHA: %s\nOPERACIÓN: %s\nINF. ADICIONAL: %s\n\n", $mensaje, date("d-m-Y H:i"), $operacion, $adicional);
		fwrite($logFilePtr, $log);
		fflush($logFilePtr);
		fclose($logFilePtr);
	}
	
	function armarMensajeError($mensaje){
		$contenido = new nyiHTML('base-error.htm');	
		$contenido->assign('error', $mensaje);
		return $contenido->fetchHTML();
	}
	
	function armarMensajeExito($mensaje){
		$contenido = new nyiHTML('base-exito.htm');	
		$contenido->assign('mensaje', $mensaje);
		return $contenido->fetchHTML();
	}
	
	function registrarCliente($matricula, $nombre, $apellido, $grupo, $idSucursal, $tecnico, $celular, $telefono, $email, $idDpto, $direccion, $clave)
	{
		$obj = new Cliente($this->Cnx);
		if($obj->existeMatricula($matricula)){
			return "Ya existe un cliente registrado con la matr&iacute;cula $matricula.";
		}
		
		$ok = $obj->registro($matricula, $nombre, $apellido, $grupo, $idSucursal, $tecnico, $celular, $telefono, $email, $idDpto, $direccion, $clave);
		if($ok === FALSE){
			$mailError = new PHPMailer();
			$mailError->IsHTML(true);
			$cont = "Ha ocurrido un error inesperado al intentar realizar el registro <br>de los datos de un cliente con matricula $matricula.";
			$cont = $mailError->WrapText($cont, 72);
			$mailError->IsSMTP();
			$mailError->Username = USUARIO_SMTP;
			$mailError->Password = CLAVE_USUARIO_SMTP;
			$mailError->Host = MAIL_HOST;
			$mailError->FromName = "Prolesa";		
			$mailError->Subject = "Prolesa - Error al intentar registrarse un cliente";
			
			/* Destinatario */
			$mailError->AddAddress("rmercader@fira.com.uy");
			$mailError->Body = utf8_encode($cont);
			$mailError->From = CASILLA_NO_REPLY;
			$success = $mailError->Send();
			return "Ha ocurrido un error inesperado al intentar realizar el registro de sus datos. Se ha generado una notificaci&oacute;n al equipo t&eacute;cnico con el detalle del problema. Disculpe las molestias.";
		}
		
		$mailReg = new PHPMailer();
		$mailReg->IsHTML(true);
		$cont = "Se ha registrado un nuevo cliente con matricula: $matricula.<br>No responda este correo.";
		$cont = $mailReg->WrapText($cont, 72);
		$mailReg->IsSMTP();
		$mailReg->Username = USUARIO_SMTP;
		$mailReg->Password = CLAVE_USUARIO_SMTP;
		$mailReg->Host = MAIL_HOST;
		$mailReg->From = CASILLA_NO_REPLY;
		$mailReg->FromName = "Prolesa";		
		$mailReg->Subject = "Prolesa - Nuevo registro de cliente";
		
		/* Destinatario */
		$mailReg->AddAddress(CASILLA_NOTIFICACION_CLIENTES);
		$mailReg->Body = utf8_encode($cont);
		$mailReg->From = CASILLA_NO_REPLY;
		$success = $mailReg->Send();
		return "";
	}
	
	function enviarClaveCliente($matricula){
		$obj = new Cliente($this->Cnx);
		$security = new Seguridad($this->Cnx);
		$clave = $security->GenerarPassword();
		$claveEnc = $security->Encriptar($clave);
		$mail = $obj->obtenerMailPorMatricula($matricula);
		if($mail != ""){
			$mailReg = new PHPMailer();
			$mailReg->IsHTML(true);
			$cont = "Su nueva clave para ingresar al sitio web de Prolesa es: $clave.<br>No responda este correo.";
			$cont = $mailReg->WrapText($cont, 72);
			$mailReg->IsSMTP();
			$mailReg->Username = USUARIO_SMTP;
			$mailReg->Password = CLAVE_USUARIO_SMTP;
			$mailReg->Host = MAIL_HOST;
			$mailReg->FromName = "Prolesa";		
			$mailReg->Subject = utf8_decode("Prolesa - Envío de nueva contraseña");
			
			/* Destinatario */
			$mailReg->AddAddress($mail);
			$mailReg->Body = utf8_encode($cont);
			$mailReg->From = CASILLA_NO_REPLY;
			$success = $mailReg->Send();
			$obj->cambiarClavePorMatricula($matricula, $claveEnc);
			return true;
		}
		else{
			return false;	
		}
	}
	
	function loginCliente($matricula, $clave){
		$obj = new Cliente($this->Cnx);
		$security = new Seguridad($this->Cnx);
		$claveEnc = $security->Encriptar($clave);
		return $obj->autenticar($matricula, $claveEnc);
	}
	
	function datosClienteParaSesionPorId($idCliente){
		$obj = new Cliente($this->Cnx);
		return $obj->datosParaSesionPorId($idCliente);
	}
	
	function obtenerDiasDeEventosPorMes($m, $a){
		$obj = new Evento($this->Cnx);
		return $obj->diasDeEventosPorMes($m, $a);
	}
	
	function obtenerEventosPorFecha($d, $m, $a){
		$obj = new Evento($this->Cnx);	
		return $obj->eventosPorFecha($d, $m, $a);
	}
	
	function obtenerEventosParaTodosLosClientes(){
		$obj = new Evento($this->Cnx);
		return $obj->eventosParaTodosLosClientes();
	}
	
	function obtenerEventosSucursalCliente($idCliente){
		$obj = new Evento($this->Cnx);
		$cliente = new Cliente($this->Cnx);
		$suc = $cliente->sucursal($idCliente);
		return $obj->eventosPorSucursal($suc[0]["id_sucursal"], ALCANCE_SUCURSAL);
	}
	
	function obtenerEventoPorId($idEvento){
		$obj = new Evento($this->Cnx);	
		return $obj->DatosEvento($idEvento);
	}
	
	function obtenerSucursalCliente($idCliente){
		$obj = new Cliente($this->Cnx);	
		$suc = $obj->sucursal($idCliente);
		return $suc[0]["nombre"];
	}
	
	function obtenerCategoriasConDocumentosComunes(){
		$obj = new CategoriaDocumento($this->Cnx);
		return $obj->categoriasConDocumentosComunes();
	}
	
	function obtenerDatosCategoriaDocumento($idCategoria){
		$obj = new CategoriaDocumento($this->Cnx);
		return $obj->datosPorId($idCategoria);
	}
	
	function obtenerDocumentosComunes(){
		$obj = new Documento($this->Cnx);
		return $obj->documentosComunes();
	}
	
	function obtenerDocumentosComunesPorCategoria($idCat){
		$obj = new Documento($this->Cnx);
		return $obj->documentosComunesPorCategoria($idCat);
	}
	
	function obtenerUrlIconoCategoriaProducto($idCat){
		$obj = new CategoriaProducto($this->Cnx);
		return $obj->getUrlIcono($idCat);
	}
	
	function esClaveDeCliente($clave, $idCliente){
		$obj = new Cliente($this->Cnx);	
		$security = new Seguridad($this->Cnx);
		$claveEnc = $security->Encriptar($clave);
		return $obj->esClaveValida($claveEnc, $idCliente);
	}
	
	function cambiarClaveCliente($idCliente, $clave){
		$obj = new Cliente($this->Cnx);	
		$security = new Seguridad($this->Cnx);
		$claveEnc = $security->Encriptar($clave);
		return $obj->cambiarClave($idCliente, $claveEnc);
	}
}
?>