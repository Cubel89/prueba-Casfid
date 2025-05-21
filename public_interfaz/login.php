<?php
session_start();
require_once 'config.php';


if (isAuthenticated()) {
    header('Location: index.php');
    exit;
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validación básica
    if (empty($username) || empty($password)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        // Intentar autenticar
        $result = callAPI('POST', '/auth', [
            'username' => $username,
            'password' => $password
        ]);

        if ($result['success'] && isset($result['data']['token'])) {
            $_SESSION[SESSION_TOKEN_KEY] = $result['data']['token'];
            header('Location: index.php?success=Has iniciado sesión correctamente');
            exit;
        } else {
            $error = $result['data']['message'] ?? 'Credenciales inválidas';
        }
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'token_expired') {
    $error = 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.';
}

$pageTitle = 'Iniciar Sesión';
$pageHeader = 'Iniciar Sesión';

ob_start();
?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                    </form>
                </div>
            </div>

            <div class="mt-3 text-center">
                <div class="alert alert-info">
                    <p class="mb-0"><strong>Usuario de prueba:</strong> admin</p>
                    <p class="mb-0"><strong>Contraseña:</strong> admin123</p>
                </div>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean();
require_once 'layout.php';
?>