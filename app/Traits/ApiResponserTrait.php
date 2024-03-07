<?php

namespace App\Traits;

trait ApiResponserTrait
{
    /**
     * Send a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, $message = 'Success', $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Send a validation error response.
     *
     * @param  array  $errors
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function validationErrorResponse($errors, $message = 'Validation Error', $status = 422)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
    
    /**
     * Send a failure/error response.
     *
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message = 'Error', $status = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $status);
    }

    public function conflictResponse($message = 'Conflict')
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], 401);
    }
    
    public function delmessage($message = 'Conflict')
    {
        return response()->json([
            'status' => true,
            'message' => $message,
        ], 409);
    }

    
    
}