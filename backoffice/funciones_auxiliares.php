<?PHP

//include_once(DIR_BASE.'seguridad/seguridad.class.php');
require_once(DIR_BASE.'xajax/xajax_core/xajax.inc.php');

// Sirve para obtener la query SQL para controlar que no exista ya una tupla
// en $tabla con el valor $valor en el campo clave $campoUnico, antes de hacer INSERT
function sqlChequeoCampoUnicoEnInsert($tabla, $campoUnico, $valor){
	$sql = "
		SELECT COUNT(*) FROM $tabla WHERE $campoUnico = $valor
	";
}

// Sirve para obtener la query SQL para controlar que no exista ya una tupla
// en $tabla con el valor $valor en el campo clave $campoUnico, y con un id
// distinto de $valorId, para antes de hacer UPDATE
function sqlChequeoCampoUnicoEnUpdate($tabla, $campoUnico, $valor, $campoId, $valorId){
	$sql = "
		SELECT COUNT(*) FROM $tabla WHERE $campoUnico = $valor AND $campoId <> $valorId
	";
}

// ---------------------------------------------------------------------------
//   Bitacora del sistema
// ---------------------------------------------------------------------------
function addBitacora($Con,$Texto,$Script){
	$Con->execute("select add_bitacora(".$Con->qstr($_SESSION["cfgusu"]["ip"]).",".
		  		  $Con->qstr($Texto).",".$Con->qstr($_SESSION['cfgusu']['nombre']).",".	
            	  $Con->qstr($Script).",".$Con->qstr($_SESSION['cfgusu']['login']).")");
}

// ---------------------------------------------------------------------------
//   Devuelvo nombre de la accion
// ---------------------------------------------------------------------------
function getNomAccion($Accion){
	$Nom = array(ACC_CONSULTA=>'Consulta',ACC_MODIFICACION=>'Modificacion',
				 ACC_BAJA=>'Baja',ACC_ALTA=>'Alta',ACC_POST=>'',ACC_ANULACION=>'Anulacion');
	return($Nom[$Accion]);
}

function LogArchivo($Texto){
	$FilePtr = fopen(LOG_SISTEMA, 'a');
	fwrite($FilePtr, "[".date("j")."-".date("n")."-".date("Y")." ".date("H").":".date("i")."] $Texto\n");
	fflush($FilePtr);
	fclose($FilePtr);
}

function LogError($Error, $Archivo, $Operacion){
	$FilePtr = fopen(LOG_ERRORES, 'a');
	fwrite($FilePtr, "[".date("j")."-".date("n")."-".date("Y")." ".date("H").":".date("i")."] ".$Error."\n");
	fwrite($FilePtr, "Archivo: $Archivo\n");
	fwrite($FilePtr, "Operacion: $Operacion\n");
	fflush($FilePtr);
	fclose($FilePtr);
}

function LogExcepcion($excepcion){
	$FilePtr = fopen(LOG_ERRORES, 'a');
	fwrite($FilePtr, "[".date("j")."-".date("n")."-".date("Y")." ".date("H").":".date("i")."] ".$excepcion->getMessage()."\n");
	fwrite($FilePtr, "Archivo: ".$excepcion->getFile()."\n");
	fwrite($FilePtr, "Linea: ".$excepcion->getLine()."\n");
	fflush($FilePtr);
	fclose($FilePtr);
}

// ---------------------------------------------------------------------------
//   Devuelvo nombre SiNo
// ---------------------------------------------------------------------------
function SiNo($T){
	if ($T == _SI) return(_SIN);
	if ($T == _NO) return(_NON);
}

// -------------------------------------------------------------------------------
//	Devuelvo configuracion de fechas en formulario, y guarda las fechas en sesion:
//	'00' significa no hay seleccionadas fechas
//	'01' significa todo hasta FECHA_HASTA
//	'10' significa de todo desde FECHA_DESDE en adelante
//	'11' significa todo entre FECHA_DESDE y FECHA_HASTA
//	Se debe llamar a esta funcion si en el formulario estan los campos 
//	FECHA_DESDE y FECHA_HASTA, y los checkbox FD_SI y FH_SI.
// -------------------------------------------------------------------------------
function getConfigFechas($keyContexto, $FORM){
	$retVal = '';
	if (!isset($FORM['FD_SI']) && !isset($FORM['FH_SI'])){ // Caso '00'
		$retVal = '00';
		$_SESSION[$keyContexto]["FECHA_DESDE"] = '';
	}
	elseif (!isset($FORM['FD_SI']) && isset($FORM['FH_SI'])){
		$retVal = '01';
		$_SESSION[$keyContexto]["FECHA_DESDE"] = '';
		$_SESSION[$keyContexto]["FECHA_HASTA"] = $FORM['FECHA_HASTAYear'].'-'.$FORM['FECHA_HASTAMonth'].'-'.$FORM['FECHA_HASTADay'];
	}
	elseif (isset($FORM['FD_SI']) && !isset($FORM['FH_SI'])){
		$retVal = '10';
		$_SESSION[$keyContexto]["FECHA_DESDE"] = $FORM['FECHA_DESDEYear'].'-'.$FORM['FECHA_DESDEMonth'].'-'.$FORM['FECHA_DESDEDay'];
		$_SESSION[$keyContexto]["FECHA_HASTA"] = '';
	}
	else{
		$retVal = '11';
		$_SESSION[$keyContexto]["FECHA_DESDE"] = $FORM['FECHA_DESDEYear'].'-'.$FORM['FECHA_DESDEMonth'].'-'.$FORM['FECHA_DESDEDay'];
		$_SESSION[$keyContexto]["FECHA_HASTA"] = $FORM['FECHA_HASTAYear'].'-'.$FORM['FECHA_HASTAMonth'].'-'.$FORM['FECHA_HASTADay'];
	}
	return $retVal;
}

