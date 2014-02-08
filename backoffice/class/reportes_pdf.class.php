<?php

// Hay que hacer un include_once() del archivo fpdf.php, donde este este ubicado
// Por ej: include_once('fpdf.php'); // si el archivo fpdf.php esta en el mismo 
// directorio que el archivo que contiene esta clase (reportes_pdf.class.php)

class ReportesPdf extends FPDF
{
	var $Columnas;
	var $Filas;
	var $Margen;
	var $Cabezal;
	var $Pie;
	var $AnchoPag;
	var $LargoPag;
	var $AnchoLabel;
	var $AnchoValor;
	var $myFontSize;
	var $myFontFamily;
	var $myLineHeight;

	// Constructor
	function ReportesPdf($Posicion='P',$Tamanio='A4', $Cabezal=True, $Pie=True){
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
		$this->Margen      = -1;
		$this->Cabezal     = $Cabezal;
		$this->Pie         = $Pie;
		$this->Columnas    = array();
		$this->Filas       = array();
		$this->AnchoLabel = 0;
		$this->AnchoValor = 0;
		$this->myFontSize = 10;
		$this->myFontFamily = 'Arial';
		$this->myLineHeight = 5;
	}
	
	// $Titulo es el titulo de la columna en la tabla
	// $Campo es el nombre del campo en la consulta correspondiente a la columna (nombre_campo_i)
	// $Ancho es el ancho en MILIMETROS para la columna
	// $Alinear es la alineacion del texto dentro de las celdas de la columna, valores posibles:
	// 'L' alinea a la izquierda
	// 'C' alinea al centro
	// 'R' alinea a la derecha
	// Valor por defecto L
	function AddColumna($Titulo, $Campo, $Ancho, $Alinear='L'){
		$this->Columna[] = array('titulo'=>$Titulo, 'campo'=>$Campo, 'ancho'=>$Ancho, 'alinear'=>$Alinear);
	}

	function AddFila($Label,$Valor){
		$this->Fila[] = array('label'=>$Label, 'valor'=>$Valor);
	}

	function addSeparacion(){
		$this->Fila[] = array('label'=>' ', 'valor'=>' ');
	}
	
