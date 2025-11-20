<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function user(Request $request) {
        $user = DB::table('users')
        ->where('user_id', $request->token_user_id)
        ->first();
        unset($user->password);
        return ResponseWebTrait::success(true, 'Success', $user);
    }

    public function userUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id'               => 'required|string',
            'fullname'              => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255',
            'photo'                 => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        if ($request->token_user_id != $request->user_id) {
            return ResponseWebTrait::error(false, 'Unauthorized ID USER', 403);
        }

        $data   = $validator->validated();
        unset($data['user_id']);
        if ($request->hasFile('photo')) {
            $result = Storage::disk('local')->put('avatars', $request->file('photo'));
            $data['photo'] = $result;
        }

        $data['updated_at'] = now();
        $user = DB::table('users')
        ->where('user_id', $request->token_user_id)
        ->update($data);

        return ResponseWebTrait::success(true, 'Success Update User', $user);
    }
}
