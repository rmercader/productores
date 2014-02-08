<?PHP
/*--------------------------------------------------------------------------
Archivo: nyiDATA.php
Descripcion: Clases para el manejo de datos
Fecha de Creaci�n: 20/11/2004
Ultima actualizacion: Fri Dec 03 16:04:46 UYT 2004 @836 /Internet Time/

Este archivo es parte del FrameWork nyi
Copyright (c) 2004 Pablo Erartes pejota@internet.com.uy

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
--------------------------------------------------------------------------*/
include_once('nyi.inc.php');

/*--------------------------------------------------------------------------
	funcion: nyi_CNX
	Descripcion: Conexion con la base de datos
--------------------------------------------------------------------------*/
function nyiCNX($TipoBase=ADODB_TIPO, $Host=ADODB_HOST,
				$Usuario=ADODB_USER, $Pass=ADODB_PASS, $Base=ADODB_BASE,
				$Debuger=false){

	// Conexion a la base
	$Aux = NewADOConnection($TipoBase);
	$Aux->Connect($Host, $Usuario, $Pass, $Base);
	$Aux->debug = false;

	// Devuelvo
	return($Aux);
}

/*--------------------------------------------------------------------------
	Clase: nyiData.php
	Descripcion: Clase para encapsular ADODB
--------------------------------------------------------------------------*/
class nyiData{
	var $_Cnx;

	// Constructor
	function nyiData($Cnx = '',$TipoBase=ADODB_TIPO, $Host=ADODB_HOST,
			$Usuario=ADODB_USER, $Pass=ADODB_PASS, $Base=ADODB_BASE,
			$Debuger=false){

		// Si no hay Conexion
		$this->_Cnx = $Cnx;
		if ($Cnx == '')
			$this->_Cnx = nyi_CNX($TipoBase,$Host,$Usuario,
									$Pass,$Base,$Debuger);
	}

	// Habilito/deshabilito debugger
	function setDebuger($Estado){
		$Cnx = $this->_Cnx;
		$Cnx->debug = $Estado;
	}

	// Devuelvo Registros
	function getRegistros($sql){
		$Cnx = $this->_Cnx;
		$Aux = $Cnx->execute($sql);
		if ($Aux === false) $Aux = $Cnx->ErrorMsg();
		return($Aux);
	}

	// Ejecuto sql
	function executeSql($sql){
		$Cnx = $this->_Cnx;
		$Aux = $Cnx->execute($sql);
		return($Aux);
	}

	// Ultimo ID
	function LastID(){
		// Conexion
		return($this->_Cnx->Insert_ID());
	}

	// Cantidad de registros de una tabla con una condicion
	function CantReg($tabla,$where=''){
		// SQL
		if ($where <> '') $where = "WHERE $where";
		$Cnx = $this->_Cnx;
		$Aux = $Cnx->getone("SELECT COUNT(*) FROM $tabla $where");
		return($Aux);
	}
}

/*--------------------------------------------------------------------------
	Clase: nyiTabla
	Descripcion: Clase para gestion de tablas (ABMC)
--------------------------------------------------------------------------*/
class nyiTabla extends nyiData{
	var $_Tabla;
	var $_Campos;

	// Constructor
	function nyiTabla($Tabla,$Cnx = '', $TipoBase=ADODB_TIPO, $Host=ADODB_HOST,
						$Usuario=ADODB_USER, $Pass=ADODB_PASS, $Base=ADODB_BASE,
						$Debuger=false){

		// Objeto nyiDATA
		$this->nyiData($Cnx,$TipoBase,$Host,$Usuario,$Pass,$Base,$Debuger);

		// Propiedades
		$this->_Tabla  = $Tabla;  // Nombre de la Tabla
		$this->_Campos = array(); // Campos

		// Creo estructura de la tabla
		$Aux = $this->_Cnx;
		$Res = $Aux->MetaColumnNames($Tabla);
		
		//Aca NO ESTA EL ERROR OJO!!!!
		if(!is_array($Res))
			LogArchivo($Tabla);
		while (list($Clave,$Valor) = each($Res)){
			$this->_Campos[$Valor] = '';
		}
	}

