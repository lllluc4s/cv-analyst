<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

trait EncryptableFields
{
    /**
     * Campos que devem ser criptografados para compliance GDPR
     * Personalizável por modelo via propriedade $encryptable
     * NOTA: email e password são excluídos pois têm tratamento especial no Laravel
     */
    protected $defaultEncryptableFields = [
        'nome',
        'apelido', 
        'telefone',
        'linkedin_url',
        'cv_path',
        'foto_path',
        'skills',
        'experiencia_profissional',
        'formacao_academica',
    ];

    /**
     * Obtém os campos que devem ser criptografados
     */
    protected function getEncryptableFields()
    {
        return property_exists($this, 'encryptable') 
            ? $this->encryptable 
            : $this->defaultEncryptableFields;
    }

    /**
     * Método público para acessar campos criptografáveis
     */
    public function getEncryptableFieldsList()
    {
        return $this->getEncryptableFields();
    }

    /**
     * Verifica se um campo deve ser criptografado
     */
    protected function shouldEncrypt($key)
    {
        return in_array($key, $this->getEncryptableFields());
    }

    /**
     * Intercepta a definição de atributos para criptografar dados sensíveis
     */
    public function setAttribute($key, $value)
    {
        if ($this->shouldEncrypt($key) && !empty($value) && !$this->isAlreadyEncrypted($value)) {
            try {
                $value = Crypt::encryptString(is_array($value) ? json_encode($value) : $value);
            } catch (\Exception $e) {
                Log::error('Erro ao criptografar campo: ' . $key, ['error' => $e->getMessage()]);
                throw new \Exception('Erro ao processar dados sensíveis');
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Intercepta a obtenção de atributos para descriptografar dados sensíveis
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->shouldEncrypt($key) && !empty($value) && $this->isAlreadyEncrypted($value)) {
            try {
                $decrypted = Crypt::decryptString($value);
                
                // Se o campo original era um array JSON, converte de volta
                if (in_array($key, ['skills', 'experiencia_profissional', 'formacao_academica', 'analysis_result'])) {
                    $decoded = json_decode($decrypted, true);
                    return $decoded !== null ? $decoded : $decrypted;
                }
                
                return $decrypted;
            } catch (\Exception $e) {
                // Log do erro mas não expõe detalhes ao usuário
                Log::warning('Erro ao descriptografar campo: ' . $key, [
                    'model' => get_class($this),
                    'id' => $this->id ?? 'new'
                ]);
                
                // Para compatibilidade com dados antigos não criptografados
                return $value;
            }
        }

        return $value;
    }

    /**
     * Verifica se um valor já está criptografado
     */
    protected function isAlreadyEncrypted($value)
    {
        if (!is_string($value)) {
            return false;
        }

        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Método público para verificar criptografia (usado em comandos)
     */
    public function isValueEncrypted($value)
    {
        return $this->isAlreadyEncrypted($value);
    }

    /**
     * Intercepta a conversão para array para descriptografar dados
     */
    public function toArray()
    {
        $array = parent::toArray();

        foreach ($this->getEncryptableFields() as $field) {
            if (array_key_exists($field, $array)) {
                $array[$field] = $this->getAttribute($field);
            }
        }

        return $array;
    }

    /**
     * Método para reprocessar registros existentes (migração)
     */
    public function encryptExistingData()
    {
        $dirty = false;
        
        foreach ($this->getEncryptableFields() as $field) {
            $value = parent::getAttribute($field);
            
            if (!empty($value) && !$this->isAlreadyEncrypted($value)) {
                $this->attributes[$field] = Crypt::encryptString(
                    is_array($value) ? json_encode($value) : $value
                );
                $dirty = true;
            }
        }
        
        if ($dirty) {
            $this->saveQuietly(); // Salva sem disparar eventos
        }
        
        return $dirty;
    }

    /**
     * Método para permanentemente remover dados (GDPR compliance)
     */
    public function permanentlyErasePersonalData()
    {
        foreach ($this->getEncryptableFields() as $field) {
            $this->attributes[$field] = null;
        }
        
        $this->saveQuietly();
        
        Log::info('Dados pessoais permanentemente removidos', [
            'model' => get_class($this),
            'id' => $this->id,
            'timestamp' => now()
        ]);
    }
}
