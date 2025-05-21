<?php

use App\Infrastructure\Cache\RedisCacheService;
use PHPUnit\Framework\TestCase;

class RedisCacheTest extends TestCase
{
    private RedisCacheService $cacheService;
    private string $testPrefix = 'test_books_api:';

    protected function setUp(): void
    {
        // Usamos un prefijo diferente para los tests para no interferir con la app real
        $this->cacheService = new RedisCacheService(
            'servidor_redis',
            6379,
            $this->testPrefix
        );

        // Limpiamos la caché de prueba antes de cada test
        $this->cacheService->clear();
    }

    protected function tearDown(): void
    {
        // Limpiamos después de cada test también
        $this->cacheService->clear();
    }

    public function testSetAndGetValue(): void
    {
        $key = 'user_paco';
        $value = ['apellido' => 'Cubel', 'edad' => 36];

        // Guardar en caché
        $result = $this->cacheService->set($key, $value);
        $this->assertTrue($result, 'El valor debería guardarse correctamente');

        // Recuperar de caché
        $cachedValue = $this->cacheService->get($key);
        $this->assertEquals($value, $cachedValue, 'El valor recuperado debería ser igual al original');
    }

    public function testGetNonExistentKey(): void
    {
        $key = 'user_inventado';

        $value = $this->cacheService->get($key);
        $this->assertNull($value, 'El valor para una clave inexistente debería ser null');
    }

    public function testHasKey(): void
    {
        $key = 'paco2';
        $value = 'datos_falsos';

        // Inicialmente la clave no existe
        $this->assertFalse($this->cacheService->has($key), 'La clave no debería existir inicialmente');

        // Guardar un valor
        $this->cacheService->set($key, $value);

        // Ahora la clave debería existir
        $this->assertTrue($this->cacheService->has($key), 'La clave debería existir después de guardar');
    }

    public function testSetWithTtl(): void
    {
        $key = 'paco3';
        $value = 'datos_inventados_2';

        // Guardar con TTL de 1 segundo
        $this->cacheService->set($key, $value, 1);

        // Verificar que el valor existe
        $this->assertTrue($this->cacheService->has($key), 'El valor debería existir inmediatamente');

        // Esperar a que expire
        sleep(2);

        // Verificar que el valor ya no existe
        $this->assertFalse($this->cacheService->has($key), 'El valor debería haber expirado');
    }

    public function testDeleteKey(): void
    {
        $key = 'user_paco4';
        $value = 'inventados';

        // Guardar valor
        $this->cacheService->set($key, $value);

        // Verificar que existe
        $this->assertTrue($this->cacheService->has($key), 'La clave debería existir');

        // Eliminar
        $result = $this->cacheService->delete($key);
        $this->assertTrue($result, 'La operación de eliminación debería ser exitosa');

        // Verificar que ya no existe
        $this->assertFalse($this->cacheService->has($key), 'La clave no debería existir después de eliminarla');
    }

    public function testClear(): void
    {
        // Guardar varios valores
        for ($i = 1; $i <= 5; $i++) {
            $this->cacheService->set("test_key_{$i}", "value_{$i}");
        }

        // Verificar que existen
        for ($i = 1; $i <= 5; $i++) {
            $this->assertTrue($this->cacheService->has("test_key_{$i}"), "La clave test_key_{$i} debería existir");
        }

        // Limpiar todo
        $result = $this->cacheService->clear();
        $this->assertTrue($result, 'La operación de limpieza debería ser exitosa');

        // Verificar que ya no existen
        for ($i = 1; $i <= 5; $i++) {
            $this->assertFalse($this->cacheService->has("test_key_{$i}"), "La clave test_key_{$i} no debería existir después de limpiar");
        }
    }
}