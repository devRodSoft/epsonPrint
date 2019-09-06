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
class Ticket  {
	
	static function printTicket ($pelicula,$clasificacion,$duracion,$seat,$idioma,$fecha,$codigo,$sala,$hora,$user,$typoBoleto,$precio) {

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
		$printer->text($pelicula. "\n");
		$printer->setEmphasis(false);		

		/* 
			print sala, time and seat
		*/
		$printer->setEmphasis(true);

		$printer->text("". "\n");		
		$printer->setTextSize(2, 2);
		$printer->text($sala. " | " . $hora  .  "\n");
		$printer->text("" . "\n");
		$printer->text($seat . "\n");

		$printer->setEmphasis(false);
		/*
			print Clasification, time and lenaguaje
		*/ 

		$printer->setTextSize(1, 1);
		$printer->text("" . "\n");
		$printer->text($clasificacion . " | " . $duracion  . " " .  "min" . " | " . $idioma . "\n");
		$printer->text($fecha . "\n");

		$printer->text("". "\n");
		$printer->text("Vendedor: " . $user . " | " . $typoBoleto . " " . $precio . "\n");	
		$printer->text("Codigo: " . $codigo . "\n");
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
		$printer->text($pelicula. "\n");
		$printer->setTextSize(1, 2);
		$printer->text($sala. " | " . $hora  . " | " . $seat . "\n");
		

		/*
			print the movie date
		*/ 

		$printer->setTextSize(1, 1);
		$printer->text("". "\n");
		$printer->text($fecha . "\n");
		$printer->text("". "\n");
		$printer->text("Vendedor: " . $user . " | " . $typoBoleto . " " . $precio . "\n");	
		$printer->text("Codigo: " . $codigo . "\n");
		$printer->text("". "\n");
		


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
}

//instance a new objetc
$tikect = new Ticket();
//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));
 
//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);



foreach($decoded['seat'] as $key=>$value) {
	$codigo = $decoded['boleto'] . $value;
	$tikect->printTicket($decoded['pelicula'],$decoded['clasificacion'],$decoded['duracion'],$value,$decoded['idioma'],$decoded['fecha'],$codigo,$decoded['sala'],$decoded['horario'], $decoded['user'], $decoded['precios'][$key]['nombre'], $decoded['precios'][$key]['precio']);
}
