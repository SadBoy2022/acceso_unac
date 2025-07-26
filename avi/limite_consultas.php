<?php
require_once 'database.php';
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    die(json_encode(['error' => 'Acceso no autorizado', 'details' => 'Sesión no iniciada']));
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Obtener datos del request
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Datos JSON no válidos: " . json_last_error_msg());
    }

    $codigoUsuario = $input['codigo_usuario'] ?? '';
    $incrementar = $input['incrementar'] ?? false;
    $revertir = $input['revertir'] ?? false;
    $soloConsulta = $input['solo_consulta'] ?? false;

    if (empty($codigoUsuario)) {
        throw new Exception("Código de usuario no proporcionado");
    }

    // Configuración de límites
    $limitePreguntas = 5;
    $tiempoLimite = 3600; // 1 hora en segundos
    $horaActual = time();
    $horaHaceUnaHora = $horaActual - $tiempoLimite;

    // 1. Verificar/crear registro para el usuario
    $query = "SELECT contador, UNIX_TIMESTAMP(ultima_consulta) as timestamp 
              FROM limite_consultas 
              WHERE codigo_usuario = :codigo";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':codigo', $codigoUsuario);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al consultar límite");
    }

    if ($stmt->rowCount() == 0) {
        // Crear registro si no existe
        $insertQuery = "INSERT INTO limite_consultas (codigo_usuario, contador) 
                        VALUES (:codigo, 0)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bindParam(':codigo', $codigoUsuario);
        
        if (!$insertStmt->execute()) {
            throw new Exception("Error al crear registro de límite");
        }
        
        // Volver a ejecutar la consulta para obtener el nuevo registro
        $stmt->execute();
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Manejar solicitud de solo consulta
    if ($soloConsulta) {
        if ($row['timestamp'] < $horaHaceUnaHora) {
            $restantes = $limitePreguntas;
            $reset = $horaActual + $tiempoLimite;
        } else {
            $restantes = max(0, $limitePreguntas - $row['contador']);
            $reset = $row['timestamp'] + $tiempoLimite;
        }
        
        echo json_encode([
            'limite' => $limitePreguntas,
            'restantes' => $restantes,
            'reset' => $reset
        ]);
        exit;
    }

    // 3. Manejar solicitud de revertir
    if ($revertir) {
        $nuevoContador = max(0, $row['contador'] - 1);
        $updateQuery = "UPDATE limite_consultas 
                       SET contador = :contador,
                           ultima_consulta = CURRENT_TIMESTAMP
                       WHERE codigo_usuario = :codigo";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':contador', $nuevoContador);
        $updateStmt->bindParam(':codigo', $codigoUsuario);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error al revertir contador");
        }
        
        echo json_encode([
            'success' => true,
            'limite' => $limitePreguntas,
            'restantes' => $limitePreguntas - $nuevoContador,
            'reset' => $row['timestamp'] + $tiempoLimite
        ]);
        exit;
    }

    // 4. Manejar solicitud de incrementar
    if ($incrementar) {
        if ($row['timestamp'] < $horaHaceUnaHora) {
            // Resetear contador si ha pasado el tiempo límite
            $nuevoContador = 1;
            $reset = $horaActual + $tiempoLimite;
        } elseif ($row['contador'] >= $limitePreguntas) {
            // Límite alcanzado
            echo json_encode([
                'error' => 'Límite alcanzado',
                'limite' => $limitePreguntas,
                'restantes' => 0,
                'reset' => $row['timestamp'] + $tiempoLimite
            ]);
            exit;
        } else {
            // Incrementar contador normalmente
            $nuevoContador = $row['contador'] + 1;
            $reset = $row['timestamp'] + $tiempoLimite;
        }
        
        // Actualizar en base de datos
        $updateQuery = "UPDATE limite_consultas 
                        SET contador = :contador, 
                            ultima_consulta = CURRENT_TIMESTAMP 
                        WHERE codigo_usuario = :codigo";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':contador', $nuevoContador);
        $updateStmt->bindParam(':codigo', $codigoUsuario);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error al actualizar contador");
        }
        
        echo json_encode([
            'limite' => $limitePreguntas,
            'restantes' => $limitePreguntas - $nuevoContador,
            'reset' => $reset
        ]);
        exit;
    }

    // Si no es ninguna operación conocida
    throw new Exception("Operación no soportada");

} catch (Exception $e) {
    error_log("Error en limite_consultas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el servidor',
        'details' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn = null;
}
?>
