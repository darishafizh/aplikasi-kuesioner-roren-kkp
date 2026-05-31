<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Log In | Monev KNMP - KKP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistem Monitoring dan Evaluasi KNMP" name="description" />
    <meta content="Kementerian Kelautan dan Perikanan" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="https://kampungnelayanmerahputih.kkp.go.id/img/favicon.ico">

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        :root {
            --kkp-primary: #0054A6;
            --kkp-primary-dark: #003D7A;
            --kkp-primary-light: #0066CC;
            --kkp-accent: #00A9CE;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .login-wrapper {
            position: fixed;
            inset: 0;
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        .login-left-panel {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ asset('assets/images/login-bg.png') }}');
            background-size: cover;
            background-position: center;
            height: 100vh;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-left-panel::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .logo-container {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .logo-container img {
            width: 160px;
            height: auto;
            margin-bottom: 35px;
            filter: drop-shadow(0 15px 35px rgba(0, 0, 0, 0.3));
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .ministry-name {
            font-size: 1.85rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            line-height: 1.4;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .ministry-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-top: 20px;
            text-align: center;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .footer-text {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .login-right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 50%;
            padding: 40px;
            background: linear-gradient(180deg, #f8f9fc 0%, #eef1f7 100%);
            position: relative;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card .card-body {
            padding: 48px 40px;
        }

        .login-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #6c757d;
            margin-bottom: 32px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.875rem;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            border: 1.5px solid #e0e5ec;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            background: #f8f9fc;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            border-color: var(--kkp-primary);
            box-shadow: 0 0 0 4px rgba(0, 84, 166, 0.12);
            background: #ffffff;
            outline: none;
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .input-group-text {
            background: #f8f9fc;
            border: 1.5px solid #e0e5ec;
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .input-group-text:hover {
            background: #eef1f7;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(135deg, var(--kkp-primary) 0%, var(--kkp-primary-dark) 100%);
            border: none;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 84, 166, 0.35);
            background: linear-gradient(135deg, var(--kkp-primary-light) 0%, var(--kkp-primary) 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .form-check-input:checked {
            background-color: var(--kkp-primary);
            border-color: var(--kkp-primary);
        }

        a {
            color: var(--kkp-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: var(--kkp-primary-dark);
        }

        .alert {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.875rem;
        }

        .password-eye {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
        }

        .invalid-feedback {
            display: none;
            font-size: 0.8rem;
            font-weight: 500;
            color: #dc3545;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 8px;
            animation: shakeIn 0.35s ease;
        }

        .is-invalid ~ .invalid-feedback,
        .has-validation .is-invalid ~ .invalid-feedback,
        .has-validation .invalid-feedback {
            display: flex;
        }

        .has-validation .invalid-feedback {
            width: 100%;
        }

        @keyframes shakeIn {
            0% { transform: translateX(-6px); opacity: 0; }
            40% { transform: translateX(4px); opacity: 0.7; }
            70% { transform: translateX(-2px); opacity: 1; }
            100% { transform: translateX(0); }
        }

        .form-control.is-invalid {
            border-color: #ef4444;
            background-image: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
        }

        .form-control.is-invalid:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .input-group-text.border-danger {
            border-color: #ef4444;
        }

        @media (max-width: 991.98px) {
            .login-wrapper {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
                overflow-y: auto;
            }

            .login-left-panel {
                width: 100%;
                height: auto;
                min-height: 260px;
                padding: 40px 30px;
            }

            .logo-container img {
                width: 90px;
                margin-bottom: 20px;
            }

            .ministry-name {
                font-size: 1.35rem;
            }

            .ministry-subtitle {
                font-size: 0.9rem;
                margin-top: 12px;
            }

            .footer-text {
                display: none;
            }

            .login-right-panel {
                width: 100%;
                height: auto;
                padding: 30px 20px 50px;
            }

            .login-card .card-body {
                padding: 32px 24px;
            }
        }

        /* Page Loader */
        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.35s ease, visibility 0.35s ease;
        }
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .page-loader-spinner {
            width: 38px;
            height: 38px;
            border: 3px solid #e2e8f0;
            border-top-color: var(--kkp-primary);
            border-radius: 50%;
            animation: loaderSpin 0.7s linear infinite;
        }
        .page-loader-text {
            margin-top: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }
        @keyframes loaderSpin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="page-loader" id="pageLoader">
        <div class="page-loader-spinner"></div>
        <div class="page-loader-text">Memuat...</div>
    </div>
    <div class="login-wrapper">
        <!-- Left Panel - Logo -->
        <div class="login-left-panel">
            <div class="logo-container">
                <img src="{{ asset('assets/images/kkp-logo.png') }}" alt="Logo KKP">
                <h1 class="ministry-name">Biro Perencanaan <br> Kementerian Kelautan dan Perikanan</h1>
                <p class="ministry-subtitle">Sistem Monitoring dan Evaluasi <br> Kampung Nelayan Merah Putih</p>
            </div>
            <div class="footer-text">
                ©
                <script>document.write(new Date().getFullYear())</script> Monev KNMP - Kementerian Kelautan dan
                Perikanan
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="login-right-panel">
            <div class="card login-card">
                <div class="card-body">
                    <h4 class="login-title text-center">Selamat Datang</h4>
                    <p class="login-subtitle text-center">Masukan username dan password Anda untuk mengakses sistem.</p>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.perform') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input class="form-control @error('username') is-invalid @enderror" type="text" id="username" name="username"
                                value="{{ old('username') }}" required autofocus placeholder="Masukan username anda">
                            @error('username')
                                <div class="invalid-feedback d-flex align-items-center mt-2">
                                    <i class="mdi mdi-alert-circle-outline me-1 fs-6"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group has-validation">
                                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" required
                                    placeholder="Masukan password anda" style="border-radius: 12px 0 0 12px;">
                                <span class="input-group-text @error('password') border-danger @enderror" onclick="togglePassword()">
                                    <i class="mdi mdi-eye" id="toggleIcon"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback d-flex align-items-center mt-2">
                                        <i class="mdi mdi-alert-circle-outline me-1 fs-6"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="captcha" class="form-label">Berapa hasil dari: <strong>{{ $captcha_question ?? '' }}</strong></label>
                            <input type="number" id="captcha" class="form-control @error('captcha') is-invalid @enderror" name="captcha" required placeholder="Masukkan jawaban">
                            @error('captcha')
                                <div class="invalid-feedback d-flex align-items-center mt-2">
                                    <i class="mdi mdi-alert-circle-outline me-1 fs-6"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <button class="btn btn-primary" type="submit">
                                <i class="mdi mdi-login me-1"></i> Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('password');
            var toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('mdi-eye');
                toggleIcon.classList.add('mdi-eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('mdi-eye-off');
                toggleIcon.classList.add('mdi-eye');
            }
        }
    </script>
    <script>
        (function() {
            var loader = document.getElementById('pageLoader');
            if (!loader) return;
            function hideLoader() { loader.classList.add('hidden'); }
            function showLoader() { loader.classList.remove('hidden'); }
            if (document.readyState === 'complete') { hideLoader(); }
            else { window.addEventListener('load', hideLoader); }
            document.addEventListener('submit', function() { showLoader(); });
            window.addEventListener('beforeunload', function() { showLoader(); });
            window.addEventListener('pageshow', function(e) { if (e.persisted) hideLoader(); });
        })();
    </script>
</body>

</html>