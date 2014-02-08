<?PHP
/*--------------------------------------------------------------------------
   Archivo:constantes.class.php
   Descripcion: Clase para tablas codigeras
  --------------------------------------------------------------------------*/
class Tconstantes{
	var $Defs;
	
	// ------------------------------------------------
	// Constructor
	// ------------------------------------------------
	function Tconstantes($Vector){
		// Inicializo datos
		$this->Defs = $Vector;
	}

	// Devuelvo vector de ids
	function getIds(){
		return(array_keys($this->Defs));
	}
		
	// Devuelvo vector de nombres
	function getNombres(){
		return(array_values($this->Defs));
	}		
	
	// Controlo si existe
	function existe($Codigo){
		return (array_key_exists($Codigo,$this->Defs));
	}
	
	// Devuelve nombre
	function getNombre($Codigo){
		return($this->Defs[$Codigo]);
	}
}
?>