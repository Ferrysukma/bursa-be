<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function signup(Request $request) {
        $validator = Validator::make($request->all(), [
            'fullname'              => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255',
            'password'              => 'required|string|min:4|max:255|confirmed',
            'password_confirmation' => 'required|string|min:4|max:255',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        // Check if user with this email already exists
        $users = DB::table('users')
        ->where('email', $request->email)
        ->get()
        ->count();
        if ($users > 0) {
            return ResponseWebTrait::error(false, 'Email already exists', 400);
        }

        // Insert user to DB
        $data   = $validator->validated();
        $data['password'] = bcrypt($request->password);
        $data['created_at'] = now();
        unset($data['password_confirmation']);
        $insertUser = DB::table('users')
        ->insertGetId($data);

        return ResponseWebTrait::success(true, 'Success Sign Up', $insertUser);
    }

    public function signin(Request $request) {
        $validator = Validator::make(request()->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $user   = DB::table('users')
        ->where('email', $request->email)
        ->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseWebTrait::error(false, 'Invalid email or password', 401);
        }
        unset($user->password);

        $token = Str::random(60);
        DB::table('api_tokens')->insert([
            'user_id'    => $user->user_id,
            'token'      => hash('sha256', $token),
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ]);

        return ResponseWebTrait::success(true, 'Success Sign In', [
            'user'       => $user,
            'token'      => $token,
            'expires_at' => now()->addHours(24)
        ]);
    }

    public function signout(Request $request) {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        DB::table('api_tokens')->where('token', hash('sha256', $token))->delete();
        return ResponseWebTrait::success(true, 'Success Sign Out', []);
    }
}
