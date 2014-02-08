<?PHP
/*--------------------------------------------------------------------------
   Archivo: nyiHTML.php
   Descripcion: Clases para el manejo del HTML
   Fecha de Creaci�n: 20/11/2004
   Ultima actualizacion: 22/11/2004

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
	Clase: nyiHTML
	Descripcion: Clase para encapsular smarty
--------------------------------------------------------------------------*/
class nyiHTML extends Smarty{
	var $_Template;

	// Creador
	function nyiHTML($Tpl, $DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO, $DirCfg=SMARTY_CONFIG, $DirCache=SMARTY_CACHE) {
		// Objeto Smarty
		$this->__construct();

		// Debug
		//$this->debugging = true;
		//$this->debug_tpl = '.';

		// Directorios
		$this->template_dir = $DirHtm;
		$this->compile_dir  = $DirCom;
		$this->config_dir   = $DirCfg;
		$this->cache_dir    = $DirCache;

		// Propiedades
		$this->_Template   = $Tpl;
	}

	// Seteo Template
	function SetTemplate($Template){
			$this->_Template = $Template;
	}

	// Genero Parametros url
	function fetchParamURL($Par){
		$Doy = '';
		if (is_array($Par)){
				$Doy = '?';
				// Recorro vector
				reset($Par);
				while (list($clave, $val) = each($Par))
						$Doy .= $clave.'='.$val.'&';
				// Saco ultimo caracter
				$Doy = substr($Doy,0,strlen($Doy)-1);
		}
		// Devuelvo
		return($Doy);
	}

	// Devuelvo HTML
	function fetchHTML(){
		return($this->fetch($this->_Template));
	}

	// Imprimo HTML
	function printHTML(){
			print($this->fetchHTML());
	}
}

/*--------------------------------------------------------------------------
		Clase: nyiPanel
		Descripcion: Panel de un modulo
	--------------------------------------------------------------------------*/
class nyiPanel extends nyiHTML{
	var $_Nombre;
	var $_Titulo;
	var $_Estado;
	var $_Largo;
	var $_Ancho;
	var $_Ubicacion;
	var $_Contenido;

	// Creador
	function nyiPanel($Nom, $Tit, $Tpl='panel.htm', $Largo = '100%',
						$Ancho = '100%', $Ubicacion = 'left',
										$DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO,
										$DirCfg=SMARTY_CONFIG, $DirCache=SMARTY_CACHE) {

					// Objeto HTML
					$this->nyiHTML($Tpl,$DirHtm,$DirCom,$DirCfg,$DirCache);

					// Propiedades
					$this->_Contenido  = '';
					$this->_Nombre     = $Nom;
					$this->_Titulo     = $Tit;
					$this->_Estado     = 'M';
					$this->_Largo      = $Largo;
					$this->_Ancho      = $Ancho;
					$this->_Ubicacion  = $Ubicacion;

					// Valores de la session
					if (isset($_SESSION['panel'][$this->_Nombre]))
							$this->_Estado = $_SESSION['panel'][$this->_Nombre];
	}

	// Seteo Titulo
	function SetTitulo($Titulo){
			$this->_Titulo = $Titulo;
	}

	// Seteo contenido
	function SetContenido($Contenido){
			$this->_Contenido = $Contenido;
	}

	// Seteo Dimension
	function SetPropiedades($Largo,$Ancho,$Ubicacion='left'){
			$this->_Largo = $Largo;
			$this->_Ancho = $Ancho;
			$this->_Ubicacion = $Ubicacion;
	}

