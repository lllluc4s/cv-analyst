<!DOCTYPE html>
<html>
<head>
    <title>CV Analyst API Documentation</title>
    <style>
        .swagger-ui .topbar {
            background-color: #2563eb !important;
        }
        .swagger-ui .topbar .download-url-wrapper .select-label {
            color: white;
        }
        .swagger-ui .info {
            margin: 50px 0;
        }
        .swagger-ui .info hgroup.main h2 {
            color: #2563eb;
        }
        .swagger-ui .btn.authorize {
            background-color: #16a34a;
            border-color: #16a34a;
        }
        .swagger-ui .btn.authorize:hover {
            background-color: #15803d;
            border-color: #15803d;
        }
        /* Personalização das tags */
        .swagger-ui .opblock-tag {
            border-bottom: 1px solid rgba(59, 65, 81, 0.3);
        }
        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background: #16a34a;
        }
        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: #2563eb;
        }
        .swagger-ui .opblock.opblock-put .opblock-summary-method {
            background: #ea580c;
        }
        .swagger-ui .opblock.opblock-delete .opblock-summary-method {
            background: #dc2626;
        }
        .header-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .quick-links {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .quick-links h4 {
            color: #1e40af;
            margin-bottom: 0.5rem;
        }
        .quick-links a {
            color: #2563eb;
            text-decoration: none;
            margin-right: 1rem;
        }
        .quick-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui-standalone-preset.js') }}"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "{{ url('storage/api-docs/api-docs.json') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "none",
                operationsSorter: "alpha",
                filter: true,
                tryItOutEnabled: true,
                requestInterceptor: function(request) {
                    // Adiciona automaticamente o Content-Type para multipart/form-data quando necessário
                    if (request.body && request.body instanceof FormData) {
                        delete request.headers['Content-Type'];
                    }
                    return request;
                }
            });
        };
    </script>
</body>
</html>
