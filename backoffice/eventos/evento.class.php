<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'fckeditor/fckeditor.php');
include_once(DIR_BASE.'class/image_handler.class.php');
include_once(DIR_BASE.'sucursales/sucursal.class.php');
include_once(DIR_BASE.'class/class.phpmailer.php');
include_once(DIR_BASE.'clientes/cliente.class.php');

class Evento extends Table {

	var $TamTextoGrilla = 200;
	var $Ajax;
	var $TablaImg;
	var $ValoresImg;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Evento($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'evento');
		$this->TablaImg   = new Table($DB, 'evento_foto');
		$this->AccionesGrid = array(ACC_BAJA,ACC_MODIFICACION,ACC_CONSULTA);
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
		$Grid  = new nyiGridDB('EVENTOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_evento');
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador_fechas.htm', array('e.id_evento'=>'Identificador', 'fecha'=>'Fecha', 'nombre_evento'=>'Nombre', 'lugar'=>'Lugar', 's.nombre'=>"Sucursal"));
	
		if(isset($_GET['PVEZ'])){
			$_SESSION["BUSCADOR_EVENTOS"]["NOMBRE_EVENTO"] = "";
			$_SESSION["BUSCADOR_EVENTOS"]["CONFIG_FECHAS"] = "00";
			unset($_SESSION["BUSCADOR_EVENTOS"]["FECHA_DESDE"]);
			unset($_SESSION["BUSCADOR_EVENTOS"]["FECHA_HASTA"]);
		}
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			// Fechas
			$configFechas = getConfigFechas("BUSCADOR_EVENTOS", $_POST);
			$_SESSION["BUSCADOR_EVENTOS"]["CONFIG_FECHAS"] = $configFechas;			
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
			
		$fi = $_SESSION["BUSCADOR_EVENTOS"]["FECHA_DESDE"];
		$ff = $_SESSION["BUSCADOR_EVENTOS"]["FECHA_HASTA"];
		switch($_SESSION["BUSCADOR_EVENTOS"]["CONFIG_FECHAS"]){
			case '00':
				$Where = "";
				$Grid->assign('CLASS_DIV_FECHA_DESDE', 'ocultar');
				$Grid->assign('CLASS_DIV_FECHA_HASTA', 'ocultar');
				break;
				
			case '01':
				$Where .= "e.fecha <= '".$ff." 23:59:00'";
				$Grid->assign('CLASS_DIV_FECHA_DESDE', 'ocultar');
				$Grid->assign('CLASS_DIV_FECHA_HASTA', 'mostrar');
				$Grid->assign('FECHA_HASTA', $_SESSION["BUSCADOR_EVENTOS"]["FECHA_HASTA"]);
				$Grid->assign('FH_SI_CHECKED', 'checked="checked"');
				break;
				
			case '10':
				$Where .= "e.fecha >= '".$fi."'";
				$Grid->assign('FECHA_DESDE', $_SESSION["BUSCADOR_EVENTOS"]["FECHA_DESDE"]);
				$Grid->assign('CLASS_DIV_FECHA_DESDE', 'mostrar');
				$Grid->assign('CLASS_DIV_FECHA_HASTA', 'ocultar');
				$Grid->assign('FD_SI_CHECKED', 'checked="checked"');
				break;
				
			case '11':
				$Where .= "e.fecha >= '".$fi."' AND e.fecha <= '".$ff." 23:59:00'";
				$Grid->assign('FECHA_DESDE', $_SESSION["BUSCADOR_EVENTOS"]["FECHA_DESDE"]);
				$Grid->assign('FECHA_HASTA', $_SESSION["BUSCADOR_EVENTOS"]["FECHA_HASTA"]);
				$Grid->assign('CLASS_DIV_FECHA_DESDE', 'mostrar');
				$Grid->assign('CLASS_DIV_FECHA_HASTA', 'mostrar');
				$Grid->assign('FD_SI_CHECKED', 'checked="checked"');
				$Grid->assign('FH_SI_CHECKED', 'checked="checked"');
				break;
		}
	