	// Devuelvo Html
	function fetchHTML($Script=''){
			// Cargo Valores
			$this->assign('TITULO',$this->_Titulo);
			$this->assign('LARGO',$this->_Largo);
			$this->assign('ANCHO',$this->_Ancho);
			$this->assign('UBICACION',$this->_Ubicacion);

			// Proceso parametros
			$Parametros = $_GET;

			// Verifico cambio de estado
			if (isset($_GET[$this->_Nombre]))
					$this->_Estado = $_GET[$this->_Nombre];

			// Segun estado del panel
			$AuxE = 'm';
			if ($this->_Estado == 'm') $AuxE = 'M';
			$Parametros[$this->_Nombre] = $AuxE;

			if ($this->_Estado == 'M'){
					$this->assign('MSGMIN','Minimizar Panel');
					$this->assign('IMGMIN','+');
					$this->assign('CONTENIDO',$this->_Contenido);
					// si hay Script
					if ($Script <> '')
							$this->assign('SCRIPT',$Script.$this->fetchParamURL($Parametros));
					// Registro en sesion
					$_SESSION['panel'][$this->_Nombre] = $this->_Estado;
			}
			else{
					$this->assign('MSGMIN','Maximizar Panel');
					$this->assign('IMGMIN','_');
					// si hay Script
					if ($Script <> '')
							$this->assign('SCRIPT',$Script.$this->fetchParamURL($Parametros));
					// Registro en sesion
					$_SESSION['panel'][$this->_Nombre] = $this->_Estado;
			}
			// Devuelvo HTML
			return($this->fetch($this->_Template));
	}

	// Imprimo HTML
	function printHTML($Script=''){
					print($this->fetchHTML($Script));
			}
	}

/*--------------------------------------------------------------------------
	Clase: nyiModulo
	Descripcion: Modulo
--------------------------------------------------------------------------*/
class nyiModulo extends nyiHTML{
	var $_Paneles;
	var $_TituloMod;
	var $_Contenido;
	var $_Usuario;
	var $_ScriptLogout;
	var $_ScriptHome;
	var $_Template;

	// Creador
	function nyiModulo($TitMod, $Tpl, $DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO, $DirCfg=SMARTY_CONFIG,  $DirCache=SMARTY_CACHE) {
		// Objeto HTML
		$this->nyiHTML($Tpl,$DirHtm,$DirCom,$DirCfg,$DirCache);

		// Propiedades
		$this->_Paneles      = '';
		$this->_TituloMod    = $TitMod;
		$this->_Template     = $Tpl;
		$this->_Contenido    = '';
		$this->_Usuario      = 'Invitado';
		$this->_ScriptLogout = '#';
		$this->_ScriptHome   = '#';
	}

	// Agregar panel
	function AddPanel($Panel){
			$this->_Paneles .= $Panel;
	}

	// Seteo Titulo Modulo
	function SetTituloMod($Txt){
			$this->_TituloMod = $Txt;
	}

	// Seteo Titulo Opcion
	function SetTituloOpc($Txt){
			$this->_TituloOpc = $Txt;
	}

	// Seteo contenido
	function SetContenido($Contenido){
			$this->_Contenido = $Contenido;
	}

	// Seteo Usuario
	function SetUsuario($Txt){
			$this->_Usuario = $Txt;
	}

	// Seteo Script Logout
	function SetScriptLogout($Txt){
			$this->_ScriptLogout = $Txt;
	}

	// Seteo Script Home
	function SetScriptHome($Txt){
			$this->_ScriptHome = $Txt;
	}

	// Seteo Template
	function SetTemplate($Template){
			$this->_Template = $Template;
	}

	// Devuelvo Html
	function fetchHTML(){
			// Cargo Valores
			$this->assign('PANELES',$this->_Paneles);
			$this->assign('TITULOMOD',$this->_TituloMod);
			$this->assign('CONTENIDO',$this->_Contenido);
			$this->assign('USUARIO',$this->_Usuario);
			$this->assign('SCRIPTLOGOUT',$this->_ScriptLogout);
			$this->assign('SCRIPTHOME',$this->_ScriptHome);
			// Devuelvo HTML
			return($this->fetch($this->_Template));
	}
}

/*--------------------------------------------------------------------------
	Clase: nyiCalendario
	Descripcion: Panel con el calendario
--------------------------------------------------------------------------*/
class nyiCalendario extends nyiHTML{
	var $_Hoy;
	var $_Mes;
	var $_Anio;
	var $_Tabla;

