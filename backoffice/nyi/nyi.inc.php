<?PHP
/*-----------------------------------------------------------------------------------------------------
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Archivo: nyi.inc.php
Descripcion: Configuracion del framework

Contrato de uso:
Se deben definir las siguientes constantes:
	DB_PROVIDER - Proveedor de base de datos, por ej. postgres|mysql
	DB_HOST - IP del servidor de bases de datos
	DB_USER - Usuario para conectarse a la base de datos
	DB_PASSWORD - Password del usuario
	DB_DATABASE - Base de datos de la aplicacion
	DIR_HTML - Directorio desde donde se leeran los templates para smarty
	SMARTY_APP_DIR - Directorio donde estaran las carpetas de compilacion de smarty para la aplicacion
-----------------------------------------------------------------------------------------------------*/

// Librerias y Clases
define('SMARTY_DIR', DIR_LIB.'smarty/');

require_once(SMARTY_DIR.'Smarty.class.php');

// Libreria ADO
include_once(DIR_LIB."adodb/toexport.inc.php");
include_once(DIR_LIB."adodb/adodb.inc.php");

// Libreria FPDF
define('FPDF_FONTPATH', DIR_LIB."fpdf/font/");
require_once(DIR_LIB."fpdf/fpdf.php");

//Configuracion ADO
define('ADODB_TIPO', DB_PROVIDER);
define('ADODB_HOST', DB_HOST);
define('ADODB_USER', DB_USER);
define('ADODB_PASS', DB_PASSWORD);
define('ADODB_BASE', DB_DATABASE);

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$ADODB_LANG = "es";

// Constantes por defecto
define('SMARTY_HTML', DIR_HTML);
define('SMARTY_COMPILADO', SMARTY_APP_DIR.'compiled/');
define('SMARTY_CONFIG', SMARTY_APP_DIR.'config/');
define('SMARTY_CACHE', SMARTY_APP_DIR.'cache/');

// Calendario
define('nyi_NOMMES','enero,febrero,marzo,abril,mayo,junio,julio,agosto,setiembre,octubre,noviembre,diciembre');
define('nyi_DOMINGO',0);

// Paginador
define('nyi_REGPAG',12);
?>
