<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function index()
    {
        $data = User::orderBy('id','DESC')->paginate();

        return response()->json(['data'=>$data]);
    }
    public function update($id,Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'position' =>'required',
            'phone' => 'required'
        ]);

        $user = User::find($id);

        if (!$user){
            return response()->json(['error' => 'Data tidak ditemukan'], 401);
        }

        $user->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'position'=>$request->position,
            'phone'=>$request->phone
        ]);

        if($request->password){
            $user->update(['password' => Hash::make($request->password)]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'));
    }
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'position'=>'required',
            'phone'=>'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'));
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        // Ambil user yang sedang login
        $user = auth()->user();

        $expiresInMinutes = auth('api')->factory()->getTTL(); // TTL = waktu hidup token (menit)
        $expiredAt = Carbon::now()->addMinutes($expiresInMinutes)->toISOString(); // format ISO8601

        return response()->json([
            'token' => $token,
            'token_expired_at' => $expiredAt, 
            'expires_in' => $expiresInMinutes * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'alamat' => $user->alamat ?? null,
                'position' => $user->position ?? null
            ]
        ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }
}