// ---------------------------------------------------------------------------
//   Prerparo importe para sql
// ---------------------------------------------------------------------------
function sqlImporte($Monto){
	return(str_replace(',','',"'$Monto'"));
}

// ---------------------------------------------------------------------------
//   Chequeo de direcciones de e-mail
// ---------------------------------------------------------------------------
function esEmailValido($email){
	if(eregi('^[_\x20-\x2D\x2F-\x7E-]+(\.[_\x20-\x2D\x2F-\x7E-]+)*@(([_a-z0-9-]([_a-z0-9-]*[_a-z0-9-]+)?){1,63}\.)+[a-z0-9]{2,6}$', $email)){
		return TRUE;
	}
	return FALSE;
}

function ValidateModuleParameters($var, $search_dir)
{
	return (isset($var)) && (preg_match("/[a-zA-Z]*/", $var)) && (file_exists($search_dir.'/'.$var.'.php'));
}

// Se asume que la fecha viene en formato aaaa-mm-dd
function FormatDate($fecha, $separator='/'){
	$arr = split('-',$fecha);
	
	if($arr[2] < 10){
		$arr[2] = str_replace('0','',$arr[2]);
	}
	
	if($arr[1] < 10){
		$arr[1] = str_replace('0','',$arr[1]);
	}
	return ($arr[2]).$separator.($arr[1]).$separator.($arr[0]);
}

// Se asume que la fecha viene en formato aaaa-mm-dd hh:mm:ss
function FormatDateLong($fecha, $seconds=false, $separator='/'){
	// Primero split para quedarnos con las dos componentes
	$comps = split(' ', $fecha);
	$arr = split('-', $comps[0]);
	
	if($arr[2] < 10){
		$arr[2] = str_replace('0','',$arr[2]);
	}
	
	if($arr[1] < 10){
		$arr[1] = str_replace('0','',$arr[1]);
	}
	
	$arrhs = split(':', $comps[1]);
	$hs = $arrhs[0].":".$arrhs[1];
	if($seconds){
		$hs .= ":".$arrhs[2];
	}
	
	return ($arr[2]).$separator.($arr[1]).$separator.($arr[0])." ".$hs;
}

// Se asume un nombre de archivo: nombre.ext
function GetExtension($nombre){
	$extension = strrchr($nombre, '.');
	$extension = strtolower($extension);
	$extension = str_replace('.', '', $extension);
	
	return $extension;
}

function GenerarOpcionesCombo($arrDatos){
	if(!is_array($arrDatos)){
		return "";
	}
	
	$Opciones = new nyiHTML('base_opcion_select.htm');
	while( list($valor, $texto) = each($arrDatos) ){
		$Opciones->append('REG', array('valor'=>$valor, 'texto'=>$texto));
	}
	
	return $Opciones->FetchHtml();
}

function BorrarDirectorio($path_to_dir){
	// Primero hay que chequear la existencia del directorio
	if(file_exists($path_to_dir)){
		$dh = opendir($path_to_dir);
		while (($obj = readdir($dh))){
			if($obj != '.' && $obj != '..')
				unlink($path_to_dir.'/'.$obj);
		}
	
		closedir($dh);
		rmdir($path_to_dir);
	}
}

// Es solo de prueba
function Clientes_LlenarCombo($texto){
	if($texto == "si"){
		$objResponse = new xajaxResponse();
		/*
		$arr = array();
		$arr[0] = "Valor 0";
		$arr[1] = "Valor 1";
		$arr[2] = "Valor 2";
		
		$html = GenerarOpcionesCombo($arr);
		
		$objResponse->assign("AJAX_COMBO", "innerHTML", $html);
		*/
		LogArchivo("Clientes anda");
		return $objResponse;
	}
}

/**
 * A small function to remove an element from a list(numerical array)
 * Arguments:    $arr - The array that should be edited
 *                $value - The value that should be deleted.
 * Returns    : The edited array
 */
function array_remove($arr,$value) {
	return array_values(array_diff($arr,array($value)));
}

