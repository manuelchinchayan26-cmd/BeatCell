<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Cache-Control: no-store');

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? ($_SESSION['usuario']['rol'] ?? '');
if ($rol !== 'Estudiante') {
    header('Location: ../../public/index.php');
    exit;
}

$escapar = static fn(mixed $valor): string => htmlspecialchars(
    (string) $valor,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$logros = [];
$error = '';

try {
    require_once __DIR__ . '/../../src/Controllers/LogroController.php';
    $controlador = new LogroController();
    $logros = $controlador->listarPorUsuario((int) $_SESSION['id_usuario']);
} catch (Throwable $e) {
    error_log('BeatCell - error al cargar los logros: ' . $e->getMessage());
    $error = 'No se pudieron cargar tus logros. Inténtalo nuevamente.';
}

$mostrarFecha = static function (mixed $fecha): string {
    if (!is_string($fecha) || trim($fecha) === '') {
        return 'Fecha no disponible';
    }

    try {
        return (new DateTimeImmutable($fecha))->format('d/m/Y');
    } catch (Throwable) {
        return 'Fecha no disponible';
    }
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis logros | BeatCell</title>
    <link rel="stylesheet" href="../../public/assets/css/estudiante.css?v=<?= filemtime(__DIR__ . '/../../public/assets/css/estudiante.css') ?>">
    <link rel="stylesheet" href="../../public/assets/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/assets/css/dashboard.css') ?>">
</head>
<body>
<?php require __DIR__ . '/_navegacion.php'; ?>
<main class="contenido-pagina">
    <header class="encabezado-logros">
        <p class="marca-logros">BeatCell · Aprendizaje</p>
        <h1>Mis logros</h1>
        <p>Revisa los reconocimientos que has conseguido durante tus actividades.</p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="aviso error" role="alert"><?= $escapar($error) ?></p>
    <?php elseif ($logros === []): ?>
        <section class="estado-vacio">
            <div class="logro-icono" aria-hidden="true">🏆</div>
            <h2>Aún no tienes logros registrados</h2>
            <p>Completa tus actividades y sigue avanzando para conseguir tus primeros reconocimientos.</p>
            <a class="boton-logros" href="actividades.php">Ver actividades</a>
        </section>
    <?php else: ?>
        <p class="resumen-logros">Tienes <strong><?= count($logros) ?></strong> logro<?= count($logros) === 1 ? '' : 's' ?> conseguido<?= count($logros) === 1 ? '' : 's' ?>.</p>
        <section class="lista-logros" aria-label="Logros conseguidos">
            <?php foreach ($logros as $logro): ?>
                <?php $icono = trim((string) ($logro['icono'] ?? '')); ?>
                <article class="tarjeta-logro">
                    <div class="logro-icono" aria-hidden="true"><?= $escapar($icono !== '' ? $icono : '🏆') ?></div>
                    <div class="detalle-logro">
                        <h2><?= $escapar($logro['nombre'] ?? 'Logro') ?></h2>
                        <?php if (!empty($logro['descripcion'])): ?>
                            <p><?= $escapar($logro['descripcion']) ?></p>
                        <?php endif; ?>
                        <small>Conseguido el <?= $escapar($mostrarFecha($logro['fecha_obtenido'] ?? null)) ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
