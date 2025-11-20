<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function list(Request $request) {
        $address = DB::table('addresses')
        ->where('user_id', $request->token_user_id)
        ->where('is_active', 1)
        ->get();
        return ResponseWebTrait::success(true, 'Success', $address);
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'label'       => 'required|string|max:255',
            'province_id' => 'required|number|min:1',
            'province'    => 'required|string|max:255',
            'city_id'     => 'required|number|min:1',
            'city'        => 'required|string|max:255',
            'address'     => 'required|string',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $address    = DB::table('addresses')
        ->where('user_id', $request->token_user_id)
        ->get()
        ->count();
        $data['is_main'] = 0;
        if ($address == 0) {
            $data['is_main'] = 1;
        }

        $data = $validator->validated();
        $data['user_id'] = $request->token_user_id;
        $data['is_active'] = 1;
        $data['created_at'] = now();
        DB::table('addresses')
        ->insert($data);

        return ResponseWebTrait::success(true, 'Success', $data);
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'          => 'required|number',
            'label'       => 'required|string|max:255',
            'province_id' => 'required|number|min:1',
            'province'    => 'required|string|max:255',
            'city_id'     => 'required|number|min:1',
            'city'        => 'required|string|max:255',
            'address'     => 'required|string',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $data = $validator->validated();
        $data['user_id'] = $request->token_user_id;
        $data['updated_at'] = now();
        DB::table('addresses')
        ->where('id', $request->id)
        ->where('user_id', $request->token_user_id)
        ->update($data);

        return ResponseWebTrait::success(true, 'Success', $data);
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|number',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        DB::table('addresses')
        ->where('id', $request->id)
        ->where('user_id', $request->token_user_id)
        ->update(['is_active' => 0, 'updated_at' => now()]);

        return ResponseWebTrait::success(true, 'Success', []);
    }

    public function setMain(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|number',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        DB::table('addresses')
        ->where('user_id', $request->token_user_id)
        ->where('id', '!=', $request->id)
        ->update(['is_main' => 0, 'updated_at' => now()]);

        DB::table('addresses')
        ->where('user_id', $request->token_user_id)
        ->where('id', $request->id)
        ->update(['is_main' => 1, 'updated_at' => now()]);

        return ResponseWebTrait::success(true, 'Success', []);
    }
}
