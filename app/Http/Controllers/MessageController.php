<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Company;
use App\Models\Candidato;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    /**
     * Get conversations list for authenticated user
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        
        if ($user instanceof Company) {
            // Para empresas, buscar todas as conversas
            $conversations = Message::where('company_id', $user->id)
                ->with(['candidato'])
                ->selectRaw('candidato_id, MAX(created_at) as last_message_at')
                ->groupBy('candidato_id')
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(function ($item) use ($user) {
                    $candidato = Candidato::find($item->candidato_id);
                    $unreadCount = Message::conversation($user->id, $item->candidato_id)
                        ->where('sender_type', 'candidato')
                        ->unread()
                        ->count();
                    
                    // Buscar a última mensagem para mostrar prévia
                    $lastMessage = Message::conversation($user->id, $item->candidato_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    return [
                        'candidato' => [
                            'id' => $candidato->id,
                            'nome' => $candidato->nome_completo,
                            'foto_url' => $candidato->foto_url,
                        ],
                        'last_message' => $lastMessage ? [
                            'content' => $lastMessage->content,
                            'sender_type' => $lastMessage->sender_type,
                            'created_at' => $lastMessage->created_at,
                        ] : null,
                        'last_message_at' => $item->last_message_at,
                        'unread_count' => $unreadCount,
                    ];
                });
                
        } else if ($user instanceof Candidato) {
            // Para candidatos, buscar todas as conversas
            $conversations = Message::where('candidato_id', $user->id)
                ->with(['company'])
                ->selectRaw('company_id, MAX(created_at) as last_message_at')
                ->groupBy('company_id')
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(function ($item) use ($user) {
                    $company = Company::find($item->company_id);
                    $unreadCount = Message::conversation($item->company_id, $user->id)
                        ->where('sender_type', 'company')
                        ->unread()
                        ->count();
                    
                    // Buscar a última mensagem para mostrar prévia
                    $lastMessage = Message::conversation($item->company_id, $user->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    return [
                        'company' => [
                            'id' => $company->id,
                            'name' => $company->name,
                            'logo_url' => $company->logo_url,
                        ],
                        'last_message' => $lastMessage ? [
                            'content' => $lastMessage->content,
                            'sender_type' => $lastMessage->sender_type,
                            'created_at' => $lastMessage->created_at,
                        ] : null,
                        'last_message_at' => $item->last_message_at,
                        'unread_count' => $unreadCount,
                    ];
                });
        }

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Get messages for a specific conversation
     */
    public function messages(Request $request)
    {
        $user = Auth::user();
        
        if ($user instanceof Company) {
            $request->validate(['candidato_id' => 'required|exists:candidatos,id']);
            $candidatoId = $request->candidato_id;
            $companyId = $user->id;
        } else if ($user instanceof Candidato) {
            $request->validate(['company_id' => 'required|exists:companies,id']);
            $companyId = $request->company_id;
            $candidatoId = $user->id;
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $messages = Message::conversation($companyId, $candidatoId)
            ->with(['company:id,name', 'candidato:id,nome,apelido'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_type' => $message->sender_type,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender_type === 'company' ? 
                        $message->company->name : 
                        $message->candidato->nome_completo,
                    'read_at' => $message->read_at,
                    'created_at' => $message->created_at,
                ];
            });

        // Marcar mensagens como lidas para o usuário atual
        $senderType = $user instanceof Company ? 'candidato' : 'company';
        Message::conversation($companyId, $candidatoId)
            ->where('sender_type', $senderType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Send a new message
     */
    public function send(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        if ($user instanceof Company) {
            $request->validate(['candidato_id' => 'required|exists:candidatos,id']);
            $candidatoId = $request->candidato_id;
            $companyId = $user->id;
            $senderType = 'company';
            $senderId = $user->id;
            $recipient = Candidato::find($candidatoId);
        } else if ($user instanceof Candidato) {
            $request->validate(['company_id' => 'required|exists:companies,id']);
            $companyId = $request->company_id;
            $candidatoId = $user->id;
            $senderType = 'candidato';
            $senderId = $user->id;
            $recipient = Company::find($companyId);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::create([
            'company_id' => $companyId,
            'candidato_id' => $candidatoId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'content' => $request->content,
        ]);

        // Broadcast em tempo real
        broadcast(new NewMessage($message));

        // Enviar notificação por email
        Notification::send($recipient, new NewMessageNotification($message));

        return response()->json([
            'message' => 'Mensagem enviada com sucesso',
            'data' => [
                'id' => $message->id,
                'content' => $message->content,
                'sender_type' => $message->sender_type,
                'sender_id' => $message->sender_id,
                'created_at' => $message->created_at,
            ]
        ], 201);
    }

    /**
     * Get companies list for candidatos to start conversations
     */
    public function companies()
    {
        $companies = Company::select('id', 'name', 'logo_url')
            ->orderBy('name')
            ->get();

        return response()->json(['companies' => $companies]);
    }

    /**
     * Get candidatos list for companies to start conversations
     */
    public function candidatos()
    {
        $candidatos = Candidato::select('id', 'nome', 'apelido', 'foto_url')
            ->orderBy('nome')
            ->get()
            ->map(function ($candidato) {
                return [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome_completo,
                    'foto_url' => $candidato->foto_url,
                ];
            });

        return response()->json(['candidatos' => $candidatos]);
    }
}
