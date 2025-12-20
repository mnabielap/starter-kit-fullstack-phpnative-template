<?php

namespace App\Utils;

class Validator
{
    /**
     * Validate data against rules
     * @param array $data (Input data)
     * @param array $rules (Validation rules)
     * @return array|bool (Returns error array or true)
     */
    public static function validate(array $data, array $rules)
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            
            foreach ($ruleList as $rule) {
                // Handle rules with parameters (e.g., min:8)
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $paramStr] = explode(':', $rule);
                    $rule = $ruleName;
                    $params = explode(',', $paramStr);
                }

                $value = $data[$field] ?? null;

                // 1. Required
                if ($rule === 'required' && empty($value)) {
                    $errors[$field] = "$field is required";
                }

                // 2. Email
                if ($value && $rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "$field must be a valid email";
                }

                // 3. Min Length
                if ($value && $rule === 'min' && strlen($value) < $params[0]) {
                    $errors[$field] = "$field must be at least {$params[0]} characters";
                }

                // 4. Strong Password (Custom)
                if ($value && $rule === 'password') {
                    if (!preg_match('/\d/', $value) || !preg_match('/[a-zA-Z]/', $value)) {
                        $errors[$field] = "Password must contain at least one letter and one number";
                    }
                    if (strlen($value) < 8) {
                        $errors[$field] = "Password must be at least 8 characters";
                    }
                }
                
                // 5. Valid (Enum)
                if ($value && $rule === 'valid' && !in_array($value, $params)) {
                    $errors[$field] = "$field must be one of: " . implode(', ', $params);
                }
            }
        }

        return empty($errors) ? true : $errors;
    }
}