	function fetchPDF($Archivo='', $modo=''){
		$this->OutPut($Archivo, $modo);
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
	function CalMargenLis(){
		// Calculo Margen
		$Anchos = 0;
		reset($this->Columna);
		while (list($Indice,$Columna) = each($this->Columna))
			$Anchos = $Anchos+$Columna['ancho'];
		$Anchos = ($this->AnchoPag-$Anchos)/2;
		if ($Anchos < 0) $Anchos = 0;
		return($Anchos);
	}
	
	// El formato de $Registros debe ser el siguiente:
	// Una lista de las filas del resultado de la consulta, basicamente en PHP:
	// array('nombre_campo_1'=>VALOR_CAMPO_1, 'nombre_campo_2'=>VALOR_CAMPO_2, ..., 'nombre_campo_N'=>VALOR_CAMPO_N)
	// en donde nombre_campo_i es el nombre de la columna de la consulta, y VALOR_CAMPO_i su correspondiente valor para la fila
	function GenerarListado($Registros){
		//Colores, ancho de linea y fuente en negrita
		$this->SetFillColor(0);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(255);

		// Margen
		$this->Margen = $this->CalMargenLis();

		// Cabezal
		$this->setx($this->Margen);
		reset($this->Columna);
		while (list($Indice,$Columna) = each($this->Columna)){
			$this->Cell($Columna['ancho'], 5, $Columna['titulo'], 0, 0, 'C', 1);
		}
		
		$this->Ln();

		// Recorro Datos
		$this->SetFillColor(215);
		$this->SetTextColor(0);
		$this->SetFont('', '', 10);
		$Fill = 0;
		while (list($NomCampo, $Valor) = each($Registros)){
			$this->setx($this->Margen);
			reset($this->Columna);
			while (list($Indice, $Columna) = each($this->Columna))
				$this->Cell($Columna['ancho'], 5, html_entity_decode($Valor[$Columna['campo']]), 1, 0, $Columna['alinear'], $Fill);
			// Salto Linea
			$this->Ln();
			$Fill = !$Fill;
		}
	}
	
	//Colored table
	function FancyTable($header,$data)
	{
			//Colors, line width and bold font
			$this->SetFillColor(255,0,0);
			$this->SetTextColor(255);
			$this->SetDrawColor(128,0,0);
			$this->SetLineWidth(.3);
			$this->SetFont('','B');
			//Header
			$w=array(40,35,40,45);
			for($i=0;$i<count($header);$i++)
					$this->Cell($w[$i],7,$header[$i],1,0,'C',true);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			//Data
			$fill=false;
			foreach($data as $row)
			{
					$this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
					$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
					$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
					$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
					$this->Ln();
					$fill=!$fill;
			}
			$this->Cell(array_sum($w),0,'','T');
	}
	
	function ExportarOrdenCarga($id_orden_carga, $id_perfil=PERFIL_CLIENTE){
		// Recupero los datos...
		$Cnx = nyiCnx();
		$sqlCampos = "o.*, c.nombre_cliente, c.ruc, c.direccion, c.telefono, ld.nombre_lugar_documentos, ed.nombre_estado_despacho";
		$sqlCampos .= ", ds.nombre_lugar_carga_descarga AS salida, dd.nombre_lugar_carga_descarga AS destino, e.nombre_estado_orden_carga, t.nombre_tipo_mercaderia, s.nombre_tipo_seguro";
		
		$sqlFrom = "orden_carga o INNER JOIN cliente c ON c.id_cliente = o.id_cliente";
		$sqlFrom .= " INNER JOIN lugar_documentos ld ON ld.id_lugar_documentos = o.id_lugar_documentos";
		$sqlFrom .= " INNER JOIN estado_despacho ed ON ed.id_estado_despacho = o.id_estado_despacho";
		$sqlFrom .= " INNER JOIN lugar_carga_descarga ds ON ds.id_lugar_carga_descarga = o.id_lugar_carga";
		$sqlFrom .= " INNER JOIN lugar_carga_descarga dd ON dd.id_lugar_carga_descarga = o.id_lugar_descarga";
		$sqlFrom .= " INNER JOIN estado_orden_carga e ON e.id_estado_orden_carga = o.id_estado_orden_carga";
		$sqlFrom .= " INNER JOIN tipo_mercaderia t ON t.id_tipo_mercaderia = o.id_tipo_mercaderia";
		$sqlFrom .= " INNER JOIN tipo_seguro s ON s.id_tipo_seguro = o.mercaderia_id_tipo_seguro";
		
		$sqlWhere = "id_orden_carga = $id_orden_carga";
		
		$Orden = $Cnx->execute("SELECT $sqlCampos FROM $sqlFrom WHERE $sqlWhere");
		if(!$Orden->EOF){
			// Margen
			$this->Margen = 10;
			$this->setx($this->Margen);
			$campos = $Orden->fields;
			
			// Recorro Datos
			$this->WriteBold(utf8_decode("Número de orden:  "));
			$this->Write($this->myLineHeight, $campos['id_orden_carga']);
			$this->Ln();
			$this->WriteBold(utf8_decode("Fecha de creación:  "));
			$this->Write($this->myLineHeight, FormatDateLong($campos['fecha_creacion']));
			$this->Ln();
			$this->WriteBold(utf8_decode("Urgente:  "));
			$this->Write($this->myLineHeight, $campos['urgente'] ? _SIN : _NON);
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Fecha de entrega:  "));
			$fecha = preg_split('/\s/', FormatDateLong($campos['fecha_pedido']));
			$fecha = $fecha[0];
			$this->Write($this->myLineHeight, $fecha);
			
			$this->Ln(10);
			// Cliente
			$this->WriteBold("DATOS DEL CLIENTE");
			$this->Ln();
			$this->Line($this->GetX(), $this->GetY(), ($this->GetX() + $this->AnchoPag -20), $this->GetY());
			$this->Ln(5);
			$this->WriteBold("Identificador:  ");
			$this->Write($this->myLineHeight, $campos['id_cliente']);
			$this->Ln();
			$this->WriteBold(utf8_decode("Número de referencia:  "));
			$this->Write($this->myLineHeight, $campos['numero_referencia']);
			$this->Ln();
			$this->WriteBold("Nombre:  ");
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['nombre_cliente'])));
			$this->Ln();
			$this->WriteBold(utf8_decode("Número de RUC:  "));
			$this->Write($this->myLineHeight, $campos['ruc']);
			$this->Ln();
			$this->WriteBold(utf8_decode("Dirección:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['direccion'])));
			$this->Ln();
			$this->WriteBold(utf8_decode("Teléfono:  "));
			$this->Write($this->myLineHeight, $campos['telefono']);
			$this->Ln();
			$this->Line($this->GetX(), $this->GetY(), ($this->GetX() + $this->AnchoPag -20), $this->GetY());
			$this->Ln(5);
			
			// Siguen los datos de la orden
			$this->WriteBold("Estado del despacho:  ");
			$this->Write($this->myLineHeight, $campos['nombre_estado_despacho']);
			$this->Ln();
			
			$this->WriteBold("Estado del pedido:  ");
			$this->Write($this->myLineHeight, $campos['nombre_estado_pedido']);
			$this->Ln();
			
			$this->WriteBold("Lugar de salida:  ");
			$lugar_salida = $campos['id_lugar_carga'] == OTRO_LUGAR_CARGA_DESCARGA ? $campos['otro_lugar_carga'] : $campos['salida'];
			$this->Write($this->myLineHeight, $lugar_salida);
			$this->Ln();
			
			$this->WriteBold("Lugar de destino:  ");
			$lugar_destino = $campos['id_lugar_descarga'] == OTRO_LUGAR_CARGA_DESCARGA ? $campos['otro_lugar_descarga'] : $campos['destino'];
			$this->Write($this->myLineHeight, $lugar_destino);
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Recepción de documentos:  "));
			$lugar_documentos = $campos['id_lugar_documentos'] == OTRO_LUGAR_DOCUMENTOS ? $campos['otro_lugar_documentos'] : $campos['nombre_lugar_documentos'];
			$this->Write($this->myLineHeight, $lugar_documentos);
			$this->Ln();
			
			$this->WriteBold("MAWB:  ");
			$this->Write($this->myLineHeight, $campos['mawb']);
			$this->Ln();
			
			$this->WriteBold("HAWB:  ");
			$this->Write($this->myLineHeight, $campos['hawb']);
			$this->Ln();
			
			$this->WriteBold("DUA:  ");
			$this->Write($this->myLineHeight, $campos['dua']);
			$this->Ln(10);
			
			// Tipo de mercaderia
			$this->WriteBold("INFORMACION DE LA MERCADERIA");
			$this->Ln();
			$this->Line($this->GetX(), $this->GetY(), ($this->GetX() + $this->AnchoPag -20), $this->GetY());
			$this->Ln(5);
			
			$this->WriteBold(utf8_decode("Tipo de mercadería:  "));
			$this->Write($this->myLineHeight, $campos['nombre_tipo_mercaderia']);
			$this->Ln();
			switch($campos['id_tipo_mercaderia']){
				case CONTENEDOR_20_PIES:
					$extra_alto = $Cnx->execute("SELECT * FROM contenedor20_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					break;
					
				case CONTENEDOR_40_PIES:
					$extra_alto = $Cnx->execute("SELECT * FROM contenedor40_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					break;
				
				case FLAT_20_PIES:
					$extra_alto = $Cnx->execute("SELECT * FROM flat20_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM flat20_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case FLAT_40_PIES:
					$extra_alto = $Cnx->execute("SELECT * FROM flat40_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM flat40_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case PLATAFORMAS:
					$extra_alto = $Cnx->execute("SELECT * FROM plataforma_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM plataforma_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case CAJAS:
					$extra_alto = $Cnx->execute("SELECT * FROM caja_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM caja_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case RACKS:
					$extra_alto = $Cnx->execute("SELECT * FROM rack_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM rack_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case ESQUELETOS:
					$extra_alto = $Cnx->execute("SELECT * FROM esqueleto_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM esqueleto_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case BULTOS:
					$extra_alto = $Cnx->execute("SELECT * FROM bulto_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM bulto_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case OTRO_TIPO_MERCADERIA:
					$this->WriteBold("Tipo: ");
					$this->Write($this->myLineHeight, $campos['otro_tipo_mercaderia']);
					$this->Ln();
					$extra_alto = $Cnx->execute("SELECT * FROM otro_tipo_mercaderia_extra_alto WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['altura']);
						$this->Ln();
					}
					$extra_ancho = $Cnx->execute("SELECT * FROM otro_tipo_mercaderia_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					// Hay que ver si es extra-alto
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					break;
					
				case MAQUINARIA:
					$tipo = $Cnx->execute("SELECT * FROM maquinaria_tipo WHERE id_orden_carga = $id_orden_carga");
					$extra_alto = $Cnx->execute("SELECT * FROM maquinaria_extra_alto WHERE id_orden_carga = $id_orden_carga");
					$extra_ancho = $Cnx->execute("SELECT * FROM maquinaria_extra_ancho WHERE id_orden_carga = $id_orden_carga");
					
					if(!$tipo->EOF){
						$this->WriteBold("Tipo: ");
						$this->Write($this->myLineHeight, $tipo->fields['descripcion_tipo_maquinaria']);
						$this->Ln();
					}
					
					// Hay que ver si es extra-alto
					if(!$extra_alto->EOF){
						$this->WriteBold("Extra alto - ");
						$this->WriteBold("Altura:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['alto']);
						$this->WriteBold(" Largo:  ");
						$this->Write($this->myLineHeight, $extra_alto->fields['largo']);
						$this->Ln();
					}
										
					// Hay que ver si es extra-ancho
					if(!$extra_ancho->EOF){
						$this->WriteBold("Extra ancho - ");
						$this->WriteBold("Ancho:  ");
						$this->Write($this->myLineHeight, $extra_ancho->fields['ancho']);
						$this->Ln();
					}
					
					break;
			}
			
			$this->WriteBold("Cantidad:  ");
			$this->Write($this->myLineHeight, $campos['mercaderia_cantidad']);
			$this->Ln();
			
			$this->WriteBold("Peso:  ");
			$this->Write($this->myLineHeight, $campos['mercaderia_kilos']." kilos");
			$this->Ln();
			
			$this->WriteBold("Volumen:  ");
			$this->Write($this->myLineHeight, $campos['mercaderia_volumen'].utf8_decode(" m³"));
			$this->Ln();
			
			$this->WriteBold("Valor:  ");
			$this->Write($this->myLineHeight, $campos['mercaderia_valor_usd']." USD");
			$this->Ln();
			
			$this->WriteBold("Tipo de seguro:  ");
			$this->Write($this->myLineHeight, $campos['mercaderia_id_tipo_seguro'] == OTRO_TIPO_SEGURO ? $campos['mercaderia_otro_tipo_seguro'] : $campos['nombre_tipo_seguro']);
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Numeración de las cajas:  "));
			$this->Ln();
			
			$numCajas = $Cnx->execute("SELECT numero_caja FROM caja_orden_carga WHERE id_orden_carga = $id_orden_carga ORDER BY numero_caja");
			while(!$numCajas->EOF){
				$this->Write($this->myLineHeight, $numCajas->fields['numero_caja']);
				$this->Ln();
				
				$numCajas->MoveNext();
			}
			
			$this->Line($this->GetX(), $this->GetY(), ($this->GetX() + $this->AnchoPag -20), $this->GetY());
			$this->Ln(5);
			
			// Informacion administrativa
			$this->WriteBold("INFORMACION ADMINISTRATIVA");
			$this->Ln();
			$this->Line($this->GetX(), $this->GetY(), ($this->GetX() + $this->AnchoPag -20), $this->GetY());
			$this->Ln(5);
			
			$this->WriteBold(utf8_decode("Matrícula del camión:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['matricula_camion'])));
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Conductor del camión:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['nombre_conductor'])));
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Número de remito:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['numero_remito'])));
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Número de factura:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['numero_factura'])));
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Número de recibo:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['numero_recibo'])));
			$this->Ln();
			
			$this->WriteBold(utf8_decode("Importe total:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['importe'])));
			$this->Ln();
			
			if($id_perfil == PERFIL_ADMINISTRADOR){
				$this->WriteBold(utf8_decode("Horas insumidas:  "));
				$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['horas_insumidas'])));
				$this->Ln();
				
				$this->WriteBold(utf8_decode("Gasto de combustible:  "));
				$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['gasto_combustible'])));
				$this->Ln();
			}
			
			$this->Line($this->GetX(), $this->GetY(), ($this->GetX() + $this->AnchoPag -20), $this->GetY());
			$this->Ln(5);
			
			$this->WriteBold(utf8_decode("Observaciones/Información adicional:  "));
			$this->Write($this->myLineHeight, utf8_decode(html_entity_decode($campos['observaciones'])));
			$this->Ln();
			
			// Emitimos el archivo
			$this->fetchPDF("OrdenCargaNro_$id_orden_carga", 'D');
		}
	}
	
	function SetFontDefault(){
		$this->SetFont($this->myFontFamily, '', $this->myFontSize);
	}
	
	function WriteBold($texto, $leave_default=true){
		$this->SetFont($this->myFontFamily, 'B', $this->myFontSize);
		$this->Write($this->myLineHeight, $texto);
		if($leave_default){
			$this->SetFontDefault();
		}
	}
	
	function inicio(){
		// Comienzo archivo
		$this->Open();
		$this->AliasNbPages();
		$this->SetAutoPageBreak(true,15);
		$this->AddPage();
	}
}
