<?php
require_once 'database.php';
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    die(json_encode(['error' => 'Acceso no autorizado']));
}

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    $codigo = $_POST['codigo'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';
    $respuesta = $_POST['respuesta'] ?? '';

    if (empty($codigo) || empty($mensaje)) {
        throw new Exception("Datos incompletos");
    }

    $query = "INSERT INTO historial_consultas 
              (codigo_alumno, mensaje_usuario, respuesta_bot) 
              VALUES (:codigo, :mensaje, :respuesta)";
    
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->bindParam(':respuesta', $respuesta);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error al registrar en el historial");
    }
} catch (Exception $e) {
    error_log("Error en registrar_historial: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn = null;
}
?>
