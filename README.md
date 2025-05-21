# Información de instalación

## Instalación del proyecto
```
docker compose up -d 
```
### Después hay que descargar las libreias de composer
```
docker exec -w /var/www servidor_apache composer install --no-interaction --optimize-autoloader
```
Llegados a este punto, ya debería estar funcionando todo.


# Información de inicio
Existe un PHPMyAdmin para poder ver la base de datos en la siguiente url:
```
http://localhost:2001/
```
Para facilitar las pruebas, he creado una interfaz web que está disponible en la url:
```
http://localhost:2004/
```
De inicio, en base de datos hay un usuario creado con las siguientes credenciales:
```
USER: admin
PASS: admin123
MAIL: admin@admin.com
```

# Información de ejecución de pruebas
## Ejecutar todas las pruebas existentes
```
docker exec -w /var/www servidor_apache vendor/bin/phpunit
```
## Ejecutar una prueba especifica
Prueba de la clase BookTest.php
```
docker exec -w /var/www servidor_apache vendor/bin/phpunit src/Tests/BookTest.php
```
Prueba de la clase UserTest.php
```
docker exec -w /var/www servidor_apache vendor/bin/phpunit src/Tests/UserTest.php
```
Prueba de la clase RedisCacheTest.php
```
docker exec -w /var/www servidor_apache vendor/bin/phpunit src/Tests/RedisCacheTest.php
```

# Información de pruebas de la API
(NO NECESARIO) Aunque ya existe un usuario registrado, se puede registrar un nuevo usuario
```
curl -X POST http://localhost:2000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "nuevoUsuario",
    "email": "test@test.com",
    "password": "inventado123"
  }'
```
Iniciar sesión para conseguir el token
```
curl -X POST http://localhost:2000/api/auth \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }'
```
Crear un libro (sustituir el TOKEN por el obtenido en el paso del login)
```
curl -X POST http://localhost:2000/api/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "title": "El Hobbit",
    "author": "J.R.R. Tolkien",
    "isbn": "9788445073803",
    "publication_year": 1937
  }'
```
Lista de libros  (sustituir el TOKEN por el obtenido en el paso del login)
```
curl -X GET "http://localhost:2000/api/books?limit=10&offset=0" \
  -H "Authorization: Bearer TOKEN"
```
Obtener un libro por id (sustituir el TOKEN por el obtenido en el paso del login)
```
curl -X GET http://localhost:2000/api/books/1 \
  -H "Authorization: Bearer TOKEN"
```
Obtener un libro por título o autor (sustituir el TOKEN por el obtenido en el paso del login)
```
curl -X GET "http://localhost:2000/api/books?search=tolkien" \
  -H "Authorization: Bearer TOKEN"
```
Actualizar un libro (sustituir el TOKEN por el obtenido en el paso del login)
```
curl -X PUT http://localhost:2000/api/books/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "title": "El Hobbit Inventado",
    "author": "J.R.R. Paco",
    "isbn": "9788445073803",
    "publication_year": 1937
  }'
```
Eliminar un libro (sustituir el TOKEN por el obtenido en el paso del login)
```
curl -X DELETE http://localhost:2000/api/books/1 \
  -H "Authorization: Bearer TOKEN"
```

# Otra información
## Sistema de Caché
### La aplicación implementa un sistema de caché utilizando Redis para optimizar las consultas a la API externa:
```
Las respuestas de Open Library se almacenan en caché durante 24 horas
Cuando se solicita información de un libro por ISBN, primero se verifica en la caché
Solo se realiza una llamada a la API externa si la información no está en caché
```