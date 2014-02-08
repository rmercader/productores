<?PHP
/*--------------------------------------------------------------------------
Archivo: nyiAJAX.php
Descripcion: Clase para el manejo de Ajax
Fecha de Creaci�n: 20/11/2004
Ultima actualizacion: Fri Dec 03 16:04:46 UYT 2004 @836 /Internet Time/

Este archivo es parte del FrameWork nyi
Por documentacion de uso, referirse a http://xajaxproject.org/docs.php
Copyright (c) 2009 Rodrigo Mercader rodrigomercader@hotmail.com

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
	Clase: nyiAjax.php
	Descripcion: Clase para encapsular AJAX
--------------------------------------------------------------------------*/
class nyiAjax extends xajax{
	
	var $xml_response;
	
	function nyiAJAX(){
		$this->xajax();
	}
	
	/// Crea el objeto XmlHttpResponse
	function CreateResponse(){
		$xml_response = new xajaxResponse();
		return $xml_response;
	}
	
	function getJavascript(){
		this->getJavascript();
	}
}