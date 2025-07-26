<?php
class ContextManager {
    private $documentsPath;
    private $documents = [];
    
    public function __construct($documentsPath = './') {
        $this->documentsPath = rtrim($documentsPath, '/') . '/';
        $this->loadDocuments();
    }
    
    /**
     * Cargar todos los archivos markdown y extraer su contenido con metadatos
     */
    private function loadDocuments() {
        $files = glob($this->documentsPath . '*.md');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $fileName = basename($file, '.md');
            
            // Parsear metadatos YAML (keywords y weights)
            $metadata = $this->parseMetadata($content);
            $cleanContent = $this->removeMetadata($content);
            
            $this->documents[$fileName] = [
                'file' => $file,
                'content' => $cleanContent,
                'keywords' => $metadata['keywords'] ?? [],
                'title' => $this->extractTitle($cleanContent),
                'sections' => $this->extractSections($cleanContent)
            ];
        }
    }
    
    /**
     * Extraer metadatos YAML del contenido
     */
    private function parseMetadata($content) {
        $metadata = ['keywords' => []];
        
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            $yamlContent = $matches[1];
            
            // Extraer keywords con sus pesos
            if (preg_match('/keywords:\s*\n((?:\s*-\s*.+\n?)+)/s', $yamlContent, $keywordMatches)) {
                $keywordLines = explode("\n", trim($keywordMatches[1]));
                
                foreach ($keywordLines as $line) {
                    if (preg_match('/^\s*-\s*(.+?):(\d+)/', $line, $match)) {
                        $keyword = trim($match[1]);
                        $weight = intval($match[2]);
                        $metadata['keywords'][$keyword] = $weight;
                    }
                }
            }
        }
        
        return $metadata;
    }
    
    /**
     * Remover metadatos YAML del contenido
     */
    private function removeMetadata($content) {
        return preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
    }
    
    /**
     * Extraer título del documento
     */
    private function extractTitle($content) {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return 'Sin título';
    }
    
    /**
     * Extraer secciones del documento
     */
    private function extractSections($content) {
        $sections = [];
        $lines = explode("\n", $content);
        $currentSection = '';
        $currentContent = '';
        
        foreach ($lines as $line) {
            if (preg_match('/^#{1,3}\s+(.+)$/', $line, $matches)) {
                if ($currentSection && $currentContent) {
                    $sections[$currentSection] = trim($currentContent);
                }
                $currentSection = trim($matches[1]);
                $currentContent = '';
            } else {
                $currentContent .= $line . "\n";
            }
        }
        
        if ($currentSection && $currentContent) {
            $sections[$currentSection] = trim($currentContent);
        }
        
        return $sections;
    }
    
    /**
     * Buscar contexto relevante basado en la consulta del usuario
     */
    public function findRelevantContext($query, $maxResults = 3) {
        $query = mb_strtolower($query, 'UTF-8');
        $queryWords = $this->extractWords($query);
        $results = [];
        
        foreach ($this->documents as $docName => $doc) {
            $score = $this->calculateRelevanceScore($queryWords, $doc);
            
            if ($score > 0) {
                $results[] = [
                    'document' => $docName,
                    'title' => $doc['title'],
                    'content' => $doc['content'],
                    'score' => $score,
                    'relevantSections' => $this->findRelevantSections($queryWords, $doc['sections'])
                ];
            }
        }
        
        // Ordenar por relevancia (score más alto primero)
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($results, 0, $maxResults);
    }
    
    /**
     * Extraer palabras clave de la consulta
     */
    private function extractWords($text) {
        // Remover acentos y convertir a minúsculas
        $text = $this->removeAccents($text);
        $text = mb_strtolower($text, 'UTF-8');
        
        // Extraer palabras (mínimo 3 caracteres)
        preg_match_all('/\b\w{3,}\b/', $text, $matches);
        return array_unique($matches[0]);
    }
    
    /**
     * Remover acentos para mejor matching
     */
    private function removeAccents($text) {
        $search = ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'];
        $replace = ['a','e','i','o','u','n','A','E','I','O','U','N'];
        return str_replace($search, $replace, $text);
    }
    
    /**
     * Calcular score de relevancia
     */
    private function calculateRelevanceScore($queryWords, $doc) {
        $score = 0;
        
        // Buscar en keywords con peso
        foreach ($doc['keywords'] as $keyword => $weight) {
            $keyword = $this->removeAccents(mb_strtolower($keyword, 'UTF-8'));
            foreach ($queryWords as $word) {
                if (strpos($keyword, $word) !== false || strpos($word, $keyword) !== false) {
                    $score += $weight;
                }
            }
        }
        
        // Buscar en el contenido (peso menor)
        $content = $this->removeAccents(mb_strtolower($doc['content'], 'UTF-8'));
        foreach ($queryWords as $word) {
            $occurrences = substr_count($content, $word);
            $score += $occurrences * 5; // Peso menor para coincidencias en contenido
        }
        
        // Buscar en el título (peso medio)
        $title = $this->removeAccents(mb_strtolower($doc['title'], 'UTF-8'));
        foreach ($queryWords as $word) {
            if (strpos($title, $word) !== false) {
                $score += 25;
            }
        }
        
        return $score;
    }
    
    /**
     * Encontrar secciones relevantes del documento
     */
    private function findRelevantSections($queryWords, $sections) {
        $relevantSections = [];
        
        foreach ($sections as $sectionTitle => $sectionContent) {
            $sectionScore = 0;
            $combinedText = $this->removeAccents(mb_strtolower($sectionTitle . ' ' . $sectionContent, 'UTF-8'));
            
            foreach ($queryWords as $word) {
                $sectionScore += substr_count($combinedText, $word);
            }
            
            if ($sectionScore > 0) {
                $relevantSections[] = [
                    'title' => $sectionTitle,
                    'content' => $sectionContent,
                    'score' => $sectionScore
                ];
            }
        }
        
        // Ordenar secciones por relevancia
        usort($relevantSections, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($relevantSections, 0, 2); // Máximo 2 secciones más relevantes
    }
    
    /**
     * Formatear el contexto para enviar al modelo
     */
    public function formatContextForLLM($query) {
        $relevantDocs = $this->findRelevantContext($query);
        
        if (empty($relevantDocs)) {
            return "No se encontró información específica en la base de conocimientos para esta consulta.";
        }
        
        $contextText = "INFORMACIÓN DISPONIBLE EN LA BASE DE CONOCIMIENTOS UNAC:\n\n";
        
        foreach ($relevantDocs as $doc) {
            $contextText .= "=== " . strtoupper($doc['title']) . " ===\n";
            
            if (!empty($doc['relevantSections'])) {
                foreach ($doc['relevantSections'] as $section) {
                    $contextText .= "\n**" . $section['title'] . "**\n";
                    $contextText .= $section['content'] . "\n";
                }
            } else {
                // Si no hay secciones relevantes específicas, usar el contenido completo pero limitado
                $content = $doc['content'];
                if (strlen($content) > 1000) {
                    $content = substr($content, 0, 1000) . "...";
                }
                $contextText .= $content . "\n";
            }
            $contextText .= "\n" . str_repeat("-", 50) . "\n\n";
        }
        
        return $contextText;
    }
    
    /**
     * Obtener estadísticas de la base de conocimientos
     */
    public function getStats() {
        $totalDocs = count($this->documents);
        $totalKeywords = 0;
        
        foreach ($this->documents as $doc) {
            $totalKeywords += count($doc['keywords']);
        }
        
        return [
            'total_documents' => $totalDocs,
            'total_keywords' => $totalKeywords,
            'documents' => array_keys($this->documents)
        ];
    }
}
?>