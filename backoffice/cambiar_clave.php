<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include_once('./seguridad/seguridad.class.php');
include_once('./seguridad/usuario.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);
$Usuario = new Usuario($Cnx, $xajax);

/*--------------------------------------------------------------------------
                             M O D U L O S
  --------------------------------------------------------------------------*/
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Tpl_Contenido = 'base_contenido.htm';

/*--------------------------------------------------------------------------
                             L O G I C A
  --------------------------------------------------------------------------*/

$Error = "";
$Html = new nyiHTML('usuarios/cambiar_clave.htm');
$Usuario->_GetDB($_SESSION["cfgusu"]["id_usuario"], 'id_usuario_admin');
$Html->assign('LOGIN', $Usuario->Registro['login']);
$Html->assign('NOMBRE_USUARIO', $Usuario->Registro['nombre_usuario_admin']);

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$clave = $_POST["CLAVE"];
	$nueva_clave = $_POST["NUEVA_CLAVE"];
	$nueva_clave_conf = $_POST["NUEVA_CLAVE_CONFIRMACION"];
	$Error = $Usuario->CambiarClave($Usuario->Registro['login'], $clave, $nueva_clave);
	if($Error == ""){
		$Error = "La clave se ha cambiado correctamente.";
	}
}

// Script Post
$Html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$Html->fetchParamURL($_GET));
// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'CAMBIO DE CLAVE');
$Cab->assign('NOMACCION', getNomAccion(ACC_MODIFICACION));
$Cab->assign('ACC', ACC_POST);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "inicio.php");
$Html->assign('NAVEGADOR', $Cab->fetchHTML());
$Html->assign('ERROR', $Error);

$mod_Contenido = $Html->fetchHTML();

/*--------------------------------------------------------------------------
                         G E N E R O   P A G I N A
--------------------------------------------------------------------------*/
// Menu Horizontal
$Menu = new nyiMenuHor('base_menu_horizontal.htm', 130, 22);

// Genero html
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('SOLAPA', $mod_Solapa);
$Contenido->assign('MODCONT', $mod_Contenido);

// Modulo
$Modulo = new nyiModulo('MI CUENTA', 'base_modulo.htm');
$Modulo->assign('NOMSCRIPT', $mod_Script);
$Modulo->SetUsuario($_SESSION["cfgusu"]["nombre_usuario_admin"]);
$Modulo->assign('MENUES', $Menu->fetchMenu());
$Modulo->SetContenido($Contenido->fetchHTML());

// Imagen
$Perfil = $Security->GetIdPerfilUsuario($_SESSION["cfgusu"]["id_usuario"]);
switch($Perfil){
	case PERFIL_ADMINISTRADOR:
		$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq.gif');
		break;
	
	case PERFIL_CLIENTE:
		$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq_cliente.gif');
		break;
}

// Ajax
$xajax->processRequest();
$Modulo->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX_PARA_ADMIN));

$Modulo->printHTML();
?>