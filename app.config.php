<?PHP

// Constantes para rutas
define('DIR_PUBLIC', "D:\\Desarrollo\\Prolesa\\www\\");
define('DIR_BASE', DIR_PUBLIC."backoffice\\");
define('DIR_LIB', DIR_BASE."nyi\\");
define('SMARTY_APP_DIR', "D:\\Smarty\\Prolesa\\");

define('DIR_HTTP_PUBLICA', 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/prolesa/');
define('DIR_HTTP', DIR_HTTP_PUBLICA.'backoffice/');
define('DIR_XAJAX', 'backoffice/xajax/');
define('DIR_XAJAX_PARA_ADMIN', '../'.DIR_XAJAX);
define('DIR_ACTIVACION', DIR_HTTP.'activar_cuenta_usuario.php');
define('LOG_USUARIOS', DIR_PUBLIC."logs\\log_usuarios.log");
define('LOG_ERRORES', DIR_PUBLIC."logs\\errores.log");
define('LOG_SISTEMA', DIR_PUBLIC."logs\\prolesa.log");

// Constantes para la base de datos
define('DB_PROVIDER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', 'prolesa');
define('DB_PASSWORD', 'prolesa');
//define('DB_PASSWORD', '#u2b0d0M9');
define('DB_DATABASE', 'prolesa');

include(DIR_BASE.'constantes.php');

?>
