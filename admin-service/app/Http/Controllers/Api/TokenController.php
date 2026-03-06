<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokenController extends Controller
{

    public function generate(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('personal-access-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'message' => 'Personal access token created successfully. Copy this token now, you won\'t see it again.'
        ]);
    }


    public function revokeAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'All tokens revoked successfully'
        ]);
    }


    public function list(Request $request)
    {
        $tokens = $request->user()->tokens()->select('id', 'name', 'created_at', 'last_used_at')->get();

        return response()->json([
            'tokens' => $tokens
        ]);
    }
}
