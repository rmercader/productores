<?PHP
/*--------------------------------------------------------------------------
   Archivo: idioma.class.php
   Descripcion: Clase para el manejo de idiomas
   Ultima actualizacion: 
  --------------------------------------------------------------------------*/  
// includes
include_once(DIR_BASE.'class/table.class.php');

class Idioma extends Table {
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Idioma($DB){
		// Conexion
		$this->Table($DB, 'idioma');
		$this->AccionesGrid = array(ACC_ALTA,ACC_BAJA,ACC_MODIFICACION,ACC_CONSULTA);
	}
		
	function GetIdiomas(){
		return $this->DB->execute("SELECT * FROM idioma ORDER BY codigo_idioma DESC");
	}
	
	function GetNombre($id){
		return $this->DB->getOne("SELECT nombre FROM idioma WHERE id_idioma = '$id'");
	}
	
	// Retorna el combo de identificadores ordenados segun el idioma
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_idioma FROM idioma ORDER BY codigo_idioma DESC");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de detalles para combo
	// ------------------------------------------------
	function GetComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre_idioma FROM idioma ORDER BY codigo_idioma DESC");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function existe($id=0){
		$cnt = $this->DB->getOne("SELECT COUNT(*) FROM idioma WHERE id_idioma = $id");
		return $cnt > 0;
	}
}
?>