<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}
$codigo = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Asistente OTI</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <style>
        body {
            margin: 0;
            background-color: #0f172a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            padding-bottom: 60px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Header mejorado con logo UNAC */
        header {
            background-color: #0753a6;
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .unac-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .header-titles h1 {
            margin: 0;
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .header-titles p {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            font-weight: 300;
        }

        .boton-cerrar {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .boton-cerrar:hover {
            background-color: #bb2d3b;
            transform: translateY(-1px);
        }

        /* Contenedor principal */
        .container-chat {
            display: flex;
            gap: 20px;
            padding: 20px;
            height: calc(100vh - 120px);
            box-sizing: border-box;
        }

        /* Sidebar con logo AVI */
        .sidebar {
            width: 250px;
            background: #001832;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .avi-logo {
            width: 180px; /* Mejor que max-width para control exacto */
            height: 180px; /* Asegura proporción cuadrada */
            margin: 0 auto 20px;
            border-radius: 50%;
            border: 3px solid #eef951ff;
            padding: 5px;
            background: 
                radial-gradient(circle at center, #334155, #1e293b);
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 0 10px rgba(255, 255, 255, 0.15);
            object-fit: contain; /* Para manejo perfecto de la imagen */
        }

        .menu-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .btn-option {
            background: #334155;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-option:hover {
            background: #3b82f6;
            transform: translateX(5px);
        }

        /* Área de chat */
        .chat-area {
            flex: 1;
            background: #1e293b;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-sizing: border-box;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Mensajes */
        .message {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .assistant-message .message-content {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            padding: 15px 20px;
            border-radius: 0 15px 15px 15px;
            flex: 1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-left: 3px solid #3b82f6;
        }

        .assistant-message .message-content strong {
            color: #a7d1ff;
        }

        .user-message {
            justify-content: flex-end;
        }

        .user-message .message-content {
            background: #3b82f6;
            padding: 15px 20px;
            border-radius: 15px 0 15px 15px;
            max-width: 80%;
        }

        #chat-messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 10px;
        }

        /* Input y contador */
        .input-container {
            display: flex;
            gap: 10px;
        }

        .input-container input {
            flex: 1;
            padding: 12px 15px;
            border-radius: 8px;
            border: none;
            background: #334155;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .input-container input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #3b82f6;
        }

        .btn-send {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-send:hover:not(:disabled) {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #3b82f6 !important;
        }

        .counter {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: #94a3b8;
        }

        #tiempo-reinicio.limit-reached {
            color: #dc3545;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #0f172a;
            color: #cbd5e1;
            text-align: center;
            padding: 15px 0;
            font-size: 14px;
            border-top: 1px solid #334155;
            z-index: 100;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-chat {
                flex-direction: column;
                height: auto;
                min-height: calc(100vh - 120px);
            }
            
            .sidebar {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
                padding: 15px;
            }
            
            .avi-logo {
                display: none;
            }
            
            .menu-options {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .btn-option {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .header-brand {
                flex-direction: column;
                text-align: center;
            }
            
            .header-titles h1 {
                font-size: 1.1rem;
            }
        }
    </style>
    </head>
    <body>

    <header>
        <div class="header-brand">
            <img src="images/unac.png" alt="UNAC" class="unac-logo">
            <div class="header-titles">
                <h1>Asistente Virtual Inteligente</h1>
                <p>Oficina de Tecnología de la Información</p>
            </div>
        </div>
        <form method="post" action="logout.php">
            <button type="submit" class="boton-cerrar">Cerrar sesión</button>
        </form>
    </header>

    <div class="container-chat">
        <div class="sidebar">
            <img src="images/avi-logo.png" alt="AVI UNAC" class="avi-logo">
            <div class="menu-options">
                <button class="btn-option">✅ FAQ</button>
                <button class="btn-option">🔒 Contraseña</button>
                <button class="btn-option">💻 Correo</button>
                <button class="btn-option">📅 Cronograma</button>
                <button class="btn-option">👨‍🏫 Autoridades</button>
            </div>
        </div>

        <div class="chat-area">
            <div id="chat-messages"></div>
            
            <div class="input-container">
                <input type="text" id="user-input" placeholder="Escribe tu pregunta...">
                <button class="btn-send" id="enviar">Enviar</button>
            </div>
            
            <div class="counter">
                Preguntas restantes: <span id="contador-preguntas">5</span>/5
                <div id="tiempo-reinicio" style="font-size: 0.8em; color: #94a3b8;"></div>
            </div>
        </div>
    </div>

    <div class="footer">
        © Oficina de Tecnología de la Información – UNAC. Todos los derechos reservados.
    </div> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnEnviar = document.getElementById('enviar');
    const userInput = document.getElementById('user-input');
    const chatMessages = document.getElementById('chat-messages');
    const contadorPreguntas = document.getElementById('contador-preguntas');
    
    const MAX_PREGUNTAS = 5;
    let preguntasRestantes = MAX_PREGUNTAS;
    let cachedContext = null;
    let lastFetchTime = 0;
    let esperandoRespuesta = false;

    // Función para mostrar mensajes en el chat
    function mostrarMensaje(remitente, mensaje, esAsistente = false) {
        const mensajeDiv = document.createElement('div');
        
        if (esAsistente) {
            mensajeDiv.className = 'message assistant-message';
            mensajeDiv.innerHTML = `
            <img src="images/avi.png" alt="AVI" class="avatar">
            <div class="message-content">
                <strong>AVI:</strong> ${formatearMensaje(mensaje)}
            </div>
        `;
        } else {
            mensajeDiv.className = 'message user-message';
            mensajeDiv.innerHTML = `
                <div class="message-content">
                    <strong>${remitente}:</strong> ${formatearMensaje(mensaje)}
                </div>
            `;
        }
        
        chatMessages.appendChild(mensajeDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Función para formatear el texto del mensaje
    function formatearMensaje(texto) {
        return texto
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>')
            .replace(/- (.*?)(<br>|$)/g, '• $1<br>')
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank">$1</a>');
    }

    // Función para obtener archivos disponibles del repositorio
    async function obtenerArchivosDisponibles() {
        try {
            const response = await fetch('https://api.github.com/repos/SadBoy2022/asistente-unac/contents/');
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            
            const files = await response.json();
            return files
                .filter(file => file.name.endsWith('.md'))
                .map(file => file.name);
        } catch (error) {
            console.error("Error al cargar lista de contextos:", error);
            return ['default.md'];
        }
    }

    // Función para determinar los contextos relevantes
    async function determinarContextosRelevantes(consulta, archivosDisponibles) {
        const consultaLower = consulta.toLowerCase();
        const puntajesArchivos = {};

        for (const archivo of archivosDisponibles) {
            try {
                const response = await fetch(`https://raw.githubusercontent.com/SadBoy2022/asistente-unac/main/${archivo}?t=${Date.now()}`);
                const contenido = await response.text();
                
                // Extraer metadatos YAML
                const yamlMatch = contenido.match(/^---\n([\s\S]*?)\n---/);
                if (yamlMatch) {
                    const yaml = yamlMatch[1];
                    const palabrasClave = {};
                    
                    // Parsear palabras clave
                    yaml.split('\n').forEach(linea => {
                        if (linea.includes('keywords:')) return;
                        const coincidencia = linea.match(/-\s*([\wáéíóúñ]+):(\d+)/);
                        if (coincidencia) palabrasClave[coincidencia[1]] = parseInt(coincidencia[2]);
                    });

                    // Calcular puntaje
                    let puntaje = 0;
                    Object.entries(palabrasClave).forEach(([palabra, peso]) => {
                        if (consultaLower.includes(palabra)) {
                            puntaje += peso;
                            // Bonus por coincidencia exacta
                            if (new RegExp(`\\b${palabra}\\b`).test(consultaLower)) {
                                puntaje += peso * 0.2;
                            }
                        }
                    });

                    // Bonus por combinación de palabras clave
                    if (Object.keys(palabrasClave).filter(p => consultaLower.includes(p)).length > 1) {
                        puntaje *= 1.3;
                    }

                    // Puntaje adicional por coincidencia con nombre de archivo
                    const nombreArchivoSinExt = archivo.replace('.md', '').toLowerCase();
                    if (consultaLower.includes(nombreArchivoSinExt)) {
                        puntaje += 50;
                    }

                    if (puntaje > 0) puntajesArchivos[archivo] = puntaje;
                }
            } catch (e) {
                console.error(`Error procesando ${archivo}:`, e);
            }
        }

        // Ordenar archivos por puntaje (mayor a menor)
        const archivosOrdenados = Object.entries(puntajesArchivos)
            .sort((a, b) => b[1] - a[1])
            .map(([archivo]) => archivo);

        console.log("Archivos relevantes ordenados:", archivosOrdenados);
        return archivosOrdenados.length > 0 ? archivosOrdenados : ['default.md'];
    }

    // Función para obtener contexto combinado
    async function obtenerContextoCombinado(consulta) {
        try {
            const archivosDisponibles = await obtenerArchivosDisponibles();
            console.log("Archivos disponibles:", archivosDisponibles);
            
            const archivosRelevantes = await determinarContextosRelevantes(consulta, archivosDisponibles);
            console.log("Archivos seleccionados:", archivosRelevantes);
            
            // Forzar actualización evitando caché del navegador
            const promesasContexto = archivosRelevantes.map(archivo => 
                fetch(`https://raw.githubusercontent.com/SadBoy2022/asistente-unac/main/${archivo}?t=${Date.now()}`)
                    .then(res => {
                        if (!res.ok) throw new Error(`Error ${res.status} al cargar ${archivo}`);
                        return res.text();
                    })
                    .then(contenido => {
                        // Eliminar el bloque YAML del contenido
                        return contenido.replace(/^---\n[\s\S]*?\n---/, '').trim();
                    })
                    .catch(error => {
                        console.error(`Error cargando ${archivo}:`, error);
                        return '';
                    })
            );
            
            const contextos = await Promise.all(promesasContexto);
            const contextoCombinado = contextos.filter(ctx => ctx.trim().length > 0).join('\n\n---\n\n');
            
            console.log("Contexto combinado:", contextoCombinado.substring(0, 200) + "...");
            return contextoCombinado || obtenerMensajePorDefecto();
        } catch (error) {
            console.error("Error al obtener contexto combinado:", error);
            return obtenerMensajePorDefecto();
        }
    }

    function obtenerMensajePorDefecto() {
        return `No encontré información específica en mis documentos. Por favor:\n\n` +
               `• Verifica que tu pregunta esté relacionada con los servicios académicos\n` +
               `• Revisa el portal UNAC: www.unac.edu.pe\n` +
               `• Contacta a tu Escuela Profesional`;
    }

    // Función para actualizar el contador de preguntas
        async function actualizarContador() {
            try {
                const response = await fetch('limite_consultas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        codigo_usuario: "<?= $codigo ?>",
                        solo_consulta: true
                    })
                });

                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const errorText = await response.text();
                    throw new Error(`La API respondió con formato incorrecto: ${errorText.substring(0, 100)}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    console.error('Error:', data.error);
                    return { restantes: 0, reset: Date.now() / 1000 + 3600 };
                }
                
                // Actualizar variable local y visual
                preguntasRestantes = data.restantes;
                contadorPreguntas.textContent = preguntasRestantes;

                // Actualizar mensaje de reinicio en el contador
                if (data.reset) {
                    const resetTime = new Date(data.reset * 1000);
                    const options = { hour: '2-digit', minute: '2-digit' };
                    const horaReinicio = resetTime.toLocaleTimeString('es-ES', options);
                    
                    document.querySelector('.counter').innerHTML = `
                        Preguntas restantes: <span id="contador-preguntas">${preguntasRestantes}</span>/5
                        <div id="tiempo-reinicio" style="font-size: 0.8em; ${preguntasRestantes <= 0 ? 'color: #dc3545; font-weight: bold;' : 'color: #94a3b8;'}">
                            ${preguntasRestantes <= 0 ? 'Has alcanzado el límite de consultas. Podrás hacer nuevas preguntas a las ' + horaReinicio : ''}
                        </div>
                    `;
                    
                    // Actualizar input cuando se alcanza el límite
                    if (preguntasRestantes <= 0) {
                        userInput.disabled = true;
                        userInput.placeholder = `Has alcanzado el límite (reinicio a las ${horaReinicio})`;
                        btnEnviar.disabled = true;
                    } else {
                        userInput.disabled = false;
                        userInput.placeholder = 'Escribe tu pregunta...';
                        btnEnviar.disabled = false;
                    }
                }
                
                return data;
            } catch (error) {
                console.error('Error al actualizar contador:', error);
                return { restantes: 0, reset: Date.now() / 1000 + 3600 };
            }
        }

        // Función auxiliar para mostrar mensaje de límite alcanzado
        async function manejarLimiteAlcanzado() {
            const estadoContador = await actualizarContador();
            if (estadoContador.restantes <= 0) {
                const resetTime = new Date(estadoContador.reset * 1000);
                const options = { hour: '2-digit', minute: '2-digit' };
                const horaReinicio = resetTime.toLocaleTimeString('es-ES', options);
                
                // Desactivar elementos de UI
                userInput.disabled = true;
                btnEnviar.disabled = true;
                userInput.placeholder = `Has alcanzado el límite (reinicio a las ${horaReinicio})`;
                
                // Actualizar contador visual
                document.querySelector('.counter').innerHTML = `
                    Preguntas restantes: <span id="contador-preguntas">0</span>/5
                    <div id="tiempo-reinicio" style="font-size: 0.8em; color: #dc3545; font-weight: bold;">
                        Has alcanzado el límite de consultas. Podrás hacer nuevas preguntas a las ${horaReinicio}
                    </div>
                `;
            }
        }

        // Función principal para enviar mensajes
        async function enviarMensaje() {
            if (esperandoRespuesta) return;
            
            const mensaje = userInput.value.trim();
            if (!mensaje) return;
            
            try {
                esperandoRespuesta = true;
                btnEnviar.disabled = true;
                userInput.disabled = true;
                
                // 1. Verificar límite primero
                const estadoContador = await actualizarContador();
                if (estadoContador.restantes <= 0) {
                    await manejarLimiteAlcanzado();
                    return;
                }
                
                // 2. Mostrar mensaje del usuario
                mostrarMensaje('Tú', mensaje);
                userInput.value = '';
                
                // 3. Obtener contexto relevante
                const contexto = await obtenerContextoCombinado(mensaje);
                
                // 4. Enviar pregunta a la API
                const response = await fetch('api_proxy.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        prompt: mensaje,
                        context: contexto,
                        codigo_usuario: "<?= $codigo ?>"
                    })
                });

                // Verificar si la respuesta es JSON válido
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const errorText = await response.text();
                    throw new Error(`La API respondió con formato incorrecto: ${errorText.substring(0, 100)}`);
                }
                
                const data = await response.json();
                
                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Error en la respuesta del servidor');
                }
                
                // 5. Mostrar respuesta de AVI
                mostrarMensaje('AVI', data.response, true);

                // 6. Actualizar contador después de enviar
                await fetch('limite_consultas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        codigo_usuario: "<?= $codigo ?>",
                        incrementar: true
                    })
                });
                
                // 7. Verificar si alcanzó el límite después de enviar
                await manejarLimiteAlcanzado();
                
                // 8. Registrar en el historial
                await fetch('registrar_historial.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        codigo: "<?= $codigo ?>",
                        mensaje: mensaje,
                        respuesta: data.response
                    })
                });
                
            } catch (error) {
                console.error('Error:', error);
                
                // Manejar específicamente errores de JSON
                if (error instanceof SyntaxError) {
                    mostrarMensaje('Asistente', 'Error al procesar la respuesta del servidor', true);
                } 
                // Otros errores
                else {
                    mostrarMensaje('Asistente', `Lo siento, ocurrió un error: ${error.message}`, true);
                    
                    // Revertir contador si hubo error
                    await fetch('limite_consultas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            codigo_usuario: "<?= $codigo ?>",
                            revertir: true
                        })
                    });
                }
                
                // Actualizar contador visual
                await actualizarContador();
            } finally {
                if (preguntasRestantes > 0) {
                    esperandoRespuesta = false;
                    btnEnviar.disabled = false;
                    userInput.disabled = false;
                }
                userInput.focus();
            }
        }

        // Event listeners
        btnEnviar.addEventListener('click', enviarMensaje);
        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') enviarMensaje();
        });

        // Mensaje de bienvenida inicial
        mostrarMensaje('AVI', `Hola <?= $codigo ?>, soy tu Asistente Virtual Inteligente de la OTI. ¿En qué puedo ayudarte?`, true);

        // Actualizar contador al cargar la página y verificar límite
        actualizarContador().then(manejarLimiteAlcanzado);
    });
</script>

</body>
</html>
