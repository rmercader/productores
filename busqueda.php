<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
ini_set('display_errors', 1);
include_once('./app.config.php');
include_once('./sitio.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 

$interfaz = new Interfaz();
$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('busqueda.htm');
$error = "";
$ROWCOUNT = 6; // # filas por pagina
$RANGE = 5; // cantidad de numeros de pagina en la barra de paginacion

if ($RANGE % 2 == 0) // calculate modulo only once for both constants
{
    $iRangeMin = (int) ($RANGE / 2) - 1;
    $iRangeMax = $iRangeMin + 1;
}
else
{
    $iRangeMin = (int) ($RANGE - 1) / 2;
    $iRangeMax = $iRangeMin;
} 

// GET working variables
$iPageNum = 1; // set default page number
if (!empty($_GET["s"])) // page number passed via GET
{
    $iPageNum = $_GET["s"]; // get actual page number
}
$iCursor = 0; // set default page cursor
if (!empty($_GET["cursor"])) // cursor passed via GET
{
    $iCursor = $_GET["cursor"]; // get actual cursor value
} 

if($_SERVER["REQUEST_METHOD"] == 'POST'){
	$txtBusqueda = trim($_POST['txtBusqueda']);
	
}
else {
	$txtBusqueda = trim(urldecode($_GET['q']));
}

if($txtBusqueda == '' || strlen($txtBusqueda) < 3){
    $error = 'Ingrese algún texto en la casilla de búsqueda, de al menos tres caracteres.';
}
else {
	$sTxtBusquedaParam = urlencode($txtBusqueda);
	$iRows = $interfaz->busquedaProductosCantidadResultados($txtBusqueda); // total # of rows in data set 
	// calculate local control variables
	$iPages = (int) ceil($iRows / $ROWCOUNT);
	$iPageMin = $iPageNum - $iRangeMin;
	$iPageMax = $iPageNum + $iRangeMax;
	$iPageMin = ($iPageMin < 1) ? 1 : $iPageMin;
	$iPageMax = ($iPageMax < ($iPageMin + $RANGE - 1)) ? $iPageMin + $RANGE - 1 : $iPageMax;

	$sPageButtons = ""; // set default (for strict correctness)
	if ($iPages > 1 ) // we need to generate a pagination bar
	{
	    if ($iPageMax > $iPages)
	    {
	        $iPageMin = ($iPageMin > 1) ? $iPages - $RANGE + 1 : 1;
	        $iPageMax = $iPages;
	    }
	    $iPageMin = ($iPageMin < 1) ? 1 : $iPageMin;
	    $s = 0; // initialize
	    $c = 0; // initialize
	    $p = 0; // initialize
	    if (($iPageNum > ($RANGE - $iRangeMin)) && ($iPages > $RANGE)) // generate left arrow button
	    {
	        $s = 1; // pro forma
	        $sPageButtons .= "\t\t<a href=\"busqueda.php?q={$sTxtBusquedaParam}&s=1&cursor=0\">&lt;</a>\r";
	    }
	    if ($iPageNum > ($iRangeMin + 1)) // generate Prev button
	    {
	        $s = $iPageMin - 1;
	        $c = ($s - 1) * $ROWCOUNT;
	        if($c >= 0){
	        	$sPageButtons .= "\t\t<a href=\"busqueda.php?q={$sTxtBusquedaParam}&s=".$s."&cursor=".$c."\">Anterior</a>\r";
	        }
	    }
	    for ($i = $iPageMin; $i <= $iPageMax; $i++) // generate numbered buttons
	    {
	        if ($i == $iPageNum)
	        {
	            $s = $i;
	            $c = ($s - 1) * $ROWCOUNT;
	            $sPageButtons .= "\t\t<span><b>".$i."</b></span>\r";
	        }
	        else
	        {
	            $s = $i;
	            $c = ($s - 1) * $ROWCOUNT;
	            $sPageButtons .= "\t\t<a href=\"busqueda.php?q={$sTxtBusquedaParam}&s=".$s."&cursor=".$c."\">".$i."</a>\r";
	        }
	    }
	    if (($iPageNum < ($iPages - $iRangeMax)) && ($iPages > $RANGE)) // generate Next button
	    {
	        $s = $iPageMax + 1;
	        $c = ($s - 1) * $ROWCOUNT;
	        $sPageButtons .= "\t\t<a href=\"busqueda.php?q={$sTxtBusquedaParam}&s=".$s."&cursor=".$c."\">Siguiente</a>\r";
	    }
	    if (($iPageNum < ($iPages - $iRangeMax)) && ($iPages > $RANGE) && ($s < $iPages)) // generate right arrow button
	    {
	        $s = $iPages;
	        $c = ($s - 1) * $ROWCOUNT;
	       	$sPageButtons .= "\t\t<a href=\"busqueda.php?q={$sTxtBusquedaParam}&s=".$s."&cursor=".$c."\">&gt;</a>\r";
	    }
	    $seccion->assign('paginacion',  "\t<div align=\"center\">\r".$sPageButtons."\t</div>\r"); // build <div>...</div> form
	} // end generation of pagination bar

	$resultados = $interfaz->busquedaProductos($txtBusqueda, $iCursor, $ROWCOUNT);
	$seccion->assign('REG', $resultados);
	$seccion->assign('txtBusquedaUrl', $sTxtBusquedaParam);
	$seccion->assign('s', isset($_GET['s']) ? $_GET['s'] : 1);
	$seccion->assign('cursor', isset($_GET['cursor']) ? $_GET['cursor'] : 0);
	$seccion->assign('txtBusqueda', $txtBusqueda);
}

$seccion->assign('servername', $_SERVER['SERVER_NAME']);
$seccion->assign('txtBusqueda', $txtBusqueda);
$seccion->assign('error', $error);
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('pagina', 'home');
$marco->assign('AJAX_JAVASCRIPT', generarCodigoParaAjax($FUNCIONES_AJAX, DIR_HTTP_PUBLICA.'ajax_eventos.php'));
$marco->printHTML();

?>