	// Where a partir de parametros
	function _fetchWhereID($R,$V){
		$Where = '(1=1)';
		if (!is_array($V)) 
		$V = array($V);
		reset($V);
		while (list($Clave,$Valor) = each($V))
				$Where .= " and (".$Valor." = '".$R[$Valor]."')";
		// Devuelvo
		return($Where);
	}

	// Doy Estructura
	function getEstructura(){
		$Aux = array();
		// Recorro campos
		reset($this->_Campos);
		while (list($Clave,$Valor) = each($this->_Campos))
				$Aux[$Clave] = '';
		// Devuelvo
		return($Aux);
	}

	// Agregar Registro
	function addRegistro($Datos,$AutoInc=''){
		// Error
		$Error = '';
		
		// Genero Query
		$Cnx = $this->_Cnx;
		$Def = $Cnx->Execute("SELECT * FROM ".$this->_Tabla." WHERE 1=-1");
		$InsertSQL = $Cnx->GetInsertSQL($Def, $Datos);
		
		/*
		$FilePtr = fopen('/srv/www/htdocs/formacril/documentos/logNyiData.sql', 'a');
		fwrite($FilePtr, $InsertSQL."\n");
		fflush($FilePtr);
		fclose($FilePtr);
		*/
		
		// Ejecuto Query
		$OK = $Cnx->Execute($InsertSQL);
		if ($OK === false) $Error = $Cnx->ErrorMsg();
		
		// Devuelvo
		return($Error);
	}

	// Editar Registro
	function editRegistro($RegNuevo,$Ids=''){
		// Error
		$Error = '';

		if ($Ids <> ''){
				// Conexion
				$Cnx = $this->_Cnx;
				// Selecciono registro actutal
				$RegAct = $Cnx->Execute("SELECT * FROM ".$this->_Tabla." WHERE ".$this->_fetchWhereID($RegNuevo,$Ids));
				// Genero Query
				$UpdateSQL = $Cnx->GetUpdateSQL($RegAct, $RegNuevo);
				if ($UpdateSQL <> ''){
					$OK = $Cnx->Execute($UpdateSQL);
					if ($OK === false) $Error = $Cnx->ErrorMsg();
				}
		}
		// Devuelvo
		return($Error);
	}

	// Eliminar Registro
	function deleteRegistro($Datos,$Ids=''){
		// Error
		$Error = '';

		if ($Ids <> ''){
				// Conexion
				$Cnx = $this->_Cnx;
				// Elimino registro
				$OK = $Cnx->Execute("DELETE FROM ".$this->_Tabla." WHERE ".
						$this->_fetchWhereID($Datos,$Ids));
			if ($OK === false) $Error = $Cnx->ErrorMsg();
		}
		// Devuelvo
		return($Error);
	}

	// Doy Registro
	function getRegistro(&$Reg,$Ids=''){
		// Inicializo
		$Error = '';
	
		if ($Ids <> ''){
			// Selecciono
			$OK = $this->_Cnx->GetRow("SELECT * FROM ".$this->_Tabla." WHERE ".$this->_fetchWhereID($Reg,$Ids));
			if ($OK === false) $Error = $this->_Cnx->ErrorMsg();
			
			// Cargo Datos
			reset($Reg);
			while (list($Clave,$Valor) = each($Reg))
				$Reg[$Clave] = $OK[$Clave];
		}
	
		// Devuelvo
		return($Error);
	}

	// Doy Columna
	function getColumna($Columna,$Orden=''){
		// si existe la columna
		if (array_key_exists($Columna,$this->getEstructura())){
					// Cargo Datos
				$Cnx = $this->_Cnx;
					// Selecciono
				$sql = "SELECT $Columna FROM ".$this->_Tabla;
				// Si existe Columna de Orden
				if (($Orden <> '') && (array_key_exists($Orden,$this->getEstructura())))
						$sql .= " ORDER BY $Orden";
				$OK = $Cnx->getcol($sql);
				if ($OK === false) $OK = array($Cnx->ErrorMsg());
			return($OK);
		}
		else{
		return(array('error'));
		}
	}
}
?>
