<?php

class ErrorHandlingMiddleware {
    public function after($params) {
        $response = Flight::response();
        
        // If there's an error in the response
        if (isset($response->data['error'])) {
            $status = $response->status() ?: 400;
            
            // Format error response
            $errorResponse = [
                'status' => 'error',
                'message' => $response->data['error'],
                'timestamp' => date('Y-m-d H:i:s'),
                'path' => Flight::request()->url
            ];
            
            // Add stack trace in development environment
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $errorResponse['debug'] = [
                    'file' => debug_backtrace()[0]['file'] ?? null,
                    'line' => debug_backtrace()[0]['line'] ?? null
                ];
            }
            
            Flight::json($errorResponse, $status);
            return false;
        }
        
        // Format success response
        if (isset($response->data)) {
            $successResponse = [
                'status' => 'success',
                'data' => $response->data,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            Flight::json($successResponse, $response->status() ?: 200);
        }
        
        return true;
    }
} 