	function nyiCalendario($Tpl='calendario.htm', $Hoy = '', $DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO, $DirCfg=SMARTY_CONFIG, $DirCache=SMARTY_CACHE){

		// Objeto nyiPanel
		$this->nyiHTML($Tpl,$DirHtm,$DirCom,$DirCfg,$DirCache);

		// Propiedades
		$this->_Hoy = $Hoy;
		if ($this->_Hoy == '')
			$Hoy = mktime(0,0,0,date(m),date(d),date(Y));

		// Propiedades
		$this->_Mes  = 0;
		$this->_Anio = 0;

		// Inicializo Tabla de dias
		for ($i = 1; $i<=5; $i++) {
			for ($f = 0; $f<=6; $f++) {
					$this->_Tabla[$i][$f] = array('dia'=>'','feriado'=>'N','agenda'=>'N','hoy'=>'N');
			}
		}

		// Separo Fecha
		$Anio = date('Y');
		if (isset($_GET['YA']))
			$Anio = $_GET['YA'];
		$Mes  = date('m');
		if (isset($_GET['MA']))
			$Mes = $_GET['MA'];
		$this->_Mes  = date('m',mktime(0,0,0,$Mes,1,$Anio));
		$this->_Anio = date('Y',mktime(0,0,0,$Mes,1,$Anio));

		// Genero Tabla
		$Semana = 1;
		$DiaSem = date('w',mktime(0,0,0,$this->_Mes,1,$this->_Anio));
		for ($dia = 1; $dia <= days_in_month(CAL_GREGORIAN,$this->_Mes,$this->_Anio); $dia++){
			$this->_Tabla[$Semana][$DiaSem]['dia'] = $dia;

			// si es domingo
			if ($DiaSem == $nyi_DOMINGO)
					$this->_Tabla[$Semana][$DiaSem]['feriado'] = 'S';

			// si es el dia de hoy
			if (mktime(0,0,0,$this->_Mes,$dia,$this->_Anio) == $Hoy)
					$this->_Tabla[$Semana][$DiaSem]['hoy'] = 'S';

			// si estoy en la quinta semana, es sabado y quedan dias
			if (($Semana == 5) && ($DiaSem == 6) && ($dia < days_in_month(CAL_GREGORIAN,$this->_Mes,$this->_Anio))){
				// agrego Semana
				for ($i = 0; $i<=6; $i++)
						$this->_Tabla[6][$i] = array('dia'=>'','feriado'=>'N','agenda'=>'N','hoy'=>'N');
			}

			// Rangos
			$DiaSem++;
			if ($DiaSem > 6){
				$DiaSem = 0;
				$Semana++;
			}
		}
	}

	// Seteo Propiedad
	function setPropiedad($Dia,$Propiedad,$Valor){
		// Busco Dia
		reset($this->_Tabla);
		while (list($Aux, $Semana) = each($this->_Tabla)){
			while (list($Aux1, $DiasS) = each($Semana)){
				if ($DiasS['dia'] == $Dia)
						$this->_Tabla[$Aux][$Aux1][$Propiedad] = $Valor;
			}
		}
	}

	// Seteo Feriados
	function SetFeriados($Dias) {
		// Proceso Feriados
		if (is_array($Dias)){
			while (list($clave, $val) = each($Dias)){
				$Dia = explode('/',$val);
				if ($Dia[1] == $this->_Mes)
						$this->setPropiedad($Dia[0],'feriado','S');
			}
		}
	}

	// Seteo Feriados
	function setAgenda($Dias) {
		// Proceso Dias ocupados
		if (is_array($Dias)){
			while (list($clave, $Dia) = each($Dias))
					$this->setPropiedad($Dia,'agenda','S');
		}
	}


