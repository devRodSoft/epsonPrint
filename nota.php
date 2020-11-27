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
			$connector = new WindowsPrintConnector("rodsoft");

			$printer = new Printer($connector);

			# Vamos a alinear al centro lo próximo que imprimamos
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			
			/*
				Try to load and print the logo image
			*/
			try{
				$logo = EscposImage::load("./images/logo.png", false);
				$printer->bitImage($logo);
			}   catch   (Exception $e)  {/*Logger here*/}


			$printer->feed(2);
			$printer->text($this->getFormatDate(). "\n");
			$printer->feed(2);

			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setEmphasis(true);
			$printer->text("Cliente: ");
			$printer->setEmphasis(false);
			$printer->text($data->name . "\n");
			$printer->setEmphasis(true);
			$printer->text("Telefono: ");
			$printer->setEmphasis(false);
			$printer->text($data->celphone . "\n");


			/*
			Print separator
			*/

			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setTextSize(2, 2);
			$printer->setEmphasis(true);
			$printer->text("________________________". "\n");
			$printer->setEmphasis(false);
			$printer->setTextSize(1, 1);
			$printer->feed(1);

			$printer -> setEmphasis(true);
			$printer -> text(new item('', '$'));
			$printer -> setEmphasis(false);
			$printer->feed(1);
			foreach($data->products as $producto) {
				$printer -> text(new Item($producto->qty . " " . $producto->desc, $producto->price . ".00"));
			}

			/* Footer */
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setTextSize(2, 2);
			$printer->setEmphasis(true);
			$printer->text("________________________". "\n");
			$printer->setEmphasis(false);
			$printer->setTextSize(1, 1);
			$printer->feed(1);
			$printer -> setEmphasis(true);
			$printer -> text(new item('', '$'));
			$printer -> setEmphasis(false);
			
			$printer -> setJustification(Printer::JUSTIFY_LEFT);
			$printer -> text(new Item('Total', $data->total . ".00"));
			//$printer->feed(1);
			$printer -> text(new Item('Anticipo', $data->anticipo . ".00"));
			//$printer->feed(1);
			$printer -> text(new Item('Restante', $data->restante . ".00"));
						
			$printer->feed(2);
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setTextSize(1, 1);
			$printer->text("INVITACIONES - IMPRESION DE PLANOS Y DOC. \n PUBLICIDAD - LONAS - DETALLES PERSONALIZADOS - Y MAS.\n");
			
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


class Item
{
    private $name;
    private $price;
    private $dollarSign;

    public function __construct($name = '', $price = '', $dollarSign = false)
    {
        $this -> name = $name;
        $this -> price = $price;
        $this -> dollarSign = $dollarSign;
    }

    public function __toString()
    {
        $rightCols = 10;
        $leftCols = 38;
        if ($this -> dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this -> name, $leftCols) ;

        $sign = ($this -> dollarSign ? '$ ' : '');
        $right = str_pad($sign . $this -> price, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}

//instance a new objetc
$tikect = new Ticket();

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));
$data;

//Attempt to decode the incoming RAW post data from Array.
parse_str($content, $data);

$data = json_decode($content);



// Call the function to start the print process
$tikect->printTicket($data);
