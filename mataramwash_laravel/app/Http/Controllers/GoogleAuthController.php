<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $credential = $request->input('credential');

        if (empty($credential)) {
            return response()->json(['success' => false, 'message' => 'Token Google tidak ditemukan.']);
        }

        $parts = explode('.', $credential);
        if (count($parts) !== 3) {
            return response()->json(['success' => false, 'message' => 'Format token Google tidak valid.']);
        }

        $payload_b64 = $parts[1];
        $payload_json = base64_decode(str_pad(strtr($payload_b64, '-_', '+/'), strlen($payload_b64) % 4, '=', STR_PAD_RIGHT));
        $payload = json_decode($payload_json, true);

        if (!$payload) {
            return response()->json(['success' => false, 'message' => 'Gagal membaca payload token Google.']);
        }

        $google_id = $payload['sub'] ?? '';
        $email = $payload['email'] ?? '';
        $name = $payload['name'] ?? '';
        $picture = $payload['picture'] ?? '';

        if (empty($google_id) || empty($email)) {
            return response()->json(['success' => false, 'message' => 'Informasi pengguna Google tidak lengkap.']);
        }

        try {
            // 1. Cari berdasarkan google_id
            $user = User::where('google_id', $google_id)->first();

            if ($user) {
                Auth::login($user, true);
                return response()->json(['success' => true, 'redirect' => '/']);
            }

            // 2. Cari berdasarkan email
            $user_by_email = User::where('email', $email)->first();

            if ($user_by_email) {
                $user_by_email->update([
                    'google_id' => $google_id,
                    'foto_profil' => $picture
                ]);
                Auth::login($user_by_email, true);
                return response()->json(['success' => true, 'redirect' => '/']);
            }

            // 3. User baru
            $new_user = User::create([
                'nama' => $name,
                'email' => $email,
                'google_id' => $google_id,
                'foto_profil' => $picture,
            ]);

            Auth::login($new_user, true);
            return response()->json(['success' => true, 'redirect' => '/']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Kesalahan database: ' . $e->getMessage()]);
        }
    }
}