	// Doy calendario
	function fetchCalendario($ScriptAgenda='',$Script='',$ParURL=''){
		// Parametros
		$Parametros = $_GET;
		//if ($ScriptAgenda == '') $ScriptAgenda = '#';
		if ($Script == '') $Script = '#';

		// Agenda
		$Parametros['YA'] = $this->_Anio;
		$Parametros['MA'] = $this->_Mes;
		if (is_array($ParURL)){
			while (list($Variable, $Valor) = each($ParURL))
				$Parametros[$Variable] = $Valor;
		}
		$this->assign('SCRIPTAGENDA',$ScriptAgenda.$this->fetchParamURL($Parametros));

		// Mes y A�o anterior
		$Parametros = $_GET;
		$Anio = $this->_Anio;
		$Mes  = $this->_Mes-1;
		if ($Mes == 0){
			$Mes  = 12;
			$Anio = $this->_Anio-1;
		}
		
		$Parametros['YA'] = $Anio;
		$Parametros['MA'] = $Mes;
		$this->assign('SCRIPTMESANT',$Script.$this->fetchParamURL($Parametros));

		// Mes y A�o posterior
		$Anio = $this->_Anio;
		$Mes  = $this->_Mes+1;
		
		if ($Mes == 13){
				$Mes  = 1;
				$Anio = $this->_Anio+1;
		}
		
		$Parametros['YA'] = $Anio;
		$Parametros['MA'] = $Mes;
		$this->assign('SCRIPTMESSIG',$Script.$this->fetchParamURL($Parametros));

		// Hoy
		$Parametros['YA'] = date('Y');
		$Parametros['MA'] = date('m');
		$this->assign('SCRIPTHOY',$Script.$this->fetchParamURL($Parametros));

		// Genero contenido
		$this->assign('CALENDARIO',$this->_Tabla);
		$AuxMes = explode(',',nyi_NOMMES);
		$this->assign('MESNOMBRE',$AuxMes[$this->_Mes-1].'/'.$this->_Anio);

		// Devuelvo
		return($this->fetchHTML());
	}
}


/*--------------------------------------------------------------------------
	Clase: nyiGridDB
	Descripcion: Grid de datos desde una base de datos
--------------------------------------------------------------------------*/
class nyiGridDB extends nyiHTML{
	var $_Nombre;
	var $_TextoBus;
	var $_CampoBus;
	var $_OrdBus;
	var $_PagActual;
	var $_PagTotal;
	var $_RegTotal;
	var $_RegPag;
	var $_RegIni;
	var $_Paginador;
	var $_FrmCriterio;
	var $_FrmOrden;
	var $_FrmOrdenTxt;
	var $_FrmConBus;
	var $_countSQL;
	var $_selectSQL;
	var $_AnchoGrid;
	var $Datos;
	var $_Variables;
	var $_FrmOrdenSel;

	// Creador
	function nyiGridDB($Nombre, $CantRegPag=nyi_REGPAG, $Tpl='grid.htm', $DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO, $DirCfg=SMARTY_CONFIG, $DirCache=SMARTY_CACHE){

		// Objeto nyiPanel
		$this->nyiHTML($Tpl,$DirHtm,$DirCom,$DirCfg,$DirCache);

		// Propiedades
		$this->_Nombre      = $Nombre;
		$this->_PagActual   = 1;
		$this->_RegPag      = $CantRegPag;
		$this->_Paginador   = '';
		$this->_FrmCriterio = '';
		$this->_FrmOrden    = '';
		$this->_FrmOrdenSel = '';
		$this->_FrmOrdenTxt = '';
		$this->_FrmConBus   = 'S';
		$this->_PagTotal    = 1;
		$this->_RegIni      = 0;
		$this->_countSQL    = '';
		$this->_selectSQL   = '';
		$this->_AnchoGrid   = '95%';
		$this->_Variables   = array();
	}

	function getFiltro(){
		// Recorro campos
		$Doy = '';
		$AuxID  = array_keys($this->_FrmOrden);
		$AuxNOM = array_values($this->_FrmOrden);
		while (list($Nro,$Valor) = each($AuxID)){
			if ($Valor == $this->_CampoBus) $Doy = "Orden: ".$AuxNOM[$Nro];
		}
		// Si hay filtro
		if ($this->_TextoBus <> '')
			$Doy .= '. Filtro: '.$this->_TextoBus;
		// Devuelvo
		return($Doy);
	}

	// Seteo Ancho
	function setAncho($Ancho){
		$this->_AnchoGrid = $Ancho;
	}
	
	// Cargo parametros
	function setParametros($Inicializar=false, $CampoDfto, $OrdDfo="ASC"){
		// Segun estado
		if ($Inicializar == true){
			$_SESSION['paginador'][$this->_Nombre]['textobus'] = '';
			$_SESSION['paginador'][$this->_Nombre]['campobus'] = $CampoDfto;
			$_SESSION['paginador'][$this->_Nombre]['ordbus'] = $OrdDfo;
			$_SESSION['paginador'][$this->_Nombre]['pagactal'] = 1;
		}
		// Cargo desde la sesion
		$this->_TextoBus  = $_SESSION['paginador'][$this->_Nombre]['textobus'];
		$this->_CampoBus  = $_SESSION['paginador'][$this->_Nombre]['campobus'];
		$this->_OrdBus    = $_SESSION['paginador'][$this->_Nombre]['ordbus'];
		$this->_PagActual = $_SESSION['paginador'][$this->_Nombre]['pagactal'];
	}

