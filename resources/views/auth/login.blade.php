<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Dapur Th Sant</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brown-dark: #3d2817;
            --brown-medium: #5c3d2e;
            --cream: #f5f0eb;
            --cream-light: #faf7f4;
            --cream-dark: #e8dfd6;
            --text-dark: #2c1810;
            --text-muted: #8b7355;
            --border-color: #e0d5c9;
            --green-stock: #2d6a4f;
        }

        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        body {
            background: var(--cream-light);
            min-height: 100vh;
        }

        /* Top Bar */
        .login-topbar {
            background: var(--cream-light);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .login-logo-icon {
            width: 32px;
            height: 32px;
            background: var(--brown-dark);
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .login-topbar-title {
            font-weight: 800;
            font-size: 0.9rem;
            color: var(--brown-dark);
            letter-spacing: 1px;
        }

        .login-topbar-sub {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .login-topbar-right {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Main Container */
        .login-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 32px;
        }

        .login-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow: 0 10px 40px rgba(61, 40, 23, 0.08);
        }

        /* Left Side - Logo */
        .login-left {
            background: var(--cream);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-right: 1px solid var(--border-color);
        }

        .login-logo-placeholder {
            width: 100%;
            max-width: 400px;
            aspect-ratio: 4/3;
            background: white;
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
        }

        .login-logo-circle {
            width: 200px;
            height: 200px;
            background: var(--cream-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .login-logo-circle img {
            max-width: 150px;
        }

        .login-logo-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
        }

        .login-telemetri {
            width: 100%;
            max-width: 400px;
        }

        .telemetri-title {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .telemetri-title::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--brown-dark);
            border-radius: 50%;
        }

        .telemetri-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .telemetri-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .telemetri-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: var(--brown-dark);
        }

        /* Right Side - Form */
        .login-right {
            padding: 60px;
            position: relative;
        }

        .login-spec-ref {
            position: absolute;
            top: 24px;
            right: 24px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--text-muted);
            background: var(--cream);
            padding: 4px 12px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .login-module-badge {
            display: inline-block;
            background: var(--cream);
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--brown-dark);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .login-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 40px;
        }

        .login-divider {
            border: none;
            border-top: 1px solid var(--border-color);
            margin: 32px 0;
        }

        .form-group-custom {
            margin-bottom: 24px;
        }

        .form-label-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .form-label-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            letter-spacing: 1px;
        }

        .form-label-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .form-control-custom {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 0.95rem;
            width: 100%;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--brown-dark);
            box-shadow: 0 0 0 3px rgba(61, 40, 23, 0.1);
        }

        .input-group-custom {
            display: flex;
        }

        .input-group-custom .form-control-custom {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
        }

        .input-group-custom .btn-toggle {
            background: var(--cream);
            border: 1px solid var(--border-color);
            border-radius: 0 8px 8px 0;
            padding: 0 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
        }

        .checkbox-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .checkbox-custom input {
            width: 18px;
            height: 18px;
            accent-color: var(--brown-dark);
        }

        .checkbox-custom label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
            margin: 0;
        }

        .security-notice {
            background: var(--cream);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-login-submit {
            background: var(--brown-dark);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 16px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s;
        }

        .btn-login-submit:hover {
            background: var(--brown-medium);
            color: white;
            transform: translateY(-2px);
        }

        .login-footer-text {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Page Footer */
        .login-page-footer {
            text-align: center;
            padding: 32px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
            }
            .login-left {
                display: none;
            }
            .login-right {
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="login-topbar">
        <div class="login-topbar-left">
            <div class="login-logo-icon">D</div>
            <div>
                <div class="login-topbar-title">DAPUR TH SANT</div>
                <div class="login-topbar-sub">// AUTHENTICATION GATEWAY</div>
            </div>
        </div>
        <div class="login-topbar-right">
            [ SECURITY CHECKPOINT : AUTH_A1 ]
        </div>
    </div>

    <!-- Main Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Left Side -->
            <div class="login-left">
                <div class="login-logo-placeholder">
                    <div class="login-logo-circle">
                        <!-- Ganti dengan logo Anda -->
                        <img src="https://via.placeholder.com/150x150/3d2817/ffffff?text=DTS" alt="Logo Dapur Th Sant">
                    </div>
                    <div class="login-logo-text">
                        [ LOGO : DAPUR TH SANT ]<br>
                        ASPECT_RATIO: 4:3 // VECTOR
                    </div>
                </div>

                <!-- ✅ BAGIAN INI SUDAH DIUPDATE MENGGUNAKAN VARIABEL DARI CONTROLLER -->
                <div class="login-telemetri">
                    <div class="telemetri-title">// TELEMETRI OPERASIONAL AKTIF</div>
                    <div class="telemetri-item">
                        <div class="telemetri-label">
                            <i class="bi bi-bag"></i> PRODUKSI HARI INI
                        </div>
                        <div class="telemetri-value">[ {{ number_format($produksiHariIni ?? 0, 0, ',', '.') }} PESANAN ]</div>
                    </div>
                    <div class="telemetri-item">
                        <div class="telemetri-label">
                            <i class="bi bi-shield-check"></i> SLA LAYANAN
                        </div>
                        <div class="telemetri-value">[ {{ number_format($slaLayanan ?? 100, 1, ',', '.') }}% ]</div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="login-right">
                <div class="login-spec-ref">SPEC_REF // NFR03-AUTH</div>

                <div class="login-module-badge">MODUL OTENTIKASI TINGKAT SISTEM</div>

                <h1 class="login-title">Pusat Kendali Rasa & Kualitas<br>Dimsum</h1>
                <p class="login-subtitle">Sistem Operasional Resmi UMKM</p>

                <hr class="login-divider">

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group-custom">
                        <div class="form-label-custom">
                            <span class="form-label-text">[ INPUT_01 ] EMAIL ATAU USERNAME ADMIN</span>
                            <span class="form-label-code">REQ_AUTH_ID</span>
                        </div>
                        <input type="email" name="email" class="form-control-custom"
                               value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group-custom">
                        <div class="form-label-custom">
                            <span class="form-label-text">[ INPUT_02 ] KATA SANDI / PASSWORD</span>
                            <span class="form-label-code">HASH_VERIFY</span>
                        </div>
                        <div class="input-group-custom">
                            <input type="password" name="password" class="form-control-custom"
                                   id="passwordInput" required placeholder="••••••••••••••••">
                            <button type="button" class="btn-toggle" onclick="togglePassword()">
                                👁 [ LIHAT ]
                            </button>
                        </div>
                    </div>

                    <div class="checkbox-custom">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">[x] Ingat sesi masuk di perangkat ini</label>
                    </div>

                    <div class="security-notice">
                        <i class="bi bi-lock-fill"></i>
                        [ Enkripsi sandi terproteksi & aman (Bcrypt/Argon2 - NFR03) ]
                    </div>

                    <button type="submit" class="btn-login-submit">
                        [ Masuk ke Dashboard Admin -> ]
                    </button>
                </form>

                <div class="login-footer-text">
                    [ Bukan Admin? Masuk sebagai Owner / Pemilik untuk mode monitoring hanya baca ]
                </div>
            </div>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="login-page-footer">
        DAPUR TH SANT © 2026 • SISTEM MANAJEMEN DAPUR PRODUKSI DIMSUM OTENTIK
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
