<?PHP
/*--------------------------------------------------------------------------
   Archivo: principal.php
   Descripcion: pagina principal de los modulos
  --------------------------------------------------------------------------*/

function CambiarUnidadesEntregadas($valor){
	$objResponse = new xajaxResponse();
	// Sanitizar entrada
	$valor = SanitizarValor($valor);
	$Cnx = nyiCNX();
	$OK = $Cnx->execute("UPDATE parametros SET unidades_entregadas = '$valor'");
	if($OK){
		$objResponse->Alert("El valor de Unidades entregadas ha sido modificado correctamente.\n");
	}
	return $objResponse;
}

$Html = new nyiHTML('principal.htm');
$Security = new Seguridad($Cnx);

// Permiso modulo novedades
if(!$Security->PermisoUsuarioModuloNovedades($_SESSION["cfgusu"]["id_usuario"])){
	$Html->assign('OCULTAR_NOVEDADES', _SI);
}

// Permiso modulo usuarios
if(!$Security->PermisoUsuarioModuloUsuarios($_SESSION["cfgusu"]["id_usuario"])){
	$Html->assign('OCULTAR_USUARIOS', _SI);
}

$xajax->registerFunction("CambiarUnidadesEntregadas");

// Genero HTML 
$mod_Contenido = $Html->fetchHTML();

?>