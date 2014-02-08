<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include_once('./seguridad/seguridad.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);
if(!$Security->PermisoUsuarioModuloUsuarios($_SESSION["cfgusu"]["id_usuario"])){
	// Redirecciono
	header("Location: inicio.php");
	exit();
}

/*--------------------------------------------------------------------------
                             M O D U L O S
  --------------------------------------------------------------------------*/
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Opc = 'usuarios';
$Tpl_Contenido = 'base_contenido.htm';

if($_GET['MOD'] == 'cambiar_clave'){
	// Redirecciono
	header("Location: cambiar_clave.php");
	exit();
}

if ( ValidateModuleParameters($_GET['MOD'], 'seguridad') ){
	$Opc = $_GET['MOD'];
}

$file = 'seguridad/'.$Opc.'.php';
include($file);

/*--------------------------------------------------------------------------
                         G E N E R O   P A G I N A
--------------------------------------------------------------------------*/
// Menu Horizontal
$Menu = new nyiMenuHor('base_menu_horizontal.htm', 170, 22);

// Menu Categorizacion
$Menu->AddOpcion(1, 'Usuarios');
$Menu->AddOpcionLink(1, 1, 'Listado de Usuarios', $mod_Script, array('MOD'=>'usuarios', 'PVEZ'=>_SI));
$Menu->AddOpcionLink(1, 2, 'Cambiar clave', $mod_Script, array('MOD'=>'cambiar_clave'));

// Genero html
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('SOLAPA', $mod_Solapa);
$Contenido->assign('MODCONT', $mod_Contenido);

// Modulo
$Modulo = new nyiModulo('USUARIOS Y PERMISOS', 'base_modulo.htm');
$Modulo->assign('NOMSCRIPT', $mod_Script);
$Modulo->SetUsuario($_SESSION["cfgusu"]["nombre"]);
$Modulo->assign('MENUES', $Menu->fetchMenu());
$Modulo->SetContenido($Contenido->fetchHTML());
$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq.gif');

// Ajax
$xajax->processRequest();
$Modulo->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX));

$Modulo->printHTML();
?>