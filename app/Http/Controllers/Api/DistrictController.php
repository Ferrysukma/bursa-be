<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseWebTrait;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
    public function province() {
        $provinces = DB::table('provinces')->get();
        return ResponseWebTrait::success(true, 'Success', $provinces);
    }

    public function cities($id) {
        $cities = DB::table('cities')
        ->where('province_id', $id)
        ->get();
        return ResponseWebTrait::success(true, 'Success', $cities);
    }
}
