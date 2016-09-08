<?php
namespace Magos\Util\Report;
use Java;
class Generate{
	protected $_connexion = null;
	protected $_crearReporte = null;
	protected $_exportaReporte = null;
	protected $_exportaExcel = null;
	protected $_parametro = null;

	public function __construct($phpbridgeversion = ''){
		try {
			defined('JAVA_HOSTS') || define("JAVA_HOSTS", \Magos\Report::getJavaHost());
			$siuBridge = \Magos\Report::getBridgeMagosVersion($phpbridgeversion);
			if(!@include_once($siuBridge)) {
				$includeError = 1;
				throw new \Exception('Servicio "Firma Comprobantes" No disponible');
			}
			$this->_connexion = new java('org.magos.JavaBridgeJdbcConnector');
			$this->_connexion->setUrl(\Magos\Report::getConexion());
			$this->_connexion->setUsername(\Magos\Report::getUser());

			$this->_connexion->setDriver(\Magos\Report::getDriver());
			$this->_connexion->setPassword(\Magos\Report::getPassword());
			$this->_crearReporte = java('net.sf.jasperreports.engine.JasperFillManager');

		} catch(JavaException $e) {
			echo 'Error: '.$e;
		} catch(\Exception $e) {
			echo 'Error Exception: '.$e;
		}
		$this->_parametro = new Java('java.util.HashMap');
	}

	public function generateReport($params, $dirJasper, $dirReporte, $ext, $adicional = array()){
		foreach($params as $reg){
			$this->_parametro->put($reg['param'], new Java($reg['tipo'], $reg['dato']));
		}
		try {
			$archivoReporte = $this->_crearReporte->fillReport($dirJasper,$this->_parametro, $this->_connexion->getConnection());
		} catch(\JavaException $e) {
			echo 'Error: '.$e; exit;
		} catch(\Exception $e) {
			echo 'Error Exception: '.$e;; exit;
		}

		switch($ext){
			case 'pdf':
				$this->_exportaReporte = java('net.sf.jasperreports.engine.JasperExportManager');
				$this->_exportaReporte->exportReportToPdfFile($archivoReporte, $dirReporte);
				break;
			case 'xls':
				$this->exportXls($dirReporte, $archivoReporte);
				break;
			case 'verpdf':
				$this->verPdf($archivoReporte);
				break;
			case 'verxls':
				$this->verXlsx($archivoReporte, $adicional);
				break;
			case 'verdoc':
				$this->verDoc($archivoReporte, $adicional);
				break;
		}
	}

	public function exportXls($dirReporte, $archivoReporte){
		$exportador = new java("net.sf.jasperreports.engine.export.JExcelApiExporter");
		$parametrosExportados = new java("net.sf.jasperreports.engine.JRExporterParameter");
		$exportadorParametrosExcel = new java("net.sf.jasperreports.engine.export.JRXlsExporterParameter");
		$exportador->setParameter($parametrosExportados->JASPER_PRINT, $archivoReporte);
		$exportador->setParameter($parametrosExportados->OUTPUT_FILE_NAME, $dirReporte);
		$exportador->setParameter($exportadorParametrosExcel->IS_ONE_PAGE_PER_SHEET, false);
		$exportador->setParameter($exportadorParametrosExcel->IS_WHITE_PAGE_BACKGROUND, false);
		$exportador->setParameter($exportadorParametrosExcel->IS_DETECT_CELL_TYPE, true);
		$exportador->exportReport();
	}

	public function verPdf($archivoReporte){
		try {
			// JRPdfExporter pdfExporter = new JRPdfExporter();
			java_set_file_encoding("UTF-8");
			$javaOutputStream = new java("java.io.ByteArrayOutputStream");
			$pdfExporter = new java("net.sf.jasperreports.engine.export.JRPdfExporter");
			// pdfExporter.setExporterInput(new SimpleExporterInput(PdfPrint));
			$simpleExporterInput = new java('net.sf.jasperreports.export.SimpleExporterInput', $archivoReporte);
			$pdfExporter->setExporterInput($simpleExporterInput);
			// pdfExporter.setExporterOutput(new SimpleOutputStreamExporterOutput(outPdfName));
			$simpleOutputStreamExporterOutput = new java('net.sf.jasperreports.export.SimpleOutputStreamExporterOutput', $javaOutputStream);
			$pdfExporter->setExporterOutput($simpleOutputStreamExporterOutput);
			// SimplePdfExporterConfiguration configuration = new SimplePdfExporterConfiguration();
			$configuration = new java('net.sf.jasperreports.export.SimplePdfExporterConfiguration');
			// configuration.setCreatingBatchModeBookmarks(true);
			$configuration->setCreatingBatchModeBookmarks(true);
			// pdfExporter.setConfiguration(configuration);
			$pdfExporter->setConfiguration($configuration);
			// pdfExporter.exportReport();
			$pdfExporter->exportReport();

			header('Content-Type: application/pdf');
			header('Content-disposition: inline; filename="'.date().'.pdf"');
			//header("Cache-Control: no-store, no-cache, must-revalidate");
			//header("Cache-Control: post-check=0, pre-check=0", false);
			//header("Pragma: no-cache"); header("Expires: 0");
			header("Cache-control: private");
			echo java_cast($javaOutputStream->toByteArray(),"S");
		} catch(\JavaException $e) {
			echo 'Error: '.$e; exit;
		} catch(\Exception $e) {
			echo 'Error Exception: '.$e;; exit;
		}
	}

