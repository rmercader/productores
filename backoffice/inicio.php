<?PHP
/*--------------------------------------------------------------------------
   Archivo:home.php
   Descripcion: Home del sistema
  --------------------------------------------------------------------------*/   

include_once('../app.config.php');
include('./admin.config.php');
include_once(DIR_BASE.'configuracion_inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);

// -------------------------------------------------------------------------------
//                                Genero Pagina
// -------------------------------------------------------------------------------
// Genero html
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Opc = 0;
$Tpl_Contenido = 'base_nada.htm';
include('./principal.php');
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('MODCONT',$mod_Contenido);

// Modulo
$Modulo = new nyiModulo('', 'base_modulo.htm');
$Modulo->assign('NOMSCRIPT',basename($_SERVER['SCRIPT_NAME']));
$Modulo->SetUsuario($_SESSION["cfgusu"]["nombre"]);
$Modulo->SetContenido($Contenido->fetchHTML());

// Imagen
$Perfil = $Security->GetIdPerfilUsuario($_SESSION["cfgusu"]["id_usuario"]);
switch($Perfil){
	case PERFIL_ADMINISTRADOR:
		$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq.gif');
		break;
	
	case PERFIL_OPERADOR_CLIENTE:
	case PERFIL_CLIENTE:
		$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq_cliente.gif');
		break;
}

// Ajax
$xajax->processRequest();
$Modulo->assign('AJAX_JAVASCRIPT', $xajax->getJavascript('../'.DIR_XAJAX));

$Modulo->printHTML();
?>