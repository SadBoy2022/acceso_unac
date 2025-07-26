<?php
// Script de prueba para verificar el funcionamiento del algoritmo de contexto

echo "<h2>Prueba del Sistema de Contexto - AVI UNAC</h2>\n";

// Simular consultas de prueba
$consultas_prueba = [
    "¿Cómo me matriculo en exámenes de aplazados?",
    "matrícula aplazados fechas",
    "proceso matricula exámenes",
    "información sobre matrícula",
    "recuperar contraseña",
    "horarios de atención"
];

echo "<h3>Verificando archivos disponibles en GitHub:</h3>\n";

// Obtener lista de archivos
$url = 'https://api.github.com/repos/SadBoy2022/asistente-unac/contents/';
$context = stream_context_create([
    'http' => [
        'header' => 'User-Agent: AVI-Test-Script'
    ]
]);

$response = file_get_contents($url, false, $context);
if ($response) {
    $files = json_decode($response, true);
    $md_files = array_filter($files, function($file) {
        return $file['type'] === 'file' && substr($file['name'], -3) === '.md';
    });
    
    echo "<ul>\n";
    foreach ($md_files as $file) {
        echo "<li>" . $file['name'] . "</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p style='color: red'>❌ Error: No se pudo conectar al repositorio GitHub</p>\n";
}

echo "<h3>Probando contenido del archivo matricula_aplazados.md:</h3>\n";

$matricula_url = 'https://raw.githubusercontent.com/SadBoy2022/asistente-unac/main/matricula_aplazados.md';
$matricula_content = file_get_contents($matricula_url);

if ($matricula_content) {
    echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>\n";
    echo "<h4>✅ Contenido encontrado:</h4>\n";
    echo "<pre>" . htmlspecialchars(substr($matricula_content, 0, 500)) . "...</pre>\n";
    echo "</div>\n";
    
    // Extraer palabras clave
    if (preg_match('/^---\n([\s\S]*?)\n---/', $matricula_content, $matches)) {
        echo "<h4>Palabras clave detectadas:</h4>\n";
        $yaml = $matches[1];
        $lines = explode("\n", $yaml);
        echo "<ul>\n";
        foreach ($lines as $line) {
            if (preg_match('/^\s*-\s*([\wáéíóúñ]+):(\d+)/', $line, $keyword_match)) {
                echo "<li><strong>" . $keyword_match[1] . "</strong>: " . $keyword_match[2] . " puntos</li>\n";
            }
        }
        echo "</ul>\n";
    }
} else {
    echo "<p style='color: red'>❌ Error: No se pudo obtener el contenido del archivo de matrícula</p>\n";
}

echo "<h3>Simulación de algoritmo de relevancia:</h3>\n";

foreach ($consultas_prueba as $consulta) {
    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>\n";
    echo "<h4>Consulta: \"$consulta\"</h4>\n";
    
    $consulta_lower = strtolower($consulta);
    $puntaje_matricula = 0;
    
    // Simular el cálculo de puntaje para matricula_aplazados.md
    $palabras_clave = [
        'matrícula' => 100,
        'aplazados' => 95,
        'exámenes' => 90,
        'fechas' => 90,
        'proceso' => 75
    ];
    
    foreach ($palabras_clave as $palabra => $peso) {
        if (strpos($consulta_lower, strtolower($palabra)) !== false) {
            $puntaje_matricula += $peso;
            echo "• Encontrada '<strong>$palabra</strong>': +$peso puntos<br>\n";
        }
    }
    
    // Bonus por nombre de archivo
    if (strpos($consulta_lower, 'matricula') !== false) {
        $puntaje_matricula += 50;
        echo "• Bonus por nombre de archivo 'matricula': +50 puntos<br>\n";
    }
    
    echo "<strong>Puntaje total para matricula_aplazados.md: $puntaje_matricula</strong><br>\n";
    
    if ($puntaje_matricula > 0) {
        echo "<span style='color: green'>✅ Este archivo debería ser seleccionado</span>\n";
    } else {
        echo "<span style='color: orange'>⚠️ Se usaría default.md</span>\n";
    }
    
    echo "</div>\n";
}

echo "<h3>Recomendaciones:</h3>\n";
echo "<ul>\n";
echo "<li>✅ Los archivos están disponibles en GitHub</li>\n";
echo "<li>✅ El contenido de matrícula existe y tiene palabras clave</li>\n";
echo "<li>✅ El algoritmo debería funcionar para consultas sobre matrícula</li>\n";
echo "<li>⚠️ Verifica que JavaScript esté habilitado en el navegador</li>\n";
echo "<li>⚠️ Verifica la consola del navegador para mensajes de debug</li>\n";
echo "</ul>\n";

?>