		$Campos = "e.id_evento AS id, e.nombre_evento, e.fecha, DATE_FORMAT( e.fecha, '%e/%c/%Y a las %H:%ihs' ) AS fecha_disp, lugar, s.nombre AS sucursal";
		$From = "evento e LEFT JOIN sucursal s ON s.id_sucursal = e.id_sucursal";
		// Datos
		if ($Where != ''){
			$Grid->getDatos($this->DB, $Campos, $From, $Where);
		}
		else{
			$Grid->getDatos($this->DB, $Campos, $From);
		}		
		
		// Devuelvo
		return($Grid);
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		$id = $this->Registro['id_evento'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('eventos/evento_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);
		
		// Datos
		$Form->assign('id_evento', $id);
		$Form->assign('nombre_evento', $this->Registro['nombre_evento']);
		$Form->assign('alcance', $this->Registro['alcance']);
		$Form->assign('lugar', $this->Registro['lugar']);
		$Form->assign('id_sucursal', $this->Registro['id_sucursal']);
		$fecha = $this->Registro['fecha'];
		$parteFecha = explode(" ", $fecha);
		$Form->assign('fecha', $parteFecha[0]);
		$Form->assign('hora', $parteFecha[1]);
		$parteHora = explode(":", $parteFecha[1]);
		
		$Form->assign('visible', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		
		// Tengo que meterlo como caja de texto enriquecido
		$editor = new FCKeditor('descripcion');
		$editor->BasePath = 'fckeditor/';
		$editor->Height = ALTURA_EDITOR;
		$editor->Config['EnterMode'] = 'br';
		$editor->Value = $this->Registro['descripcion'];
		$contenido = $editor->CreateHtml();
		$Form->assign('descripcion', $contenido);
		$Form->assign('CANTIDAD_IMAGENES', 1);
		$src = $this->GetURLImagenPreview($id).'?time='.time();
		$Form->assign('src_imagen', $src);
				
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		$Form->assign('alcance_ids', array(ALCANCE_CLIENTES, ALCANCE_SUCURSAL, ALCANCE_PUBLICO));
		$Form->assign('alcance_dsc', array(ALCANCE_CLIENTES_DSC, ALCANCE_SUCURSAL_DSC, ALCANCE_PUBLICO_DSC));
		
		$Form->assign('ALCANCE_SUCURSAL', ALCANCE_SUCURSAL);
		$Form->assign('ALCANCE_SUCURSAL_DSC', utf8_encode(html_entity_decode(ALCANCE_SUCURSAL_DSC)));
		$objSuc = new Sucursal($Cnx);
		$Form->assign('sucursales_ids', $objSuc->GetComboIds(true, 0));
		$Form->assign('sucursales_dsc', $objSuc->GetComboNombres(true, ''));
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'EVENTOS');
		$Cab->assign('NOMACCION', getNomAccion($Accion));
		$Cab->assign('ACC', $Accion);
		
		// Script Salir
		$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		
		// Script Listado
		$Parametros = $_GET;
		unset($Parametros['ACC']);
		unset($Parametros['COD']);
		$Cab->assign('SCRIPT_LIS', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		$Form->assign('NAVEGADOR', $Cab->fetchHTML());
	
		// Contenido
		return($Form->fetchHTML());
	}
	
	function GetURLImagenPreview($idN, $num=1){
		return DIR_HTTP_FOTOS_EVENTOS.$idN.'/'.$num.'.prv.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenPreviewLocal($idN, $num=1){
		return DIR_FOTOS_EVENTOS.$idN.'/'.$num.'.prv.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenThumbnail($idN, $num=1){
		return DIR_HTTP_FOTOS_EVENTOS.$idN.'/'.$num.'.thu.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagen($idN, $num=1){
		return DIR_HTTP_FOTOS_EVENTOS.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetURLImagenLocal($idN, $num=1){
		return DIR_FOTOS_EVENTOS.$idN.'/'.$num.'.'.$this->GetExtensionImagen($idN, $num);
	}
	
	function GetExtensionImagen($id, $i){
		return $this->DB->getOne("SELECT extension_imagen FROM evento_foto WHERE id_evento = $id AND numero_imagen = $i");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_evento'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function GetDescripcion($id_evento){
		return $this->DB->getOne("SELECT descripcion FROM evento WHERE id_evento = $id_evento");
	}
	
	function GetNombre($id_evento){
		return $this->DB->getOne("SELECT nombre FROM evento WHERE id_evento = $id_evento");
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_evento'] = $_POST['id_evento'];
		$hora = $_POST["horaHour"].":".$_POST["horaMinute"].":00";
		$fecha = $_POST["fechaYear"]."-".$_POST["fechaMonth"]."-".$_POST["fechaDay"];
		$this->Registro['fecha'] = $fecha." ".$hora;
		$this->Registro['nombre_evento'] = $_POST['nombre_evento'];
		$this->Registro['descripcion'] = stripslashes($_POST['descripcion']);
		$this->Registro['lugar'] = stripslashes($_POST['lugar']);
		$this->Registro['alcance'] = $_POST['alcance'];
		$this->Registro['id_sucursal'] = $_POST['id_sucursal'];
		$this->Registro['visible'] = $_POST['visible'] ? 1 : 0;
		$this->CargarFotos();
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('TAM_TXT', $this->TamTextoGrilla);
		
		// devuelvo
		return ($Grid->fetchGrid('eventos/evento_grid.htm', 'Agenda de eventos',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_evento) FROM evento");
	}

	function controlAntesSalvar(){
		$alcance = $this->Registro['alcance'];
		$sucursal = $this->Registro['id_sucursal'];
		if($alcance == ALCANCE_SUCURSAL && $sucursal == 0){
			$this->Error .= "Se ha seleccionado alcance ".ALCANCE_SUCURSAL_DSC." pero no se ha seleccionado una sucursal de referencia. ";
		}
		elseif($sucursal == 0){
			$this->Registro['id_sucursal'] = 'NULL';
		}
	}

	function beforeInsert(){
		$this->controlAntesSalvar();
	}
	
	function afterInsert($LastID){
		// Aca la parte de las fotos
		$this->TablaImg->Registro['id_evento'] = $LastID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			$extension = GetExtension($this->ValoresImg[$num_imagen]['nombre_imagen']);
			$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
			$this->TablaImg->Registro['extension_imagen'] = $extension;
			$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
			
			if($this->Error == ''){
				$this->SalvarFoto($LastID, $num_imagen, $extension);
			}
		}
		
		// Aca la notificacion a clientes
		$clObj = new Cliente($this->DB);
		$alcance = $this->Registro['alcance'];
		$lstMails = array();
		switch($alcance){
			case ALCANCE_CLIENTES:
				$lstMails = $clObj->mailsClientesHabilitados();
				break;
				
			case ALCANCE_SUCURSAL:
				$sucursal = $this->Registro['id_sucursal'];
				$lstMails = $clObj->mailsClientesHabilitadosDeSucursal($sucursal);
				break;
				
			default:
				break;
		}
		
		if(count($lstMails) > 0){
			$mailEvt = new PHPMailer();
			// Armado del mail
			$mailEvt->FromName = "Prolesa";		
			$mailEvt->Subject = "Prolesa - Notificación de nuevo evento";
			$mailEvt->IsHTML(true);
			$cont = "Estimado cliente, le queremos notificar la realización de un evento llamado ".utf8_decode($this->Registro['nombre_evento'])." el dia ".FormatDateLong($this->Registro['fecha'])." en ".utf8_decode($this->Registro['lugar']).".<br>No responda este correo.";
			$cont = $mailEvt->WrapText($cont, 72);
			$mailEvt->Body = $cont;
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
			}
		}
	}
	
	function beforeEdit(){
		$this->controlAntesSalvar();
	}

	function afterEdit(){
		$ID = $this->Registro['id_evento'];
		// Actualizacion de fotos
		$this->TablaImg->Registro['id_evento'] = $ID;
		while( list($num_imagen, $datos) = each($this->ValoresImg) ){
			if($datos['subir_imagen'] === true){
				$nom = $datos['nombre_imagen'];
				$tipo = $datos['tipo_imagen'];
				$size = $datos['size_imagen'];
				$img = $datos['imagen'];
				
				$extOld = $this->DB->getOne("SELECT extension_imagen FROM evento_foto WHERE id_evento = $ID AND numero_imagen = $num_imagen");
				$extension = GetExtension($datos['nombre_imagen']);
				
				$this->DB->execute("DELETE FROM evento_foto WHERE id_evento = $ID AND numero_imagen = $num_imagen");
				
				$this->TablaImg->Registro['numero_imagen'] = $num_imagen;
				$this->TablaImg->Registro['extension_imagen'] = $extension;
				$this->Error .= $this->TablaImg->TablaDB->addRegistro($this->TablaImg->Registro);
				
				if($this->Error == ''){
										
					$imgOld = DIR_FOTOS_EVENTOS.$ID.'/'.$num_imagen.'.'.$extOld;
					$imgOldThu = DIR_FOTOS_EVENTOS.$ID.'/'.$num_imagen.'.thu.'.$extOld;
					$imgOldPrv = DIR_FOTOS_EVENTOS.$ID.'/'.$num_imagen.'.prv.'.$extOld;
					if($extension != $extOld){
						if(file_exists($imgOld)){
							@unlink($imgOld);	
						}
						if(file_exists($imgOldThu)){
							@unlink($imgOldThu);	
						}
						if(file_exists($imgOldPrv)){
							@unlink($imgOldPrv);	
						}
					}
										
					$this->SalvarFoto($ID, $num_imagen, $extension);
				}
			}
		}
	}
	
	// Salva la foto de la noticia al disco
	function SalvarFoto($id, $numero, $extension='jpg'){
		// Directorio
		$nuevoDir = DIR_FOTOS_EVENTOS.$id;
		if(!file_exists($nuevoDir)){
			mkdir($nuevoDir);
			chmod($nuevoDir, 0755);
		}
		
		$nuevoDir = $nuevoDir.'/';
		
		// Extraigo los datos de la foto :contenido, y tamano
		$size = $this->ValoresImg[$numero]['size_imagen'];
		$content = $this->ValoresImg[$numero]['imagen'];
		
		$rutaImg = $nuevoDir.$numero.'.'.$extension;
		$rutaImgThu = $nuevoDir.$numero.'.thu.'.$extension;
		$rutaImgPrv = $nuevoDir.$numero.'.prv.'.$extension;
		$fp = fopen($rutaImg, 'w');
		fwrite($fp, $content, $size);
		fclose($fp);
		chmod($rutaImg, 0755);
		
		$ImgHandler = new ImageHandler();
		// Imagen Thumbnail
		if ($ImgHandler->open_image($rutaImg) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_THUMBNAIL_EVENTO, ANCHO_THUMBNAIL_EVENTO);
			// La guarda
			$ImgHandler->image_to_file($rutaImgThu);
			chmod($rutaImgThu, 0755);
		}
		
		// Imagen Preview
		if ($ImgHandler->open_image($rutaImg) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_PREVIEW_EVENTO, ANCHO_PREVIEW_EVENTO);
			// La guarda
			$ImgHandler->image_to_file($rutaImgPrv);
			chmod($rutaImgPrv, 0755);
		}
	}
	
