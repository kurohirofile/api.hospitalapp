<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="User",
 *     description="API Endpoints for User",
 * )
 */

class AuthController extends Controller
{
    public function __construct()
    {
            // Middleware 'auth:api' yang diterapkan pada konstruktor
    // Kecuali untuk metode 'login' dan 'register'
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

/**
 * @OA\Post(
 *     path="/auth/register",
 *     tags={"User"},
 *     summary="Membuat User baru",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             example={
 *                 "name": "John",
 *                 "email": "john@example.com",
 *                 "password": "secret"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Registrasi berhasil",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Registrasi berhasil")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validasi gagal",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid.")
 *         )
 *     )
 * )
 */
    public function register()
    {
        // Lakukan validasi terhadap input yang diterima
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
    
        // Jika validasi gagal, kembalikan respons JSON dengan pesan kesalahan validasi
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
    
        // Buat entri pengguna baru dalam tabel 'users' menggunakan model 'User'
        $user = User::create([
            'email' => request('email'),
            'name' => request('name'),
            'password' => Hash::make(request('password')),
        ]);
    
        // Periksa apakah pembuatan pengguna berhasil
        if ($user) {
            // Jika registrasi berhasil, kembalikan respons JSON dengan pesan sukses
            return response()->json(['message' => 'Registrasi berhasil'], 200);
        } else {
            // Jika registrasi gagal, kembalikan respons JSON dengan pesan kegagalan
            return response()->json(['message' => 'Registrasi gagal'], 500);
        }
        
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

/**
 * @OA\Post(
 *     path="/auth/login",
 *     tags={"User"},
 *     summary="Login Pengguna",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             example={
 *                 "email": "john@example.com",
 *                 "password": "secret"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login berhasil",
 *         @OA\JsonContent(
 *             example={"token": "generated_token_here"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             example={"error": "Email atau password salah"}
 *         )
 *     )
 * )
 */

    public function login()
    {
        // Ambil email dan password dari request yang diterima
        $credentials = request(['email', 'password']);
    
        // Coba untuk mengautentikasi pengguna berdasarkan kredensial yang diberikan
        if (! $token = auth()->attempt($credentials)) {
            // Jika autentikasi gagal, kembalikan respons JSON dengan pesan kesalahan yang sesuai
            return response()->json(['error' => 'Email atau password salah'], 401);
        }
    
        // Jika autentikasi berhasil, panggil fungsi respondWithToken() untuk menghasilkan respons dengan token autentikasi
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

     /**
/**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth"
 * )
 */

/**
 * @OA\Post(
 *     path="/auth/me",
 *     tags={"User"},
 *     summary="Informasi Pengguna Terotentikasi",
 *     security={{"bearerAuth": {}}},
 *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     @OA\Response(
 *         response=200,
 *         description="Informasi pengguna berhasil diambil",
 *         @OA\JsonContent(
 *             example={
 *                 "id": 1,
 *                 "name": "John Doe",
 *                 "email": "john@example.com"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             example={"error": "Unauthorized"}
 *         )
 *     )
 * )
 */

    public function me()
    {
        // Mengambil informasi pengguna yang saat ini terotentikasi
        $user = auth()->user();
    
        // Mengembalikan respons JSON yang berisi informasi pengguna
        return response()->json($user);
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */

     /**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth"
 * )
 */

/**
 * @OA\Post(
 *     path="/auth/logout",
 *     tags={"User"},
 *     summary="Keluar dari Sesi Pengguna Terotentikasi",
 *     security={{"bearerAuth": {}}},
 *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     @OA\Response(
 *         response=200,
 *         description="Logout berhasil",
 *         @OA\JsonContent(
 *             example={"message": "Berhasil keluar dari sesi"}
 *         )
 *     )
 * )
 */

    public function logout()
    {
        // Logout atau keluar dari sesi pengguna yang saat ini terotentikasi
        auth()->logout();
    
        // Mengembalikan respons JSON dengan pesan 'Berhasil keluar dari sesi'
        return response()->json(['message' => 'Berhasil keluar ']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

 /**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth"
 * )
 */

/**
 * @OA\Post(
 *     path="/auth/refresh",
 *     tags={"User"},
 *     summary="Memperbarui Token Autentikasi",
 *     security={{"bearerAuth": {}}},
 *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     @OA\Response(
 *         response=200,
 *         description="Token autentikasi berhasil diperbarui",
  * @OA\JsonContent(
 *             example={
 *                 "email": "john@example.com",
 *                 "password": "secret"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             example={"error": "Unauthorized"}
 *         )
 *     )
 * )
 */

    public function refresh()
    {
        // Memperbarui token autentikasi yang saat ini terotentikasi
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
    
     protected function respondWithToken($token)
    {
        // Mengambil pengguna yang saat ini terotentikasi
        $user = auth()->user();
    
        // Mengembalikan respons JSON dengan token autentikasi, nama, dan email pengguna
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }
}
