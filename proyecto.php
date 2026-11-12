<?php
/**
 * Tecnopatas - Portal de Noticias Tecnológicas
 * Un solo archivo PHP con conexión a MySQL, página web y administración
 */

$host = 'localhost';
$db   = 'tecnopatas_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        badge VARCHAR(100) NOT NULL,
        category VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        `desc` TEXT NOT NULL,
        full_content LONGTEXT NOT NULL,
        date_str VARCHAR(100) NOT NULL,
        read_time VARCHAR(50) NOT NULL,
        image_url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        source VARCHAR(100) NOT NULL,
        date_str VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
    if ($stmt->fetchColumn() == 0) {
        $stmtIns = $pdo->prepare("INSERT INTO articles (badge, category, title, `desc`, full_content, date_str, read_time, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->execute([
            'Inteligencia Artificial',
            'IA',
            'El impacto de la nueva generación de microprocesadores en el desarrollo de IA',
            'La arquitectura de los nuevos procesadores promete multiplicar la eficiencia en tareas de inteligencia artificial y aprendizaje automático.',
            '<p>Los microprocesadores de arquitectura neuromórfica están marcando el hito más grande de la última década en computación local.</p><p>Gracias a la inclusión de unidades de procesamiento tensorial (TPUs) directamente integradas en el silicio, los desarrolladores pueden ejecutar modelos de IA en tiempo real.</p>',
            '11 de Noviembre, 2026',
            '6 min lectura',
            'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80'
        ]);

        $stmtIns->execute([
            'Hardware',
            'Hardware',
            'Nuevas GPUs optimizadas para renderizado 3D y simulaciones en tiempo real',
            'La nueva serie de tarjetas gráficas introduce núcleos dedicados para trazado de rayos extremo y cálculo físico acelerado por hardware.',
            '<p>Las nuevas arquitecturas gráficas revolucionan el flujo de trabajo en render 3D y simulaciones complejas.</p>',
            '11 de Noviembre, 2026',
            '4 min lectura',
            'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=300&q=80'
        ]);

        $stmtIns->execute([
            'Ciberseguridad',
            'Seguridad',
            'Recomendaciones clave para asegurar servidores de aplicaciones web en 2026',
            'Estrategias avanzadas de mitigación de ataques DDoS, cifrado post-cuántico y auditoría continua de contenedores en la nube.',
            '<p>La transición hacia algoritmos de cifrado resistentes a computación cuántica es ahora una prioridad crítica de infraestructura.</p>',
            '10 de Noviembre, 2026',
            '8 min lectura',
            'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80'
        ]);
    }
} catch (PDOException $e) {
    $pdo = null;
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if ($pdo) {
        $action = $_POST['action'] ?? '';

        if ($action === 'login') {
            $email = $_POST['email'] ?? '';
            $passValue = $_POST['pass'] ?? '';

            if ($email === 'admin@tecnopatas.com' && $passValue === 'tecnopatas2026') {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
            }
            exit;
        }

        if ($action === 'save_article') {
            $id = $_POST['id'] ?? '';
            $badge = $_POST['badge'] ?? '';
            $category = $_POST['category'] ?? '';
            $title = $_POST['title'] ?? '';
            $desc = $_POST['desc'] ?? '';
            $fullContent = $_POST['full_content'] ?? '';
            $readTime = $_POST['read_time'] ?? '5 min lectura';
            $imageUrl = $_POST['image_url'] ?? '';
            $dateStr = date('d \d\e F, Y');

            if ($id) {
                $stmt = $pdo->prepare("UPDATE articles SET badge=?, category=?, title=?, `desc`=?, full_content=?, read_time=?, image_url=? WHERE id=?");
                $stmt->execute([$badge, $category, $title, $desc, $fullContent, $readTime, $imageUrl, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO articles (badge, category, title, `desc`, full_content, date_str, read_time, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$badge, $category, $title, $desc, $fullContent, $dateStr, $readTime, $imageUrl]);
            }

            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete_article') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'save_contact') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            $source = $_POST['source'] ?? 'Contacto';
            $dateStr = date('d/m/Y H:i');

            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, source, date_str) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message, $source, $dateStr]);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete_contact') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'MySQL no disponible']);
    exit;
}

