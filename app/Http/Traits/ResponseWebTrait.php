<?php

namespace App\Http\Traits;


trait ResponseWebTrait
{
    public static function success($status, $message, $data)
    {
        return response()->json([
            'status'    => $status,
            'message'   => $message,
            'data'      => $data
        ], 200);
    }

    public static function error($status, $message, $code)
    {
        return response()->json([
            'status'    => $status,
            'message'   => $message,
        ], $code);
    }
}
