<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consentimento para Comunicações - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">
                    Consentimento para Comunicações
                </h1>
                <p class="text-gray-600">
                    Olá <strong>{{ $candidatura->nome }}</strong>, 
                    gerencie suas preferências de comunicação para a candidatura.
                </p>
            </div>

            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-medium text-blue-900 mb-2">Sobre suas informações:</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Suas notas e comentários inseridos pelas empresas são <strong>sempre privados</strong></li>
                    <li>• Apenas empresas com acesso à oportunidade podem ver seus dados</li>
                    <li>• Você pode revogar o consentimento a qualquer momento</li>
                </ul>
            </div>

            <form id="consentForm" class="space-y-6">
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <input 
                            type="checkbox" 
                            id="consentimento_emails" 
                            name="consentimento_emails"
                            class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <div class="flex-1">
                            <label for="consentimento_emails" class="font-medium text-gray-900">
                                Aceito receber emails de atualização sobre minha candidatura
                            </label>
                            <p class="text-sm text-gray-600 mt-1">
                                Você receberá notificações quando o status da sua candidatura for atualizado.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <input 
                            type="checkbox" 
                            id="pode_ser_contactado" 
                            name="pode_ser_contactado"
                            class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <div class="flex-1">
                            <label for="pode_ser_contactado" class="font-medium text-gray-900">
                                Autorizo ser contactado pela empresa sobre esta candidatura
                            </label>
                            <p class="text-sm text-gray-600 mt-1">
                                A empresa poderá entrar em contacto direto consigo sobre o processo seletivo.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Salvar Preferências
                    </button>
                    <button 
                        type="button" 
                        onclick="revokeConsent()"
                        class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        Revogar Tudo
                    </button>
                </div>
            </form>

            <div id="message" class="mt-4 hidden p-3 rounded-md"></div>

            <div class="mt-8 text-xs text-gray-500 border-t pt-4">
                <p><strong>Política de Privacidade:</strong> Os seus dados são tratados de acordo com o RGPD. 
                Para mais informações, contacte {{ config('mail.from.address') }}</p>
            </div>
        </div>
    </div>

    <script>
        const candidaturaSlug = '{{ $candidatura->slug }}';
        
        // Carregar preferências atuais
        async function loadCurrentPreferences() {
            try {
                const response = await axios.get(`/api/candidate/privacy/${candidaturaSlug}/status`);
                const data = response.data.candidatura;
                
                document.getElementById('consentimento_emails').checked = data.consentimento_emails;
                document.getElementById('pode_ser_contactado').checked = data.pode_ser_contactado;
            } catch (error) {
                console.error('Erro ao carregar preferências:', error);
            }
        }

        // Salvar preferências
        document.getElementById('consentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                consentimento_emails: document.getElementById('consentimento_emails').checked,
                pode_ser_contactado: document.getElementById('pode_ser_contactado').checked
            };

            try {
                const response = await axios.post(`/api/candidate/privacy/${candidaturaSlug}/consent`, formData);
                showMessage(response.data.message, 'success');
            } catch (error) {
                showMessage('Erro ao salvar preferências', 'error');
                console.error('Erro:', error);
            }
        });

        // Revogar consentimento
        async function revokeConsent() {
            if (confirm('Tem certeza que deseja revogar todo o consentimento? Não receberá mais emails automáticos.')) {
                try {
                    const response = await axios.post(`/api/candidate/privacy/${candidaturaSlug}/revoke`);
                    showMessage(response.data.message, 'success');
                    
                    // Desmarcar checkboxes
                    document.getElementById('consentimento_emails').checked = false;
                    document.getElementById('pode_ser_contactado').checked = false;
                } catch (error) {
                    showMessage('Erro ao revogar consentimento', 'error');
                    console.error('Erro:', error);
                }
            }
        }

        // Mostrar mensagem
        function showMessage(text, type) {
            const messageEl = document.getElementById('message');
            messageEl.textContent = text;
            messageEl.className = `mt-4 p-3 rounded-md ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
            messageEl.classList.remove('hidden');
            
            setTimeout(() => {
                messageEl.classList.add('hidden');
            }, 5000);
        }

        // Carregar ao iniciar página
        loadCurrentPreferences();
    </script>
</body>
</html>
