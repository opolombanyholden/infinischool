<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * TeacherMessageController
 * 
 * Gère la messagerie de l'enseignant
 * Messages individuels, conversations de groupe, notifications
 * 
 * @package App\Http\Controllers\Teacher
 */
class TeacherMessageController extends Controller
{
    /**
     * Liste des conversations
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $filter = $request->input('filter', 'all'); // all, unread, starred, archived
        
        // Récupérer les conversations
        $query = Conversation::where(function($q) use ($teacher) {
                $q->where('user1_id', $teacher->id)
                  ->orWhere('user2_id', $teacher->id);
            })
            ->orWhereHas('participants', function($q) use ($teacher) {
                $q->where('user_id', $teacher->id);
            });
        
        // Filtres
        if ($filter === 'unread') {
            $query->whereHas('messages', function($q) use ($teacher) {
                $q->where('receiver_id', $teacher->id)
                  ->whereNull('read_at');
            });
        } elseif ($filter === 'starred') {
            $query->whereHas('messages', function($q) use ($teacher) {
                $q->where(function($sq) use ($teacher) {
                    $sq->where('sender_id', $teacher->id)
                       ->orWhere('receiver_id', $teacher->id);
                })
                ->where('is_starred', true);
            });
        } elseif ($filter === 'archived') {
            $query->where('is_archived', true);
        }
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('messages', function($q) use ($search) {
                $q->where('body', 'like', "%{$search}%");
            });
        }
        
        $conversations = $query->with([
                'lastMessage',
                'participants' => function($q) use ($teacher) {
                    $q->where('user_id', '!=', $teacher->id);
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => Conversation::whereHas('participants', function($q) use ($teacher) {
                $q->where('user_id', $teacher->id);
            })->count(),
            'unread' => $this->getUnreadCount($teacher->id),
            'starred' => $this->getStarredCount($teacher->id),
        ];
        
        return view('teacher.messages.index', compact('conversations', 'stats', 'filter'));
    }
    
    /**
     * Affiche une conversation
     * 
     * @param Conversation $conversation
     * @return View
     */
    public function show(Conversation $conversation): View
    {
        $this->authorize('view', $conversation);
        
        $teacher = Auth::user();
        
        // Charger les messages
        $messages = Message::where('conversation_id', $conversation->id)
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Marquer comme lus
        Message::where('conversation_id', $conversation->id)
            ->where('receiver_id', $teacher->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
        
        // Participants
        $participants = $conversation->participants()
            ->where('user_id', '!=', $teacher->id)
            ->with('user')
            ->get();
        
        return view('teacher.messages.show', compact('conversation', 'messages', 'participants'));
    }
    
    /**
     * Affiche le formulaire de nouveau message
     * 
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        $teacher = Auth::user();
        
        // Destinataire pré-sélectionné (optionnel)
        $recipientId = $request->input('to');
        $recipient = $recipientId ? User::find($recipientId) : null;
        
        // Étudiants disponibles (depuis les classes de l'enseignant)
        $students = User::whereHas('enrollments.class.teachers', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->where('role', 'student')
            ->orderBy('last_name', 'asc')
            ->get();
        
        // Classes pour messages de groupe
        $classes = ClassModel::whereHas('teachers', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();
        
        return view('teacher.messages.create', compact('recipient', 'students', 'classes'));
    }
    
    /**
     * Envoie un nouveau message
     * 
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required_without:recipients|exists:users,id',
            'recipients' => 'required_without:recipient_id|array',
            'recipients.*' => 'exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $teacher = Auth::user();
            
            // Message unique ou groupe
            if ($request->has('recipient_id')) {
                // Message individuel
                $conversation = $this->getOrCreateConversation($teacher->id, $request->input('recipient_id'));
                $message = $this->sendMessage($conversation, $teacher->id, $request->input('body'), $request->input('subject'));
            } else {
                // Message de groupe
                $conversation = $this->createGroupConversation(
                    $teacher->id,
                    $request->input('recipients'),
                    $request->input('subject')
                );
                $message = $this->sendMessage($conversation, $teacher->id, $request->input('body'), $request->input('subject'));
            }
            
            // Gérer les pièces jointes
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->attachFile($message, $file);
                }
            }
            
            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message envoyé avec succès !',
                    'conversation_id' => $conversation->id,
                ]);
            }
            
            return redirect()->route('teacher.messages.show', $conversation)
                ->with('success', 'Message envoyé avec succès !');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur : ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'envoi : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Répond à un message (AJAX)
     * 
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function reply(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        
        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $teacher = Auth::user();
            
            // Envoyer le message
            $message = $this->sendMessage(
                $conversation,
                $teacher->id,
                $request->input('body')
            );
            
            // Gérer les pièces jointes
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->attachFile($message, $file);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Message envoyé !',
                'data' => $message->load(['sender', 'attachments']),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Marque un message comme lu
     * 
     * @param Message $message
     * @return JsonResponse
     */
    public function markAsRead(Message $message): JsonResponse
    {
        $teacher = Auth::user();
        
        if ($message->receiver_id !== $teacher->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }
        
        $message->update(['read_at' => Carbon::now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Message marqué comme lu.',
        ]);
    }
    
    /**
     * Marque tous les messages comme lus
     * 
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $teacher = Auth::user();
        
        Message::where('receiver_id', $teacher->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Tous les messages marqués comme lus.',
        ]);
    }
    
    /**
     * Épingle/Désépingle un message
     * 
     * @param Message $message
     * @return JsonResponse
     */
    public function toggleStar(Message $message): JsonResponse
    {
        $teacher = Auth::user();
        
        if ($message->sender_id !== $teacher->id && $message->receiver_id !== $teacher->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }
        
        $message->update(['is_starred' => !$message->is_starred]);
        
        return response()->json([
            'success' => true,
            'is_starred' => $message->is_starred,
        ]);
    }
    
    /**
     * Archive une conversation
     * 
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function archive(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        
        $conversation->update(['is_archived' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Conversation archivée.',
        ]);
    }
    
    /**
     * Désarchive une conversation
     * 
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function unarchive(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        
        $conversation->update(['is_archived' => false]);
        
        return response()->json([
            'success' => true,
            'message' => 'Conversation restaurée.',
        ]);
    }
    
    /**
     * Supprime un message
     * 
     * @param Message $message
     * @return JsonResponse
     */
    public function destroy(Message $message): JsonResponse
    {
        $this->authorize('delete', $message);
        
        try {
            // Supprimer les pièces jointes
            foreach ($message->attachments as $attachment) {
                \Storage::delete($attachment->path);
                $attachment->delete();
            }
            
            $message->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Message supprimé.',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Recherche dans les messages
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $teacher = Auth::user();
        $query = $request->input('q');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Recherche vide.',
            ], 400);
        }
        
