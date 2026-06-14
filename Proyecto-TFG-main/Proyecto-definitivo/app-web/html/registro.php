<?php
session_start();
require_once 'config.php';

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre    = trim($_POST["nombre"]    ?? "");
    $apellidos = trim($_POST["apellidos"] ?? "");
    $email     = trim($_POST["email"]     ?? "");
    $password  = $_POST["password"]       ?? "";
    $password2 = $_POST["password2"]      ?? "";

    // Validaciones
    if (!$nombre || !$email || !$password) {
        $error = "Nombre, email y contraseña son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no tiene un formato válido.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            $db = getDB();

            // Comprobar si el email ya existe
            $check = $db->prepare("SELECT id FROM Usuarios WHERE email = ? LIMIT 1");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = "Ya existe una cuenta con ese email.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO Usuarios (nombre, apellidos, email, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $apellidos ?: null, $email, $hash]);
                $success = "Usuario <strong>" . htmlspecialchars($nombre) . "</strong> registrado correctamente.";
            }
        } catch (PDOException $e) {
            $error = "Error de base de datos: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aromaris – Registro de usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

<div class="card border-0 shadow rounded-4 p-4" style="width: 100%; max-width: 480px;">

    <!-- Cabecera -->
    <div class="text-center mb-4">
        <i class="bi bi-flower1 display-5 text-aromaris"></i>
        <h4 class="fw-bold mt-2">Registro de usuario</h4>
        <p class="text-muted small">Rellena el formulario para crear una cuenta</p>
    </div>

    <!-- Alertas -->
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $success ?>
        </div>
        <div class="text-center">
            <a href="index.php" class="btn btn-aromaris">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ir al inicio de sesión
            </a>
            <button onclick="document.querySelector('form').reset(); document.querySelector('.alert-success').remove();"
                    class="btn btn-outline-secondary ms-2">
                Registrar otro
            </button>
        </div>
    <?php else: ?>

    <!-- Formulario -->
    <form method="POST" novalidate>

        <div class="mb-3">
            <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0"
                       id="nombre" name="nombre"
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                       placeholder="Tu nombre" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="apellidos" class="form-label fw-semibold">Apellidos</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0"
                       id="apellidos" name="apellidos"
                       value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>"
                       placeholder="Tus apellidos (opcional)">
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input type="email" class="form-control border-start-0"
                       id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="correo@ejemplo.com" required autocomplete="email">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input type="password" class="form-control border-start-0"
                       id="password" name="password"
                       placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePass">
                    <i class="bi bi-eye" id="eyeIcon1"></i>
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label for="password2" class="form-label fw-semibold">Repetir contraseña <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock-fill text-muted"></i>
                </span>
                <input type="password" class="form-control border-start-0"
                       id="password2" name="password2"
                       placeholder="Repite la contraseña" required autocomplete="new-password">
                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePass2">
                    <i class="bi bi-eye" id="eyeIcon2"></i>
                </button>
            </div>
            <div id="matchMsg" class="form-text"></div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-aromaris btn-lg">
                <i class="bi bi-person-plus me-2"></i>Crear cuenta
            </button>
        </div>
    </form>

    <?php endif; ?>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
        ¿Ya tienes cuenta? <a href="index.php" class="text-aromaris fw-semibold">Inicia sesión</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle contraseña 1
    document.getElementById("togglePass").addEventListener("click", function () {
        const input = document.getElementById("password");
        const icon  = document.getElementById("eyeIcon1");
        input.type  = input.type === "password" ? "text" : "password";
        icon.classList.toggle("bi-eye");
        icon.classList.toggle("bi-eye-slash");
    });

    // Toggle contraseña 2
    document.getElementById("togglePass2").addEventListener("click", function () {
        const input = document.getElementById("password2");
        const icon  = document.getElementById("eyeIcon2");
        input.type  = input.type === "password" ? "text" : "password";
        icon.classList.toggle("bi-eye");
        icon.classList.toggle("bi-eye-slash");
    });

    // Comprobación en tiempo real de que las contraseñas coinciden
    document.getElementById("password2").addEventListener("input", function () {
        const p1  = document.getElementById("password").value;
        const msg = document.getElementById("matchMsg");
        if (!this.value) { msg.textContent = ""; return; }
        if (this.value === p1) {
            msg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Las contraseñas coinciden</span>';
        } else {
            msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Las contraseñas no coinciden</span>';
        }
    });
</script>
</body>
</html>
