<?php
require __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/email-otp-helper.php';

$errors = [];

if (!isset($pdo)) {
    die('Database connection not available. Please check your configuration.');
}

// Already logged in — bounce straight to the dashboard.
if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

// Are we on step 1 (password) or step 2 (OTP)?
$step = !empty($_SESSION['pending_admin_id']) && ($_SESSION['pending_admin_step'] ?? '') === 'otp'
    ? 'otp'
    : 'password';

// Constant-time fallback so timing reveals nothing about whether the email exists.
// Cost matched to the seeded admin hashes (cost=10).
const ADMIN_LOGIN_DUMMY_HASH = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8K8HfqjKXM/4z6Q5y7q6cV1aP2pVay';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please refresh and try again.';
    } elseif (!checkRateLimit($pdo, 'admin_login', 5, 900)) {
        // 5 attempts per 15 minutes per session. Covers both steps so a stalled OTP can't
        // be brute-forced indefinitely either.
        $errors[] = 'Too many login attempts. Please try again in 15 minutes.';
    } elseif (($_POST['step'] ?? '') === 'otp' && $step === 'otp') {
        // ─── Step 2: verify the email OTP ───────────────────────────────────────
        $otp        = preg_replace('/\D/', '', $_POST['otp'] ?? '');
        $adminEmail = $_SESSION['pending_admin_email'] ?? '';
        $adminId    = $_SESSION['pending_admin_id']    ?? 0;
        $adminName  = $_SESSION['pending_admin_name']  ?? '';

        if (strlen($otp) !== 6) {
            $errors[] = 'Please enter the 6-digit code sent to your email.';
        } elseif (empty($adminEmail) || empty($adminId)) {
            // Session lost between steps — restart from scratch.
            unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_email'],
                  $_SESSION['pending_admin_name'], $_SESSION['pending_admin_step']);
            $errors[] = 'Session expired. Please sign in again.';
            $step = 'password';
        } else {
            $verify = verifyEmailLoginOtp($pdo, $adminEmail, $otp);
            if (!$verify['success']) {
                error_log('Admin OTP failed for ' . $adminEmail . ' — ' . $verify['message']);
                $errors[] = $verify['message'];
            } else {
                // Auth fully passed — promote the pending session into a real admin session.
                session_regenerate_id(true);
                $_SESSION['admin_id']    = $adminId;
                $_SESSION['admin_name']  = $adminName;
                $_SESSION['admin_email'] = $adminEmail;
                $_SESSION['admin_login_at'] = time();
                unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_email'],
                      $_SESSION['pending_admin_name'], $_SESSION['pending_admin_step'],
                      $_SESSION['rate_limit_admin_login']);
                error_log('Admin login OK for ' . $adminEmail . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
                redirect('dashboard.php');
            }
        }
    } else {
        // ─── Step 1: verify password, then send OTP ────────────────────────────
        $email    = strtolower(trim(sanitize($_POST['email'] ?? '')));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $errors[] = 'All fields required';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ? AND is_admin = 1 AND is_active = 1 LIMIT 1');
                $stmt->execute([$email]);
                $admin = $stmt->fetch();

                // Constant-time: always run password_verify even if no admin matched.
                $hash = $admin['password'] ?? ADMIN_LOGIN_DUMMY_HASH;
                $passwordOk = password_verify($password, $hash);

                if ($admin && $passwordOk) {
                    if (empty($admin['email'])) {
                        $errors[] = 'This admin account has no email on file. OTP login cannot proceed — contact support.';
                    } else {
                        $send = sendEmailLoginOtp($pdo, $admin['email'], $admin['name'], 'login');
                        if (!$send['success']) {
                            error_log('Admin OTP send failed for ' . $admin['email'] . ': ' . $send['message']);
                            $errors[] = $send['message'];
                        } else {
                            $_SESSION['pending_admin_id']    = $admin['id'];
                            $_SESSION['pending_admin_email'] = $admin['email'];
                            $_SESSION['pending_admin_name']  = $admin['name'];
                            $_SESSION['pending_admin_step']  = 'otp';
                            $step = 'otp';
                        }
                    }
                } else {
                    error_log('Admin login fail (' . $email . ') from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
                    $errors[] = 'Invalid credentials';
                }
            } catch (PDOException $e) {
                error_log('Admin login query error: ' . $e->getMessage());
                $errors[] = 'A database error occurred. Please try again later.';
            }
        }
    }
}

// Handle "start over" link from the OTP screen.
if (isset($_GET['restart']) && $step === 'otp') {
    unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_email'],
          $_SESSION['pending_admin_name'], $_SESSION['pending_admin_step']);
    redirect('login.php');
}

