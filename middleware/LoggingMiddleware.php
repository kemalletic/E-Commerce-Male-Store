<?php

class LoggingMiddleware {
    private $logFile;
    private $logLevel;
    
    public function __construct($logFile = 'app.log', $logLevel = 'info') {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
    }
    
    private function log($message, $level = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        error_log($logMessage, 3, $this->logFile);
    }
    
    public function before($params) {
        $request = Flight::request();
        
        // Log request details
        $requestLog = sprintf(
            "Request: %s %s\nHeaders: %s\nBody: %s",
            $request->method,
            $request->url,
            json_encode(getallheaders()),
            json_encode($request->data)
        );
        
        $this->log($requestLog, 'info');
        return true;
    }
    
    public function after($params) {
        $response = Flight::response();
        
        // Get response body
        $responseBody = ob_get_contents();
        if ($responseBody === false) {
            $responseBody = '';
        }
        
        // Log response details
        $responseLog = sprintf(
            "Response: Status %d\nBody: %s",
            $response->status(),
            $responseBody
        );
        
        $this->log($responseLog, 'info');
        
        // Check for error in response body
        if (strpos($responseBody, '"error"') !== false) {
            $errorLog = sprintf(
                "Error in response\nPath: %s\nBody: %s",
                Flight::request()->url,
                $responseBody
            );
            
            $this->log($errorLog, 'error');
        }
        
        return true;
    }
} 