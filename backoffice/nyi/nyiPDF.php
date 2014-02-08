<?PHP
/*--------------------------------------------------------------------------
Archivo: nyiPDF.php
Descripcion: Clases para la generacion de PDF
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

class nyiGridPDF extends FPDF{
	var $Columnas;
	var $Filas;
	var $Titulo;
	var $Subtitulo;
	var $empNOMBRE;
	var $empDATOS;
	var $empLOGO;
	var $Usuario;
	var $Margen;
	var $Cabezal;
	var $Pie;
	var $AnchoPag;
	var $LargoPag;
	var $AnchoLabel;
	var $AnchoValor;

	function nyiGridPDF($Posicion='P',$Tamanio='A4', $Cabezal=True, $Pie=True){
		// Creo objeto
		$this->FPDF($Posicion,'mm',$Tamanio);

		$TamPag = array('A4'=>array(210,297),
						'A3'=>array(297,420),
						'A5'=>array(148,210),
						'Letter'=>array(210,280),
						'Legal'=>array(210,355));

		// Si viene medida especial
		if (is_array($Tamanio)){
			$TamPag['PER'] = array($Tamanio[0],$Tamanio[1]);
			$Tamanio = 'PER';
		}

		// Cargo Ancho y Largo
		$this->AnchoPag = $TamPag[$Tamanio][0];
		$this->LargoPag = $TamPag[$Tamanio][1];
		if ($Posicion <> 'P'){
			$this->AnchoPag = $TamPag[$Tamanio][1];
			$this->LargoPag = $TamPag[$Tamanio][0];
		}

		// Propiedades
		$this->Titulo      = '';
		$this->Subtitulo   = '';
		$this->Margen      = -1;
		$this->Cabezal     = Cabezal;
		$this->Pie         = Pie;
		$this->Columnas    = array();
		$this->Filas       = array();
		$this->AnchoLabel = 0;
		$this->AnchoValor = 0;
	}

	function inicio(){
		// Comienzo archivo
		$this->Open();
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true,15);
		$this->AddPage();
		$this->SetTitle($this->Titulo);
	}

	// Seteo datos del cabezal
	function setCabezal($Tit='', $Sub='', $empNOMBRE='', $empDATOS='', $empLOGO='', $usuario=''){
		$this->Titulo    = $Tit;
		$this->Subtitulo = $Sub;
		$this->empNOMBRE = $empNOMBRE;
		$this->empDATOS  = $empDATOS;
		$this->empLOGO   = $empLOGO;
		$this->Usuario   = $usuario;
	}

	// Seteo ficha
	function setFicha($L,$V){
		$this->AnchoLabel = $L;
		$this->AnchoValor = $V;
	}

	// Seteo margen
	function setMargen($M){
		$this->Margen = $M;
	}

	// Calculo margen de los listados
	function calMargenLis(){
		// Calculo Margen
		$Anchos = 0;
		reset($this->Columna);
		while (list($Indice,$Columna) = each($this->Columna))
			$Anchos = $Anchos+$Columna['ancho'];
		$Anchos = ($this->AnchoPag-$Anchos)/2;
		if ($Anchos < 0) $Anchos = 0;
		return($Anchos);
	}

	// Calculo margen de los fichas
	function calMargenFic(){
		// Calculo
		$Anchos = ($this->AnchoPag-($this->AnchoLabel+$this->AnchoValor))/2;
		if ($Anchos < 0) $Anchos = 0;
		return($Anchos);
	}

	//Pie de p�gina
	function Footer(){
		// si hay que mostrar pie
		if ($this->Pie){
			//Posici�n: a 1,5 cm del final
			$this->SetY(-15);
			$this->SetFont('Arial','I',8);
			$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	//Cabecera de p�gina
	function Header() {
		// si hay que mostrar cab
		if ($this->Cabezal){
			// Logo
			if (file_exists($this->empLOGO))
				$this->Image($this->empLOGO,5,5,15);
			// Empresa
			$this->setxy(28,10);
			$this->SetFont('Arial','B',14);
			$this->Cell(0,0,$this->empNOMBRE,0,1,'L');
			// Direccion
			$this->setxy(60,10);
			$this->SetFont('Arial','',10);
			$this->Cell(0,0,$this->empDATOS,0,0,'R');
			// Titulo y Subtitulo
			$this->setxy(28,15);
			$this->SetFont('Arial','B',10);
			$this->Cell(0,0,$this->Titulo,0,0,'L');
			$this->setxy(28,19);
			$this->SetFont('Arial','',8);
			$this->Cell(0,0,$this->Subtitulo,0,0,'L');
			// Usuario y fecha
			$this->setxy(60,15);
			$this->SetFont('Arial','',8);
			$this->Cell(0,0,$this->Usuario,0,0,'R');
			$this->setxy(60,19);
			$this->Cell(0,0,date("d/m/Y H:i:s"),0,0,'R');
							
			// Linea Separadora
			$this->SetLineWidth(.3);
			$this->Line(5,23,$this->w-5,23);
			//Salto de l�nea
			$this->SetY(26);
		}
	}

	function addColumna($Titulo,$Campo,$Ancho,$Alinear='L'){
		$this->Columna[] = array('titulo'=>$Titulo,'campo'=>$Campo,
								'ancho'=>$Ancho,'alinear'=>$Alinear);
	}

	function addFila($Label,$Valor){
		$this->Fila[] = array('label'=>$Label,'valor'=>$Valor);
	}

	function addSeparacion(){
		$this->Fila[] = array('label'=>' ','valor'=>' ');
	}

	function genSubTitulo($Texto){
		$this->Ln();
		$this->SetFont('Arial','B',8);
		$this->setx(5);
		$this->Cell($this->AnchoPag-10,5,$Texto,0,0,'C');
		$this->Ln();
	}

	function genFicha(){
		// Margen
		$this->Margen = $this->calMargenFic();

		// Recorro Datos
		$this->SetTextColor(0);
		$this->SetFont('','',10);
		$this->SetFillColor(215);
		while (list($Aux,$Linea) = each($this->Fila)){
		$this->setx($this->Margen);
		$this->Cell($this->AnchoLabel,5,$Linea['label'],1,0,'R',1);
		$this->MultiCell($this->AnchoValor,5,$Linea['valor'],1,'L',0);
		}
	}

	function genListado($Registros){
		//Colores, ancho de l�nea y fuente en negrita
		$this->SetFillColor(0);
		$this->SetFont('Arial','B',10);
		$this->SetTextColor(255);

		// Margen
		$this->Margen = $this->calMargenLis();

		// Cabezal
		$this->setx($this->Margen);
		reset($this->Columna);
		while (list($Indice,$Columna) = each($this->Columna))
		$this->Cell($Columna['ancho'],5,$Columna['titulo'],0,0,'C',1);
		$this->Ln();

		// Recorro Datos
		$this->SetFillColor(215);
		$this->SetTextColor(0);
		$this->SetFont('','',10);
		$Fill = 0;
		while (list($NomCampo,$Valor) = each($Registros)){
		$this->setx($this->Margen);
		reset($this->Columna);
		while (list($Indice,$Columna) = each($this->Columna))
			$this->Cell($Columna['ancho'],5,html_entity_decode($Valor[$Columna['campo']]),1,0,$Columna['alinear'],$Fill);
		// Salto Linea
		$this->Ln();
		$Fill = !$Fill;
		}
	}
	
	function genCatalogo($Registros,$CantCol,$AnchoCol,$AnchoImagen,$AltoImagen,
						$CampoImagen,$CampoDetalle1,$CampoDetalle2){
		//Colores, ancho de l�nea y fuente en negrita
		$this->SetFillColor(0);
		$this->SetFont('Arial','B',10);
		$this->SetTextColor(255);
		
		//Separacion y Tama�s
		$SeparacionX = ($this->AnchoPag-($CantCol*$AnchoCol))/($CantCol+1);
		$SeparacionY = 5;   // Separacion entre filas del cataloga
		$SeparacionD = 3;   // Separacion entre el cuadrado/imagen el detalle
		$HCelda      = 4;   // Altura de cada celda del detalle
		$AltoFila    = $AltoImagen+($HCelda*2)+$SeparacionY+$SeparacionD;			
					
		// Margen
		$this->Margen = $SeparacionX;

		// Recorro Datos
		$this->SetFillColor(215);
		$this->SetTextColor(0);
		$this->SetFont('','',8);
		$Fill = 0;
		
		// Coordenadas
		$NroCol  = 1;
		
		$Y = $this->GetY(); 
		$X = $this->Margen;           
		while (list($NomCampo,$Valor) = each($Registros)){
			// Si hay foto
			if (file_exists($Valor[$CampoImagen])){
				// Dibujo Foto
				$this->Image($Valor[$CampoImagen],$X+(($AnchoCol-$AnchoImagen)/2),$Y,$AnchoImagen);
			}
			else{
				// Dibujo cuadrado
				$this->Rect($X,$Y,$AnchoCol,$AltoImagen);
			}
						
			// Detalle
			$this->sety($Y+$AltoImagen+$SeparacionD);
			$this->setx($X);
			$this->Cell($AnchoCol,$HCelda,$Valor[$CampoDetalle1],0,0,'C',0);
			$this->sety($Y+$AltoImagen+$HCelda+$SeparacionD);
			$this->setx($X);
			$this->Cell($AnchoCol,$HCelda,$Valor[$CampoDetalle2],0,0,'C',0);				
			
			// Siguiente Columna
			$NroCol++;
			$X = $X+$AnchoCol+$SeparacionX;
		// Controlo X
		if ($NroCol > $CantCol){ 	
			$Y = $Y+$AltoFila;
			$X = $this->Margen;
			$NroCol = 1;
			}
			
			// Controlo Y
			if ($Y+$AltoFila > $this->LargoPag){
				// Agrego Hoja
				$this->AddPage();
				$Y = $this->GetY();
			} 
		}
	}             

	function fetchPDF($Archivo=''){
		$this->OutPut($Archivo);
	}

}
?>