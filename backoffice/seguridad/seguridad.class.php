<?PHP
/*--------------------------------------------------------------------------
   Archivo: seguridad.class.php
   Descripcion: Clase para gestion de seguridad del sistema
   Ultima actualizacion: 
  --------------------------------------------------------------------------*/  
// includes
include_once(DIR_BASE.'seguridad/usuario.class.php');

class Seguridad {
	
	var $DB;
		
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Seguridad($DB){
		// Conexion
		$this->DB = $DB;
	}
		
	function Encriptar($texto){
		return md5($texto);
	}
	
	function GenerarPassword(){
		return $this->generatePassword();
	}
	
	function PermisoUsuarioModuloMarcoOperativo($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloQueEsMevir($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloComoAcceder($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloLicitaciones($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloAutoridades($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloContacto($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloMevirEnLosMedios($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloCorreoInterno($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
		
	function PermisoUsuarioModuloNovedades($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloUsuarios($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
		
	function PermisoUsuarioModuloDocumentos($id_usuario){
		return true;
	}
	
	function PermisoUsuarioModuloGaleriaFotos($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
	
	function PermisoUsuarioModuloProgramas($id_usuario){
		$Perfil = $this->GetIdPerfilUsuario($id_usuario);
		return $Perfil == PERFIL_ADMINISTRADOR;
	}
		
	function GetIdPerfilUsuario($id_usuario){
		$Perfil = $this->DB->getOne("SELECT id_perfil FROM usuario WHERE id_usuario = $id_usuario");
		return $Perfil;
	}
	
	// Retorna el id_cliente asociado al usuario
	function GetIdClienteUsuario($id_usuario){
		return $this->DB->getOne("SELECT id_cliente FROM usuario_cliente WHERE id_usuario = $id_usuario");
	}
	
	function GenerarLogin($nombre_completo){
		$nombre = strtolower($nombre_completo);
		// me sirve solo nombre y apellido
		$arr_texto = split(' ', $nombre);
		if(count($arr_texto) > 0){
			if(count($arr_texto) == 1){
				$username = $arr_texto[0];
			}
			else{
				$username = substr($arr_texto[0], 0, 1).substr($arr_texto[1], 0, 14);
			}
		}
		
		$user = new Usuario($this->DB);
		
		$username = substr($username, 0, 15);
		$i = 0;
		$intento = $username;
		while ($user->ExisteLogin($intento)){
			$i++;
			$intento = $username.$i;
		}
		
		return $intento;
	}	
	
	function generatePassword($length=6,$level=2) {
		list($usec, $sec) = explode(' ', microtime());
		srand((float) $sec + ((float) $usec * 100000));
		$validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
		$validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";
	
		$password  = "";
		$counter   = 0;
	
		while ($counter < $length) {
			$actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);
	
			// All character must be different
			if (!strstr($password, $actChar)) {
					$password .= $actChar;
					$counter++;
			}
		}
	
		return $password;
	}
	
	// Retorna el combo de identificadores ordenados segun el idioma
	function GetComboIdsPerfiles($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_perfil FROM perfil ORDER BY nombre_perfil");
		
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
	function GetComboNombresPerfiles($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre_perfil FROM perfil ORDER BY nombre_perfil");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
}
?>