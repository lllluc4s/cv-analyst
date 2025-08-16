<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Registro</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-social {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 20px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-social i {
            margin-right: 10px;
        }
        .btn-google {
            background-color: #DB4437;
        }
        .btn-google:hover {
            background-color: #C1351E;
            color: white;
        }
        .btn-github {
            background-color: #24292e;
        }
        .btn-github:hover {
            background-color: #000000;
            color: white;
        }
        .btn-linkedin {
            background-color: #0077B5;
        }
        .btn-linkedin:hover {
            background-color: #005E8C;
            color: white;
        }
        .or-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        .or-divider:before, .or-divider:after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .or-divider span {
            padding: 0 10px;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <h1>{{ config('app.name', 'Laravel') }}</h1>
                    <p class="text-muted">Crie sua conta para começar</p>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="social-login mb-4">
                            <a href="{{ route('social.redirect', 'google') }}" class="btn btn-social btn-google w-100">
                                <i class="fab fa-google"></i> Registrar com Google
                            </a>
                            <a href="{{ route('social.redirect', 'github') }}" class="btn btn-social btn-github w-100">
                                <i class="fab fa-github"></i> Registrar com GitHub
                            </a>
                            <a href="{{ route('social.redirect', 'linkedin') }}" class="btn btn-social btn-linkedin w-100">
                                <i class="fab fa-linkedin"></i> Registrar com LinkedIn
                            </a>
                        </div>

                        <div class="or-divider">
                            <span>OU</span>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Nome completo</label>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password-confirm" class="form-label">Confirmar senha</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Registrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p>Já tem uma conta? <a href="{{ route('login') }}">Faça login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
