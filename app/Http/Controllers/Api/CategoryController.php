<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function list() {
        $categories = DB::table('categories')->get();
        return ResponseWebTrait::success(true, 'Success', $categories);
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name'       => 'required|string|max:255',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $addCategory = DB::table('categories')
                        ->insert($validator->validated());

        return ResponseWebTrait::success(true, 'Success', $addCategory);
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name'       => 'required|string|max:255',
        ]);
        if ($validator->errors()->count() > 0) {
            return ResponseWebTrait::error(false, $validator->errors()->first(), 400);
        }

        $editCategory = DB::table('categories')
                        ->where('id', $request->id)
                        ->update($validator->validated());

        return ResponseWebTrait::success(true, 'Success', $editCategory);
    }
}
