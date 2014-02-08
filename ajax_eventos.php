<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();

include_once('./app.config.php');
include_once('./sitio.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'class/interfaz.class.php');

function armarCalendarioEventos($month, $year, $idDivCalendario){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	$output = '';
	
	if($month == '' && $year == '') { 
		$time = time();
		$month = date('n', $time);
		$year = date('Y', $time);
	}
	
	$date = getdate(mktime(0, 0, 0, $month, 1, $year));
	$today = getdate();
	$hours = $today['hours'];
	$mins = $today['minutes'];
	$secs = $today['seconds'];
	
	if(strlen($hours)<2) $hours="0".$hours;
	if(strlen($mins)<2) $mins="0".$mins;
	if(strlen($secs)<2) $secs="0".$secs;
	
	$days=date("t",mktime(0,0,0,$month,1,$year));
	$start = $date['wday']+1;
	$name = traducirMes($date['month']);
	$year2 = $date['year'];
	$offset = $days + $start - 1;
	 
	if($month==12) { 
		$next=1; 
		$nexty=$year + 1; 
	} else { 
		$next=$month + 1; 
		$nexty=$year; 
	}
	
	if($month==1) { 
		$prev=12; 
		$prevy=$year - 1; 
	} else { 
		$prev=$month - 1; 
		$prevy=$year; 
	}
	/*
	if($offset <= 28) $weeks=28; 
	elseif($offset > 35) $weeks = 42; 
	else $weeks = 35; */
	$weeks = 42;
	
	$diasEventos = $interfaz->obtenerDiasDeEventosPorMes($month, $year);
	
	$output .= "
	<table class='cal' cellspacing='1'>
	<tr>
		<td colspan='7'>
			<table class='calhead'>
			<tr>
				<td width='20%' align='right'>
					<a href='javascript:navigate($prev,$prevy)' class='lnk-eventos'><img src='pics/calLeft.gif' border='0' /></a>
				</td>
				<td align='center'>
					<div>$name $year2</div>
				</td>
				<td width='20%' align='left'>
					<a href='javascript:navigate($next,$nexty)' class='lnk-eventos'><img src='pics/calRight.gif' border='0' /></a>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class='dayhead'>
		<td>Dom</td>
		<td>Lun</td>
		<td>Mar</td>
		<td>Mi&eacute;</td>
		<td>Jue</td>
		<td>Vie</td>
		<td>S&aacute;b</td>
	</tr>";
	
	$col=1;
	$cur=1;
	$next=0;
	
	for($i=1; $i <= $weeks; $i++) { 
		if($next==3) $next=0;
		if($col==1) $output.="<tr class='dayrow'>";
	
		if($i <= ($days+($start-1)) && $i >= $start) {
			$bStyle = "";
			$cssDayClass = "dayout";
			if(($cur == $today[mday]) && ($name == traducirMes($today[month]))){
				$bStyle = "style='color:#FFF; font-size: 11px;'";
				$cssDayClass = "hoy";
			}
			$evtOnClick = "";
			if(in_array($cur, $diasEventos)){
				$cssDayClass = "dayposible";
				$evtOnClick = "onclick=\"this.id='dia$cur';mostrarEventosDia($cur, $month, $year);\"";
			}
			$output .= "<td valign='top' class=\"$cssDayClass\" $evtOnClick>";			
			$output .= "<div class='day'><b $bStyle>$cur</b></div></td>";
			$cur++; 
			$col++;
		} 
		else { 
			$output .= "<td valign='top' onMouseOver=\"this.className='dayover'\" onMouseOut=\"this.className='dayout'\" class=\"dayout\">";
			$output.="&nbsp;</td>"; 
			$col++; 
		}  
			
		if($col==8) { 
			$output.="</tr>"; 
			$col=1; 
		}
	}
	
	$output.="</table>";
	$objResponse->assign($idDivCalendario, 'innerHTML', $output);
	return $objResponse;
}

generarCodigoParaAjax(array("armarCalendarioEventos"), '', false);

?>