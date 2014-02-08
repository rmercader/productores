<?PHP
/*--------------------------------------------------------------------------
   Archivo:sino.class.php
   Descripcion: Clase para tabla SI/NO
  --------------------------------------------------------------------------*/
include_once(DIR_BASE.'class/constantes.class.php');  
  
class Tsino extends Tconstantes {

	// ------------------------------------------------
	// Constructor
	// ------------------------------------------------
	function Tsino(){
		// Inicializo datos
		$this->tconstantes(array(_SI=>_SIN,_NO=>_NON));
	}
}
?>