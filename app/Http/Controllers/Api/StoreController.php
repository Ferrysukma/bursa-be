<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function open(Request $request) {
        $validator = Validator::make($request->all(), [
            'store_name'  => 'required|string|max:255',
            'description' => 'required|string',
            'photo'       => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'province_id' => 'required|integer|min:1',
            'province'    => 'required|string|max:255',
            'city_id'     => 'required|integer|min:1',
            'city'        => 'required|string|max:255',
            'address'     => 'required|string',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $store = DB::table('stores')
        ->where('user_id', $request->token_user_id)
        ->get()
        ->count();
        if ($store > 0) {
            return ResponseWebTrait::error(false, 'Store Already Open', 400);
        }

        $data   = $validator->validated();
        if ($request->hasFile('photo')) {
            $result = Storage::disk('local')->put('store', $request->file('photo'));
            $data['photo'] = $result;
        }

        $data['user_id'] = $request->token_user_id;
        $data['slug'] = Str::slug($request->store_name);
        $data['created_at'] = now();
        $createStore = DB::table('stores')
                        ->insert($data);

        if (!$createStore) {
            return ResponseWebTrait::error(false, 'Failed Create Store', 400);
        }
        DB::table('users')
        ->where('user_id', $request->token_user_id)
        ->update(['is_seller' => 1]);

        return ResponseWebTrait::success(true, 'Success', $data);
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'store_id'    => 'required|integer|min:1',
            'store_name'  => 'required|string|max:255',
            'description' => 'required|string',
            'photo'       => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'province_id' => 'required|integer|min:1',
            'province'    => 'required|string|max:255',
            'city_id'     => 'required|integer|min:1',
            'city'        => 'required|string|max:255',
            'address'     => 'required|string',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $store = DB::table('stores')
        ->where('user_id', $request->token_user_id)
        ->where('store_id', $request->store_id)
        ->first();
        if (!$store) {
            return ResponseWebTrait::error(false, 'Store Not Found', 400);
        }

        $data   = $validator->validated();
        if ($request->hasFile('photo')) {
            $result = Storage::disk('local')->put('store', $request->file('photo'));
            $data['photo'] = $result;
        }

        $data['updated_at'] = now();
        DB::table('stores')
        ->where('store_id', $request->store_id)
        ->where('user_id', $request->token_user_id)
        ->update($data);

        return ResponseWebTrait::success(true, 'Success', $data);
    }
}