// Mask the admin email shown on the OTP screen (jo***@example.com).
$maskedAdminEmail = '';
if ($step === 'otp' && !empty($_SESSION['pending_admin_email'])) {
    $em    = $_SESSION['pending_admin_email'];
    $atPos = strpos($em, '@');
    if ($atPos !== false) {
        $local = substr($em, 0, $atPos);
        $maskedAdminEmail = (strlen($local) <= 2
            ? $local
            : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2))
        ) . substr($em, $atPos);
    }
}

$csrfToken = generateCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Innovative Furniture</title>

    <!-- Favicon -->
    <link rel="icon" href="../images/logo/logo.png" type="image/png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap CSS -->
    <link href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at 20% 50%, rgba(158, 103, 71, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(216, 157, 67, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(158, 103, 71, 0.05) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
            z-index: 0;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-container {
            max-width: 1000px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }

        /* Left Side - Branding */
        .login-brand {
            background: linear-gradient(135deg, #9e6747 0%, #d89d43 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .login-brand img {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            margin-bottom: 30px;
            background: #fff;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .login-brand h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .login-brand p {
            font-size: 1.1rem;
            opacity: 0.95;
            line-height: 1.6;
            max-width: 300px;
        }

        .brand-features {
            margin-top: 40px;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            opacity: 0.9;
        }

        .feature-item i {
            font-size: 1.3rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 8px;
        }

        /* Right Side - Form */
        .login-form-side {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #8A8C8A;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #5F615E;
            margin-bottom: 10px;
            font-size: 0.9rem;
            display: block;
            width: 100%;
        }

        .input-wrapper {
            margin-bottom: 24px;
            width: 100%;
        }

        .input-group {
            position: relative;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9e6747;
            font-size: 1.2rem;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px 16px 52px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafafa;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #9e6747;
            box-shadow: 0 0 0 4px rgba(158, 103, 71, 0.08);
            background: #fff;
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #9e6747 0%, #d89d43 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(158, 103, 71, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(158, 103, 71, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            border: 2px solid #fcc;
            color: #c33;
            padding: 16px 18px;
            border-radius: 12px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-custom i {
            font-size: 1.4rem;
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: #9e6747;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .back-link a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            background: #fff;
            padding: 0 15px;
            color: #8A8C8A;
            position: relative;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
            }

            .login-brand {
                padding: 40px 30px;
                min-height: auto;
            }

            .brand-features {
                display: none;
            }

            .login-form-side {
                padding: 40px 30px;
            }

            .form-header h2 {
                font-size: 1.75rem;
            }

            .login-brand h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Left Side - Branding -->
            <div class="login-brand">
                <div class="brand-content">
                    <img src="../images/logo/logo.png" alt="Innovative Furniture">
                    <h1>Innovative</h1>
                    <p>Premium Furniture Store Management System</p>

                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            <span>Secure & Reliable</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-speedometer2"></i>
                            <span>Fast Performance</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-graph-up"></i>
                            <span>Real-time Analytics</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-form-side">
                <div class="form-header">
                    <?php if ($step === 'otp'): ?>
                        <h2>Verify it's you</h2>
                        <p>We've sent a 6-digit code to <strong><?= htmlspecialchars($maskedAdminEmail) ?></strong></p>
                    <?php else: ?>
                        <h2>Welcome Back</h2>
                        <p>Sign in to access your admin dashboard</p>
                    <?php endif; ?>
                </div>

                <?php if ($errors): ?>
                    <div class="alert-custom">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($step === 'otp'): ?>
                    <form method="post" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="step" value="otp">

                        <div class="input-wrapper">
                            <label class="form-label">6-digit code</label>
                            <div class="input-group">
                                <i class="bi bi-shield-lock-fill input-icon"></i>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="otp"
                                    inputmode="numeric"
                                    pattern="\d{6}"
                                    maxlength="6"
                                    placeholder="123456"
                                    required
                                    autofocus
                                    style="letter-spacing: 8px; font-size: 1.3rem; text-align: center;"
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn-login">
                            <span>Verify &amp; Sign In</span>
                            <i class="bi bi-shield-check"></i>
                        </button>
                    </form>

                    <div class="back-link" style="margin-top: 20px;">
                        <a href="login.php?restart=1">
                            <i class="bi bi-arrow-left"></i>
                            <span>Use a different account</span>
                        </a>
                    </div>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="input-wrapper">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="bi bi-envelope-fill input-icon"></i>
                                <input
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    placeholder="admin@innovativehomesi.com"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="input-wrapper">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <i class="bi bi-lock-fill input-icon"></i>
                                <input
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn-login">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right-circle-fill"></i>
                        </button>
                    </form>
                <?php endif; ?>

                <div class="divider">
                    <span>&copy; <?= date('Y') ?> Innovative Furniture</span>
                </div>
            </div>
        </div>

        <div class="back-link">
            <a href="../index.php">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Website</span>
            </a>
        </div>
    </div>
</body>
</html>
