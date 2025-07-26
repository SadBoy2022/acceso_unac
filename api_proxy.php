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

// Incluir el administrador de contexto
require_once 'context_manager.php';

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

    // Inicializar el administrador de contexto
    $contextManager = new ContextManager('./');
    
    // Buscar contexto relevante basado en la consulta del usuario
    $dynamicContext = $contextManager->formatContextForLLM($data['prompt']);
    
    // Combinar contexto dinámico con contexto manual (si existe)
    $manualContext = $data['context'] ?? '';
    $fullContext = '';
    
    if (!empty($dynamicContext)) {
        $fullContext = $dynamicContext;
        if (!empty($manualContext)) {
            $fullContext .= "\n\nINFORMACIÓN ADICIONAL:\n" . $manualContext;
        }
    } else {
        $fullContext = !empty($manualContext) ? $manualContext : 'No hay contexto específico disponible.';
    }

    // Instrucciones mejoradas para el sistema
    $systemInstructions = "Tu nombre es AVI, la Asistente Virtual Inteligente de la OTI de la Universidad Nacional del Callao (UNAC).

INSTRUCCIONES IMPORTANTES:
1. Responde ÚNICAMENTE sobre temas académicos y administrativos de la UNAC
2. Usa SOLAMENTE la información del contexto proporcionado
3. Si no tienes información específica en el contexto, indica que no tienes esa información y sugiere contactar a la oficina correspondiente
4. Responde de manera clara, profesional y amigable
5. Incluye números de teléfono, correos y pasos específicos cuando estén disponibles
6. Si el usuario pregunta sobre temas no relacionados con la UNAC, redirige la conversación educadamente

CONTEXTO DISPONIBLE:
" . $fullContext;

    $payload = [
        'model' => 'llama3-70b-8192',
        'messages' => [
            [
                'role' => 'system',
                'content' => $systemInstructions
            ],
            [
                'role' => 'user',
                'content' => $data['prompt']
            ]
        ],
        'temperature' => 0.3, // Reducido para respuestas más consistentes
        'max_tokens' => 1024,
        'top_p' => 0.8, // Reducido para mayor precisión
        'frequency_penalty' => 0.1,
        'presence_penalty' => 0.1
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

    // Obtener estadísticas del contexto para debugging (opcional)
    $contextStats = $contextManager->getStats();

    echo json_encode([
        'success' => true,
        'response' => $responseData['choices'][0]['message']['content'],
        'debug_info' => [
            'context_found' => !empty($dynamicContext),
            'context_stats' => $contextStats,
            'context_length' => strlen($fullContext)
        ]
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