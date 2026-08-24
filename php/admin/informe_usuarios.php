<?php 

// AddPage(orientacion[PORTRAIT,LANDSCAPE], tamaño[A3,A4,A5, LETTER, LEGAL])
//SetFont(tipo[COURIER,HELVETICA,ARIAL,SYMBOL,ZAPDINGBATS],estilo[normal,B,I,U],tamaño)
//Cell(ancho,alto,bordes,?,alineacion,rellenar,link)
//OutPut(destino[I,D,F,S], nombre_archivo,utf8)
//write(tamaño,texto)
//image(ruta,posicionX,posicionY,ancho, alto,link)

require ('fpdf/fpdf.php');

require('../config/db.php');

$resultado = mysqli_query($conn,"SELECT * FROM usuarios") or die("problemas en el select".mysqli_error($conn));

$fpdf = new FPDF();

$fpdf -> AddPage('LANDSCAPE','letter');


$fpdf ->SetFont('arial','B');
$fpdf->SetY(20);
$texto = 'LISTA DE USUARIOS';
$textWidth = $fpdf -> GetStringWidth($texto);
$pageWidth = $fpdf -> GetPageWidth();
$x = ($pageWidth - $textWidth)/2;
$fpdf -> setX($x);

$fpdf -> write(7,'LISTA DE USUARIOS');
$fpdf -> ln(10);

$fpdf->SetFillcolor(258,90,180);
$fpdf ->cell(10,7,'ID',1,0,'C',TRUE);
$fpdf ->cell(60,7,'NOMBRE',1,0,'C',TRUE);
$fpdf ->cell(50,7,'EMAIL',1,0,'C',TRUE);
$fpdf ->cell(40,7,'PASSWORD',1,0,'C',TRUE);
$fpdf ->cell(20,7,'ROL',1,0,'C',TRUE);
$fpdf ->cell(30,7,'DIRECCION',1,0,'C',TRUE);
$fpdf ->cell(40,7,'CREADO',1,1,'C',TRUE);
$fpdf ->ln(0);
WHILE($fila = $resultado ->fetch_assoc()){
	$fpdf ->cell(10,10,$fila['id'],1,0,'C',FALSE);
	$fpdf ->cell(60,10,$fila['nombre'],1,0,'C',FALSE);
	$fpdf ->cell(50,10,$fila['email'],1,0,'C',FALSE);
	$fpdf ->cell(40,10,$fila['password'],1,0,'C',FALSE);
	$fpdf ->cell(20,10,$fila['rol'],1,0,'C',FALSE);
	$fpdf ->cell(30,10,$fila['direccion'],1,0,'C',FALSE);
	$fpdf ->cell(40,10,$fila['creado'],1,1,'C',FALSE);
}

$fpdf-> Output();

 ?>