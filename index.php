<?php
// ==================== CONFIGURATION ====================
$config = [
    'email_destinataire' => 'contact@nextconcept.ma',
    'sujet_email' => 'Nouveau contact SafeBackup',
    'fichier_messages' => 'messages.json',
    'admin_password' => 'admin123' 
];

// ==================== INITIALISATION ====================
$formData = [
    'name' => '',
    'company' => '',
    'email' => '',
    'phone' => '',
    'message' => ''
];

$formErrors = [];
$formSuccess = false;
$formMessage = "";

// ==================== FONCTIONS ====================
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function sauvegarder_message($data, $fichier) {
    $messages = [];
    if (file_exists($fichier)) {
        $content = file_get_contents($fichier);
        $messages = json_decode($content, true) ?: [];
    }
    
    $nouveau_message = [
        'id' => uniqid(),
        'date' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Inconnu',
        'name' => $data['name'],
        'company' => $data['company'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'message' => $data['message'],
        'lu' => false
    ];
    
    array_unshift($messages, $nouveau_message);
    file_put_contents($fichier, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ==================== TRAITEMENT ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($formData as $key => $value) {
        $formData[$key] = clean_input($_POST[$key] ?? '');
    }

    // Validation
    if (empty($formData['name'])) {
        $formErrors['name'] = "Veuillez entrer votre nom complet.";
    }

    if (empty($formData['email'])) {
        $formErrors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = "Email invalide.";
    }

    if (empty($formData['message'])) {
        $formErrors['message'] = "Veuillez écrire votre message.";
    }

    // Si aucune erreur
    if (empty($formErrors)) {

        // Sauvegarder le message
        sauvegarder_message($formData, $config['fichier_messages']);

        // Protection contre injection header
        $safeEmail = str_replace(["\r", "\n"], '', $formData['email']);
        $safeName  = str_replace(["\r", "\n"], '', $formData['name']);

        $headers  = "From: {$safeName} <{$safeEmail}>\r\n";
        $headers .= "Reply-To: {$safeEmail}\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

        $message_email  = "Nouveau contact SafeBackup:\n\n";
        $message_email .= "Nom: {$formData['name']}\n";
        $message_email .= "Entreprise: {$formData['company']}\n";
        $message_email .= "Email: {$formData['email']}\n";
        $message_email .= "Téléphone: {$formData['phone']}\n\n";
        $message_email .= "Message:\n{$formData['message']}\n";

        if (mail($config['email_destinataire'], $config['sujet_email'], $message_email, $headers)) {
            $formSuccess = true;
            $formMessage = "Votre message a été envoyé avec succès ✅";

            // Reset formulaire
            foreach ($formData as $key => $value) {
                $formData[$key] = '';
            }

        } else {
            $formErrors['general'] = "Erreur lors de l'envoi du message (mail() désactivé sur XAMPP sans SMTP).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBackup - Sauvegarde Cloud Sécurisée</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ==================== STYLE CSS ==================== */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --success: #10b981;
            --danger: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Header */
        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: var(--light);
            transform: skewY(-2deg);
            z-index: 1;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        header h1 {
            font-size: 2.8rem;
            margin-bottom: 0.5rem;
            animation: fadeInDown 1s ease;
        }
        
        header p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 1.5rem;
            opacity: 0.9;
        }
        
        /* Navigation */
        nav {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 0.5rem;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 2rem;
        }
        
        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .nav-links a i {
            margin-right: 0.3rem;
        }
        
        /* Sections */
        section {
            padding: 4rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            color: var(--primary);
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .section-title h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--secondary);
            margin: 0.5rem auto 0;
        }
        
        /* Hero Section */
        .hero {
            background: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            height: 60vh;
            display: flex;
            align-items: center;
            position: relative;
            color: white;
            text-align: center;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 2px solid var(--primary);
        }
        
        .btn:hover {
            background: transparent;
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .btn-outline {
            background: transparent;
            color: white;
            border-color: white;
            margin-left: 1rem;
        }
        
        .btn-outline:hover {
            background: white;
            color: var(--primary);
        }
        
        /* Features */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        /* Stats */
        .stats {
            background: var(--primary);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .stat-item {
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Pourquoi section */
        .why-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        .why-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .why-content h3 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        
        .why-item {
            margin-bottom: 1.5rem;
            display: flex;
        }
        
        .why-icon {
            color: var(--secondary);
            font-size: 1.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        /* Solutions */
        .solutions {
            background: #f1f5f9;
        }
        
        .pricing-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .pricing-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
        }
        
        .pricing-header {
            background: var(--primary);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .pricing-header h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .pricing-price {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .pricing-body {
            padding: 2rem;
        }
        
        .pricing-features {
            list-style: none;
            margin-bottom: 2rem;
        }
        
        .pricing-features li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .pricing-features li i {
            color: var(--success);
            margin-right: 0.5rem;
        }
        
        /* Contact Form */
        .contact-form {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        .text-danger {
            color: var(--danger);
            font-size: 0.9rem;
            margin-top: 0.3rem;
            display: block;
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 1.5rem;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-col h3 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background: var(--secondary);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }
        
        .footer-links a:hover {
            color: var(--secondary);
        }
        
        .footer-links a i {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
            color: #94a3b8;
        }
        
        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
            }
            
            .nav-links {
                margin-top: 1rem;
            }
            
            .nav-links li {
                margin-left: 1rem;
                margin-right: 1rem;
            }
            
            .why-container {
                grid-template-columns: 1fr;
            }
            
            .why-image {
                order: -1;
            }
            
            header h1 {
                font-size: 2.2rem;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- ==================== HTML ==================== -->
    <header>
        <div class="header-content">
            <h1><i class="fas fa-cloud-upload-alt"></i> SafeBackup Pro</h1>
            <p>La solution de sauvegarde cloud la plus sécurisée pour protéger vos données critiques</p>
            <a href="#contact" class="btn">Essai gratuit</a>
        </div>
    </header>

    <nav>
        <div class="container nav-container">
            <a href="#" class="logo"><i class="fas fa-cloud-upload-alt"></i> SafeBackup</a>
            <ul class="nav-links">
                <li><a href="#features"><i class="fas fa-star"></i> Fonctionnalités</a></li>
                <li><a href="#why"><i class="fas fa-question-circle"></i> Pourquoi ?</a></li>
                <li><a href="#solutions"><i class="fas fa-cubes"></i> Solutions</a></li>
                <li><a href="#contact"><i class="fas fa-envelope"></i> Contact</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Se connecter</a></li>
                <li><a href="https://www.nextconcept.ma/" target="_blank"><i class="fas fa-external-link-alt"></i> Site principal</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h2>Protégez vos données professionnelles</h2>
            <p>Notre solution de sauvegarde cloud sécurisée protège automatiquement vos fichiers contre les pannes, les ransomwares et les catastrophes.</p>
            <div>
                <a href="#solutions" class="btn">Voir nos solutions</a>
                <a href="#contact" class="btn btn-outline">Contactez-nous</a>
            </div>
        </div>
    </section>

    <section id="features" class="container">
        <div class="section-title">
            <h2>Nos Fonctionnalités</h2>
            <p>Découvrez ce qui fait de SafeBackup la solution idéale pour votre entreprise</p>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>Chiffrement AES-256</h3>
                <p>Vos données sont chiffrées avant même de quitter vos appareils avec un algorithme militaire.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3>Historique des versions</h3>
                <p>Conservez jusqu'à 365 jours d'historique pour restaurer n'importe quelle version de vos fichiers.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h3>Sauvegarde automatique</h3>
                <p>Configurez une fois et oubliez, nos agents gèrent tout automatiquement selon votre planning.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>Multi-cloud</h3>
                <p>Vos données sont répliquées sur plusieurs datacenters à travers le monde pour une disponibilité maximale.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Protection ransomware</h3>
                <p>Détection proactive des attaques et restauration instantanée de vos données saines.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Support 24/7</h3>
                <p>Notre équipe d'experts est disponible à tout moment pour vous assister en cas de besoin.</p>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="container stats-container">
            <div class="stat-item">
                <div class="stat-number">99.99%</div>
                <div class="stat-label">Disponibilité</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">10 000+</div>
                <div class="stat-label">Clients satisfaits</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5PB</div>
                <div class="stat-label">Données sauvegardées</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support technique</div>
            </div>
        </div>
    </section>

    <section id="why" class="container">
        <div class="section-title">
            <h2>Pourquoi Sauvegarder ?</h2>
            <p>Les risques sont réels, ne prenez pas de chances avec vos données</p>
        </div>
        
        <div class="why-container">
            <div class="why-content">
                <h3>Vos données sont votre actif le plus précieux</h3>
                
                <div class="why-item">
                    <div class="why-icon">
                        <i class="fas fa-hard-drive"></i>
                    </div>
                    <div>
                        <h4>Pannes matérielles</h4>
                        <p>63% des entreprises ont subi une perte de données due à une panne de disque dur. La durée moyenne de remplacement est de 3 jours.</p>
                    </div>
                </div>
                
                <div class="why-item">
                    <div class="why-icon">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <div>
                        <h4>Cyberattaques</h4>
                        <p>Une entreprise est victime d'un ransomware toutes les 11 secondes. 60% des PME ferment dans les 6 mois suivant une attaque.</p>
                    </div>
                </div>
                
                <div class="why-item">
                    <div class="why-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div>
                        <h4>Conformité</h4>
                        <p>Le RGPD impose des amendes jusqu'à 4% du chiffre d'affaires annuel pour non-protection des données personnelles.</p>
                    </div>
                </div>
                
                <div class="why-item">
                    <div class="why-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h4>Productivité</h4>
                        <p>La perte de données entraîne en moyenne 18,5 heures d'arrêt de production par incident.</p>
                    </div>
                </div>
            </div>
            
            <div class="why-image">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Data security">
            </div>
        </div>
    </section>

    <section id="solutions" class="solutions">
        <div class="container">
            <div class="section-title">
                <h2>Nos Solutions</h2>
                <p>Choisissez le plan adapté à vos besoins</p>
            </div>
            
            <div class="pricing-cards">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Starter</h3>
                        <div class="pricing-price">19€<span>/mois</span></div>
                        <small>Jusqu'à 500GB</small>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> Sauvegarde automatique</li>
                            <li><i class="fas fa-check"></i> Chiffrement AES-256</li>
                            <li><i class="fas fa-check"></i> Historique 30 jours</li>
                            <li><i class="fas fa-check"></i> Support email</li>
                            <li><i class="fas fa-times"></i> Protection ransomware</li>
                            <li><i class="fas fa-times"></i> Multi-cloud</li>
                        </ul>
                        <a href="#contact" class="btn">Choisir ce plan</a>
                    </div>
                </div>
                
                <div class="pricing-card">
                    <div class="pricing-header" style="background: var(--secondary);">
                        <h3>Professionnel</h3>
                        <div class="pricing-price">49€<span>/mois</span></div>
                        <small>Jusqu'à 2TB</small>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> Sauvegarde automatique</li>
                            <li><i class="fas fa-check"></i> Chiffrement AES-256</li>
                            <li><i class="fas fa-check"></i> Historique 90 jours</li>
                            <li><i class="fas fa-check"></i> Support 24/7</li>
                            <li><i class="fas fa-check"></i> Protection ransomware</li>
                            <li><i class="fas fa-times"></i> Multi-cloud</li>
                        </ul>
                        <a href="#contact" class="btn">Choisir ce plan</a>
                    </div>
                </div>
                
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Entreprise</h3>
                        <div class="pricing-price">99€<span>/mois</span></div>
                        <small>Stockage illimité</small>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> Sauvegarde automatique</li>
                            <li><i class="fas fa-check"></i> Chiffrement AES-256</li>
                            <li><i class="fas fa-check"></i> Historique 365 jours</li>
                            <li><i class="fas fa-check"></i> Support 24/7 prioritaire</li>
                            <li><i class="fas fa-check"></i> Protection ransomware</li>
                            <li><i class="fas fa-check"></i> Multi-cloud</li>
                        </ul>
                        <a href="#contact" class="btn">Choisir ce plan</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="container">
        <div class="section-title">
            <h2>Contactez-nous</h2>
            <p>Demandez une démonstration ou un devis personnalisé</p>
        </div>
        
        <div class="contact-form">
            <?php if ($formSuccess): ?>
                <div class="alert alert-success">
                    <?php echo $formMessage; ?>
                </div>
            <?php elseif (!empty($formErrors)): ?>
                <div class="alert alert-danger">
                    Veuillez corriger les erreurs dans le formulaire.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="#contact">
                <div class="form-group">
                    <label for="name" class="form-label">Nom complet*</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                    <?php if (isset($formErrors['name'])): ?>
                        <span class="text-danger"><?php echo $formErrors['name']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="company" class="form-label">Entreprise</label>
                    <input type="text" id="company" name="company" class="form-control" value="<?php echo htmlspecialchars($formData['company']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                    <?php if (isset($formErrors['email'])): ?>
                        <span class="text-danger"><?php echo $formErrors['email']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">Message*</label>
                    <textarea id="message" name="message" class="form-control" required><?php echo htmlspecialchars($formData['message']); ?></textarea>
                    <?php if (isset($formErrors['message'])): ?>
                        <span class="text-danger"><?php echo $formErrors['message']; ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn">Envoyer le message</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="container footer-container">
            <div class="footer-col">
                <h3>SafeBackup</h3>
                <p>La solution de sauvegarde cloud la plus sécurisée pour les professionnels et les entreprises.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-col">
                <h3>Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="#features"><i class="fas fa-chevron-right"></i> Fonctionnalités</a></li>
                    <li><a href="#why"><i class="fas fa-chevron-right"></i> Pourquoi sauvegarder</a></li>
                    <li><a href="#solutions"><i class="fas fa-chevron-right"></i> Nos solutions</a></li>
                    <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>Légal</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Politique de confidentialité</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> CGU</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Mentions légales</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> RGPD</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>Contact</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Avenue es-semara, Rue N°1, Immeuble Hagounia, App N°2 Lâayoune, Maroc</a></li>
                    <li><a href="tel:+212661456971"><i class="fas fa-phone"></i> +212-6-61-45-69-71</a></li>
                    <li><a href="tel:+2120528997503"><i class="fas fa-phone"></i> +212-05-28-99-75-03</a></li>
                    <li><a href="mailto:contact@nextconcept.ma"><i class="fas fa-envelope"></i> contact@nextconcept.ma</a></li>
                    <li><a href="#"><i class="fas fa-clock"></i> Lun-Ven: 9h-17h</a></li>
                </ul>
            </div>
        </div>
        
        <div class="container footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> SafeBackup. Tous droits réservés. | <a href="https://www.nextconcept.ma/" target="_blank">Visitez notre site principal</a></p>
        </div>
    </footer>

    <script>
        // ==================== JAVASCRIPT ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Animation de la navigation
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Pour les ancres
                    if (this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            window.scrollTo({
                                top: target.offsetTop - 80,
                                behavior: 'smooth'
                            });
                        }
                    }
                });
            });
            
            // Ajout de la classe active au scroll
            window.addEventListener('scroll', function() {
                const scrollPosition = window.scrollY;
                
                document.querySelectorAll('section').forEach(section => {
                    const sectionTop = section.offsetTop - 100;
                    const sectionHeight = section.offsetHeight;
                    const sectionId = section.getAttribute('id');
                    
                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                        navLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${sectionId}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            });
            
            // Animation des éléments au scroll
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.feature-card, .pricing-card, .why-item');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (elementPosition < screenPosition) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Initialisation des animations
            document.querySelectorAll('.feature-card, .pricing-card, .why-item').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
            });
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Pour les éléments déjà visibles au chargement
            
            // Gestion du formulaire avec feedback amélioré
            const contactForm = document.querySelector('#contact form');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    const requiredFields = this.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = 'var(--danger)';
                            isValid = false;
                        } else {
                            field.style.borderColor = '';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger';
                        errorDiv.textContent = 'Veuillez remplir tous les champs obligatoires.';
                        
                        const existingAlert = this.querySelector('.alert');
                        if (existingAlert) {
                            existingAlert.replaceWith(errorDiv);
                        } else {
                            this.insertBefore(errorDiv, this.firstChild);
                        }
                        
                        window.scrollTo({
                            top: this.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>