	// Cargo Paginador
	function setPaginador($Txt=''){
		$this->_Paginador = $Txt;
	}

	// Cargo SQL
	function setSQL($C='',$S=''){
		$this->_countSQL  = $C;
		$this->_selectSQL = $S;
	}

	// Agrego Variable Smarty
	function addVariable($Variable,$Valor){
		$this->_Variables[$Variable] = $Valor;
	}

	// Cargo Formulario de Criterio
	function setFrmCriterio($Txt='', $Orden=array(), $Busqueda='S'){
		// inicializo
		$this->_FrmOrden    = '';
		$this->_FrmCriterio = '';
		$this->_FrmConBus   = 'S';
		// Si no es nulo
		if ($Txt <> ''){
			$this->_FrmCriterio = $Txt;
			$this->_FrmOrden    = $Orden;
			$this->_FrmConBus   = $Busqueda;
		}
	}

	// Seteo Criterio de Filtrado
	function setCriterio($Campo,$Txt,$NroPag='',$OrdBus='ASC'){
		// Si es distinto a lo que hay
		if (($_SESSION['paginador'][$this->_Nombre]['textobus'] <> $Txt) ||
			($_SESSION['paginador'][$this->_Nombre]['campobus'] <> $Campo) || 
			($_SESSION['paginador'][$this->_Nombre]['ordbus'] <> $OrdBus)){
			// Cambio Criterio
			$_SESSION['paginador'][$this->_Nombre]['textobus'] = $Txt;
			$_SESSION['paginador'][$this->_Nombre]['campobus'] = $Campo;
			$_SESSION['paginador'][$this->_Nombre]['ordbus']   = $OrdBus;
			$_SESSION['paginador'][$this->_Nombre]['pagactal'] = 1;
		}
		else{
			if ($NroPag <> '')
				$_SESSION['paginador'][$this->_Nombre]['pagactal'] = $NroPag;
		}
		// Cargo en objeto
		$this->_TextoBus  = $_SESSION['paginador'][$this->_Nombre]['textobus'];
		$this->_CampoBus  = $_SESSION['paginador'][$this->_Nombre]['campobus'];
		$this->_OrdBus  = $OrdBus;
		$this->_PagActual = $_SESSION['paginador'][$this->_Nombre]['pagactal'];
	}

	// Seteo pagina
	function setPaginaAct($Numero){
		//Si hay numero
		if ($Numero <> ''){
			$_SESSION['paginador'][$this->_Nombre]['pagactal'] = $Numero;
			$this->_PagActual = $Numero;
		}
	}

	// Devuelvo todos los registros
	function fetchDatos($Cnx, $Campos='', $From='', $Where='(1=1)'){
		// Si hay busqueda
		if ($this->_TextoBus <> '')
			$Where .= " and (upper(".$this->_CampoBus.") LIKE '%".strtoupper($this->_TextoBus)."%')";

		// Todos
		$Sql = $this->_selectSQL;
		if ($this->_selectSQL == '')
			$Sql = "SELECT $Campos FROM $From";

		// Selecciono datos
		$Orden = '';
		if ($this->_CampoBus <> '')
			$Orden = "ORDER BY ".$this->_CampoBus." ".$this->_OrdBus;
		
		$Datos = $Cnx->execute("$Sql WHERE $Where $Orden");
		
		if ($Datos === false) die($Cnx->ErrorMsg());
		$Aux = $Datos->GetArray();
		$Datos->Close;

		// Devuelvo datos
		return($Aux);
	}
	
	function _gensql($sql,$where){
		// Si hay GROUP BY
		if (substr_count(strtoupper($sql),'GROUP') > 0){
			// Hay GROUP BY
			$sql = str_replace('GROUP'," WHERE $where GROUP",$sql);
			$sql = str_replace('group'," WHERE $where group",$sql);
		}
		else{
			$sql .= " WHERE $where";
		}
		return($sql);
	}

