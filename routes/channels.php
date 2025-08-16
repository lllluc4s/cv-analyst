<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Company;
use App\Models\Candidato;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{companyId}.{candidatoId}', function ($user, $companyId, $candidatoId) {
    // Verificar se o usuário autenticado é parte da conversa
    if ($user instanceof Company) {
        return (int) $user->id === (int) $companyId;
    }
    
    if ($user instanceof Candidato) {
        return (int) $user->id === (int) $candidatoId;
    }
    
    return false;
});
