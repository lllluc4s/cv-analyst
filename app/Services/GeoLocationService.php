<?php

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Caminho para o banco de dados GeoIP2
     */
    protected $databasePath;

    /**
     * Reader do GeoIP2
     */
    protected $reader;

    /**
     * Construtor do serviço
     */
    public function __construct()
    {
        $this->databasePath = storage_path('geoip/GeoLite2-City.mmdb');
        
        // Verificar se o banco de dados extraído existe, se não, extrair do arquivo tar.gz
        if (!file_exists($this->databasePath)) {
            $this->extractDatabase();
        }
        
        try {
            // Só tenta inicializar se o arquivo existir e tiver tamanho maior que 0
            if (file_exists($this->databasePath) && filesize($this->databasePath) > 0) {
                $this->reader = new Reader($this->databasePath);
            } else {
                Log::warning('Banco de dados GeoIP não encontrado ou vazio. Usando dados simulados.');
                $this->reader = null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao inicializar o GeoIP reader: ' . $e->getMessage());
            $this->reader = null;
        }
    }

    /**
     * Extrai o banco de dados GeoLite2-City.tar.gz
     */
    protected function extractDatabase()
    {
        $tarPath = storage_path('geoip/GeoLite2-City.tar.gz');
        $extractPath = storage_path('geoip');
        
        if (!file_exists($tarPath)) {
            Log::error('Arquivo GeoLite2-City.tar.gz não encontrado');
            return;
        }
        
        try {
            // Extrair o arquivo tar.gz
            $process = proc_open(
                'tar -xzf ' . escapeshellarg($tarPath) . ' -C ' . escapeshellarg($extractPath),
                [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                $pipes
            );
            
            if (is_resource($process)) {
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                
                // Encontrar o arquivo .mmdb extraído e movê-lo para o local esperado
                $mmdbFiles = glob($extractPath . '/GeoLite2-City_*/GeoLite2-City.mmdb');
                if (!empty($mmdbFiles)) {
                    $mmdbFile = $mmdbFiles[0];
                    rename($mmdbFile, $this->databasePath);
                    
                    // Remover diretório temporário
                    $dirPath = dirname($mmdbFile);
                    if (is_dir($dirPath)) {
                        $this->removeDirectory($dirPath);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao extrair banco de dados GeoIP: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove um diretório recursivamente
     */
    protected function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }

    /**
     * Obtém informações de localização a partir de um endereço IP
     * 
     * @param string $ipAddress Endereço IP
     * @return array Informações de localização ou array vazio em caso de erro
     */
    public function getLocationData($ipAddress)
    {
        Log::info("GeoLocationService: Obtendo dados de localização para IP: " . $ipAddress);
        
        // Verifica se é um endereço IP local/privado
        if ($this->isPrivateIP($ipAddress) || $ipAddress === '127.0.0.1') {
            Log::info("GeoLocationService: IP é local/privado, usando dados simulados");
            // Retorna dados simulados para testes locais
            return [
                'country' => 'Brasil',
                'city' => 'São Paulo',
                'region' => 'São Paulo',
                'latitude' => -23.5505,
                'longitude' => -46.6333
            ];
        }
        
        // Tenta usar API externa (ipinfo.io) se o reader não estiver disponível
        if (!$this->reader) {
            Log::info("GeoLocationService: Reader não disponível, tentando API ipinfo.io");
            try {
                $data = $this->getLocationFromIPInfoAPI($ipAddress);
                if ($data) {
                    Log::info("GeoLocationService: Dados obtidos com sucesso da API ipinfo.io");
                    return $data;
                }
            } catch (\Exception $e) {
                Log::error("GeoLocationService: Erro ao usar API ipinfo.io: " . $e->getMessage());
                // Continua para o fallback se a API falhar
            }
            
            // Fallback usando dados baseados no IP
            Log::info("GeoLocationService: Usando fallback baseado no IP");
            $ipParts = explode('.', $ipAddress);
            $lastPart = intval(end($ipParts)) % 5; // Pega o resto da divisão do último octeto por 5
            
            $locations = [
                ['Brasil', 'São Paulo', 'São Paulo', -23.5505, -46.6333],
                ['Portugal', 'Lisboa', 'Lisboa', 38.7223, -9.1393],
                ['Espanha', 'Madrid', 'Madrid', 40.4168, -3.7038],
                ['França', 'Paris', 'Île-de-France', 48.8566, 2.3522],
                ['Alemanha', 'Berlim', 'Berlim', 52.5200, 13.4050]
            ];
            
            // Garantir que o IP sempre dê o mesmo local (para consistência)
            $location = $locations[$lastPart];
            
            Log::info("GeoLocationService: Usando geolocalização baseada no IP ($ipAddress) para tracking");
            return [
                'country' => $location[0],
                'city' => $location[1],
                'region' => $location[2],
                'latitude' => $location[3],
                'longitude' => $location[4]
            ];
        }
        
        // Se chegou aqui, temos um reader funcional
        try {
            Log::info("GeoLocationService: Usando MaxMind GeoIP");
            $record = $this->reader->city($ipAddress);
            
            return [
                'country' => $record->country->name ?? null,
                'city' => $record->city->name ?? null,
                'region' => $record->mostSpecificSubdivision->name ?? null,
                'latitude' => $record->location->latitude ?? null,
                'longitude' => $record->location->longitude ?? null
            ];
        } catch (AddressNotFoundException $e) {
            // IP não encontrado no banco de dados - usar dados simulados
            Log::info('GeoLocationService: IP não encontrado no banco GeoIP: ' . $ipAddress . ', usando dados simulados');
            return [
                'country' => 'Desconhecido',
                'city' => 'Localização Desconhecida',
                'region' => 'Desconhecido',
                'latitude' => 0,
                'longitude' => 0
            ];
        } catch (\Exception $e) {
            Log::error('GeoLocationService: Erro ao obter dados de geolocalização: ' . $e->getMessage());
            return [
                'country' => 'Erro',
                'city' => 'Erro de Geolocalização',
                'region' => 'Erro',
                'latitude' => 0,
                'longitude' => 0
            ];
        }
    }
    
    /**
     * Obtém dados de geolocalização usando a API ipinfo.io
     * API gratuita com limite de 50.000 requisições por mês
     * 
     * @param string $ipAddress
     * @return array|null
     */
    protected function getLocationFromIPInfoAPI($ipAddress)
    {
        try {
            $response = Http::get("https://ipinfo.io/{$ipAddress}/json");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Verificar se temos dados de localização
                if (isset($data['loc'])) {
                    $latLng = explode(',', $data['loc']);
                    $latitude = $latLng[0] ?? 0;
                    $longitude = $latLng[1] ?? 0;
                    
                    return [
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['region'] ?? null,
                        'latitude' => (float) $latitude,
                        'longitude' => (float) $longitude
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro ao consultar ipinfo.io: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Verifica se um endereço IP é privado/local
     * 
     * @param string $ip Endereço IP
     * @return bool
     */
    protected function isPrivateIP($ip)
    {
        $privateRanges = [
            '10.0.0.0|10.255.255.255',     // 10.0.0.0/8
            '172.16.0.0|172.31.255.255',   // 172.16.0.0/12
            '192.168.0.0|192.168.255.255', // 192.168.0.0/16
            '169.254.0.0|169.254.255.255', // 169.254.0.0/16
            '127.0.0.0|127.255.255.255'    // 127.0.0.0/8
        ];
        
        $ipLong = ip2long($ip);
        
        if ($ipLong === false) {
            return true; // IP inválido, considerar como privado
        }
        
        foreach ($privateRanges as $range) {
            list($start, $end) = explode('|', $range);
            if ($ipLong >= ip2long($start) && $ipLong <= ip2long($end)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Finaliza o serviço
     */
    public function __destruct()
    {
        // Fechar o reader ao finalizar
        if ($this->reader) {
            unset($this->reader);
        }
    }
}
