<?php

namespace Modules\Inventory\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        try {
            $message = trim($request->message);
            $history = $request->input('history', []);

            $role = $request->input('role', 'guest');

            $apiResponse = Http::timeout(300)
                ->acceptJson()
                ->post("https://love14-mewar-erp-bot.hf.space/chatbot/", [
                    'query'   => trim($request->message),
                    'history' => $request->input('history', []),
                    'role'    => "superadmin",
                    'ui_filters' => (object)[]
                ]);

            $rawBody = $apiResponse->body();
            $json = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'results' => [
                        [
                            'type'    => 'chat',
                            'message' => $rawBody ?: 'No response received from bot.',
                        ]
                    ]
                ]);
            }

            // Direct results array
            if (isset($json['results']) && is_array($json['results'])) {
                return response()->json([
                    'results' => $json['results']
                ]);
            }

            // Fallbacks
            if (isset($json['reply'])) {
                return response()->json([
                    'results' => [
                        [
                            'type'    => 'chat',
                            'message' => $json['reply'],
                        ]
                    ]
                ]);
            }

            if (isset($json['message'])) {
                return response()->json([
                    'results' => [
                        [
                            'type'    => 'chat',
                            'message' => $json['message'],
                        ]
                    ]
                ]);
            }

            if (isset($json['response'])) {
                return response()->json([
                    'results' => [
                        [
                            'type'    => 'chat',
                            'message' => $json['response'],
                        ]
                    ]
                ]);
            }

            return response()->json([
                'results' => [
                    [
                        'type'    => 'chat',
                        'message' => 'Response mila lekin expected format me nahi tha.',
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Chatbot API Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'results' => [
                    [
                        'type'    => 'chat',
                        'message' => 'Server error: ' . $e->getMessage(),
                    ]
                ]
            ], 500);
        }
    }
}