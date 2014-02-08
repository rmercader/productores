<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion_inicial.php');
include(DIR_BASE.'clientes/cliente.class.php');
require_once './excel/PHPExcel.php';

/*
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);*/
ini_set("memory_limit", "64M");

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setTitle("Planilla de clientes de Prolesa")
							 ->setSubject("Planilla de clientes de Prolesa")
							 ->setDescription("Planilla de todos los clientes de la base de datos de Prolesa.");


// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow(0, 1, 'Nombre')
            ->setCellValueByColumnAndRow(1, 1, 'Apellido')
            ->setCellValueByColumnAndRow(2, 1, 'Matrícula')
            ->setCellValueByColumnAndRow(3, 1, 'Email')
            ->setCellValueByColumnAndRow(4, 1, 'Celular')
            ->setCellValueByColumnAndRow(5, 1, 'Teléfono fijo')
            ->setCellValueByColumnAndRow(6, 1, 'Dirección')
            ->setCellValueByColumnAndRow(7, 1, 'Departamento')
            ->setCellValueByColumnAndRow(8, 1, 'Sucursal')
            ->setCellValueByColumnAndRow(9, 1, 'Técnico de referencia')
            ->setCellValueByColumnAndRow(10, 1, 'Grupo económico')
            ->setCellValueByColumnAndRow(11, 1, 'Fecha registrado')
            ->setCellValueByColumnAndRow(12, 1, 'Admitido')
            ->setCellValueByColumnAndRow(13, 1, 'Fecha de admisión')
            ->setCellValueByColumnAndRow(14, 1, 'Activo');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Clientes');

// Style the header
$objPHPExcel->getActiveSheet()
			->getStyle('A1:O1')
			->getFill()
			->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()
			->setARGB('FFD7E4BC');

$objPHPExcel->getActiveSheet()
			->getStyle('A1:O1')
			->getBorders()
			->getBottom()
			->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

$objPHPExcel->getActiveSheet()
			->getStyle('A1:O1')
			->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()
			->getStyle('A1:O1')
			->getFont()
			->setBold(true);

for($i = 0; $i <= 14; $i++){
	$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// We finished with the header, now it's time to bring the data
$objClientes = new Cliente($Cnx);
$datosClientes = $objClientes->obtenerParaExcel();

$rowIdx = 2;
foreach ($datosClientes as $row) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowIdx, $row['nombre']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowIdx, $row['apellido']);
	$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $rowIdx)->setValueExplicit($row['matricula'], PHPExcel_Cell_DataType::TYPE_STRING);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowIdx, $row['email']);
	$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $rowIdx)->setValueExplicit($row['celular'], PHPExcel_Cell_DataType::TYPE_STRING);
	$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5, $rowIdx)->setValueExplicit($row['telefono'], PHPExcel_Cell_DataType::TYPE_STRING);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowIdx, $row['direccion']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowIdx, $row['nombre_departamento']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowIdx, $row['nombre_sucursal']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowIdx, $row['tecnico']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $rowIdx, $row['grupo_economico']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $rowIdx, $row['fecha_registro']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowIdx, $row['admitido']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowIdx, $row['fecha_admision']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowIdx, $row['activo']);
	$rowIdx++;
}
           
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Clientes.xls"');
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