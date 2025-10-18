<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalChatbotController extends Controller
{
    public function respond(Request $request)
    {
        try {
            $response = Http::timeout(10)
                ->post('http://127.0.0.1:8001/chat', [
                    'message' => $request->input('message')
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Si falla, responde con fallback
        }

        return response()->json([
            'reply' => 'El asistente no está disponible. Por favor, inténtelo más tarde.'
        ]);
    }
}