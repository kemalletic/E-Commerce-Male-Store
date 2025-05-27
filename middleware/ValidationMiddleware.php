<?php

class ValidationMiddleware {
    private $rules = [];
    
    public function __construct($rules = []) {
        $this->rules = $rules;
    }
    
    public function before($params) {
        $request = Flight::request();
        $data = $request->data;
        
        foreach ($this->rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && (!isset($data[$field]) || empty($data[$field]))) {
                Flight::json(['error' => "Field '$field' is required"], 400);
                return false;
            }
            
            if (isset($data[$field])) {
                if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    Flight::json(['error' => "Field '$field' must be a valid email"], 400);
                    return false;
                }
                
                if (strpos($rule, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $rule, $matches);
                    $min = $matches[1];
                    if (strlen($data[$field]) < $min) {
                        Flight::json(['error' => "Field '$field' must be at least $min characters"], 400);
                        return false;
                    }
                }
                
                if (strpos($rule, 'max:') !== false) {
                    preg_match('/max:(\d+)/', $rule, $matches);
                    $max = $matches[1];
                    if (strlen($data[$field]) > $max) {
                        Flight::json(['error' => "Field '$field' must not exceed $max characters"], 400);
                        return false;
                    }
                }
                
                if (strpos($rule, 'numeric') !== false && !is_numeric($data[$field])) {
                    Flight::json(['error' => "Field '$field' must be numeric"], 400);
                    return false;
                }
            }
        }
        
        return true;
    }
} 