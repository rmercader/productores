<?PHP
/*--------------------------------------------------------------------------
   Archivo: tabla.class.php
   Descripcion: Clase para manejar tablas
  --------------------------------------------------------------------------*/  
class Table {
	var $DB;
	var $TablaDB;
	var $TablaNom;
	var $Registro;
	var $Error;
	var $UsarTrnx;
	
	function Table($DB,$Tabla){
		// Conexion
		$this->DB    = $DB;
		$this->Error = '';
		$this->UsarTrnx = true;
		
		// Genero Registro
		$AuxTbl  = new nyiTabla($Tabla, $DB);
		$this->Registro = $AuxTbl->getEstructura();
		$this->TablaDB  = $AuxTbl;
		$this->TablaNom = $Tabla;
	}
	
	// ------------------------------------------------
	// Busco si existe un registro con el criterio
	// ------------------------------------------------
	function existe($Criterio){
		// Genero Where
		$Where = '(1=1) ';
		while (list($Campo,$Valor) = each($Criterio))
			$Where .= "AND ($Campo='$Valor') ";
			
		// Devuelvo
		$Cant = $this->TablaDB->CantReg($this->TablaNom,$Where);
		return($Cant>0?true:false);
	}
	
	
	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro,$Campo);
	}
	
	// ------------------------------------------------
	// Devuelve HTML de Consulta
	// ------------------------------------------------
	function consulta($Cod=-1){
		// Cargo registro
		$this->_GetDB($Cod);
		// Devuelvo
		return($this->_Frm(ACC_CONSULTA));
   }
   
	// ------------------------------------------------
	// Realiza insert y Devuelve HTML de Alta
	// ------------------------------------------------
	function Insert(){
		$HayForm = true;
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			// Si trabaja con transacciones
			if($this->UsarTrnx === true){
				$this->StartTransaction();	
			}
			
			// Cargo desde el formulario
			$this->_GetFrm();
			// Si tiene definido control
			if (method_exists($this,'beforeInsert'))
				$this->beforeInsert();
			// Si no hay errores
			if ($this->Error == ''){
				// Agrego a la base de datos
				$this->Error .= $this->TablaDB->addRegistro($this->Registro);
				// Insert OK
				if ($this->Error == ''){
					// Si tiene definido metodo after y metodo de ultimo id
					if ((method_exists($this,'afterInsert')) && 
							(method_exists($this,'getLastId'))){  
						$Aux = $this->getLastId();
						$this->afterInsert($Aux);	
					}
				}
				if ($this->Error == '') $HayForm =false;
			}
			if($this->UsarTrnx === true){
				$this->CompleteTransaction();
			}
		}
		// Si hay que hacer formulario
		if ($HayForm){
			return($this->_Frm(ACC_ALTA));
		}
		else{
			return(true);
		}		
	}

	// Inicia una Transaccion contra la base
	function StartTransaction(){
		$this->DB->execute("BEGIN");
	}
	
	// Realiza COMMIT si no existen errores sql, ROLLBACK en caso contrario
	function CompleteTransaction(){
		if($this->DB->ErrorMsg() == ''){
			$this->DB->execute("COMMIT");
		}
		else{
			$this->DB->execute("ROLLBACK");
		}
	}

	// ------------------------------------------------
	// Realiza edit y Devuelve HTML de Edit
	// ------------------------------------------------
	function Update($Cod,$Campo='id'){
		$HayForm = true;
		// Cargo Registro actual
		$this->_GetDB($Cod,$Campo);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			// Si trabaja con transacciones
			if($this->UsarTrnx === true){
				//$this->DB->StartTrans();
				$this->StartTransaction();
			}
			
			// Cargo Datos del formulario
			$this->_GetFrm();
			// Si tiene definido control
			if (method_exists($this,'beforeEdit'))
				$this->beforeEdit();
			// Si no hay errores
			if ($this->Error == ''){
				// Edito registro en la base de datos
				$AuxTbl = $this->TablaDB;
				$this->Error .= $AuxTbl->editRegistro($this->Registro,$Campo);
				// edit OK
				if ($this->Error == ''){
					// Si tiene definido metodo
					if (method_exists($this,'afterEdit'))
						$this->afterEdit();
				}
				if ($this->Error == '') $HayForm =false;
			}
			
			if($this->UsarTrnx === true){
				$this->CompleteTransaction();
			}
		}
		
		// Si hay que hacer formulario
		if ($HayForm){
			return($this->_Frm(ACC_MODIFICACION));
		}
		else{
			return(true);
		}
	}
	
	// ------------------------------------------------
	// Realiza delete
	// ------------------------------------------------
	function Delete($Cod,$Campo='id'){
		$HayForm = true;
		// Cargo Registro actual
		$this->_GetDB($Cod);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			// Si trabaja con transacciones
			if($this->UsarTrnx === true){
				$this->StartTransaction();	
			}
			
			// Si tiene definido control
			if (method_exists($this,'beforeDelete'))
				$this->beforeDelete($Cod);
			
			// Si no hay errores
			if ($this->Error == ''){
				// borro
				$AuxTbl = $this->TablaDB;
				$this->Error .= $AuxTbl->deleteRegistro($this->Registro,$Campo);
				// edit OK
				if ($this->Error == ''){
					// Si tiene definido metodo
					if (method_exists($this,'afterDelete'))
						$this->afterDelete($Cod);
				}
				if ($this->Error == '') $HayForm =false;
			}
			
			if($this->UsarTrnx === true){
				$this->CompleteTransaction();
			}
		}
		
		// Si hay que hacer formulario
		if ($HayForm){
			return($this->_Frm(ACC_BAJA));
		}
		else{
			return(true);
		}
	}	
}	
?>