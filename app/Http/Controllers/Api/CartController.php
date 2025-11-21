<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function list(Request $request) {
        $carts = DB::table('carts')
        ->where('carts.user_id', $request->token_user_id)
        ->leftJoin('products', 'carts.product_id', '=', 'products.id')
        ->select('carts.*', 'products.product_name', 'products.photos as product_photos', 'products.price as product_price', 'products.slug as product_slug')
        ->get();

        return ResponseWebTrait::success(true, 'Success', $carts);
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|min:1',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $cart = DB::table('carts')
        ->where('user_id', $request->token_user_id)
        ->where('product_id', $request->product_id)
        ->first();

        $data = $validator->validated();
        if ($cart) {
            $data['quantity'] = $cart->quantity + 1;
            $data['id'] = $cart->id;
        } else {
            $data['quantity'] = 1;
        }
        $data['user_id'] = $request->token_user_id;

        $product = DB::table('products')
        ->where('id', $request->product_id)
        ->first();

        if ($data['quantity'] > $product->stock) {
            return ResponseWebTrait::error(false, 'Product stock not enough', 400);
        }

        DB::table('carts')
        ->upsert($data, ['user_id', 'product_id'], ['quantity']);

        return ResponseWebTrait::success(true, 'Success add cart', $data);
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
            'quantity' => 'required|integer',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $data = $validator->validated();
        if ($request->quantity == 0) {
            DB::table('carts')
            ->where('id', $request->id)
            ->where('user_id', $request->token_user_id)
            ->delete();
            return ResponseWebTrait::success(true, 'Success delete cart', []);
        }

        $cart = DB::table('carts')
        ->where('id', $request->id)
        ->where('user_id', $request->token_user_id)
        ->first();

        $product = DB::table('products')
        ->where('id', $cart->product_id)
        ->first();

        if ($product->stock < $request->quantity) {
            return ResponseWebTrait::error(false, 'Product stock not enough', 400);
        }

        DB::table('carts')
        ->where('id', $request->id)
        ->update($data);

        return ResponseWebTrait::success(true, 'Success update cart', $data);
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $cart = DB::table('carts')
        ->where('id', $request->id)
        ->delete();

        return ResponseWebTrait::success(true, 'Success delete cart', $cart);
    }
}
