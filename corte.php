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
				$logo = EscposImage::load("logo.png", false);
				$printer->bitImage($logo);
			}   catch   (Exception $e)  {/*Logger here*/}

			$printer->feed(1);
			$printer->setTextSize(1, 1);
			$printer->text("Punto de Venta". "\n");			
			$printer->text("Rodsoft Arandas, Jal". "\n");
			$printer->text("Tel: 3481246642". "\n");
			$printer->text($this->getFormatDate(). "\n");
			$printer->setTextSize(1, 1);

			/*
			Print separator
			*/
			$printer->feed(1);
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setTextSize(2, 2);
			$printer->text('Corte de Caja' . "\n");
			$printer->feed(2);

			$printer->setTextSize(1, 1);
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setEmphasis(true);
			$printer->text('Productos vendidos' . "\n");
			$printer->setEmphasis(false);
			$printer->feed(1);
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			
			
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(true);
			$printer->text(new item('QTY Producto', '$'));
			$printer->text("________________________________________________". "\n");
			$printer->text("". "\n");
			$printer->setEmphasis(false);

			foreach($data["productos"] as $producto) {
				$printer -> text(new Item($producto['selectedCantidad'] . " " . $producto['descripcion'], ( $producto['precio'] * $producto['selectedCantidad'] )  . ".00"));
			}

			$printer->feed(2);
			$printer->setTextSize(1, 1);
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setEmphasis(true);
			$printer->text('Productos cancelados' . "\n");
			$printer->setEmphasis(false);
			$printer->feed(1);
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(true);
			$printer->text(new item('QTY Producto', '$'));
			$printer->text("________________________________________________". "\n");
			$printer->text("". "\n");
			$printer->setEmphasis(false);
			foreach($data["productosCancel"] as $producto) {
				$printer -> text(new Item($producto['selectedCantidad'] . " " . $producto['descripcion'], ( $producto['precio'] * $producto['selectedCantidad'] )  . ".00"));
			}

			$printer->feed(2);
			$printer->setTextSize(1, 1);
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setEmphasis(true);
			$printer->text('Detalle de ventas' . "\n");
			$printer->setEmphasis(false);
			$printer->feed(2);


			$printer->setEmphasis(true);
			$printer->text("Caja Id: ");
			$printer->setEmphasis(false);
			$printer->text($data['id'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Sucursal: ");
			$printer->setEmphasis(false);
			$printer->text($data['sucursal'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Vendedor: ");
			$printer->setEmphasis(false);
			$printer->text($data['empleado'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Saldo Inicial:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['saldoInitial'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Ventas Efectivo:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['saldoInitial'] . "\n");
				
			$printer->setEmphasis(true);
			$printer->text("Abonos Efectivo:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['ventasAbonosEfectivo'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Ventas Tarjeta:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['ventasTargeta'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Abonos Tarjeta:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['abonosTargeta'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Salidas:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['salidas'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Saldo Final:");
			$printer->setEmphasis(false);
			$printer->text(" $ " . $data['saldoFinal'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Apertura: ");
			$printer->setEmphasis(false);
			$printer->text($data['apertura'] . "\n");

			$printer->setEmphasis(true);
			$printer->text("Cierre: ");
			$printer->setEmphasis(false);
			$printer->text($data['cierre'] . "\n");
			$printer->feed(3);


			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->setTextSize(2, 2);
			$printer->setEmphasis(true);
			$printer->text("__________    __________". "\n");
			
			$printer->setEmphasis(false);
			$printer->setTextSize(1, 1);
			$printer->feed(1);

			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setEmphasis(true);
			$printer->text("       Entrega                   Recibe ". "\n");
			$printer->setEmphasis(false);
			$printer->feed(1);

			$printer->feed(5);
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
$data = [];

//Attempt to decode the incoming RAW post data from Array.
parse_str($content, $data);



// Call the function to start the print process
$tikect->printTicket($data);
