<?php

namespace App\Infrastructure\Api;

use App\Infrastructure\Cache\CacheFactory;
use App\Infrastructure\Cache\CacheServiceInterface;
use App\Infrastructure\Persistence\Database\MySQLiConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OpenLibraryApiClient
{
    private Client $httpClient;
    private MySQLiConnection $db;
    private CacheServiceInterface $cache;
    private const BASE_URL = 'https://openlibrary.org/api/books';
    private const CACHE_TTL = 86400; //24 horas en segundos

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
        $this->db = MySQLiConnection::getInstance();

        // Inicializar el servicio de caché
        $this->cache = CacheFactory::create('redis');
    }

    /**
     * Busca un libro por ISBN y retorna su descripción y URL de portada
     */
    public function getBookInfoByIsbn(string $isbn): array
    {
        // Limpiar el ISBN para usar como clave de caché
        $cleanIsbn = preg_replace('/[^0-9X]/', '', $isbn);
        $cacheKey = 'book_info_' . $cleanIsbn;

        // Verificar si tenemos la información en caché
        $cachedInfo = $this->cache->get($cacheKey);
        if ($cachedInfo !== null) {
            return $cachedInfo;
        }

        // Si no está en caché, consultar la API
        $startTime = microtime(true);
        $responseCode = 0;
        $info = [
            'title' => null,
            'author' => null,
            'publication_year' => null,
            'description' => null,
            'cover_url' => null
        ];

        try {
            $response = $this->httpClient->request('GET', self::BASE_URL, [
                'query' => [
                    'bibkeys' => "ISBN:{$cleanIsbn}",
                    'format' => 'json',
                    'jscmd' => 'data'
                ]
            ]);

            $responseCode = $response->getStatusCode();

            if ($responseCode === 200) {
                $data = json_decode($response->getBody(), true);

                $bookKey = "ISBN:{$cleanIsbn}";

                if (isset($data[$bookKey])) {
                    $bookData = $data[$bookKey];

                    if (isset($bookData['title'])) {
                        $info['title'] = $bookData['title'];
                    }

                    if (isset($bookData['authors']) && !empty($bookData['authors'])) {
                        $info['author'] = $bookData['authors'][0]['name'] ?? null;
                    }

                    if (isset($bookData['publish_date'])) {
                        // Intentar extraer el año de la fecha de publicación usando expresiones regulares
                        if (preg_match('/(\d{4})/', $bookData['publish_date'], $matches)) {
                            $info['publication_year'] = intval($matches[1]);
                        }
                    }

                    if (isset($bookData['key'])) {
                        $bookDetailsUrl = 'https://openlibrary.org' . $bookData['key'] . '.json';
                        try {
                            $detailsResponse = $this->httpClient->request('GET', $bookDetailsUrl);
                            if ($detailsResponse->getStatusCode() === 200) {
                                $detailsData = json_decode($detailsResponse->getBody(), true);
                                if (isset($detailsData['description'])) {
                                    $info['description'] = is_array($detailsData['description'])
                                        ? $detailsData['description']['value']
                                        : $detailsData['description'];
                                }
                            }
                        } catch (\Exception $e) {
                            error_log("Error al obtener detalles extendidos del libro: " . $e->getMessage());
                        }
                    }

                    // Alternativas si no hay descripción
                    if (empty($info['description'])) {
                        if (isset($bookData['notes'])) {
                            $info['description'] = "Notas: " . (is_array($bookData['notes']) ? $bookData['notes']['value'] : $bookData['notes']);
                        } elseif (isset($bookData['excerpts']) && !empty($bookData['excerpts'][0]['text'])) {
                            $info['description'] = "Extracto del libro: \"" . $bookData['excerpts'][0]['text'] . "\"";
                        } elseif (isset($bookData['table_of_contents']) && !empty($bookData['table_of_contents'])) {
                            $contents = array_column($bookData['table_of_contents'], 'title');
                            $info['description'] = "Contenido: " . implode("; ", $contents);
                        } elseif (isset($bookData['subjects']) && !empty($bookData['subjects'])) {
                            $subjects = array_map(function ($subject) {
                                return is_array($subject) ? ($subject['name'] ?? '') : $subject;
                            }, $bookData['subjects']);
                            $info['description'] = "Temas: " . implode(", ", $subjects);
                        }
                    }

                    // Extraer URL de portada
                    if (isset($bookData['cover'])) {
                        if (isset($bookData['cover']['large'])) {
                            $info['cover_url'] = $bookData['cover']['large'];
                        } elseif (isset($bookData['cover']['medium'])) {
                            $info['cover_url'] = $bookData['cover']['medium'];
                        } elseif (isset($bookData['cover']['small'])) {
                            $info['cover_url'] = $bookData['cover']['small'];
                        }
                    } else {
                        //Si no hay cover en la respuesta, usar el servicio de portadas de Open Library
                        $info['cover_url'] = "https://covers.openlibrary.org/b/isbn/{$cleanIsbn}-L.jpg";
                    }

                    $this->cache->set($cacheKey, $info, self::CACHE_TTL);
                }
            }
        } catch (GuzzleException $e) {
            error_log("Error al consultar Open Library API: " . $e->getMessage());
            $responseCode = $e->getCode() ?: 500;
        } finally {
            $this->logApiRequest($isbn, $responseCode, microtime(true) - $startTime);
        }

        return $info;
    }

    /**
     * Registra la petición a la API en la base de datos
     */
    private function logApiRequest(string $isbn, int $responseCode, float $responseTime): void
    {
        $endpoint = $this->db->escape(self::BASE_URL);
        $parameters = $this->db->escape("bibkeys=ISBN:{$isbn},format=json,jscmd=data");
        $responseCode = (int)$responseCode;
        $responseTime = (float)$responseTime;

        $sql = "INSERT INTO api_requests_log (endpoint, parameters, response_code, response_time) 
                VALUES ('{$endpoint}', '{$parameters}', {$responseCode}, {$responseTime})";

        try {
            $this->db->query($sql);
        } catch (\Exception $e) {
            error_log("Error al registrar petición API en el log: " . $e->getMessage());
        }
    }

    /**
     * Limpia la caché de información de libros
     */
    public function clearCache(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Elimina la caché de un libro específico por ISBN
     */
    public function clearBookCache(string $isbn): bool
    {
        $cleanIsbn = preg_replace('/[^0-9X]/', '', $isbn);
        $cacheKey = 'book_info_' . $cleanIsbn;
        return $this->cache->delete($cacheKey);
    }
}