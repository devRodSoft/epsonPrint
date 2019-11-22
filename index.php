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
* @property string $pelicula
* @property string $clasificacion
* @property string $duracion
* @property string $seat
* @property string $idioma
* @property string $fecha
* @property string $boleto
* @property string $codigo
* @property string $sala
* @property string $horario
*/
date_default_timezone_set('america/mexico_city');

class Ticket  {

	public $pelicula;
	public $clasificacion;
	public $duracion;
	public $seat;
	public $idioma;
	public $fecha;
	public $codigo;
	public $sala;
	public $hora;
	public $user;
	public $typoBoleto;
	public $precio; 
	public $reImprecion;

	
	public function printTicket () {

		//Set the windows connector and connect to the printer, this have to be shared! 
		$connector = new WindowsPrintConnector('cine-1');
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
	
		/*
			Print movie name
		*/
		$printer->text("". "\n");
		$printer->text("". "\n");
		$printer->setTextSize(3, 3);
		$printer->setEmphasis(true);
		$printer->text($this->pelicula. "\n");
		$printer->setEmphasis(false);		

		/* 
			print sala, time and seat
		*/
		$printer->setEmphasis(true);

		$printer->text("". "\n");		
		$printer->setTextSize(2, 2);
		$printer->text($this->sala. " | " . $this->horario  .  "\n");
		$printer->text("" . "\n");
		$printer->text($this->seat . "\n");

		$printer->setEmphasis(false);
		/*
			print Clasification, time and lenaguaje
		*/ 

		$printer->setTextSize(1, 1);
		$printer->text("" . "\n");
		$printer->text($this->clasificacion . " | " . $this->duracion  . " " .  "min" . " | " . $this->idioma . "\n");
		$printer->text($this->fecha . "\n");

		$printer->text("". "\n");
		$printer->text("Vendedor: " . $this->user . " | " . $this->typoBoleto . " " . $this->precio . "\n");	
		$printer->text("Codigo: " . $this->codigo . "\n");
		$printer->text("". "\n");
			

		/*
			Print separator
		*/
		$printer->setTextSize(2, 2);
		$printer->setEmphasis(true);
		$printer->text("------------------------". "\n");
		$printer->setEmphasis(false);
		$printer->setTextSize(1, 1);
		/* 
			print sala, time and seat to cut 
		*/ 

		$printer->text("". "\n");
		$printer->setTextSize(2, 1);
		$printer->text($this->pelicula. "\n");
		$printer->setTextSize(1, 2);
		$printer->text($this->sala. " | " . $this->horario  . " | " . $this->seat . "\n");
		
		/*
			print the movie date
		*/ 
		$printer->setTextSize(1, 1);
		$printer->text("". "\n");
		$printer->text($this->fecha . "\n");
		$printer->text("". "\n");
		$printer->text("Vendedor: " . $this->user . " | " . $this->typoBoleto . " " . $this->precio . "\n");	
		$printer->text("Codigo: " . $this->codigo . "\n");
		$printer->text("". "\n");

		if ($this->reImprecion) {
			$printer->text("Boleto Reimpreso" ."\n");
			$printer->text("". "\n");
		}
		
		/*Alimentamos el papel 3 veces*/
		$printer->feed(5);
		
		/*
			Cortamos el papel. Si nuestra impresora
			no tiene soporte para ello, no generará
			ningún error
		*/
		$printer->cut();
		
		/*
			Por medio de la impresora mandamos un pulso.
			Esto es útil cuando la tenemos conectada
			por ejemplo a un cajón
		*/
		//$printer->pulse();
		
		/*
			Para imprimir realmente, tenemos que "cerrar"
			la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
		*/
		$printer->close();
	}

	public function  getTicketData($seats) {

		$this->pelicula 	 = $seats['pelicula'];
		$this->clasificacion = $seats['clasificacion'];
		$this->duracion      = $seats['duracion'];
		$this->idioma        = $seats['idioma'];
		$this->reImprecion   = $seats['reImprecion'];
		$this->fecha         = $this->getFormatDate($seats['fecha'],$seats['reImprecion']);
		$this->sala          = $seats['sala'];
		$this->horario       = $seats['horario'];
		$this->user          = $seats['user'];
		
		foreach ($seats['seat'] as $key => $value) {
			
			$this->seat   	   = $value;
			$this->codigo      = $seats['boleto'] . $value;
			$this->typoBoleto  = $seats['precios'][$key]['nombre'];
			$this->precio      = $seats['precios'][$key]['precio'];

			$this->printTicket();
		}
	}

	static function getFormatDate($fecha, $rePrint) {

		//when do re print ticket backend send us the corrrect format so we need to do some validation to parse or not
		if (!$rePrint) {
			$fecha = substr($fecha, 0, 10);
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
		}  else {
			return $fecha;
		}
	}
}

//instance a new objetc
$tikect = new Ticket();

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

// Call the function to start the print process
$tikect->getTicketData($decoded);
