<?php
header('Content-Type: application/json');

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
    http_response_code(401);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// Obtener datos del POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos JSON no válidos', 'details' => json_last_error_msg()]);
    exit;
}

// Validar que exista el prompt
if (empty($data['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El campo "prompt" es requerido']);
    exit;
}

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
            'content' => "Tu nombre es AVI la Asistente Virtual Inteligente de la OTI. Usa el siguiente contexto para responder preguntas:\n\n" . 
                        ($data['context'] ?? 'No hay contexto disponible') . 
                        "\n\nInstrucciones: Responde preguntas sobre temas académicos de la UNAC de manera clara y precisa."
            ],
            [
                'role' => 'user',
                'content' => $data['prompt']
            ]
        ],
        'temperature' => 0.5,
        'max_tokens' => 1024,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0
    ];

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
        throw new Exception('Error en cURL: ' . curl_error($ch));
    }

    // Verificar si la respuesta es JSON válido
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Respuesta inválida de Groq API: ' . $response);
        throw new Exception('Error al procesar la respuesta de la API');
    }

    if (!isset($responseData['choices'][0]['message']['content'])) {
        error_log('Estructura de respuesta inesperada: ' . json_encode($responseData));
        throw new Exception('La API devolvió una estructura inesperada');
    }

    echo json_encode([
        'success' => true,
        'response' => $responseData['choices'][0]['message']['content']
    ]);

} catch (Exception $e) {
    error_log('Error en api_proxy: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => isset($response) ? $response : null
    ]);
} finally {
    if (isset($ch)) curl_close($ch);
}
?>
