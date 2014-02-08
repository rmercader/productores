<?PHP
// Includes
include(DIR_BASE.'clientes/cliente.class.php');

// Parametros
$Acc = ACC_GRID;
if (isset($_GET['ACC']))
    $Acc = $_GET['ACC'];

$ParamMod = "";
if (isset($_GET['MOD']))
    $ParamMod = "?MOD=".$_GET['MOD'];

// Objeto
$Tabla = new Cliente($Cnx, $xajax);

// Segun accion
$mod_Contenido = '';

switch ($Acc) {
	case ACC_ALTA:
		$mod_Contenido = $Tabla->Insert();
		// Si grabo
		if ($mod_Contenido === true){
			header("Location: ".basename($_SERVER['SCRIPT_NAME']).$ParamMod);
      		exit();
		}
		break;
	case ACC_MODIFICACION:
   		if (isset($_GET['COD'])){
			$mod_Contenido = $Tabla->Update($_GET['COD'], 'id_cliente');
			// Si grabo
			if ($mod_Contenido === true){
				header("Location: ".basename($_SERVER['SCRIPT_NAME']).$ParamMod);
      			exit();
			}
		}	
		break;
	case ACC_BAJA:
   		if (isset($_GET['COD'])){
			$mod_Contenido = $Tabla->Delete($_GET['COD'], 'id_cliente');
			// Si borro
			if ($mod_Contenido === true){
				header("Location: ".basename($_SERVER['SCRIPT_NAME']).$ParamMod);
      			exit();
			}
   		}
		break;
	case ACC_CONSULTA:
		// Si hay codigo
   		if (isset($_GET['COD']))
			$mod_Contenido = $Tabla->consulta($_GET['COD']);
		break;
	case ACC_PDF:
		$Tabla->PDF();
		break;
	case ACC_GRID:
		$mod_Contenido = $Tabla->grid($Reg_Pag);
		break;

	case "E":
		// Exportar a excel
		header("Location: exportar_clientes_excel.php");
		exit();
		break;

	default:
		$mod_Contenido = 'Error de parametros';
		break;
}
?>