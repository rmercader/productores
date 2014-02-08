<?PHP

// Emailing
define('MAIL_HOST', "smtp5.conaprole.com.uy");
define('CASILLA_CONTACTO', "info@prolesa.com.uy");
define('CASILLA_NOTIFICACION_CLIENTES', "altasdeclientes@prolesa.com.uy");
define('CASILLA_NO_REPLY', "noreply@prolesa.com.uy");
define('USUARIO_SMTP', "mailer");
define('CLAVE_USUARIO_SMTP', "prolesa.MX.2o12");

// Perfiles de usuario
define('PERFIL_ADMINISTRADOR', 1);
define('PERFIL_CLIENTE', 2);

// Constantes para links en modulos
define('LNK_NADA', 'nada');

// Constantes para acciones
define('ACC_GRID','G');
define('ACC_PDF','F');
define('ACC_ALTA','A');
define('ACC_MODIFICACION','M');
define('ACC_CONSULTA','C');
define('ACC_SELECCIONAR','L');
define('ACC_BAJA','B');
define('ACC_POST','S');
define('ACC_VER','X');
define('ACC_ANULACION', 'N');

// Constantes para las novedades
define('DIR_HTTP_FOTOS_NOVEDADES', DIR_HTTP.'novedades/fotos/');
define('DIR_FOTOS_NOVEDADES', DIR_BASE.'novedades/fotos/');
define('LARGO_THUMBNAIL_NOVEDAD', 80);
define('ANCHO_THUMBNAIL_NOVEDAD', 80);
define('LARGO_FOTO_NOVEDAD', 300);
define('ANCHO_FOTO_NOVEDAD', 140);

// Constantes para las sucursales
define('DIR_HTTP_FOTOS_SUCURSALES', DIR_HTTP.'sucursales/fotos/');
define('DIR_FOTOS_SUCURSALES', DIR_BASE.'sucursales/fotos/');

// Constantes para los catalogos
define('DIR_HTTP_CATALOGOS', DIR_HTTP.'productos/catalogos/');
define('DIR_CATALOGOS', DIR_BASE.'productos/catalogos/');

// Constantes para los iconos de categorias de productos
define('DIR_HTTP_ICONOS_CATEGORIAS_PRODUCTOS', DIR_HTTP.'productos/iconos_categorias/');
define('DIR_ICONOS_CATEGORIAS_PRODUCTOS', DIR_BASE.'productos/iconos_categorias/');

// Constantes para los productos
define('DIR_HTTP_FOTOS_PRODUCTOS', DIR_HTTP.'productos/fotos/');
define('DIR_FOTOS_PRODUCTOS', DIR_BASE.'productos/fotos/');
define('LARGO_THUMBNAIL_PRODUCTO', 50);
define('ANCHO_THUMBNAIL_PRODUCTO', 50);
define('LARGO_PREVIEW_PRODUCTO', 150);
define('ANCHO_PREVIEW_PRODUCTO', 150);

// Constantes para la feria de prolesa
define('DIR_HTTP_FOTOS_FERIA', DIR_HTTP.'feria/fotos/');
define('DIR_FOTOS_FERIA', DIR_BASE.'feria/fotos/');
define('LARGO_FOTO_FERIA', 550);
define('ANCHO_FOTO_FERIA', 400);

// Constantes para los eventos
define('DIR_HTTP_FOTOS_EVENTOS', DIR_HTTP.'eventos/fotos/');
define('DIR_FOTOS_EVENTOS', DIR_BASE.'eventos/fotos/');
define('LARGO_PREVIEW_EVENTO', 195);
define('ANCHO_PREVIEW_EVENTO', 195);
define('LARGO_THUMBNAIL_EVENTO', 50);
define('ANCHO_THUMBNAIL_EVENTO', 50);
define('ALCANCE_CLIENTES', 1);
define('ALCANCE_CLIENTES_DSC', 'Todos los clientes');
define('ALCANCE_SUCURSAL', 2);
define('ALCANCE_SUCURSAL_DSC', 'Sucursal espec&iacute;fica');
define('ALCANCE_PUBLICO', 3);
define('ALCANCE_PUBLICO_DSC', 'P&uacute;blico');
define('DIAS_ANTES_NOTIFICAR_EVENTOS', 3);

//Constantes para los documentos
define('DIR_DOCUMENTOS', DIR_BASE.'documentos/uploaded/');
define('DIR_HTTP_DOCUMENTOS', DIR_HTTP.'documentos/uploaded/');
define('DIR_HTTP_EXTENSIONES_DOCUMENTOS', DIR_HTTP.'documentos/extensiones_img/');



// Constantes Generales
define('_SI','S');
define('_NO','N');
define('_SIN','Si');
define('_NON','No');
define('ID_SN',_SI.'|'._NO);
define('NOM_SN','Si|No');
define('CANT_DEC', 6);
define('ID_IDIOMA_ADMIN', 2);
define('ALTURA_EDITOR', '300');
define('CREDENCIALES_CLIENTE', "CREDENCIALES_CLIENTE");
define('COOKIE_ID_CLIENTE', "COOKIE_ID_CLIENTE");

// Error Level
define('_ERROR','ERROR');
define('_OK','OK');

// Agenda
$DIASEM = array('Domigo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
$HORAS  = array('00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00','24:00');

// Paneles por defecto
$Tpl_Panel      = 'base_panel.htm';
$Tpl_Calendario = 'base_calendario.htm';
$Tpl_Grid       = 'base_grid.htm';
$Tpl_Menu       = 'base_menu.htm';

// Temas HTML
$TEMASHTML_id  = array('estilo01');
$TEMASHTML_nom = array('Tema por defecto');

// Variables por defecto
$ESTILO_HTML = 'estilo01';
$Reg_Pag = 10;
$Reg_Pag_bt = 15;
define('TAM_PAGINA', $Reg_Pag);

?>
