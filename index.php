<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

/* Change to the correct path if you copy this example! */
require  './vendor/autoload.php';
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

/**
* Install the printer using USB printing support, and the "Generic / Text Only" driver,
* then share it (you can use a firewall so that it can only be seen locally).
*
* Use a WindowsPrintConnector with the share name to print.
*
* @property string $prodcutos
* @property string $total
* @property string $descuento
*/
date_default_timezone_set('america/mexico_city');

class Ticket  {
	
	public function printTicket ($data) {

		try {
			// Enter the share name for your USB printer here
			$connector = null;
			$connector = new WindowsPrintConnector("printer");

			$printer = new Printer($connector);

			# Vamos a alinear al centro lo próximo que imprimamos
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			
			/*
				Try to load and print the logo image
			*/
			try{
				$logo = EscposImage::load("logo.png", false);
				$printer->bitImage($logo);
			}   catch   (Exception $e)  {/*Logger here*/}

			$printer->feed(1);
			$printer->setTextSize(1, 1);
			$printer->text("MAFRA Boutique". "\n");			
			$printer->text("Hernández #66 Arandas, Jal". "\n");
			$printer->text("Tel: 3481953503". "\n");
			$printer->text($this->getFormatDate(). "\n");
			$printer->setTextSize(1, 1);

			/*
			Print separator
			*/

			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setTextSize(2, 2);
			$printer->setEmphasis(true);
			$printer->text("------------------------". "\n");
			$printer->setEmphasis(false);
			$printer->setTextSize(1, 1);
			$printer->feed(1);



			if ($data['backendPrint']) {
				foreach($data["productos"] as $producto) {
					
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text($producto['selectedCantidad']);
					$printer->text(" " . $producto['descripcion']);
					$printer->text(" $ " . ( $producto['precio'] * $producto['selectedCantidad'] ) . "\n");

				}
			} else {
				//try to print procducos
				foreach($data['productos'] as $producto) {
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text($producto['selectedCantidad']);
					$printer->text(" " . $producto['producto']['descripcion']);
					//$printer->setJustification(Printer::JUSTIFY_RIGHT);	
					
					if ($data["precioSelected"] == "1") {
						$printer->text(" $ " . ( $producto['producto']['precio'] * $producto['selectedCantidad'] ) . "\n");
					} else  {
						$printer->text(" $ " . ( $producto['producto']['precio1'] * $producto['selectedCantidad'] ) . "\n");
					}
				}
			}

			$printer->feed(1);
			$printer->setTextSize(2, 2);
			$printer->setEmphasis(true);
			$printer->text("------------------------". "\n");
			$printer->setEmphasis(false);
			$printer->setTextSize(1, 1);
			$printer->feed(1);
			
			//$printer->feed(5);
			$printer->setJustification(Printer::JUSTIFY_RIGHT);
			$printer->setEmphasis(false);
			$printer->setTextSize(2, 1);
			$printer->text("Descuento:  $" . $data['descuento']. " MXN\n");
			$printer->text("Total: $" . $data['total']. " MXN \n");
			
			$printer->feed(2);
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setTextSize(1, 1);
			//$printer->text("15 dias de garantia en defectos de fabrica.\n");
			$printer->text("Gracia por su compra :D.\n");
			$printer->feed(2);

			//$printer->feed(5);
			$printer->cut();
			
			/* Close printer */
			$printer->close();
		} catch (Exception $e) {
			echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
		}
	}

	static function getFormatDate() {
		$fecha = date('Y-m-d H:i:s');
		$numeroDia = date('d', strtotime($fecha));
		$dia = date('l', strtotime($fecha));
		$mes = date('F', strtotime($fecha));
		$anio = date('Y', strtotime($fecha));
		$dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
		$dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		$nombredia = str_replace($dias_EN, $dias_ES, $dia);
		$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		$nombreMes = str_replace($meses_EN, $meses_ES, $mes);

		return $nombredia . " " . $numeroDia . " " . $nombreMes . " " . $anio;
		
	}

}

//instance a new objetc
$tikect = new Ticket();

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));
$data = [];

//Attempt to decode the incoming RAW post data from Array.
parse_str($content, $data);


// Call the function to start the print process
$tikect->printTicket($data);
