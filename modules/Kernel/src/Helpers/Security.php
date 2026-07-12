<?php

namespace Isjm\Helpers;

/**
 * Security Helper Class
 * 
 * Centralizes common security defenses, such as Honeypot checking.
 */
class Security
{
    /**
     * Check if a honeypot field is filled.
     * Supports both standard POST arrays and JSON-decoded inputs.
     * Exits immediately with a 400 Bad Request JSON response if spam is detected.
     *
     * @param array|null $input Data array to check. If null, automatically parses POST and JSON body.
     * @param string $field The name of the honeypot field.
     */
    public static function checkHoneypot(array $input = null, string $field = 'middle_name'): void
    {
        if ($input === null) {
            $input = $_POST;
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonInput)) {
                $input = array_merge($input, $jsonInput);
            }
        }

        if (!empty($input[$field])) {
            // Log the blocked attempt
            error_log("Spam attempt blocked by Honeypot. Field '{$field}' was filled with: " . print_r($input[$field], true));
            
            // Return 400 Bad Request
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Spam detected. Request blocked.']);
            exit;
        }
    }
}
