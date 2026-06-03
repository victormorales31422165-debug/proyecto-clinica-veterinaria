<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../vendor/autoload.php';
require_once '../config/db.php';

// IMPORTANTE: FPDF suele estar en el espacio global, no se usa "use FPDF\FPDF;"
// Si usas el paquete setasign/fpdf, simplemente llamamos a \FPDF

$id_cita = (int)$_GET['id'];

// Consultar datos completos
$query = "SELECT c.*, m.nombre as mascota, m.especie, m.raza, m.edad, 
          cl.nombre as dueño, cl.apellido, u.nombre as vet_nom, u.especialidad as vet_esp
          FROM cita c 
          JOIN mascota m ON c.id_mascota = m.id_mascota 
          JOIN cliente cl ON m.id_cliente = cl.id_cliente
          JOIN usuario u ON c.id_veterinario = u.id_veterinario
          WHERE c.id_cita = $id_cita";
$res = mysqli_query($con, $query);
$d = mysqli_fetch_assoc($res);

if (!$d) { die("Cita no encontrada."); }

// --- CLASE PERSONALIZADA PARA HEADER Y FOOTER ---
// Heredamos de \FPDF (clase global)
class PDF extends \FPDF {
    protected $vet_nom;
    protected $vet_esp;

    public function setVetData($nom, $esp) {
        $this->vet_nom = $nom;
        $this->vet_esp = $esp;
    }

    function Header() {
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(25, 135, 84); // Verde El Colibrí
        $this->Cell(0, 10, utf8_decode('CLÍNICA VETERINARIA EL COLIBRÍ'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100);
        $this->Cell(0, 5, utf8_decode('Atención Médica Profesional para tu Mascota'), 0, 1, 'C');
        
        $this->Ln(5);
        $this->SetDrawColor(25, 135, 84);
        $this->SetLineWidth(0.8);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer() {
        // POSICIÓN FIJA AL FINAL DE LA PÁGINA (a 3.5 cm del final)
        $this->SetY(-35);
        $this->SetDrawColor(200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
        
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(50);
        $this->Cell(0, 6, utf8_decode('Atendido por: Dr. ' . $this->vet_nom), 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, utf8_decode($this->vet_esp), 0, 1, 'C');
        
        $this->Ln(5);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(150);
        $this->Cell(0, 5, utf8_decode('© 2026 Clínica Veterinaria El Colibrí'), 0, 0, 'C');
    }
}

// --- GENERACIÓN DEL DOCUMENTO ---
$pdf = new PDF();
$pdf->setVetData($d['vet_nom'], $d['vet_esp']);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// 1. Título sin el número #
$pdf->SetFillColor(245, 245, 245);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(68, 68, 68);
$pdf->Cell(0, 12, utf8_decode('INFORME DE ATENCIÓN MÉDICA'), 0, 1, 'C', true);
$pdf->Ln(10);

// Cuadrícula de Datos
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(25, 135, 84);
$pdf->Cell(95, 7, utf8_decode('DATOS DEL PACIENTE'), 0, 0);
$pdf->Cell(95, 7, utf8_decode('DATOS DEL PROPIETARIO'), 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(50);
$pdf->Cell(95, 6, utf8_decode('Nombre: ' . $d['mascota']), 0, 0);
$pdf->Cell(95, 6, utf8_decode('Dueño: ' . $d['dueño'] . ' ' . $d['apellido']), 0, 1);

$pdf->Cell(95, 6, utf8_decode('Especie/Raza: ' . $d['especie'] . ' - ' . $d['raza']), 0, 0);
$pdf->Cell(95, 6, utf8_decode('Fecha: ' . date('d/m/Y H:i', strtotime($d['fecha_hora']))), 0, 1);

$pdf->Cell(95, 6, utf8_decode('Edad: ' . $d['edad'] . ' años'), 0, 1);
$pdf->Ln(10);

// Motivo de Consulta
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(25, 135, 84);
$pdf->Cell(0, 7, utf8_decode('MOTIVO DE CONSULTA'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(50);
$pdf->MultiCell(0, 6, utf8_decode($d['motivo']), 0, 'L');
$pdf->Ln(10);

// Diagnóstico y Tratamiento
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(25, 135, 84);
$pdf->Cell(0, 7, utf8_decode('DIAGNÓSTICO Y TRATAMIENTO'), 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(33);
$pdf->SetFillColor(252, 252, 252);
// El recuadro del diagnóstico
$pdf->MultiCell(0, 8, utf8_decode($d['diagnostico']), 1, 'L', true);

// Salida del PDF (I = Inline / Abrir en navegador)
$pdf->Output('I', 'Informe_Medico_' . $d['mascota'] . '.pdf');