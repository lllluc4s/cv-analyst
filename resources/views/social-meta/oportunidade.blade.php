<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Basic Meta -->
    <title>{{ $metaTags['title'] }}</title>
    <meta name="description" content="{{ $metaTags['description'] }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $metaTags['type'] }}">
    <meta property="og:url" content="{{ $metaTags['url'] }}">
    <meta property="og:title" content="{{ $metaTags['title'] }}">
    <meta property="og:description" content="{{ $metaTags['description'] }}">
    <meta property="og:image" content="{{ $metaTags['image'] }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="{{ $metaTags['siteName'] }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $metaTags['url'] }}">
    <meta property="twitter:title" content="{{ $metaTags['title'] }}">
    <meta property="twitter:description" content="{{ $metaTags['description'] }}">
    <meta property="twitter:image" content="{{ $metaTags['image'] }}">
    
    <!-- LinkedIn -->
    <meta property="linkedin:card" content="summary">
    <meta property="linkedin:url" content="{{ $metaTags['url'] }}">
    <meta property="linkedin:title" content="{{ $metaTags['title'] }}">
    <meta property="linkedin:description" content="{{ $metaTags['description'] }}">
    <meta property="linkedin:image" content="{{ $metaTags['image'] }}">
    
    <!-- Redirect to Frontend -->
    <script>
        // Redirecionar para o frontend após 2 segundos
        setTimeout(function() {
            window.location.href = '{{ config("app.frontend_url") }}/oportunidade/{{ $oportunidade->slug }}';
        }, 2000);
    </script>
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            text-align: center;
            max-width: 600px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .company {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .redirect-info {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            @if($oportunidade->company && $oportunidade->company->logo_url)
                <img src="{{ $oportunidade->company->logo_url }}" alt="Logo da {{ $metaTags['companyName'] }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display: none; align-items: center; justify-content: center; width: 100%; height: 100%;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="#667eea">
                        <path d="M20 6h-2V4c0-1.11-.89-2-2-2H8c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11h20V8c0-1.11-.89-2-2-2zM8 4h8v2H8V4z"/>
                    </svg>
                </div>
            @else
                <svg width="40" height="40" viewBox="0 0 24 24" fill="#667eea">
                    <path d="M20 6h-2V4c0-1.11-.89-2-2-2H8c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11h20V8c0-1.11-.89-2-2-2zM8 4h8v2H8V4z"/>
                </svg>
            @endif
        </div>
        
        <h1>{{ $oportunidade->titulo }}</h1>
        <div class="company">{{ $metaTags['companyName'] }}</div>
        
        @if($oportunidade->localizacao)
            <div style="opacity: 0.8; margin-bottom: 1rem;">
                📍 {{ $oportunidade->localizacao }}
            </div>
        @endif
        
        <div class="redirect-info">
            <div class="spinner"></div>
            Redirecionando para a página da vaga...
        </div>
    </div>
</body>
</html>
