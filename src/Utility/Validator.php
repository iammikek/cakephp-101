<?php
declare(strict_types=1);

namespace App\Utility;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $rules
     */
    public static function firstError(array $data, array $rules): ?string
    {
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $skipField = false;
            foreach ($fieldRules as $rule) {
                if ($rule === 'nullable' && ($value === null || $value === '')) {
                    $skipField = true;
                    break;
                }
                if ($skipField) {
                    break;
                }
                $error = self::checkRule($field, $value, $rule);
                if ($error !== null) {
                    return $error;
                }
            }
        }

        return null;
    }

    private static function checkRule(string $field, mixed $value, string $rule): ?string
    {
        $label = str_replace('_', ' ', $field);

        if ($rule === 'required') {
            if ($value === null || $value === '') {
                return "The {$label} field is required.";
            }

            return null;
        }

        if (str_starts_with($rule, 'min:')) {
            $min = (int)substr($rule, 4);
            if (is_string($value) && strlen($value) < $min) {
                return "The {$label} field must be at least {$min} characters.";
            }
            if (is_numeric($value) && (float)$value < $min) {
                return "The {$label} field must be at least {$min}.";
            }

            return null;
        }

        if (str_starts_with($rule, 'max:')) {
            $max = (int)substr($rule, 4);
            if (is_string($value) && strlen($value) > $max) {
                return "The {$label} field must not be greater than {$max} characters.";
            }
            if (is_numeric($value) && (float)$value > $max) {
                return "The {$label} field must not be greater than {$max}.";
            }

            return null;
        }

        if ($rule === 'email') {
            if (!is_string($value) || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                return "The {$label} field must be a valid email address.";
            }

            return null;
        }

        if ($rule === 'string') {
            if ($value !== null && !is_string($value)) {
                return "The {$label} field must be a string.";
            }

            return null;
        }

        if ($rule === 'integer') {
            if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
                return "The {$label} field must be an integer.";
            }

            return null;
        }

        if ($rule === 'numeric') {
            if ($value !== null && !is_numeric($value)) {
                return "The {$label} field must be a number.";
            }

            return null;
        }

        if ($rule === 'gt:0') {
            if ($value !== null && is_numeric($value) && (float)$value <= 0) {
                return "The {$label} field must be greater than 0.";
            }

            return null;
        }

        return null;
    }
}
