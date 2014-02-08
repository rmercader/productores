<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'fckeditor/fckeditor.php');
include_once(DIR_BASE.'class/image_handler.class.php');
include_once(DIR_BASE.'sucursales/sucursal.class.php');
include_once(DIR_BASE.'class/class.phpmailer.php');
include_once(DIR_BASE.'clientes/cliente.class.php');

class EventoFeria extends Table {

	var $TamTextoGrilla = 200;
	var $Ajax;
	var $TablaImg;
	var $ValoresImg;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function EventoFeria($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'feria_evento');
		$this->TablaActividades = new Table($DB, 'feria_actividad');
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
		$Grid->setParametros(isset($_GET['PVEZ']), 'e.id_feria_evento');
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('e.id_feria_evento'=>'Identificador', 'fecha_inicio'=>'Fecha de inicio', 'fecha_fin'=>'Fecha de fin', 'lugar'=>'Lugar'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		// Numero de Pagina
		if (isset($_GET['NROPAG']))
			$Grid->setPaginaAct($_GET['NROPAG']);
			
		$Campos = "e.id_feria_evento AS id, DATE_FORMAT( e.fecha_inicio, '%e/%c/%Y' ) AS fecha_inicio_dsc, DATE_FORMAT( e.fecha_fin, '%e/%c/%Y' ) AS fecha_fin_dsc, lugar";
		$From = "feria_evento e";
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
		$id = $this->Registro['id_feria_evento'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('feria/evento_feria_frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);		
		
		// Datos
		$Form->assign('id_feria_evento', $id);
		$Form->assign('lugar', $this->Registro['lugar']);
		$Form->assign('como_llegar', $this->Registro['como_llegar']);
		$Form->assign('fecha_inicio', $this->Registro['fecha_inicio']);
		$Form->assign('fecha_fin', $this->Registro['fecha_fin']);
		$Form->assign('visible', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		
		$editor = new FCKeditor('DETALLES') ;
		$editor->BasePath = 'fckeditor/' ;
		$editor->Height = ALTURA_EDITOR;
		$editor->Config['EnterMode'] = 'br';
		$editor->Value = $this->Registro['detalles'];
		$contenido = $editor->CreateHtml();
		$Form->assign('DETALLES', $contenido);
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'EVENTOS');
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
	function _GetDB($Cod=-1,$Campo='id_feria_evento'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function validarFormulario(){
		$fechaIniTime = strtotime($this->Registro['fecha_inicio']);
		$fechaFinTime = strtotime($this->Registro['fecha_fin']);
		if($fechaFinTime < $fechaIniTime){
			$this->Error .= "La fecha de fin debe ser mayor o igual a la fecha de inicio.\n";	
		}
		
		if($this->Registro['id_feria_evento'] == ""){
			$anoIni = $_POST["fecha_inicioYear"];
			$cntEvtsAno = $this->DB->getOne("SELECT COUNT(*) FROM feria_evento WHERE DATE_FORMAT(fecha_inicio, '%Y') = '{$anoIni}'");
			if($cntEvtsAno > 0){
				$this->Error .= "Ya existe un evento que inicia en $anoIni.\n";
			}
		}
		
		if(trim($this->Registro['lugar']) == ""){
			$this->Error .= "Debe ingresar el lugar del evento.\n";	
		}

		if(trim($this->Registro['detalles']) == "<br />"){
			$this->Error .= "Debe ingresar los detalles del evento.\n";	
		}
		
		if(trim($this->Registro['como_llegar']) == ""){
			$this->Error .= "Debe ingresar la referencia a googlemaps de como llegar al lugar del evento.\n";	
		}
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_feria_evento'] = $_POST['id_feria_evento'];
		$this->Registro['detalles'] = $_POST['DETALLES'];
		$this->Registro['fecha_inicio'] = $_POST["fecha_inicioYear"]."-".$_POST["fecha_inicioMonth"]."-".$_POST["fecha_inicioDay"];
		$this->Registro['fecha_fin'] = $_POST["fecha_finYear"]."-".$_POST["fecha_finMonth"]."-".$_POST["fecha_finDay"];
		$this->Registro['lugar'] = stripslashes($_POST['lugar']);
		$this->Registro['como_llegar'] = $_POST['como_llegar'];
		$this->Registro['visible'] = $_POST['visible'] ? 1 : 0;
		$this->validarFormulario();
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('TAM_TXT', $this->TamTextoGrilla);
		
		// devuelvo
		return ($Grid->fetchGrid('feria/evento_feria_grid.htm', 'Eventos de La Feria de Prolesa',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_feria_evento) FROM feria_evento");
	}

	function afterDelete($id){
		/*$dir = DIR_FOTOS_EVENTOS.$id;
		BorrarDirectorio($dir);*/
	}
	
	function getDetallesEvento($id){
		$Cnx = $this->DB;
		$datos = array();
		$q = "SELECT e.*, DATE_FORMAT( e.fecha_inicio, '%e/%c/%Y' ) AS fecha_inicio_dsc, DATE_FORMAT( e.fecha_fin, '%e/%c/%Y' ) AS fecha_fin_dsc FROM feria_evento e WHERE e.id_feria_evento = $id";
		$qr = $Cnx->execute($q);
		if(!$qr->EOF){
			$datos['id_feria_evento'] = $qr->fields['id_feria_evento'];
			$datos['fecha_inicio'] = $qr->fields['fecha_inicio'];
			$datos['fecha_fin'] = $qr->fields['fecha_fin'];
			$datos['fecha_inicio_dsc'] = $qr->fields['fecha_inicio_dsc'];
			$datos['fecha_fin_dsc'] = $qr->fields['fecha_fin_dsc'];
			$datos['lugar'] = $qr->fields['lugar'];
			$datos['como_llegar'] = $qr->fields['como_llegar'];
			$datos['detalles'] = $qr->fields['detalles'];
			return $datos;
		}
	}
	
	function asociarNuevaFoto($idEventoFeria, $fileTmpName, $fileName){
		if(!file_exists(DIR_FOTOS_FERIA."{$idEventoFeria}")){
			mkdir(DIR_FOTOS_FERIA."{$idEventoFeria}");
			chmod(DIR_FOTOS_FERIA."{$idEventoFeria}", 0755);
		}
		
		$extension = GetExtension($fileName);
		$this->StartTransaction();
		$index = $this->DB->getOne("SELECT MAX(orden) FROM feria_evento_foto WHERE id_feria_evento = $idEventoFeria");
		$index++;
		$nuevoNombre = HTMLize(str_ireplace(".$extension", "", $fileName));
		$nuevoArchivo = "{$nuevoNombre}.{$extension}";
		
		// Validacion nombre
		$cnt = $this->DB->getOne("SELECT COUNT(*) FROM feria_evento_foto WHERE id_feria_evento = $idEventoFeria AND archivo = '$nuevoArchivo'");
		if($cnt > 0){
			return "Ya existe una foto asociada al evento con el nombre $fileName.";	
		}
		
		$errorRedim = false;
		$ImgHandler = new ImageHandler();
		
		// Imagen Thumbnail
		if ($ImgHandler->open_image($fileTmpName) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(75, 75);
			// La guarda
			$rutaImgThu = DIR_FOTOS_FERIA."{$idEventoFeria}/{$nuevoNombre}-thu.{$extension}";
			$ImgHandler->image_to_file($rutaImgThu);
			chmod($rutaImgThu, 0755);
		}
		else{
			$errorRedim = true;	
		}
		
		// Imagen Original
		if ($ImgHandler->open_image($fileTmpName) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_FOTO_FERIA, ANCHO_FOTO_FERIA);
			// La guarda
			$rutaImg = DIR_FOTOS_FERIA."{$idEventoFeria}/{$nuevoArchivo}";
			$ImgHandler->image_to_file($rutaImg);
			chmod($rutaImg, 0755);
		}
		else{
			$errorRedim = true;	
		}
		
		if(!$errorRedim){
			$this->DB->execute("INSERT INTO feria_evento_foto(id_feria_evento, archivo, orden) VALUES ($idEventoFeria, '{$nuevoArchivo}', $index)");
			$this->CompleteTransaction();
			if($this->DB->ErrorMsg() != ""){
				LogError("Error al guardar foto de evento de la feria en la base de datos:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "asociarNuevaFoto");
				return "Se ha producido un error al intentar almacenar la foto (1).";
			}
		}
		else{
			$this->DB->execute("ROLLBACK");
			LogError("Error al redimensionar foto de evento de la feria:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "asociarNuevaFoto");
			return "Se ha producido un error al intentar redimensionar la foto.";
		}
		
		return "";
	}
	
	function obtenerGaleriaFotos($idEventoFeria){
		return $this->DB->execute("SELECT * FROM feria_evento_foto WHERE id_feria_evento = {$idEventoFeria} ORDER BY orden");
	}
	
	function eliminarFoto($idEventoFeria, $nombre){
		$ext = GetExtension($nombre);
		$soloNombre = str_replace(".{$ext}", "", $nombre);
		
		$rutaImgThu = DIR_FOTOS_FERIA."{$idEventoFeria}/{$soloNombre}-thu.{$ext}";
		if(file_exists($rutaImgThu)){
			@unlink($rutaImgThu);
		}
		
		$rutaImg = DIR_FOTOS_FERIA."{$idEventoFeria}/{$nombre}";
		if(file_exists($rutaImg)){
			@unlink($rutaImg);
		}
		
		$this->StartTransaction();
		$orden = $this->DB->getOne("SELECT orden FROM feria_evento_foto WHERE id_feria_evento = $idEventoFeria AND archivo = '{$nombre}'");
		$this->DB->execute("DELETE FROM feria_evento_foto WHERE id_feria_evento = {$idEventoFeria} AND archivo = '{$nombre}'");
		$this->DB->execute("UPDATE feria_evento_foto SET orden = (orden-1) WHERE orden > {$orden} AND id_feria_evento = $idEventoFeria");
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			LogError("Error al eliminar foto $nombre de evento $idEventoFeria de la feria:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "eliminarFoto");
			return "Se ha producido un error al intentar eliminar la foto.";
		}
		return "";
	}
	
	function ordenarFotos($idEventoFeria, $nuevoOrden){
		$i = 1;
		$this->StartTransaction();
		foreach($nuevoOrden as $archivo){
			$this->DB->execute("UPDATE feria_evento_foto SET orden = {$i} WHERE archivo = '{$archivo}' AND id_feria_evento = $idEventoFeria");
			$i++;
		}
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			LogError("Error al ordenar fotos de evento $idEventoFeria de la feria:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "ordenarFotos");
			return "Se ha producido un error al intentar ordenar las fotos.";
		}
		return "";
	}
	
	function asociarNuevoVideo($idEventoFeria, $nombre, $codigo){
		$this->StartTransaction();
		$index = $this->DB->getOne("SELECT MAX(orden) FROM feria_video WHERE id_feria_evento = $idEventoFeria");
		$index++;
		
		// Validacion nombre
		$cnt = $this->DB->getOne("SELECT COUNT(*) FROM feria_video WHERE id_feria_evento = $idEventoFeria AND nombre = '$nombre'");
		if($cnt > 0){
			return "Ya existe un video asociado al evento con el nombre $nombre.";	
		}
		
		$this->DB->execute("INSERT INTO feria_video(id_feria_evento, nombre, codigo, orden) VALUES ($idEventoFeria, '{$nombre}', '{$codigo}', $index)");
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			LogError("Error al guardar video de evento de la feria en la base de datos:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "asociarNuevoVideo");
			return "Se ha producido un error al intentar almacenar el video.";
		}
		
		return "";
	}
	
	function obtenerVideos($idEventoFeria){
		return $this->DB->execute("SELECT * FROM feria_video WHERE id_feria_evento = {$idEventoFeria} ORDER BY orden");
	}
	
	function eliminarVideo($idEventoFeria, $codigo){
		$this->StartTransaction();
		$orden = $this->DB->getOne("SELECT orden FROM feria_video WHERE id_feria_evento = $idEventoFeria AND codigo = '{$codigo}'");
		$this->DB->execute("DELETE FROM feria_video WHERE id_feria_evento = {$idEventoFeria} AND codigo = '{$codigo}'");
		$this->DB->execute("UPDATE feria_video SET orden = (orden-1) WHERE orden > {$orden} AND id_feria_evento = $idEventoFeria");
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			LogError("Error al eliminar video $codigo de evento $idEventoFeria de la feria:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "eliminarVideo");
			return "Se ha producido un error al intentar eliminar el video.";
		}
		return "";
	}
	
	function ordenarVideos($idEventoFeria, $nuevoOrden){
		$i = 1;
		$this->StartTransaction();
		foreach($nuevoOrden as $codigo){
			$this->DB->execute("UPDATE feria_video SET orden = {$i} WHERE codigo = '{$codigo}' AND id_feria_evento = $idEventoFeria");
			$i++;
		}
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			LogError("Error al ordenar videos de evento $idEventoFeria de la feria:\n".$this->DB->ErrorMsg(), "evento_feria.class.php", "ordenarVideos");
			return "Se ha producido un error al intentar ordenar los videos.";
		}
		return "";
	}
	
	function obtenerHtmlListaVideosEditar($idEventoFeria){
		$listaVideos = $this->obtenerVideos($idEventoFeria);
		$html = "";
		while(!$listaVideos->EOF){
			$nombre = $listaVideos->fields['nombre'];
			$codigo = $listaVideos->fields['codigo'];
			$html .= "<div class=\"image\" id=\"{$codigo}\">\r\n";
			$html .= "<div class=\"tit-campo\">{$nombre}</div>\r\n";
			$html .= "<img src=\"http://img.youtube.com/vi/{$codigo}/1.jpg\" border=\"0\" height=\"100\" width=\"130\" />\r\n";
			$html .= "<a href=\"#\" class=\"delete\"><img src=\"templates/img/ico-lst-eliminar.gif\" /></a>\r\n";
			$html .= "<div class=\"tit-campo\" style=\"padding-bottom: 20px;\"><a target=\"_blank\" href=\"http://www.youtube.com/watch?v={$codigo}\">Ver video</a></div>\r\n";
			$html .= "</div>\r\n";
			$listaVideos->MoveNext();
		}
		return $html;
	}
	
	function getDatosFechaInicio($idEventoFeria){
		$query = "SELECT DATE_FORMAT(fecha_inicio, '%Y') AS anio, DATE_FORMAT(fecha_inicio, '%m') AS mes, DATE_FORMAT(fecha_inicio, '%d') AS dia ";								
		$query .="FROM feria_evento WHERE id_feria_evento = $idEventoFeria";
		return $this->DB->execute($query);	
	}
	
	function getAnioFin($idEventoFeria){
		return $this->DB->getOne("SELECT DATE_FORMAT( fecha_fin, '%Y' ) FROM feria_evento WHERE id_feria_evento = $idEventoFeria");		
	}
	
	function obtenerHtmlListaActividades($idEventoFeria){
		$listaActividades = $this->obtenerActividades($idEventoFeria);
		$html = new nyiHTML('feria/lista-actividades.htm');
		while(!$listaActividades->EOF){
			$idActividad = $listaActividades->fields['id_feria_actividad'];
			//$idEvento = $listaActividades->fields['id_feria_evento'];
			$nombre = $listaActividades->fields['nombre_actividad'];
			$fecha = $listaActividades->fields['fecha'];
			$fechaDsc = $listaActividades->fields['fecha_dsc'];
			$hora = $listaActividades->fields['hora'];
			$horaDsc = $listaActividades->fields['hora_dsc'];
			$descripcion = $listaActividades->fields['descripcion'];
			$arrDatos = array(
				'id'=>$idActividad, 
				'nombre'=>$nombre, 
				'fecha'=>$fecha, 
				'fecha_dsc'=>$fechaDsc, 
				'hora'=>$hora, 
				'hora_dsc'=>$horaDsc, 
				'descripcion'=>$descripcion
			);
			$html->append('REG', $arrDatos);
			$listaActividades->MoveNext();
		}
		return $html->fetchHTML();	
	}
	
	function obtenerActividades($idEventoFeria){
		return $this->DB->execute("SELECT *, DATE_FORMAT(fecha, '%e/%c/%Y') AS fecha_dsc, DATE_FORMAT(hora, '%H:%i') AS hora_dsc FROM feria_actividad WHERE id_feria_evento = {$idEventoFeria} ORDER BY fecha, hora");
	}
	
	/**
	* Valida que la fecha de la actividad sea valida para el evento
	* @param $fecha en formato yyyy-mm-aa
	*/
	function validarFechaActividad($idEventoFeria, $fecha){
		$query = "SELECT COUNT(*) FROM feria_evento WHERE '$fecha' BETWEEN fecha_inicio AND fecha_fin AND id_feria_evento = $idEventoFeria";
		$cnt = $this->DB->getOne($query);
		return $cnt > 0;
	}
	
	function asociarNuevaActividad($idEventoFeria, $nombre, $fecha, $hora, $descripcion){
		$this->TablaActividades->Registro['id_feria_evento'] = $idEventoFeria;
		$this->TablaActividades->Registro['nombre_actividad'] = $nombre;
		$this->TablaActividades->Registro['fecha'] = $fecha;
		$this->TablaActividades->Registro['hora'] = $hora;
		$this->TablaActividades->Registro['descripcion'] = $descripcion;
		$res = $this->TablaActividades->TablaDB->addRegistro($this->TablaActividades->Registro);
		if($res != ""){
			LogError("Error salvando actividad de evento de la feria $idEventoFeria:\n$res", "evento_feria.class.php", "asociarNuevaActividad");
			LogArchivo("Error salvando actividad de evento de la feria $idEventoFeria:\n$res");
			return "Ocurrió un error al salvar la actividad.";
		}
		return "";
	}
	
	function modificarActividad($idActividad, $idEventoFeria, $nombre, $fecha, $hora, $descripcion){
		$this->TablaActividades->Registro['id_feria_actividad'] = $idActividad;
		$this->TablaActividades->Registro['id_feria_evento'] = $idEventoFeria;
		$this->TablaActividades->Registro['nombre_actividad'] = $nombre;
		$this->TablaActividades->Registro['fecha'] = $fecha;
		$this->TablaActividades->Registro['hora'] = $hora;
		$this->TablaActividades->Registro['descripcion'] = $descripcion;
		$res = $this->TablaActividades->TablaDB->editRegistro($this->TablaActividades->Registro, 'id_feria_actividad');
		if($res != ""){
			LogError("Error salvando actividad $idActividad de evento de la feria:\n$res", "evento_feria.class.php", "modificarActividad");
			LogArchivo("Error salvando actividad $idActividad de evento de la feria:\n$res");
			return "Ocurrió un error al salvar la actividad.";
		}
		return "";
	}
	
	function eliminarActividad($idActividad){
		$this->TablaActividades->Registro['id_feria_actividad'] = $idActividad;
		$res = $this->TablaActividades->TablaDB->deleteRegistro($this->TablaActividades->Registro, 'id_feria_actividad');
		if($res != ""){
			LogError("Error eliminando actividad $idActividad de evento de la feria:\n$res");
			LogArchivo("Error eliminando actividad $idActividad de evento de la feria:\n$res");
			return "Ocurrió un error al eliminando la actividad.";
		}
		return "";	
	}
	
	function obtenerDetallesEventoActual(){
		$anoIni = date("Y");
		$id = $this->DB->getOne("SELECT id_feria_evento FROM feria_evento WHERE visible = 1 AND DATE_FORMAT(fecha_inicio, '%Y') = '{$anoIni}'");
		if(intval($id) > 0){
			return $this->getDetallesEvento($id);
		}
	}
}
?>