	// Genero Datos
	function getDatos($Cnx, $Campos='', $From='', $Where='(1=1)'){
		// Si hay busqueda
		if ($this->_TextoBus <> '')
			$Where .= " and (upper(".$this->_CampoBus.") LIKE '%".strtoupper($this->_TextoBus)."%')";

		// Cantidad de Registros
		$Sql = $this->_countSQL;
		if ($this->_countSQL == '')
			$Sql = "SELECT COUNT(*) FROM $From";
		
		//LogArchivo($this->_gensql($Sql,$Where));
		
		$Aux = $Cnx->getone($this->_gensql($Sql,$Where));
		if ($Aux === false) die($Cnx->ErrorMsg());
		$this->_RegTotal = $Aux;

		// Si hay que paginar
		$this->_PagTotal = 1;
		if ($this->_RegPag > 0){
			// Total de paginas
			$Aux = $this->_RegTotal/$this->_RegPag;                 
			if ($this->_RegTotal%$this->_RegPag > 0)
				$Aux++;
			// convierto en integer
			settype($Aux,'integer');
			if ($Aux == 0) $Aux = 1;
			$this->_PagTotal = $Aux;
			
			// Si pagina actual es mayor
			if ($this->_PagActual > $this->_PagTotal)
			$this->_PagActual = $this->_PagTotal;
		}
		
		// Orden
		$Orden = '';
		if ($this->_CampoBus <> '')
		$Orden = "ORDER BY ".$this->_CampoBus." ".$this->_OrdBus;

		// Selecciono Registros
		if ($this->_RegPag > 0){
			// Con Rango
			$this->_RegIni = ($this->_PagActual-1)*$this->_RegPag;
			$Sql = $this->_selectSQL;
			if ($this->_selectSQL == '')
				$Sql = "SELECT $Campos FROM $From";

			//LogArchivo($this->_gensql($Sql,$Where)." $Orden");

			$Datos = $Cnx->SelectLimit($this->_gensql($Sql,$Where)." $Orden",
									$this->_RegPag, $this->_RegIni);
		}
		else{
		$Sql = $this->_selectSQL;
		if ($this->_selectSQL == '')
				$Sql = "SELECT $Campos FROM $From";
			// Todos
			//LogArchivo($this->_gensql($Sql,$Where)." $Orden");
			$Datos = $Cnx->execute($this->_gensql($Sql,$Where)." $Orden");
		}
		if ($Datos === false) die($Cnx->ErrorMsg());
		$this->Datos = $Datos->GetArray();
		$Datos->Close;
	}

	// Agrego Campo
	function addColumna($Nombre,$Dfto=''){
		reset($this->Datos);
		// Nueva matriz
		$NewD = array();
		while (list($Nro,$Campos) = each($this->Datos))
			$NewD[$Nro] = array_merge($Campos,array($Nombre=>$Dfto));
		// Copio
		$this->Datos = $NewD;
	}

