<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function responseError($status, $data, $message = '')
    {
        $response = [
            'status' => 'error',
            'data' => $data,
            'message' => $message,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    public function responseSuccess($status, $data, $message = '')
    {
        $response = [
            'status' => 'success',
            'data' => $data,
            'message' => $message,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }
}