	public function verXlsx($archivoReporte,$adicional){
		java_set_file_encoding("UTF-8");
		if (is_array($adicional)) {
			if (array_key_exists('outputfile', $adicional)) {
				$outputFile = $adicional['outputfile'];
			} else {
				$outputFile = 'tmpfile_'.time();
			}
		} else {
			$outputFile = 'tmpfile_'.time();
		}
		$outputFile =  \Magos\Report::getHttpdTmp().DIRECTORY_SEPARATOR.$outputFile.'.xlsx';
		// JRXlsExporter xlsExporter = new JRXlsExporter();
		$xlsExporter = new java('net.sf.jasperreports.engine.export.ooxml.JRXlsxExporter');
		// xlsExporter.setExporterInput(new SimpleExporterInput(xlsPrint));
		$simpleExporterInput = new java('net.sf.jasperreports.export.SimpleExporterInput', $archivoReporte);
		$xlsExporter->setExporterInput($simpleExporterInput);
		// xlsExporter.setExporterOutput(new SimpleOutputStreamExporterOutput(outXlsName));
		$simpleOutputStreamExporterOutput = new java('net.sf.jasperreports.export.SimpleOutputStreamExporterOutput', $outputFile);
		$xlsExporter->setExporterOutput($simpleOutputStreamExporterOutput);
		// SimpleXlsReportConfiguration xlsReportConfiguration = new SimpleXlsReportConfiguration();
		$xlsReportConfiguration = new java('net.sf.jasperreports.export.SimpleXlsxReportConfiguration');
		// xlsReportConfiguration.setOnePagePerSheet(false);
		$xlsReportConfiguration->setOnePagePerSheet(false);
		// xlsReportConfiguration.setRemoveEmptySpaceBetweenRows(true);
		$xlsReportConfiguration->setRemoveEmptySpaceBetweenRows(true);
		// xlsReportConfiguration.setDetectCellType(false);
		$xlsReportConfiguration->setDetectCellType(false);
		// xlsReportConfiguration.setWhitePageBackground(false);
		$xlsReportConfiguration->setWhitePageBackground(false);
		// xlsExporter.setConfiguration(xlsReportConfiguration);
		$xlsExporter->setConfiguration($xlsReportConfiguration);
		// xlsExporter.exportReport();
		$xlsExporter->exportReport();

		$nombre = date('d-m-Y').'_' . $adicional['name'] . '.xlsx';
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($outputFile));
		readfile($outputFile);
		unlink($outputFile);
		exit;
	}
	public function verDoc($archivoReporte,$adicional){
		java_set_file_encoding("UTF-8");
		if (is_array($adicional)) {
			if (array_key_exists('outputfile', $adicional)) {
				$outputFile = $adicional['outputfile'];
			} else {
				$outputFile = 'tmpfile_'.time();
			}
		} else {
			$outputFile = 'tmpfile_'.time();
		}
		$outputFile =  \Magos\Report::getHttpdTmp().DIRECTORY_SEPARATOR.$outputFile.'.xlsx';
		// JRXlsExporter docExporter = new JRXlsExporter();
		$docExporter = new java('net.sf.jasperreports.engine.export.ooxml.JRDocxExporter');
		// docExporter.setExporterInput(new SimpleExporterInput(xlsPrint));
		$simpleExporterInput = new java('net.sf.jasperreports.export.SimpleExporterInput', $archivoReporte);
		$docExporter->setExporterInput($simpleExporterInput);
		// docExporter.setExporterOutput(new SimpleOutputStreamExporterOutput(outXlsName));
		$simpleOutputStreamExporterOutput = new java('net.sf.jasperreports.export.SimpleOutputStreamExporterOutput', $outputFile);
		$docExporter->setExporterOutput($simpleOutputStreamExporterOutput);
		// docExporter.exportReport();
		$docExporter->exportReport();

		$nombre = date('d-m-Y').'_' . $adicional['name'] . '.docx';
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($outputFile));
		readfile($outputFile);
		unlink($outputFile);
		exit;
	}
}