	function afterDelete($id){
		$dir = DIR_FOTOS_EVENTOS.$id;
		BorrarDirectorio($dir);
	}
	
	function CargarFotos(){
		$cant = $_POST['CANTIDAD_IMAGENES'];
		if(!is_array($this->ValoresImg))
			$this->ValoresImg = array();
		
		for($i=1; $i <= $cant; $i++){
			$label = "IMAGEN_$i";
			if(is_uploaded_file($_FILES[$label]['tmp_name']) && $_FILES[$label]['size'] > 0){
				// Archivo imagen
				$fileName = $_FILES[$label]['name'];
				$tmpName  = $_FILES[$label]['tmp_name'];
				$fileSize = $_FILES[$label]['size'];
				$fileType = $_FILES[$label]['type'];
				
				$fp      = fopen($tmpName, 'r');
				$content = fread($fp, filesize($tmpName));
				fclose($fp);
				
				if(!get_magic_quotes_gpc()){
						$fileName = addslashes($fileName);
				}
				
				$this->ValoresImg[$i] = array();
				$this->ValoresImg[$i]['subir_imagen'] = true;
				$this->ValoresImg[$i]['id_evento'] = $this->Registro['id_evento'];
				$this->ValoresImg[$i]['numero_imagen'] = $i;
				$this->ValoresImg[$i]['nombre_imagen'] = $fileName;
				$this->ValoresImg[$i]['tipo_imagen'] = $fileType;
				$this->ValoresImg[$i]['size_imagen'] = $fileSize;
				$this->ValoresImg[$i]['imagen'] = $content;
			}
		}
	}
	
