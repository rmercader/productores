<?php
/*--------------------------------------------------------------------------
   Archivo: nyiFILE.php
   Descripcion: Clases para la generacion de menu de ocpiones
   Fecha de Creaci�n: 20/11/2004
   Ultima actualizacion: 04/12/2004

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
	Clase: nyiUpload
	Descripcion: Menu de opciones
	--------------------------------------------------------------------------*/
class nyiUpload{
	// class variable
	var $MIME_permitidos;  // Array con los MIME types permitido
	var $dir_destino;      // Directorio destino
	var $tamanio_max;      // Tama�o maximo

	// Constructor
	function nyiUpload($Permitidos, $TamMax=300000, $Destino='.'){
		// Si no hay Permitidos
		if (empty($Permitidos))
			$Permitidos = array("image/pjpeg","image/x-png","image/jpeg","image/png","image/gif","image/bmp");

		// Cargo propiedades
		$this->MIME_permitidos = $Permitidos;
		$this->tamanio_max     = $TamMax;
		$this->dir_destino     = $Destino;
	}

	function putfile($Origen,$Destino='',$SobreEsc = 'S'){
		// Cargo Datos desde Vector superGlobal de Archivos subidos
		$f_Tipo    = strtok($_FILES[$Origen]['type'],";");
		$f_Nombre  = $_FILES[$Origen]['name'];
		$f_Tamanio = $_FILES[$Origen]['size'];
		$f_NomTmp  = $_FILES[$Origen]['tmp_name'];

		// Destino
		if ($Destino == "")
			$Destino = basename($f_Nombre);

		// Verifico Tipo de archivo
		$error = "Este tipo de archivo no esta permitido: $f_Tipo<br>";
		$MIME_permitidos = $this->MIME_permitidos;
		while (list($Aux,$TipoPer) = each($MIME_permitidos)){
			if (($f_Tipo == $TipoPer) || ($TipoPer == '*')) $error = '';
		}

		// Verifico Tama�o
		if (($f_Tamanio <= 0) || ($f_Tamanio >$this->tamanio_max))
			$error .= " Error en el tama� de archivo: $f_Tamanio Kb.<br>";

		// Si no hay errores Copia archivo
		if ($error == ""){
			// Verifico archivo destino
			$Cont = 1;
			$NomDestino = $Destino;

			// Sobrescribir
			if ($SobreEsc == 'S'){
				// Borro actual
				if (file_exists($this->dir_destino."/".$NomDestino))
					@unlink($this->dir_destino."/".$NomDestino);
			}
			else{
				while (file_exists($this->dir_destino."/".$NomDestino)){
					// Cambio Nombre
					$NomDestino = $Cont."_".$Destino;
					$Cont ++;
				}
			}
			// Controles internos
			if(!is_uploaded_file($f_NomTmp))
				$error .= "Archivo $f_Nombre no fue cargado correctamente. ";
			// Copio a destino y borro temporal
			if (!@move_uploaded_file($f_NomTmp,$this->dir_destino."/".$NomDestino))
				$error .= "Imposible copiar $f_Nombre a $f_NomTmp en el directorio destino. ";					

			// Devuelvo
			if ($error == ''){
				return array(true,$NomDestino);
			}
			else{
				return array(false,$error);
			}
		}
	}
}
?>