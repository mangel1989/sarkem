<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - SARKEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Styles for the login page similar to the example */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            border-radius: 16px;
            padding: 2rem 3rem;
            width: 360px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .login-box h1 {
            margin-bottom: 0.5rem;
            color: #6b46c1;
            font-weight: 700;
        }
        .login-box p {
            margin-bottom: 1.5rem;
            color: #718096;
            font-weight: 500;
        }
        .alert-error {
            background: #fed7d7;
            color: #c53030;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 600;
            color: #4a5568;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #6b46c1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.3);
        }
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .features {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .feature-item {
            background: #ebf4ff;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.875rem;
        }
        .feature-item svg {
            width: 20px;
            height: 20px;
            color: #4299e1;
        }
    </style>
</head>
<body>
    <?php 
    session_start();
    $error_message = $_SESSION['login_error'] ?? '';
    unset($_SESSION['login_error']);
    ?>
    <div class="login-box">
        <h1>SARKEM</h1>
        <p>Sistem Absensi Rekap Kehadiran dan Manajemen</p>
        <?php if (!empty($error_message)): ?>
            <div class="alert-error" role="alert" style="font-size: 1.25rem; padding: 1.5rem; border-radius: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin-right: 1rem;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="auth/login_process.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required autocomplete="username" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password" />
            </div>
            <button type="submit" class="btn-submit">Masuk ke Dashboard</button>
        </form>
        <div class="features" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
            <div class="feature-item" style="flex: 1 1 40%; max-width: 45%;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Real-time Tracking
            </div>
            <div class="feature-item" style="flex: 1 1 40%; max-width: 45%;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                Analytics Dashboard
            </div>
            <div class="feature-item" style="flex: 1 1 40%; max-width: 45%;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Multi-Role Access
            </div>
            <div class="feature-item" style="flex: 1 1 40%; max-width: 45%;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Smart Reports
            </div>
        </div>
    </div>
</body>
</html>