	// Genero HTML
	function fetchGrid($Tpl, $Titulo, $Script, $ScriptPDF='', $ScriptHOME='', $ScriptMto='',$Acciones=''){
		// Parametros
		$Parametros = array();
		while (list($I,$V) = each($_GET))
			if ($I <> 'PVEZ') $Parametros[$I] = $V;
	
		// Formulario de Criterio
		if ($this->_FrmCriterio <> ''){
			$this->assign('FRMCRITERIO',$this->_FrmCriterio);
			$this->assign('ORDEN_ID',array_keys($this->_FrmOrden));
			$this->assign('ORDEN_NOM',array_values($this->_FrmOrden));
			$this->assign('ORDEN_DFTO',$this->_CampoBus);
			$this->assign('ORDEN_TXT',$this->_TextoBus);
			$this->assign('CAMPOBUS',$this->_FrmConBus);
		}
	
		// Paginador
		if ($this->_Paginador <> ''){
			$this->assign('PAGINADOR',$this->_Paginador);
	
			// Pagina Actual
			$this->assign('PAGACT',$this->_PagActual);
			// Total de Paginas
			$this->assign('PAGTOT',$this->_PagTotal);
			
	
			// Combo de paginas
			$AuxPag = array();
			for ($i=1; $i <= $this->_PagTotal; $i++)
				$AuxPag[] = $i;
				
			$this->assign('CBPAGINA_ID',$AuxPag);
	
			// Primero y Anterior
			$Parametros['NROPAG'] = 1;
			$this->assign('SCRIPT_PRI',$Script.$this->fetchParamURL($Parametros));
			if ($this->_PagActual > 1)
				$Parametros['NROPAG'] = $this->_PagActual-1;
			$this->assign('SCRIPT_ANT',$Script.$this->fetchParamURL($Parametros));
	
			// Siguiente y Ultimo
			$Parametros['NROPAG'] = $this->_PagTotal;
			$this->assign('SCRIPT_ULT',$Script.$this->fetchParamURL($Parametros));
			if ($this->_PagActual < $this->_PagTotal)
				$Parametros['NROPAG'] = $this->_PagActual+1;
			$this->assign('SCRIPT_SIG',$Script.$this->fetchParamURL($Parametros));
		}

		// Scripts
		if ($ScriptPDF <> '')
			$ScriptPDF .= $this->fetchParamURL(array_merge($Parametros,array('ACC'=>'F'))); 
		
		$this->assign('SCRIPT_PDF',$ScriptPDF);
		$this->assign('SCRIPT_HOME',$ScriptHOME);
		$Parametros['NROPAG'] = $this->_PagActual;
		$this->assign('SCRIPT_FRM',$Script.$this->fetchParamURL($Parametros));

		// Titulos y anchos
		$this->assign('ANCHOGRID',$this->_AnchoGrid);
		$this->assign('LIS_TITULO', $Titulo);
		$this->assign('LIS_SUBTITULO','Listando '.$this->_RegTotal.' registros. Pagina '.$this->_PagActual.' de '.$this->_PagTotal);

		// Genero tabla de Datos
		$Contenido = new nyiHTML($Tpl);

		// Acciones
		unset($Parametros['COD']);
		unset($Parametros['ACC']);
		if (in_array('A',$Acciones)){
			if (strpos($ScriptMto,'javascript') === false){
				$this->assign('SCRIPT_ADD',$ScriptMto.$this->fetchParamURL(array_merge(array('ACC'=>'A'),$Parametros)));
			}
			else{
				$this->assign('SCRIPT_ADD',$ScriptMto);
			}
		}
		
		if (in_array('B',$Acciones))
			$Contenido->assign('SCRIPT_DEL',$ScriptMto.$this->fetchParamURL(array_merge(array('ACC'=>'B'),$Parametros)));
		if (in_array('M',$Acciones))
			$Contenido->assign('SCRIPT_ED',$ScriptMto.$this->fetchParamURL(array_merge(array('ACC'=>'M'),$Parametros)));
		if (in_array('C',$Acciones))
			$Contenido->assign('SCRIPT_CT',$ScriptMto.$this->fetchParamURL(array_merge(array('ACC'=>'C'),$Parametros)));
		if (in_array('L',$Acciones))
			$Contenido->assign('SCRIPT_SEL','#');

		// Otras variables
		while (list($Variable,$Valor) = each($this->_Variables))
			$Contenido->assign($Variable,$Valor);                          

		// Datos
		reset($this->Datos);
		while (list($NroReg,$Campos) = each($this->Datos)){
			// Recorro campos
			$Row = array();
			while (list($NomCampo,$Valor) = each($Campos)){
				// si es nulo
				if ($Valor == '') $Valor = '&nbsp;';
				$Row[$NomCampo] = $Valor;
			}
			// Asigno a la tabla
			$Contenido->append('REG',$Row);
		}

		// Contenido
		$this->assign('LIS_CONTENIDO',$Contenido->fetchHTML());
		// Devuelvo
		return($this->fetchHTML());
	}

	function CantidadRegistros(){
		return count($this->Datos);
	}
}

/*--------------------------------------------------------------------------
	Clase: nyiMenu
	Descripcion: Menu de opciones
--------------------------------------------------------------------------*/
class nyiMenu extends nyiHTML{
		var $_Opciones;

		// Creador
		function nyiMenu($Tpl=NYI_MENU, $Opciones = '',
					$DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO,
					$DirCfg=SMARTY_CONFIG, $DirCache=SMARTY_CACHE){

					// Objeto nyiPanel
					$this->nyiHTML($Tpl,$DirHtm,$DirCom,$DirCfg,$DirCache);

					// Propiedades
					$this->_Opciones = array();
					if (is_array($Opciones))
					$this->$_Opciones = array_merge ($this->$_Opciones,$Opciones);
		}

