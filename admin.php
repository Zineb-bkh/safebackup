<?php
session_start();

// ==================== CONFIGURATION ====================
$config = [
    'admin_password' => 'admin123', // À changer en production
    'fichier_messages' => 'messages.json'
];

// ==================== LOGOUT ====================
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ==================== LOGIN ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $password = $_POST['password'] ?? '';
    
    if ($password === $config['admin_password']) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = "Mot de passe incorrect";
    }
}

// ==================== AUTHENTICATION CHECK ====================
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Afficher le formulaire de login
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - Connexion</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .login-container {
                background: white;
                border-radius: 15px;
                padding: 3rem;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 400px;
                width: 100%;
                text-align: center;
            }
            
            .login-header {
                margin-bottom: 2rem;
            }
            
            .login-header h1 {
                color: #667eea;
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }
            
            .login-header p {
                color: #6b7280;
            }
            
            .login-icon {
                font-size: 3rem;
                color: #667eea;
                margin-bottom: 1rem;
            }
            
            .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
            }
            
            .form-label {
                display: block;
                margin-bottom: 0.5rem;
                color: #374151;
                font-weight: 500;
            }
            
            .form-control {
                width: 100%;
                padding: 0.8rem 1rem;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .btn {
                width: 100%;
                padding: 0.8rem 1.5rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
            }
            
            .btn:hover {
                opacity: 0.9;
                transform: translateY(-2px);
                transition: all 0.3s;
            }
            
            .alert {
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                background: #fee2e2;
                color: #b91c1c;
                border: 1px solid #fecaca;
            }
            
            .back-link {
                display: block;
                margin-top: 1.5rem;
                color: #667eea;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="login-header">
                <h1>Admin Panel</h1>
                <p>Connectez-vous pour accéder au dashboard</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="alert">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required autofocus>
                </div>
                <button type="submit" name="login" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour au site
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ==================== ACTIONS ADMIN ====================
$success_message = "";
$error_message = "";

// Marquer comme lu
if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['id'])) {
    $messages = [];
    if (file_exists($config['fichier_messages'])) {
        $messages = json_decode(file_get_contents($config['fichier_messages']), true) ?: [];
    }
    
    foreach ($messages as &$msg) {
        if ($msg['id'] === $_GET['id']) {
            $msg['lu'] = true;
            break;
        }
    }
    
    file_put_contents($config['fichier_messages'], json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

// Supprimer un message
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $messages = [];
    if (file_exists($config['fichier_messages'])) {
        $messages = json_decode(file_get_contents($config['fichier_messages']), true) ?: [];
    }
    
    $messages = array_filter($messages, function($msg) {
        return $msg['id'] !== $_GET['id'];
    });
    
    file_put_contents($config['fichier_messages'], json_encode(array_values($messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php?deleted=1');
    exit;
}

// ==================== CHARGER LES MESSAGES ====================
$messages = [];
if (file_exists($config['fichier_messages'])) {
    $messages = json_decode(file_get_contents($config['fichier_messages']), true) ?: [];
}

// Compter les messages non lus
$unread_count = count(array_filter($messages, function($msg) {
    return !$msg['lu'];
}));

// ==================== DASHBOARD ====================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --dark: #1f2937;
            --light: #f9fafb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: var(--dark);
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0 1rem;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .badge {
            background: var(--danger);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .sidebar-footer {
            margin-top: 2rem;
            padding: 0 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #fee2e2;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            font-size: 2rem;
            color: var(--dark);
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.primary {
            background: #e0e7ff;
            color: var(--primary);
        }
        
        .stat-icon.success {
            background: #d1fae5;
            color: var(--success);
        }
        
        .stat-icon.warning {
            background: #fef3c7;
            color: var(--warning);
        }
        
        .stat-info h3 {
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }
        
        .stat-info p {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        /* Messages Table */
        .messages-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .messages-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .messages-header h2 {
            font-size: 1.25rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f9fafb;
        }
        
        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.875rem;
            text-transform: uppercase;
        }
        
        tbody tr:hover {
            background: #f9fafb;
        }
        
        .unread {
            background: #eff6ff !important;
        }
        
        .unread td:first-child {
            border-left: 4px solid var(--primary);
        }
        
        .badge-unread {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-read {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.75rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .message-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #6b7280;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-view {
            background: #e0e7ff;
            color: var(--primary);
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .btn-delete {
            background: #fee2e2;
            color: var(--danger);
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            font-size: 1.125rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 1rem 0;
            }
            
            .sidebar-header h2,
            .sidebar-menu span,
            .logout-btn span {
                display: none;
            }
            
            .sidebar-menu a {
                justify-content: center;
                padding: 0.75rem;
            }
            
            .main-content {
                margin-left: 70px;
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            th, td {
                padding: 0.75rem;
            }
            
            .message-content {
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin</h2>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="admin.php" class="active">
                        <i class="fas fa-inbox"></i>
                        <span>Messages</span>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="index.php" target="_blank">
                        <i class="fas fa-globe"></i>
                        <span>Voir le site</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="admin.php?action=logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-envelope"></i> Boîte de réception</h1>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-globe"></i> Voir le site
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="fas fa-check-circle"></i> Message supprimé avec succès
                </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($messages); ?></h3>
                        <p>Total messages</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $unread_count; ?></h3>
                        <p>Non lus</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($messages) - $unread_count; ?></h3>
                        <p>Lus</p>
                    </div>
                </div>
            </div>
            
            <!-- Messages Table -->
            <div class="messages-section">
                <div class="messages-header">
                    <h2>Tous les messages</h2>
                </div>
                
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun message pour le moment</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Expéditeur</th>
                                    <th>Email</th>
                                    <th>Entreprise</th>
                                    <th>Téléphone</th>
                                    <th>Message</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="<?php echo !$msg['lu'] ? 'unread' : ''; ?>">
                                        <td>
                                            <?php if (!$msg['lu']): ?>
                                                <span class="badge-unread">Non lu</span>
                                            <?php else: ?>
                                                <span class="badge-read">Lu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($msg['date'])); ?></td>
                                        <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--primary);">
                                                <?php echo htmlspecialchars($msg['email']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['company'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($msg['phone'] ?: '-'); ?></td>
                                        <td class="message-content" title="<?php echo htmlspecialchars($msg['message']); ?>">
                                            <?php echo htmlspecialchars($msg['message']); ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (!$msg['lu']): ?>
                                                    <a href="admin.php?action=mark_read&id=<?php echo $msg['id']; ?>" 
                                                       class="btn-sm btn-view" title="Marquer comme lu">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="admin.php?action=delete&id=<?php echo $msg['id']; ?>" 
                                                   class="btn-sm btn-delete" title="Supprimer"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>