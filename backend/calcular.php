<?php
// Configurar encabezados para responder en JSON y permitir CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Si es una petición OPTIONS (pre-flight de CORS), terminamos la ejecución con éxito
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar que la petición sea estrictamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "ok" => false,
        "error" => "Método no permitido. Utilice peticiones HTTP POST."
    ]);
    exit;
}

// Capturar los datos JSON enviados desde el Frontend (Vue)
$json = file_get_contents("php://input");
$datos = json_decode($json, true);

// VALIDACIÓN 1: Campos vacíos / Datos incompletos
if (!isset($datos['operador1']) || !isset($datos['operador2']) || !isset($datos['operacion'])) {
    echo json_encode([
        "ok" => false,
        "error" => "Datos incompletos. Se requieren 'operador1', 'operador2' y 'operacion'."
    ]);
    exit;
}

$op1 = $datos['operador1'];
$op2 = $datos['operador2'];
$operacion = strtolower(trim($datos['operacion'])); // Limpiar el texto

// VALIDACIÓN 2: Valores no numéricos
if (!is_numeric($op1) || !is_numeric($op2)) {
    echo json_encode([
        "ok" => false,
        "error" => "Los valores ingresados deben ser numéricos."
    ]);
    exit;
}

// Convertir a floats para poder procesar decimales correctamente
$op1 = (float)$op1;
$op2 = (float)$op2;
$resultado = 0;

// Procesar las operaciones y aplicar las validaciones restantes
switch ($operacion) {
    case 'suma':
        $resultado = $op1 + $op2;
        break;
        
    case 'resta':
        $resultado = $op1 - $op2;
        break;
        
    case 'multiplicacion':
        $resultado = $op1 * $op2;
        break;
        
    case 'division':
        // VALIDACIÓN 3: División entre cero
        if ($op2 == 0) {
            echo json_encode([
                "ok" => false,
                "error" => "No es posible dividir entre cero."
            ]);
            exit;
        }
        $resultado = $op1 / $op2;
        break;

    // VALIDACIÓN 4: Operaciones inválidas
    default:
        echo json_encode([
            "ok" => false,
            "error" => "Operación inválida. Use: suma, resta, multiplicacion o division."
        ]);
        exit;
}

// 7. Retornar la respuesta exitosa en JSON si todo salió bien
echo json_encode([
    "ok" => true,
    "resultado" => $resultado
]);