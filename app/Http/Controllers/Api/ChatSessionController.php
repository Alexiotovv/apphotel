<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatSessionController extends Controller
{
    /**
     * Obtener o crear sesión de chat.
     */
    public function getOrCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->input('user_id');
        $initialData = $request->input('data', []);

        $session = ChatSession::createOrUpdate($userId, $initialData);

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'data' => $session->data,
                'created_at' => $session->created_at,
                'updated_at' => $session->updated_at,
            ]
        ]);
    }

    /**
     * Actualizar sesión de chat.
     */
    public function update(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $session = ChatSession::createOrUpdate($userId, $request->input('data'));

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'data' => $session->data,
                'updated_at' => $session->updated_at,
            ]
        ]);
    }

    /**
     * Obtener sesión por user_id.
     */
    public function show($userId)
    {
        $session = ChatSession::findByUserId($userId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'data' => $session->data,
                'created_at' => $session->created_at,
                'updated_at' => $session->updated_at,
            ]
        ]);
    }

    /**
     * Eliminar sesión.
     */
    public function destroy($userId)
    {
        $deleted = ChatSession::clear($userId);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Session cleared' : 'Session not found'
        ]);
    }

    /**
     * Obtener sesiones activas.
     */
    public function activeSessions(Request $request)
    {
        $hours = $request->input('hours', 24);
        $limit = $request->input('limit', 50);

        $sessions = ChatSession::where('updated_at', '>=', now()->subHours($hours))
                              ->orderBy('updated_at', 'desc')
                              ->limit($limit)
                              ->get();

        return response()->json([
            'success' => true,
            'count' => $sessions->count(),
            'sessions' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'data_summary' => array_keys($session->data ?? []),
                    'updated_at' => $session->updated_at,
                ];
            })
        ]);
    }

    /**
     * Limpieza de sesiones antiguas.
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        
        $deleted = ChatSession::cleanupOldSessions($days);

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old sessions",
            'deleted_count' => $deleted
        ]);
    }
}