// Obtener datos
$articles = [];
$contacts = [];

if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM articles ORDER BY id DESC");
    $articles = $stmt->fetchAll();

    $stmt2 = $pdo->query("SELECT * FROM contacts ORDER BY id DESC");
    $contacts = $stmt2->fetchAll();
} else {
    $articles = [
        [
            'id' => 1,
            'badge' => 'Inteligencia Artificial',
            'category' => 'IA',
            'title' => 'El impacto de la nueva generación de microprocesadores en el desarrollo de IA',
            'desc' => 'La arquitectura de los nuevos procesadores promete multiplicar la eficiencia en tareas de inteligencia artificial y aprendizaje automático.',
            'full_content' => '<p>Los microprocesadores de arquitectura neuromórfica están marcando el hito más grande de la última década.</p>',
            'date_str' => '11 de Noviembre, 2026',
            'read_time' => '6 min lectura',
            'image_url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnopatas - Portal de Noticias Tecnológicas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0b0c10;
            --bg-soft: #12151d;
            --card: #171b24;
            --card-hover: #1d2430;
            --border: rgba(255,255,255,0.08);
            --text: #edf3ff;
            --muted: #b9c3d5;
            --brand: #8b5cf6;
            --brand-2: #7c3aed;
            --success: #22c55e;
            --danger: #ef4444;
            --shadow: 0 20px 50px rgba(0,0,0,0.25);
        }

        * { box-sizing: border-box; }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #0b0c10 0%, #0d1220 100%);
            color: var(--text);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
            display: block;
        }

        button, input, textarea {
            font: inherit;
        }

        .container {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(23, 27, 36, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 78px;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: -0.04em;
            font-size: clamp(1.3rem, 2vw, 2rem);
            cursor: pointer;
        }

        .logo-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(139, 92, 246, 0.18);
            border: 1px solid rgba(139, 92, 246, 0.4);
            display: grid;
            place-items: center;
            color: var(--brand);
            font-size: 1.4rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: #c5cedd;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--brand);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .icon-btn,
        .admin-btn {
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.02);
            color: #dfe7f7;
            border-radius: 999px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .icon-btn {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
        }

        .icon-btn:hover,
        .admin-btn:hover {
            border-color: rgba(139, 92, 246, 0.5);
            color: white;
            transform: translateY(-1px);
        }

        .admin-btn {
            padding: 10px 16px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--brand);
            background: rgba(139, 92, 246, 0.12);
        }

        .main-container {
            padding: 32px 0 60px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.7fr 0.9fr;
            gap: 28px;
            align-items: stretch;
        }

        .featured-card {
            position: relative;
            min-height: 540px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            background: var(--card);
        }

        .featured-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: opacity 0.3s ease, background-image 0.3s ease;
        }

        .featured-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(11,12,16,0.12) 0%, rgba(11,12,16,0.85) 70%, rgba(11,12,16,0.95) 100%);
        }

        .featured-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: end;
            padding: 32px 28px;
            gap: 14px;
        }

        .badge {
            display: inline-block;
            width: fit-content;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.35);
            color: #e9ddff;
        }

        .featured-content h1 {
            margin: 0;
            font-size: clamp(2rem, 3vw, 3.3rem);
            line-height: 1.06;
            letter-spacing: -0.05em;
            max-width: 760px;
        }

        .featured-content p {
            margin: 0;
            font-size: 1rem;
            line-height: 1.7;
            color: rgba(237, 243, 255, 0.82);
            max-width: 700px;
        }

        .featured-meta {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
            color: rgba(237, 243, 255, 0.8);
            font-size: 0.9rem;
            margin-top: 6px;
        }

        .btn-read {
            display: inline-block;
            width: fit-content;
            margin-top: 10px;
            padding: 12px 22px;
            border-radius: 999px;
            font-weight: 700;
            background: var(--brand);
            color: white;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.35);
            transition: 0.2s ease;
        }

        .btn-read:hover {
            background: var(--brand-2);
            transform: translateY(-1px);
        }

        .sidebar-news {
            background: rgba(23, 27, 36, 0.9);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.3rem;
            letter-spacing: -0.03em;
        }

        .view-all {
            color: var(--brand);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .sidebar-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .news-mini-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px;
            border-radius: 18px;
            background: transparent;
            border: 1px solid transparent;
            transition: 0.2s ease;
            cursor: pointer;
        }

        .news-mini-card:hover,
        .news-mini-card.active {
            background: rgba(255,255,255,0.02);
            border-color: rgba(139, 92, 246, 0.2);
        }

        .news-mini-card img {
            width: 78px;
            height: 78px;
            object-fit: cover;
            border-radius: 14px;
            flex-shrink: 0;
        }

        .news-mini-info {
            min-width: 0;
        }

        .mini-category {
            display: inline-block;
            color: var(--brand);
            font-size: 0.63rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .news-mini-card h3 {
            margin: 0;
            font-size: 0.96rem;
            line-height: 1.4;
            color: var(--text);
        }

        .mini-time {
            display: inline-block;
            color: rgba(237,243,255,0.7);
            font-size: 0.75rem;
            margin-top: 8px;
        }

        .categories-section {
            margin-top: 38px;
            background: rgba(23,27,36,0.84);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 28px;
            box-shadow: var(--shadow);
        }

        .categories-section h2 {
            margin: 0 0 18px;
            font-size: clamp(1.5rem, 2vw, 2.2rem);
            letter-spacing: -0.04em;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .category-item {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px 16px;
            transition: 0.2s ease;
        }

        .category-item:hover {
            background: rgba(139, 92, 246, 0.08);
            border-color: rgba(139, 92, 246, 0.3);
            transform: translateY(-2px);
        }

        .cat-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(139, 92, 246, 0.14);
            font-size: 1.5rem;
        }

        .category-item span {
            font-size: 1.02rem;
            font-weight: 700;
        }

        .hidden {
            display: none !important;
        }

        .admin-shell {
            margin-top: 30px;
            background: rgba(23,27,36,0.9);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .admin-title {
            margin: 0;
            font-size: clamp(1.8rem, 2vw, 2.4rem);
            letter-spacing: -0.05em;
        }

        .admin-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .danger-btn {
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(239, 68, 68, 0.35);
            background: rgba(239, 68, 68, 0.08);
            color: #fca5a5;
            cursor: pointer;
            font-weight: 700;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1.1fr 1.5fr;
            gap: 22px;
        }

        .panel {
            background: rgba(255,255,255,0.015);
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 22px;
        }

        .panel h3 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 1.25rem;
        }

        .form-grid {
            display: grid;
            gap: 12px;
        }

        .field {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(11,12,16,0.7);
            color: var(--text);
            outline: none;
        }

        .field:focus {
            border-color: rgba(139, 92, 246, 0.6);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.14);
        }

        textarea.field {
            resize: vertical;
            min-height: 110px;
        }

        .primary-btn {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
            color: white;
            font-weight: 800;
            padding: 14px 16px;
            border-radius: 12px;
            cursor: pointer;
        }

        .article-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
        }

        .article-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(11, 12, 16, 0.7);
        }

        .article-item small {
            display: block;
            color: var(--brand);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }

        .article-item strong {
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .article-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .small-btn {
            padding: 8px 10px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            border-radius: 10px;
            cursor: pointer;
        }

        .small-btn.danger {
            border-color: rgba(239, 68, 68, 0.36);
            color: #fca5a5;
        }

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.72);
            display: grid;
            place-items: center;
            z-index: 200;
            padding: 18px;
        }

        .modal.hidden {
            display: none;
        }

        .modal-card {
            background: rgba(23,27,36,1);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: min(100%, 440px);
            padding: 24px 24px 18px;
            box-shadow: var(--shadow);
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            color: #dfe7f7;
            cursor: pointer;
        }

        .modal-card h2 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 1.8rem;
            letter-spacing: -0.04em;
        }

        .login-form {
            display: grid;
            gap: 12px;
        }

        .login-form .field {
            padding: 14px 14px;
        }

        .title-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #bbf7d0;
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.32);
            border-radius: 999px;
            padding: 6px 10px;
        }

        @media (max-width: 980px) {
            .content-grid,
            .admin-grid {
                grid-template-columns: 1fr;
            }

            .categories-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 620px) {
            .nav-inner {
                height: auto;
                padding: 16px 0;
                flex-wrap: wrap;
            }

            .nav-links {
                width: 100%;
                justify-content: center;
                order: 3;
                flex-wrap: wrap;
                gap: 12px 20px;
            }

            .featured-content {
                padding: 22px 20px;
            }

            .featured-card {
                min-height: 480px;
            }

            .categories-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-header {
                align-items: start;
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="container nav-inner">
            <div class="logo" onclick="navigateTo('home')">
                <div class="logo-mark">⚡</div>
                <span>Tecnopatas</span>
            </div>

            <nav>
                <ul class="nav-links">
                    <li><a href="#" class="active" onclick="navigateTo('home'); return false;">Inicio</a></li>
                    <li><a href="#" onclick="navigateTo('about'); return false;">Quiénes Somos</a></li>
                    <li><a href="#" onclick="navigateTo('admin'); return false;">Admin</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <button class="icon-btn" aria-label="Buscar">⌕</button>
                <button class="admin-btn" onclick="handleAdminNavClick()">🔐 Admin SQL</button>
            </div>
        </div>
    </header>

    <main class="main-container">
        <div class="container">
            <section id="view-home">
                <div class="content-grid">
                    <section class="featured-card" id="featured-article">
                        <div class="featured-bg" id="feat-bg" style="background-image: url('<?php echo htmlspecialchars($articles[0]['image_url'] ?? ''); ?>');"></div>
                        <div class="featured-overlay"></div>
                        <div class="featured-content">
                            <span class="badge" id="feat-badge"><?php echo htmlspecialchars($articles[0]['badge'] ?? ''); ?></span>
                            <h1 id="feat-title"><?php echo htmlspecialchars($articles[0]['title'] ?? ''); ?></h1>
                            <p id="feat-desc"><?php echo htmlspecialchars($articles[0]['desc'] ?? ''); ?></p>

                            <div class="featured-meta">
                                <span>📅 <span id="feat-date"><?php echo htmlspecialchars($articles[0]['date_str'] ?? ''); ?></span></span>
                                <span>⏱️ <span id="feat-read"><?php echo htmlspecialchars($articles[0]['read_time'] ?? ''); ?></span></span>
                            </div>

                            <a href="#" class="btn-read" onclick="openModalArticle(); return false;">Leer más →</a>
                        </div>
                    </section>

                    <aside class="sidebar-news">
                        <div class="sidebar-header">
                            <h2>Últimas Noticias</h2>
                            <a href="#" class="view-all" onclick="return false;">Ver todas →</a>
                        </div>

                        <div class="sidebar-list">
                            <?php foreach (array_slice($articles, 0, 4) as $index => $article): ?>
                                <article class="news-mini-card <?php echo $index === 0 ? 'active' : ''; ?>" onclick="switchNews(<?php echo $index; ?>)">
                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <div class="news-mini-info">
                                        <span class="mini-category"><?php echo htmlspecialchars($article['category']); ?></span>
                                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                        <span class="mini-time">⏱️ <?php echo htmlspecialchars($article['read_time']); ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </aside>
                </div>

                <section class="categories-section">
                    <h2>Explora por categoría</h2>
                    <div class="categories-grid">
                        <a href="#" class="category-item">
                            <div class="cat-icon">💻</div>
                            <span>Hardware</span>
                        </a>
                        <a href="#" class="category-item">
                            <div class="cat-icon">🌐</div>
                            <span>Software</span>
                        </a>
                        <a href="#" class="category-item">
                            <div class="cat-icon">🤖</div>
                            <span>IA</span>
                        </a>
                        <a href="#" class="category-item">
                            <div class="cat-icon">🛡️</div>
                            <span>Seguridad</span>
                        </a>
                    </div>
                </section>
            </section>

            <section id="view-about" class="hidden">
                <div class="admin-shell">
                    <div class="title-wrap">
                        <h1 class="admin-title">Quiénes Somos • <span style="color: var(--brand);">Tecnopatas</span></h1>
                        <span class="status">✔ Portal tecnológico con MySQL</span>
                    </div>
                    <p style="color: var(--muted); font-size: 1.05rem; line-height: 1.8; margin-top: 18px;">
                        Somos un portal especializado en noticias de hardware, software e inteligencia artificial. Nuestro sistema
                        integra una base de datos MySQL para gestionar artículos, contenidos y mensajes de contacto de forma dinámica.
                    </p>
                </div>
            </section>

            <section id="view-admin" class="hidden">
                <div class="admin-shell">
                    <div class="admin-header">
                        <h1 class="admin-title">Panel Administrador SQL</h1>
                        <div class="admin-actions">
                            <button class="danger-btn" onclick="logoutAdmin()">Cerrar Sesión</button>
                        </div>
                    </div>

                    <div class="admin-grid">
                        <div class="panel">
                            <h3>Publicar Noticia</h3>
                            <form id="sqlArticleForm" onsubmit="saveSqlArticle(event)" class="form-grid">
                                <input type="hidden" id="sqlEditId" value="">
                                <input type="text" id="sqlTitle" required placeholder="Título" class="field">
                                <input type="text" id="sqlBadge" required placeholder="Badge" class="field">
                                <input type="text" id="sqlCategory" required placeholder="Categoría" class="field">
                                <input type="url" id="sqlImage" required placeholder="URL de imagen" class="field">
                                <textarea id="sqlDesc" required placeholder="Resumen corto" class="field"></textarea>
                                <textarea id="sqlContent" required placeholder="Contenido HTML completo" class="field"></textarea>
                                <button type="submit" class="primary-btn">Guardar en MySQL</button>
                            </form>
                        </div>

                        <div class="panel">
                            <h3>Artículos almacenados</h3>
                            <ul class="article-list">
                                <?php foreach ($articles as $a): ?>
                                    <li class="article-item">
                                        <div>
                                            <small><?php echo htmlspecialchars($a['category']); ?></small>
                                            <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                                        </div>
                                        <div class="article-actions">
                                            <button class="small-btn" type="button" onclick="editSqlArticle(<?php echo (int)$a['id']; ?>)">Editar</button>
                                            <button class="small-btn danger" type="button" onclick="deleteSqlArticle(<?php echo (int)$a['id']; ?>)">Eliminar</button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div id="loginModal" class="modal hidden">
        <div class="modal-card">
            <button class="close-modal" onclick="closeLoginModal()">✕</button>
            <h2>Acceso Admin SQL</h2>
            <form class="login-form" onsubmit="handleLogin(event)">
                <input type="email" id="loginEmail" value="admin@tecnopatas.com" class="field" required>
                <input type="password" id="loginPass" value="tecnopatas2026" class="field" required>
                <button type="submit" class="primary-btn">Entrar al Panel</button>
            </form>
        </div>
    </div>

    <script>
        const articlesData = <?php echo json_encode($articles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        let currentIndex = 0;

        function showView(viewId) {
            document.getElementById('view-home').classList.add('hidden');
            document.getElementById('view-about').classList.add('hidden');
            document.getElementById('view-admin').classList.add('hidden');

            if (viewId === 'home') {
                document.getElementById('view-home').classList.remove('hidden');
            }
            if (viewId === 'about') {
                document.getElementById('view-about').classList.remove('hidden');
            }
            if (viewId === 'admin') {
                document.getElementById('view-admin').classList.remove('hidden');
            }
        }

        function navigateTo(viewId) {
            if (viewId === 'admin' && !sessionStorage.getItem('admin_sql')) {
                document.getElementById('loginModal').classList.remove('hidden');
                return;
            }

            showView(viewId);
        }

        function openModalArticle() {
            const art = articlesData[currentIndex];
            if (!art) return;

            alert(
                'Artículo: ' + art.title + '\n\n' +
                art.desc + '\n\n' +
                'Categoría: ' + art.category + '\n' +
                'Fecha: ' + art.date_str + '\n' +
                'Tiempo: ' + art.read_time
            );
        }

        function switchNews(index) {
            currentIndex = index;
            const item = articlesData[currentIndex];
            if (!item) return;

            const bg = document.getElementById('feat-bg');
            const badge = document.getElementById('feat-badge');
            const title = document.getElementById('feat-title');
            const desc = document.getElementById('feat-desc');
            const date = document.getElementById('feat-date');
            const read = document.getElementById('feat-read');

            bg.style.opacity = '0.5';
            setTimeout(() => {
                bg.style.backgroundImage = `url('${item.image_url}')`;
                badge.textContent = item.badge;
                title.textContent = item.title;
                desc.textContent = item.desc;
                date.textContent = item.date_str;
                read.textContent = item.read_time;
                bg.style.opacity = '1';
            }, 180);

            const cards = document.querySelectorAll('.news-mini-card');
            cards.forEach((card, idx) => {
                card.classList.toggle('active', idx === currentIndex);
            });
        }

        setInterval(() => {
            if (articlesData.length > 0) {
                currentIndex = (currentIndex + 1) % articlesData.length;
                switchNews(currentIndex);
            }
        }, 20000);

        function handleLogin(e) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'login');
            fd.append('email', document.getElementById('loginEmail').value);
            fd.append('pass', document.getElementById('loginPass').value);

            fetch('', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('admin_sql', 'true');
                    document.getElementById('loginModal').classList.add('hidden');
                    showView('admin');
                } else {
                    alert('Credenciales incorrectas');
                }
            })
            .catch(() => alert('Error al iniciar sesión'));
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }

        function handleAdminNavClick() {
            if (sessionStorage.getItem('admin_sql')) {
                showView('admin');
            } else {
                document.getElementById('loginModal').classList.remove('hidden');
            }
        }

        function logoutAdmin() {
            sessionStorage.removeItem('admin_sql');
            showView('home');
        }

        function saveSqlArticle(e) {
            e.preventDefault();

            const fd = new FormData();
            const id = document.getElementById('sqlEditId').value;

            fd.append('action', 'save_article');
            fd.append('id', id);
            fd.append('title', document.getElementById('sqlTitle').value);
            fd.append('badge', document.getElementById('sqlBadge').value);
            fd.append('category', document.getElementById('sqlCategory').value);
            fd.append('image_url', document.getElementById('sqlImage').value);
            fd.append('desc', document.getElementById('sqlDesc').value);
            fd.append('full_content', document.getElementById('sqlContent').value);
            fd.append('read_time', '5 min lectura');

            fetch('', {
                method: 'POST',
                body: fd
            })
            .then(() => location.reload());
        }

        function deleteSqlArticle(id) {
            if (!confirm('¿Eliminar este artículo de MySQL?')) return;

            const fd = new FormData();
            fd.append('action', 'delete_article');
            fd.append('id', id);

            fetch('', {
                method: 'POST',
                body: fd
            })
            .then(() => location.reload());
        }

        function editSqlArticle(id) {
            const article = articlesData.find(item => Number(item.id) === Number(id));
            if (!article) return;

            document.getElementById('sqlEditId').value = article.id;
            document.getElementById('sqlTitle').value = article.title;
            document.getElementById('sqlBadge').value = article.badge;
            document.getElementById('sqlCategory').value = article.category;
            document.getElementById('sqlImage').value = article.image_url;
            document.getElementById('sqlDesc').value = article.desc;
            document.getElementById('sqlContent').value = article.full_content;
            showView('admin');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Cargar vista inicial
        showView('home');
    </script>
</body>
</html>