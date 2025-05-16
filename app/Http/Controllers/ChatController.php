<?php

namespace App\Http\Controllers;
use App\Models\ChatHistory;
use Illuminate\Http\Request;


class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $prompt = $validated['text'];

        // Setup cURL untuk streaming
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://localhost:11434/api/generate');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // JSON payload dengan streaming aktif
        $postData = json_encode([
            'model' => 'tinyllama',
            'prompt' => $prompt,
            'stream' => true,
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // Eksekusi cURL dan simpan stream ke dalam satu string
        $fullResponse = '';
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$fullResponse) {
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line && str_starts_with($line, "{")) {
                    $json = json_decode($line, true);
                    if (isset($json['response'])) {
                        $fullResponse .= $json['response'];
                    }
                }
            }
            return strlen($data);
        });

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json(['error' => 'Curl Error: ' . $error], 500);
        }

        curl_close($ch);

        // curl_exec($ch);
        // curl_close($ch);

        $user = auth()->user();
        if($user) {
            ChatHistory::create([
                'user_id' => $user->id,
                'user_message' => $prompt,
                'ai_response' => $fullResponse
            ]);
        }

        return response()->json(['result' => $fullResponse]);
    }

    public function history()
    {
        $user = auth()->user();

        if(!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // echo"$user->id";
        $history = ChatHistory::where('user_id', $user->id)->latest()->get(['user_message', 'ai_response', 'created_at']);

        return response()->json($history);
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken()); // Ini menginvalidate token JWT saat ini
        return response()->json(['message' => 'Successfully logged out']);
    }

}