        $messages = Message::where(function($q) use ($teacher, $query) {
                $q->where('sender_id', $teacher->id)
                  ->orWhere('receiver_id', $teacher->id);
            })
            ->where(function($q) use ($query) {
                $q->where('body', 'like', "%{$query}%")
                  ->orWhere('subject', 'like', "%{$query}%");
            })
            ->with(['sender', 'receiver', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        return response()->json([
            'success' => true,
            'results' => $messages,
        ]);
    }
    
    /**
     * Récupère les nouveaux messages (polling AJAX)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getNewMessages(Request $request): JsonResponse
    {
        $teacher = Auth::user();
        $lastMessageId = $request->input('last_message_id', 0);
        
        $newMessages = Message::where('receiver_id', $teacher->id)
            ->where('id', '>', $lastMessageId)
            ->with(['sender', 'conversation'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'messages' => $newMessages,
            'count' => $newMessages->count(),
        ]);
    }
    
    /**
     * Récupère le nombre de messages non lus
     * 
     * @return JsonResponse
     */
    public function getUnreadCount(): JsonResponse
    {
        $teacher = Auth::user();
        
        $count = Message::where('receiver_id', $teacher->id)
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
    
    /**
     * Envoie un message à toute une classe
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return RedirectResponse
     */
    public function sendToClass(Request $request, ClassModel $class): RedirectResponse
    {
        $this->authorize('view', $class);
        
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $teacher = Auth::user();
            $sent = 0;
            
            foreach ($class->students as $student) {
                $conversation = $this->getOrCreateConversation($teacher->id, $student->id);
                $this->sendMessage(
                    $conversation,
                    $teacher->id,
                    $request->input('body'),
                    $request->input('subject')
                );
                $sent++;
            }
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', "Message envoyé à {$sent} étudiant(s) !");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Helper: Récupère ou crée une conversation
     * 
     * @param int $user1Id
     * @param int $user2Id
     * @return Conversation
     */
    private function getOrCreateConversation(int $user1Id, int $user2Id): Conversation
    {
        $conversation = Conversation::where(function($q) use ($user1Id, $user2Id) {
                $q->where('user1_id', $user1Id)
                  ->where('user2_id', $user2Id);
            })
            ->orWhere(function($q) use ($user1Id, $user2Id) {
                $q->where('user1_id', $user2Id)
                  ->where('user2_id', $user1Id);
            })
            ->first();
        
        if (!$conversation) {
            $conversation = Conversation::create([
                'user1_id' => $user1Id,
                'user2_id' => $user2Id,
                'type' => 'private',
            ]);
        }
        
        return $conversation;
    }
    
    /**
     * Helper: Crée une conversation de groupe
     * 
     * @param int $creatorId
     * @param array $participantIds
     * @param string|null $subject
     * @return Conversation
     */
    private function createGroupConversation(int $creatorId, array $participantIds, ?string $subject = null): Conversation
    {
        $conversation = Conversation::create([
            'type' => 'group',
            'subject' => $subject,
            'created_by' => $creatorId,
        ]);
        
        // Ajouter le créateur
        $conversation->participants()->create(['user_id' => $creatorId]);
        
        // Ajouter les participants
        foreach ($participantIds as $userId) {
            $conversation->participants()->create(['user_id' => $userId]);
        }
        
        return $conversation;
    }
    
    /**
     * Helper: Envoie un message
     * 
     * @param Conversation $conversation
     * @param int $senderId
     * @param string $body
     * @param string|null $subject
     * @return Message
     */
    private function sendMessage(Conversation $conversation, int $senderId, string $body, ?string $subject = null): Message
    {
        // Déterminer le receiver
        $receiverId = $conversation->type === 'private'
            ? ($conversation->user1_id === $senderId ? $conversation->user2_id : $conversation->user1_id)
            : null;
        
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'body' => $body,
        ]);
        
        // Mettre à jour la conversation
        $conversation->update(['updated_at' => Carbon::now()]);
        
        return $message;
    }
    
    /**
     * Helper: Attache un fichier à un message
     * 
     * @param Message $message
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    private function attachFile(Message $message, $file): void
    {
        $path = $file->store('message-attachments', 'private');
        
        DB::table('message_attachments')->insert([
            'message_id' => $message->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
    
   
    
    /**
     * Helper: Compte les messages épinglés
     * 
     * @param int $userId
     * @return int
     */
    private function getStarredCount(int $userId): int
    {
        return Message::where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->where('is_starred', true)
            ->count();
    }
}