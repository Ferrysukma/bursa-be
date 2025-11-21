<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function listAll(Request $request) {
        $validator = Validator::make($request->all(), [
            'search'                => 'string|max:255|nullable',
            'category_id'           => 'integer|nullable',
            'page'                  => 'integer',
            'limit'                 => 'integer',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $products = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->join('stores', 'products.store_id', '=', 'stores.store_id')
        ->select('products.*', 'categories.category_name', 'stores.store_name', 'stores.photo as store_photo', 'stores.city as store_city', 'stores.province as store_province')
        ->when(!empty($request->search), function ($query) use ($request) {
            $query->where('products.product_name', 'LIKE', '%' . $request->input('search') . '%');
        })
        ->when(!empty($request->category_id), function ($query) use ($request) {
            $query->where('category_id', $request->input('category_id'));
        })
        ->where('products.is_active', 1)
        ->orderBy('products.created_at', 'desc')
        ->paginate($request->limit);

        return ResponseWebTrait::success(true, 'List All Products', $products);
    }

    public function detail($slug) {
        $product = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->join('stores', 'products.store_id', '=', 'stores.store_id')
        ->select('products.*', 'categories.category_name', 'stores.store_name', 'stores.photo as store_photo', 'stores.city as store_city', 'stores.province as store_province')
        ->where('products.slug', $slug)
        ->first();

        return ResponseWebTrait::success(true, 'Detail Product', $product);
    }

    public function listByStore(Request $request) {
        $validator = Validator::make($request->all(), [
            'search'                => 'string|max:255|nullable',
            'page'                  => 'integer',
            'limit'                 => 'integer',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $store = DB::table('stores')
        ->where('user_id', $request->token_user_id)
        ->first();

        $products = DB::table('products')
        ->where('products.store_id', $store->store_id)
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->join('stores', 'products.store_id', '=', 'stores.store_id')
        ->select('products.*', 'categories.category_name', 'stores.store_name', 'stores.photo as store_photo', 'stores.city as store_city', 'stores.province as store_province')
        ->when(!empty($request->search), function ($query) use ($request) {
            $query->where('products.product_name', 'LIKE', '%' . $request->input('search') . '%');
        })
        ->orderBy('products.created_at', 'desc')
        ->paginate($request->limit);

        return ResponseWebTrait::success(true, 'List Products By Store', $products);
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'description'  => 'required|string',
            'stock'        => 'required|integer',
            'price'        => 'required|integer',
            'category_id'  => 'required|integer',
            'photos'       => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $store = DB::table('stores')
        ->where('user_id', $request->token_user_id)
        ->first();

        $data = $validator->validated();
        if ($request->hasFile('photos')) {
            $result = Storage::disk('local')->put('products', $request->file('photos'));
            $data['photos'] = $result;
        }
        $data['created_at'] = now();
        $data['store_id'] = $store->store_id;
        $data['is_active'] = 1;
        $data['slug'] = Str::slug($request->product_name);
        // return ResponseWebTrait::success(true, 'Add Product Success', $data);

        $addProduct = DB::table('products')
        ->insert($data);

        return ResponseWebTrait::success(true, 'Add Product Success', $addProduct);
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'           => 'required|integer',
            'product_name' => 'required|string|max:255',
            'description'  => 'required|string',
            'stock'        => 'required|integer',
            'price'        => 'required|integer',
            'category_id'  => 'required|integer',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $product = DB::table('products')
        ->where('id', $request->id)
        ->first();
        if (!$product) {
            return ResponseWebTrait::error(false, 'Product Not Found', 400);
        }

        $data = $validator->validated();
        if ($request->hasFile('photos')) {
            $result = Storage::disk('local')->put('products', $request->file('photos'));
            $data['photos'] = $result;
        }
        $data['updated_at'] = now();
        $data['slug'] = Str::slug($request->product_name);

        $updateProduct = DB::table('products')
        ->where('id', $request->id)
        ->update($data);

        return ResponseWebTrait::success(true, 'Update Product Success', $updateProduct);
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $product = DB::table('products')
        ->where('id', $request->id)
        ->first();
        if (!$product) {
            return ResponseWebTrait::error(false, 'Product Not Found', 400);
        }

        $deleteProduct = DB::table('products')
        ->where('id', $request->id)
        ->update(['is_active' => 0]);

        return ResponseWebTrait::success(true, 'Delete Product Success', $deleteProduct);
    }
}