	function SetOrdinal($id, $valOrdinal){
		$sql = "UPDATE evento SET ordinal = $valOrdinal WHERE id_evento = $id";
		$OK = $this->DB->execute($sql);
		if($OK === false){
			$this->Error = $this->DB->ErrorMsg();
		}
	}
	
	function DatosEvento($id){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT e.*, DATE_FORMAT(e.fecha, '%m') AS mes, DATE_FORMAT(e.fecha, '%d') AS dia, DATE_FORMAT(e.fecha, '%H:%i hs') AS hora, s.nombre AS sucursal FROM evento e LEFT JOIN sucursal s ON s.id_sucursal = e.id_sucursal WHERE e.id_evento = $id";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$Datos['id_evento'] = $qr->fields['id_evento'];
			$Datos['dia'] = $qr->fields['dia'];
			$Datos['mes'] = $qr->fields['mes'];
			$Datos['hora'] = $qr->fields['hora'];
			$Datos['nombre_evento'] = $qr->fields['nombre_evento'];
			$Datos['lugar'] = $qr->fields['lugar'];
			$Datos['sucursal'] = $qr->fields['sucursal'];
			$Datos['descripcion'] = $qr->fields['descripcion'];
			$Datos['img_src'] = $this->GetURLImagen($id);
			$Datos['img_src_local'] = $this->GetURLImagenLocal($id);
			return $Datos;
		}
	}
	
	function diasDeEventosPorMes($m, $a){
		$q = "SELECT DISTINCT DATE_FORMAT(e.fecha, '%e') FROM evento e WHERE e.fecha BETWEEN '$a-$m-01 00:00:00' AND '$a-$m-31 23:59:59' AND e.visible = 1 ORDER BY e.fecha";
		return $this->DB->getCol($q);
	}
	
	function diasDeEventosPublicosPorMes($m, $a){
		$q = "SELECT DISTINCT DATE_FORMAT(e.fecha, '%e') FROM evento e WHERE e.fecha BETWEEN '$a-$m-01 00:00:00' AND '$a-$m-31 23:59:59' AND e.alcance = ".ALCANCE_PUBLICO." AND e.visible = 1 ORDER BY e.fecha";
		return $this->DB->getCol($q);
	}
	
	// Dia $dia en dd, Mes $mes en mm, Anio $a en aaaa
	function eventosPorFecha($dia, $mes, $a, $visible=1, $alcance=''){
		$d = $dia;
		if(intval($dia) < 10 && strlen($dia) == 1){
			$d = "0$dia";
		}
		$m = $mes;
		if(intval($mes) < 10 && strlen($mes) == 1){
			$m = "0$mes";
		}
		$eventos = array();
		if($alcance != ''){
			$qAlcance = "AND e.alcance = $alcance";	
		}
		$q = "SELECT e.id_evento, DATE_FORMAT(e.fecha, '%H:%i hs') AS hora, e.nombre_evento, e.descripcion, e.lugar, s.nombre AS sucursal
		      FROM evento e LEFT JOIN sucursal s ON s.id_sucursal = e.id_sucursal 
			  WHERE e.visible = $visible AND DATE_FORMAT(e.fecha, '%Y-%m-%d') = '$a-$m-$d' $qAlcance ORDER BY e.fecha ASC";
		$qr = $this->DB->execute($q);
		while(!$qr->EOF){
			array_push($eventos, array(
				'id_evento'=>$qr->fields['id_evento'],
				'hora'=>$qr->fields['hora'],
				'nombre_evento'=>$qr->fields['nombre_evento'],
				'sucursal'=>$qr->fields['sucursal'],
				'descripcion'=>$qr->fields['descripcion'],
				'lugar'=>$qr->fields['lugar'],
				'img_src'=>$this->GetURLImagenPreview($qr->fields['id_evento']),
				'img_src_local'=>$this->GetURLImagenPreviewLocal($qr->fields['id_evento'])
			));
			$qr->MoveNext();	
		}
		return $eventos;
	}
	
	function eventosParaTodosLosClientes($visible=1){
		$eventos = array();
		$q = "SELECT e.id_evento, e.nombre_evento, DATE_FORMAT( e.fecha, '%e/%c/%Y a las %H:%ihs' ) AS fecha_disp
		      FROM evento e 
			  WHERE e.visible = $visible AND e.fecha >= NOW() AND e.alcance = ".ALCANCE_CLIENTES." ORDER BY e.fecha ASC";
		$qr = $this->DB->execute($q);
		while(!$qr->EOF){
			array_push($eventos, array(
				'id_evento'=>$qr->fields['id_evento'],
				'fecha_disp'=>$qr->fields['fecha_disp'],
				'nombre_evento'=>$qr->fields['nombre_evento']
			));
			$qr->MoveNext();	
		}
		return $eventos;	
	}
	
	function eventosPorSucursal($idSucursal, $alcance='', $visible=1){
		$eventos = array();
		if($alcance != ''){
			$qAlcance = "AND e.alcance = $alcance";	
		}
		$q = "SELECT e.id_evento, e.nombre_evento, DATE_FORMAT( e.fecha, '%e/%c/%Y a las %H:%ihs' ) AS fecha_disp
		      FROM evento e 
			  WHERE e.visible = $visible AND e.fecha >= NOW() AND e.id_sucursal = $idSucursal $qAlcance ORDER BY e.fecha ASC";

		$qr = $this->DB->execute($q);
		while(!$qr->EOF){
			array_push($eventos, array(
				'id_evento'=>$qr->fields['id_evento'],
				'fecha_disp'=>$qr->fields['fecha_disp'],
				'nombre_evento'=>$qr->fields['nombre_evento']
			));
			$qr->MoveNext();	
		}
		return $eventos;	
	}
	
	function eventosParaNotificar($xDias=3){
		// Eventos a realizarse en xDias dias
		$q = "SELECT e.id_evento, e.nombre_evento, DATE_FORMAT( e.fecha, '%e/%c/%Y a las %H:%ihs' ) AS fecha_disp, e.lugar, e.alcance, e.id_sucursal
		      FROM evento e 
			  WHERE DATE_FORMAT(DATE_ADD(NOW(), INTERVAL $xDias DAY), '%Y/%m/%d') = DATE_FORMAT(e.fecha, '%Y/%m/%d') ORDER BY e.fecha ASC";
		$qr = $this->DB->execute($q);
		if(!$qr->EOF){
			return $qr->GetRows();
		}
		return array();
	}
}
?>