<?php
header('Content-Type: application/json');

// Habilitar logging de errores
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Verificar si la solicitud es OPTIONS (para CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Iniciar sesión y verificar autenticación
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    error_log('API: Acceso no autorizado - Usuario no logueado');
    http_response_code(401);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// Obtener datos del POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('API: Error JSON - ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Datos JSON no válidos', 'details' => json_last_error_msg()]);
    exit;
}

// Validar que exista el prompt
if (empty($data['prompt'])) {
    error_log('API: Prompt vacío recibido');
    http_response_code(400);
    echo json_encode(['error' => 'El campo "prompt" es requerido']);
    exit;
}

// Log de la consulta recibida
error_log('API: Consulta recibida - ' . $data['prompt']);
error_log('API: Contexto disponible - ' . (isset($data['context']) ? 'SÍ (' . strlen($data['context']) . ' caracteres)' : 'NO'));

try {
    // Configuración de la API de Groq (usa una variable de entorno en producción)
    $apiKey = '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'; // REEMPLAZA ESTO CON TU KEY REAL
    
    // Validar que la API key esté configurada
    if (empty($apiKey)) {
        throw new Exception('API key no configurada');
    }

    $payload = [
        'model' => 'llama3-70b-8192',
        'messages' => [
            [
            'role' => 'system',
            'content' => "Eres AVI, la Asistente Virtual Inteligente de la Oficina de Tecnología de la Información (OTI) de la UNAC.\n\n" .
                        "INSTRUCCIONES IMPORTANTES:\n" .
                        "1. Usa EXCLUSIVAMENTE el contexto proporcionado para responder\n" .
                        "2. Si el contexto contiene información relevante, úsala completamente\n" .
                        "3. Estructura tus respuestas de forma clara con encabezados y listas\n" .
                        "4. Para preguntas sobre matrícula, fechas, pagos, y procesos académicos, sé muy específico\n" .
                        "5. Si no hay contexto suficiente, indica las opciones de contacto disponibles\n" .
                        "6. Siempre mantén un tono amable y profesional\n" .
                        "7. Incluye detalles específicos como fechas, costos, y pasos exactos cuando estén disponibles\n\n" .
                        "CONTEXTO DISPONIBLE:\n" . ($data['context'] ?? 'No hay contexto específico disponible') . 
                        "\n\nResponde de manera clara, completa y útil basándote en este contexto."
            ],
            [
                'role' => 'user',
                'content' => $data['prompt']
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 1024,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0
    ];

    // Log del payload (sin mostrar la API key completa)
    $log_payload = $payload;
    error_log('API: Enviando solicitud a Groq con modelo ' . $payload['model']);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_FAILONERROR => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $curl_error = curl_error($ch);
        error_log('API: Error cURL - ' . $curl_error);
        throw new Exception('Error en cURL: ' . $curl_error);
    }

    error_log('API: Respuesta HTTP ' . $httpCode . ' de Groq');

    // Verificar si la respuesta es JSON válido
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('API: Respuesta inválida de Groq - ' . substr($response, 0, 200));
        throw new Exception('Error al procesar la respuesta de la API');
    }

    if (!isset($responseData['choices'][0]['message']['content'])) {
        error_log('API: Estructura de respuesta inesperada - ' . json_encode($responseData));
        throw new Exception('La API devolvió una estructura inesperada');
    }

    $ai_response = $responseData['choices'][0]['message']['content'];
    error_log('API: Respuesta exitosa generada (' . strlen($ai_response) . ' caracteres)');

    echo json_encode([
        'success' => true,
        'response' => $ai_response
    ]);

} catch (Exception $e) {
    error_log('API: Error en procesamiento - ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => isset($response) ? substr($response, 0, 500) : null
    ]);
} finally {
    if (isset($ch)) curl_close($ch);
}
?>
