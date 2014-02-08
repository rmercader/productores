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
$Opc = 'documentos';
$Tpl_Contenido = 'base_contenido.htm';
if(!isset($_GET['MOD'])){
	$_GET['PVEZ'] = _SI;	
}

if ( ValidateModuleParameters($_GET['MOD'], 'documentos') ){
	$Opc = $_GET['MOD'];
}

$file = 'documentos/'.$Opc.'.php';
include($file);

/*--------------------------------------------------------------------------
						G E N E R O   P A G I N A
--------------------------------------------------------------------------*/
// Menu Horizontal
$Menu = new nyiMenuHor('base_menu_horizontal.htm', 150, 22);

// Menu Categorias
$Menu->AddOpcion(1, 'Categorias');
$Menu->AddOpcionLink(1, 1, 'Nueva Categoria', $mod_Script, array('MOD'=>'categorias_documento', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(1, 2, 'Lista Categorias',$mod_Script, array('MOD'=>'categorias_documento','PVEZ'=>_SI));

// Menu Documentos
$Menu->AddOpcion(2, 'Documentos');
$Menu->AddOpcionLink(2, 1, 'Nuevo Documento', $mod_Script, array('MOD'=>'documentos', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(2, 2, 'Lista Documentos',$mod_Script, array('MOD'=>'documentos','PVEZ'=>_SI));

/*
// Menu Videos
$Menu->AddOpcion(3, 'Videos');
$Menu->AddOpcionLink(3, 1, 'Nuevo Video', $mod_Script, array('MOD'=>'videos', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(3, 2, 'Lista Videos',$mod_Script, array('MOD'=>'videos','PVEZ'=>_SI));
*/

// Genero html
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('SOLAPA', $mod_Solapa);
$Contenido->assign('MODCONT', $mod_Contenido);

// Modulo
$Modulo = new nyiModulo('DOCUMENTOS', 'base_modulo.htm');
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