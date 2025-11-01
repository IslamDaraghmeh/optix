<?php
/**
 * Validator Helper Class
 *
 * Handles input validation with various rules
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use App\Helpers\Database;

class Validator
{
    /**
     * @var array Validation errors
     */
    private array $errors = [];

    /**
     * @var Database Database instance
     */
    private Database $db;

    /**
     * @var array Data to validate
     */
    private array $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Validate data against rules
     *
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return bool
     */
    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply validation rule
     *
     * @param string $field Field name
     * @param string $rule Rule string
     * @return void
     */
    private function applyRule(string $field, string $rule): void
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $ruleParam = $parts[1] ?? null;

        $value = $this->data[$field] ?? null;

        switch ($ruleName) {
            case 'required':
                $this->validateRequired($field, $value);
                break;
            case 'email':
                $this->validateEmail($field, $value);
                break;
            case 'min':
                $this->validateMin($field, $value, $ruleParam);
                break;
            case 'max':
                $this->validateMax($field, $value, $ruleParam);
                break;
            case 'numeric':
                $this->validateNumeric($field, $value);
                break;
            case 'integer':
                $this->validateInteger($field, $value);
                break;
            case 'date':
                $this->validateDate($field, $value);
                break;
            case 'phone':
                $this->validatePhone($field, $value);
                break;
            case 'unique':
                $this->validateUnique($field, $value, $ruleParam);
                break;
            case 'match':
                $this->validateMatch($field, $value, $ruleParam);
                break;
            case 'alpha':
                $this->validateAlpha($field, $value);
                break;
            case 'alphanumeric':
                $this->validateAlphanumeric($field, $value);
                break;
            case 'url':
                $this->validateUrl($field, $value);
                break;
            case 'in':
                $values = explode(',', $ruleParam);
                $this->validateIn($field, $value, $values);
                break;
            case 'regex':
                $this->validateRegex($field, $value, $ruleParam);
                break;
        }
    }

    /**
     * Validate required field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateRequired(string $field, $value): void
    {
        if (empty($value) && $value !== '0') {
            $this->addError($field, $this->formatFieldName($field) . ' is required');
        }
    }

    /**
     * Validate email
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateEmail(string $field, $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $this->formatFieldName($field) . ' must be a valid email address');
        }
    }

    /**
     * Validate minimum length/value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param int $min Minimum value
     * @return void
     */
    private function validateMin(string $field, $value, int $min): void
    {
        if (is_string($value) && strlen($value) < $min) {
            $this->addError($field, $this->formatFieldName($field) . " must be at least {$min} characters");
        } elseif (is_numeric($value) && $value < $min) {
            $this->addError($field, $this->formatFieldName($field) . " must be at least {$min}");
        }
    }

    /**
     * Validate maximum length/value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param int $max Maximum value
     * @return void
     */
    private function validateMax(string $field, $value, int $max): void
    {
        if (is_string($value) && strlen($value) > $max) {
            $this->addError($field, $this->formatFieldName($field) . " must not exceed {$max} characters");
        } elseif (is_numeric($value) && $value > $max) {
            $this->addError($field, $this->formatFieldName($field) . " must not exceed {$max}");
        }
    }

    /**
     * Validate numeric value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateNumeric(string $field, $value): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, $this->formatFieldName($field) . ' must be a number');
        }
    }

    /**
     * Validate integer value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateInteger(string $field, $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, $this->formatFieldName($field) . ' must be an integer');
        }
    }

    /**
     * Validate date
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateDate(string $field, $value): void
    {
        if (!empty($value)) {
            $d = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                $this->addError($field, $this->formatFieldName($field) . ' must be a valid date (YYYY-MM-DD)');
            }
        }
    }

    /**
     * Validate phone number
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validatePhone(string $field, $value): void
    {
        if (!empty($value)) {
            $pattern = '/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/';
            if (!preg_match($pattern, $value)) {
                $this->addError($field, $this->formatFieldName($field) . ' must be a valid phone number');
            }
        }
    }

    /**
     * Validate unique value in database
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $table Table name (format: table,column or table,column,id)
     * @return void
     */
    private function validateUnique(string $field, $value, string $table): void
    {
        if (empty($value)) {
            return;
        }

        $parts = explode(',', $table);
        $tableName = $parts[0];
        $columnName = $parts[1] ?? $field;
        $excludeId = $parts[2] ?? null;

        $where = "{$columnName} = ?";
        $params = [$value];

        if ($excludeId) {
            $where .= " AND id != ?";
            $params[] = $excludeId;
        }

        if ($this->db->exists($tableName, $where, $params)) {
            $this->addError($field, $this->formatFieldName($field) . ' already exists');
        }
    }

    /**
     * Validate field matches another field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $matchField Field to match
     * @return void
     */
    private function validateMatch(string $field, $value, string $matchField): void
    {
        $matchValue = $this->data[$matchField] ?? null;
        if ($value !== $matchValue) {
            $this->addError($field, $this->formatFieldName($field) . ' must match ' . $this->formatFieldName($matchField));
        }
    }

    /**
     * Validate alphabetic characters only
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateAlpha(string $field, $value): void
    {
        if (!empty($value) && !preg_match('/^[a-zA-Z\s]+$/', $value)) {
            $this->addError($field, $this->formatFieldName($field) . ' must contain only letters');
        }
    }

    /**
     * Validate alphanumeric characters
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateAlphanumeric(string $field, $value): void
    {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9\s]+$/', $value)) {
            $this->addError($field, $this->formatFieldName($field) . ' must contain only letters and numbers');
        }
    }

    /**
     * Validate URL
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return void
     */
    private function validateUrl(string $field, $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $this->formatFieldName($field) . ' must be a valid URL');
        }
    }

    /**
     * Validate value is in array
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $allowedValues Allowed values
     * @return void
     */
    private function validateIn(string $field, $value, array $allowedValues): void
    {
        if (!empty($value) && !in_array($value, $allowedValues)) {
            $this->addError($field, $this->formatFieldName($field) . ' must be one of: ' . implode(', ', $allowedValues));
        }
    }

    /**
     * Validate against regex pattern
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $pattern Regex pattern
     * @return void
     */
    private function validateRegex(string $field, $value, string $pattern): void
    {
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, $this->formatFieldName($field) . ' format is invalid');
        }
    }

    /**
     * Add validation error
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return void
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Get all validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for specific field
     *
     * @param string $field Field name
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get first error for field
     *
     * @param string $field Field name
     * @return string|null
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if field has errors
     *
     * @param string $field Field name
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Format field name for display
     *
     * @param string $field Field name
     * @return string
     */
    private function formatFieldName(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
