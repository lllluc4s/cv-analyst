<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Candidatura - {{ $oportunidade->titulo }}</title>

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
        .oportunidade-header {
            background-color: #6c5ce7;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .candidatura-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="oportunidade-header">
        <div class="container">
            <h1>{{ $oportunidade->titulo }}</h1>
            <p>{{ $oportunidade->descricao_curta }}</p>
        </div>
    </div>

    <div class="container">
        <div class="candidatura-container">
            <h2 class="mb-4">Formulário de Candidatura</h2>

            <form method="POST" action="{{ route('api.candidaturas.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="oportunidade_id" value="{{ $oportunidade->id }}">
                
                <!-- Dados do usuário -->
                <div class="mb-4">
                    <h4>Seus dados</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="nome" value="{{ Auth::user()->name }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="{{ Auth::user()->email }}" readonly>
                        </div>
                    </div>

                    @if(Auth::user()->profile_url)
                    <div class="mb-3">
                        <label for="profile" class="form-label">Perfil</label>
                        <div>
                            <a href="{{ Auth::user()->profile_url }}" target="_blank">
                                {{ Auth::user()->profile_url }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Campos do formulário -->
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone *</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" required>
                </div>

                <div class="mb-3">
                    <label for="cv_file" class="form-label">Currículo (PDF, DOC, DOCX) *</label>
                    <input type="file" class="form-control" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" required>
                    <div class="form-text">Tamanho máximo: 5MB</div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rgpd" name="rgpd" required>
                    <label class="form-check-label" for="rgpd">
                        Concordo com o processamento dos meus dados para fins de recrutamento
                    </label>
                </div>

                <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Enviar Candidatura</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gerar token reCAPTCHA ao enviar o formulário
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ env('RECAPTCHA_SITE_KEY') }}', {action: 'candidatura'})
                        .then(function(token) {
                            document.getElementById('recaptcha_token').value = token;
                            form.submit();
                        });
                });
            });
        });
    </script>
</body>
</html>