function SanitizarValor($value) {
	if( get_magic_quotes_gpc() )
	{
		$value = stripslashes( $value );
	}
	//check if this function exists
	if( function_exists( "mysql_real_escape_string" ) )
	{
		$value = mysql_real_escape_string( $value );
	}
	//for PHP version < 4.3.0 use addslashes
	else
	{
		$value = addslashes( $value );
	}
	return $value;
}

function generarCodigoParaAjax($funciones, $requestUri, $return=true){
	$xajax = new xajax();
	if(is_array($funciones)){
		foreach($funciones as $funcion){
			$xajax->registerFunction($funcion);
		}
	}
	$xajax->processRequest();
	if($requestUri != ""){
		$xajax->setRequestURI($requestUri);
	}
	if($return){
		return $xajax->getJavascript(DIR_XAJAX);
	}
	$xajax->printJavascript(DIR_XAJAX);
}

function traducirMes($mes){
	switch(strtolower($mes)){
		case "january":
			$trad = "Enero";
			break;
		
		case "february":
			$trad = "Febrero";
			break;
		
		case "march":
			$trad = "Marzo";
			break;
		
		case "april":
			$trad = "Abril";
			break;
		
		case "may":
			$trad = "Mayo";
			break;
		
		case "june":
			$trad = "Junio";
			break;
		
		case "july":
			$trad = "Julio";
			break;
		
		case "august":
			$trad = "Agosto";
			break;
		
		case "september":
			$trad = "Setiembre";
			break;
		
		case "october":
			$trad = "Octubre";
			break;
		
		case "november":
			$trad = "Noviembre";
			break;
		
		case "december":
			$trad = "Diciembre";
			break;
		
		default:
			$trad = "";
			break;			
	}
	return $trad;
}

function traducirFechaFormateada($fechaFormateada){
	// Dias
	$fechaEsp = preg_replace("/\bMon\b/i", "Lunes", $fechaFormateada);
	$fechaEsp = preg_replace("/\bTue\b/i", "Martes", $fechaEsp);
	$fechaEsp = preg_replace("/\bWed\b/i", "Miércoles", $fechaEsp);
	$fechaEsp = preg_replace("/\bThu\b/i", "Jueves", $fechaEsp);
	$fechaEsp = preg_replace("/\bFri\b/i", "Viernes", $fechaEsp);
	$fechaEsp = preg_replace("/\bSat\b/i", "Sábado", $fechaEsp);
	$fechaEsp = preg_replace("/\bSun\b/i", "Domingo", $fechaEsp);

	// Meses
	$fechaEsp = preg_replace("/\bJanuary\b/i", "Enero", $fechaEsp);
	$fechaEsp = preg_replace("/\bFebruary\b/i", "Febrero", $fechaEsp);
	$fechaEsp = preg_replace("/\bMarch\b/i", "Marzo", $fechaEsp);
	$fechaEsp = preg_replace("/\bApril\b/i", "Abril", $fechaEsp);
	$fechaEsp = preg_replace("/\bMay\b/i", "Mayo", $fechaEsp);
	$fechaEsp = preg_replace("/\bJune\b/i", "Junio", $fechaEsp);
	$fechaEsp = preg_replace("/\bJuly\b/i", "Julio", $fechaEsp);
	$fechaEsp = preg_replace("/\bAugust\b/i", "Agosto", $fechaEsp);
	$fechaEsp = preg_replace("/\bSeptember\b/i", "Setiembre", $fechaEsp);
	$fechaEsp = preg_replace("/\bOctober\b/i", "Octubre", $fechaEsp);
	$fechaEsp = preg_replace("/\bNovember\b/i", "Noviembre", $fechaEsp);
	$fechaEsp = preg_replace("/\bDecember\b/i", "Diciembre", $fechaEsp);
	
	return $fechaEsp;
}

function HTMLize($string) {
	return utf8_encode(strtolower(preg_replace(array(
		"/[\s]+/",
		"/['`´]+/",
		"/[¨\^]+/",
		utf8_decode("/[^a-zA-Z0-9_\-ÁáÉéÍíÓóÚúÑñÀàÈèÌìÒòÙùÂâÊêÎîÔôÛûÄäËëÏïÖöÜü]+/"),
		utf8_decode("/[áÁàÀäÄâÂ]/"),
		utf8_decode("/[éÉèÈëËêÊ]/"),
		utf8_decode("/[íÍìÌïÏîÎ]/"),
		utf8_decode("/[óÓòÒöÖôÔ]/"),
		utf8_decode("/[úÚùÙüÜûÛ]/"),
		utf8_decode("/[ñÑ]/"),
		"/[\-]+/",
		"/[_]+/"
	), array(
		"-",
		"_",
		"",
		"",
		"a",
		"e",
		"i",
		"o",
		"u",
		"n",
		"-",
		"_"
	),utf8_decode(trim($string)))));
}

?>