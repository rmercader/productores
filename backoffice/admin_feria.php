<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);
/*
if(!$Security->PermisoUsuarioModuloNovedades($_SESSION["cfgusu"]["id_usuario"])){
	// Redirecciono
	header("Location: inicio.php");
	exit();
}
*/
/*--------------------------------------------------------------------------
                             M O D U L O S
  --------------------------------------------------------------------------*/
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Opc = 'eventos_feria';
$Tpl_Contenido = 'base_contenido.htm';
if(!isset($_GET['MOD'])){
	$_GET['PVEZ'] = _SI;	
}

if ( ValidateModuleParameters($_GET['MOD'], 'feria') ){
	$Opc = $_GET['MOD'];
}

$file = 'feria/'.$Opc.'.php';
include($file);

/*--------------------------------------------------------------------------
						G E N E R O   P A G I N A
--------------------------------------------------------------------------*/
// Menu Horizontal
$Menu = new nyiMenuHor('base_menu_horizontal.htm', 150, 22);

// Menu Eventos
$Menu->AddOpcion(1, 'Eventos');
$Menu->AddOpcionLink(1, 1, 'Nuevo Evento', $mod_Script, array('MOD'=>'eventos_feria', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(1, 2, 'Lista de eventos',$mod_Script, array('MOD'=>'eventos_feria','PVEZ'=>_SI));

// Menu FAQs
$Menu->AddOpcion(2, 'FAQs');
$Menu->AddOpcionLink(2, 1, 'Nueva pregunta', $mod_Script, array('MOD'=>'faqs', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(2, 2, 'Lista preguntas',$mod_Script, array('MOD'=>'faqs','PVEZ'=>_SI));

// Genero html
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('SOLAPA', $mod_Solapa);
$Contenido->assign('MODCONT', $mod_Contenido);

// Modulo
$Modulo = new nyiModulo('LA FERIA DE PROLESA', 'base_modulo.htm');
$Modulo->assign('NOMSCRIPT', $mod_Script);
$Modulo->SetUsuario('Usuario: '.$_SESSION["cfgusu"]["nombre"]);
$Modulo->assign('MENUES', $Menu->fetchMenu());
$Modulo->SetContenido($Contenido->fetchHTML());

// Imagen
$Perfil = $Security->GetIdPerfilUsuario($_SESSION["cfgusu"]["id_usuario"]);
switch($Perfil){
	case PERFIL_ADMINISTRADOR:
		$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq.gif');
		break;
}

// Ajax
$xajax->processRequest();
$Modulo->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX_PARA_ADMIN));

$Modulo->printHTML();
?>