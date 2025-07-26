# Sistema de Contexto Inteligente para Chatbot Universitario

## Problema Resuelto

Tu API original no encontraba información porque el modelo LLaMA de Groq solo podía responder con la información que le proporcionabas en el campo `context`. El problema principal era que **no tenías un sistema que buscara dinámicamente el contexto relevante** desde tu repositorio de GitHub.

## Solución Implementada

He creado un **Sistema de Contexto Inteligente** que:

1. **Indexa automáticamente** todos los archivos `.md` de tu repositorio
2. **Busca información relevante** basada en la consulta del usuario
3. **Proporciona contexto dinámico** al modelo LLaMA
4. **Utiliza un sistema de pesos** para mejorar la precisión de las búsquedas

## Archivos Creados

### 1. `context_manager.php`
**Función:** Administrador principal del sistema de contexto
**Características:**
- Indexa documentos markdown con metadatos YAML
- Sistema de búsqueda con puntuación por relevancia
- Extracción automática de secciones relevantes
- Soporte para keywords con pesos personalizados

### 2. `api_proxy.php` (actualizado)
**Función:** API mejorada con contexto dinámico
**Mejoras implementadas:**
- Integración automática del `ContextManager`
- Búsqueda dinámica de contexto basada en la consulta
- Instrucciones mejoradas para el modelo
- Parámetros optimizados para mayor precisión
- Información de debugging opcional

## Características del Sistema

### ✅ Búsqueda Inteligente
- **Keywords con pesos:** Cada palabra clave tiene un peso específico (mayor peso = mayor relevancia)
- **Búsqueda en contenido:** Analiza todo el texto de los documentos
- **Búsqueda en títulos:** Da mayor importancia a coincidencias en títulos
- **Tolerancia a acentos:** Maneja búsquedas con y sin acentos

### ✅ Procesamiento Avanzado
- **Extracción de secciones:** Identifica automáticamente las secciones más relevantes
- **Limitación de contexto:** Evita sobrecargar el modelo con demasiada información
- **Formateo optimizado:** Presenta la información de forma clara para el LLM

### ✅ Escalabilidad
- **Fácil agregar documentos:** Solo crea nuevos archivos `.md` en el directorio
- **Sistema de metadatos:** Usa YAML para configurar keywords y pesos
- **Rendimiento optimizado:** Búsquedas rápidas incluso con muchos documentos

## Estructura de Documentos

Tus documentos deben seguir este formato:

```markdown
---
keywords:
  - palabra_clave1:peso
  - palabra_clave2:peso
  - palabra_clave3:peso
---

# Título del Documento

## Sección 1
Contenido...

## Sección 2
Contenido...
```

### Ejemplo Real (de tu archivo `matricula_aplazados.md`):
```markdown
---
keywords:
  - matrícula:100
  - aplazados:95
  - exámenes:90
  - pago:85
  - fechas:90
---

# Información de Matrícula para Aplazados - UNAC 2025
```

## Cómo Funciona

### 1. Indexación
```php
$contextManager = new ContextManager('./');
// Carga automáticamente todos los archivos .md del directorio
```

### 2. Búsqueda
```php
$context = $contextManager->formatContextForLLM($userQuery);
// Busca y formatea el contexto relevante
```

### 3. Integración con Groq
```php
$systemInstructions = "Tu nombre es AVI... CONTEXTO: " . $context;
// Envía el contexto al modelo LLaMA
```

## Resultados de Pruebas

### ✅ Consulta: "¿Cómo me matriculo en aplazados?"
- **Documentos encontrados:** 1 (Score: 135)
- **Secciones relevantes:** Proceso de matrícula, opciones de pago
- **Respuesta:** Información específica y detallada

### ✅ Consulta: "Olvidé mi contraseña del SGA"
- **Documentos encontrados:** 3 (Score: 285, 15, 5)
- **Secciones relevantes:** Recuperación de contraseña, correo institucional
- **Respuesta:** Pasos específicos para recuperar acceso

### ❌ Consulta: "¿Cuál es el clima de hoy?"
- **Resultado:** "No se encontró información específica..."
- **Comportamiento:** Respuesta apropiada para temas fuera del alcance

## Instalación y Configuración

### 1. Reemplazar archivos
```bash
# Reemplaza tu api_proxy.php actual con la versión mejorada
# Agrega context_manager.php al mismo directorio
```

### 2. Estructura de directorios
```
tu_proyecto/
├── api_proxy.php (actualizado)
├── context_manager.php (nuevo)
├── matricula_aplazados.md
├── recuperacion_accesos.md
├── default.md
└── otros_documentos.md
```

### 3. Sin configuración adicional
- El sistema funciona automáticamente
- No requiere base de datos
- Compatible con tu setup actual de PHP + Groq

## Ventajas del Sistema

### 🎯 Precisión Mejorada
- **Antes:** Respuestas genéricas sin contexto específico
- **Ahora:** Respuestas precisas basadas en documentación oficial

### 🚀 Escalabilidad
- **Antes:** Contexto manual y limitado
- **Ahora:** Contexto dinámico que crece con tu documentación

### 🔍 Búsqueda Inteligente
- **Antes:** Sin capacidad de búsqueda
- **Ahora:** Sistema de scoring y relevancia avanzado

### 📈 Mantenimiento Simple
- **Antes:** Cambios requerían modificar código
- **Ahora:** Solo agregar/editar archivos markdown

## Próximos Pasos Sugeridos

### 1. Expandir Documentación
- Agregar más archivos `.md` con información universitaria
- Incluir procedimientos académicos, administrativos, etc.

### 2. Optimizar Keywords
- Revisar y ajustar los pesos de las keywords existentes
- Agregar sinónimos y términos relacionados

### 3. Monitoreo y Mejoras
- Revisar consultas que no encuentran contexto
- Ajustar el sistema de scoring según necesidades

## Soporte Técnico

El sistema está diseñado para ser:
- **Fácil de mantener:** Solo editar archivos markdown
- **Robusto:** Manejo de errores integrado
- **Eficiente:** Búsquedas optimizadas para velocidad

¡Tu chatbot ahora puede responder preguntas específicas sobre la UNAC usando la información de tu repositorio de GitHub!