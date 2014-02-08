<?PHP
/*--------------------------------------------------------------------------
   Archivo: etiqueta.class.php
   Descripcion: Clase para el manejo de etiquetas y traducciones
   Ultima actualizacion: 
  --------------------------------------------------------------------------*/  
// includes
include_once(DIR_BASE.'class/table.class.php');

class Etiqueta extends Table {
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Etiqueta($DB){
		// Conexion
		$this->Table($DB, 'etiqueta');
	}
		
	function obtenerTraduccion($idEtiqueta, $idIdioma=ID_IDIOMA_ADMIN){
		return $this->DB->getOne("SELECT traduccion FROM etiqueta WHERE id_etiqueta = '$idEtiqueta' AND id_idioma = $idIdioma");
	}
}
?>