		// Agrego Opcion
		function addOpcion($Nombre, $Modulo, $Parametros='', $Ancho='', $Activa='N', $Dest=''){
				$this->_Opciones[] = array('nombre'=>$Nombre,
											'link'=>$Modulo,
											'parametros'=>$Parametros,
											'ancho'=>$Ancho,
											'activa'=>$Activa,
											'destino'=>$Dest);
		}

		// Genero HTML
		function fetchMenu(){
				// Parametros
				$Parametros = $_GET;

				// Cargo contenido
				while (list($clave, $Opcion) = each($this->_Opciones)){
						// Cargo en HTML
						$this->append('REG',array('nombre'=>$Opcion['nombre'],
													'destino'=>$Opcion['destino'],
													'ancho'=>$Opcion['ancho'],
													'activa'=>$Opcion['activa'],
													'link'=>$Opcion['link'].
													$this->fetchParamURL($Opcion['parametros'])));
						// Saco parametro
						unset($Parametros['parametro']);
				}
				// Devuelvo
				return($this->fetchHTML());
		}
}


/*--------------------------------------------------------------------------
	Clase: nyiMenuHor
	Descripcion: Menu de opciones horizontal
--------------------------------------------------------------------------*/
class nyiMenuHor extends nyiHTML{
	var $_Opciones;
	var $_Cant;
	var $_Left;
	var $_Ancho;

	// Creador
	function nyiMenuHor($Tpl, $AnchoOpcion=100, $Left = 22, $DirHtm=SMARTY_HTML, $DirCom=SMARTY_COMPILADO,
						$DirCfg=SMARTY_CONFIG, $DirCache=SMARTY_CACHE){

		// Objeto html
		$this->nyiHTML($Tpl,$DirHtm,$DirCom,$DirCfg,$DirCache);
		
		// Inicializo
		$this->_Cant     = -1;
		$this->_Left     = $Left;
		$this->_Ancho    = $AnchoOpcion;
	}
	
	// Doy left
	function getLeft(){
		return($this->_Left+($this->_Cant*$this->_Ancho));
	}   
		
	// Opcion principal
	function addOpcion($Id,$Nombre,$Link='',$Parametros='', $Dest=''){
		// Parametros

		$AuxLink = '';
		if ($Link <> '')
			$AuxLink = $Link.$this->fetchParamURL($Parametros);
		
		// Agrego a Menu
		$this->_Cant++;
		$this->_Opciones[$Id] = array('menu_id'=>$Id,'menu_nombre'=>$Nombre, 'menu_link'=>$AuxLink, 'menu_cant'=>0,
										'menu_activo'=>0, 'menu_destino'=>$Dest, 'menu_ancho'=>$this->_Ancho,
										'menu_left'=>$this->getLeft(),'menu_links'=>array());
	}
		
	// SubOpciones
	function addOpcionLink($Id, $IdL, $Nombre, $Link, $Parametros='', $Dest=''){
		// Link
		$AuxLink = '';
		if ($Link <> '')
			$AuxLink = $Link.$this->fetchParamURL($Parametros);
			
		// agrego opcion
		$this->_Opciones[$Id]['menu_cant']  = $this->_Opciones[$Id]['menu_cant']+1;  
		$this->_Opciones[$Id]['menu_links'][$IdL] = array('link_nombre'=>$Nombre, 'link_link'=>$AuxLink,
															'link_activo'=>0, 'link_destino'=>$Dest,
															'link_ancho'=>$this->_Ancho,'link_left'=>$this->getLeft());
	}
	
	// Link activo
	function linkActivo($Id,$IdL){
		$this->_Opciones[$Id]['menu_activo'] = 1;
		$this->_Opciones[$Id]['menu_links'][$IdL]['Link_activo'] = 1;
	}
						
	// Genero HTML
	function fetchMenu(){
		// Devuelvo
		$this->assign('REG',$this->_Opciones);
		$this->assign('REG2',$this->_Opciones);
		return($this->fetchHTML());
	}
}
	
?>
