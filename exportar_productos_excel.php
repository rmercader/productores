<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

ini_set('display_errors', 'on');
ini_set("memory_limit", "128M");
include_once('./app.config.php');
include_once(DIR_BASE.'funciones_auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 
require_once DIR_BASE . 'excel/PHPExcel.php';

define('DS', DIRECTORY_SEPARATOR);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setTitle("Planilla de productos de Prolesa")
							 ->setSubject("Planilla de productos de Prolesa")
							 ->setDescription("Planilla de todos los productos de la base de datos de Prolesa.");


// Add some data
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValueByColumnAndRow(0, 1, 'Imagen')
            ->setCellValueByColumnAndRow(1, 1, 'Nombre')
            ->setCellValueByColumnAndRow(2, 1, 'Categoría')
            ->setCellValueByColumnAndRow(3, 1, 'Código SAP')
            ->setCellValueByColumnAndRow(4, 1, 'Descripción HTML');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Productos');

// Style the header
$objPHPExcel->getActiveSheet()
			->getStyle('A1:E1')
			->getFill()
			->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()
			->setARGB('FFD7E4BC');

$objPHPExcel->getActiveSheet()
			->getStyle('A1:E1')
			->getBorders()
			->getBottom()
			->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

$objPHPExcel->getActiveSheet()
			->getStyle('A1:E1')
			->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()
			->getStyle('A1:E1')
			->getFont()
			->setBold(true);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);

$interfaz = new Interfaz();
$productos = $interfaz->obtenerTodosLosProductos('nombre_categoria_producto');
$dirProductosBase = DIR_BASE . 'productos' . DS . 'fotos' . DS;
$urlProductosBase = DIR_HTTP . 'productos/fotos/';

$rowIdx = 2;
foreach ($productos as $row) {

	// Manejo de la foto para adjuntar
	$rutaImgPrv = $dirProductosBase . $row['id_producto'] . DS . '1.prv.jpg';
	if(file_exists($rutaImgPrv)){
		// Si existe foto, la inserto en la celda
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Foto ' . $row['nombre_producto']);
		$objDrawing->setPath($rutaImgPrv); 
		
		$objDrawing->setOffsetX(12);
		$objDrawing->setOffsetY(12);
		$objDrawing->setCoordinates('A' . $rowIdx);
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

		// Ajusto la altura de la fila, a la altura de la foto
		$objPHPExcel->getActiveSheet()->getRowDimension($rowIdx)->setRowHeight(ANCHO_PREVIEW_PRODUCTO - 20);
	}
	else {
		$objPHPExcel->getActiveSheet()
			->getCellByColumnAndRow(4, $rowIdx)
			->setValueExplicit('Foto no disponible', PHPExcel_Cell_DataType::TYPE_STRING);
	}
	

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowIdx, $row['nombre_producto'])
		->getStyle()
		->getAlignment()
		->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowIdx, $row['nombre_categoria_producto'])
		->getStyle()
		->getAlignment()
		->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $rowIdx)->setValueExplicit($row['codigo_sapp'], PHPExcel_Cell_DataType::TYPE_STRING)
		->getStyle()
		->getAlignment()
		->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $rowIdx)->setValueExplicit($row['descripcion'], PHPExcel_Cell_DataType::TYPE_STRING)
		->getStyle()
		->getAlignment()
		->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$rowIdx++;
}
           
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Autosize en true para todas las columnas
for($i = 1; $i <= 5; $i++){
	$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
}


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Productos.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>