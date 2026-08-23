<?php

namespace App\Services;

class QueryValidator
{
    private array $allowedStatements;
    private array $blockedKeywords;
    private int $maxQueryLength;
    private int $maxResultRows;

    public function __construct()
    {
        $this->allowedStatements = config('security.query_execution.allowed_statements', ['SELECT', 'WITH']);
        $this->blockedKeywords = config('security.query_execution.blocked_keywords', []);
        $this->maxQueryLength = config('security.query_execution.max_query_length', 10000);
        $this->maxResultRows = config('security.query_execution.max_result_rows', 1000);
    }

    public function validate(string $query): array
    {
        $query = trim($query);

        if (empty($query)) {
            return ['valid' => false, 'message' => 'Query cannot be empty'];
        }

        if (strlen($query) > $this->maxQueryLength) {
            return ['valid' => false, 'message' => "Query exceeds maximum length of {$this->maxQueryLength} characters"];
        }

        $normalizedQuery = $this->normalizeQuery($query);

        if (!$this->isSingleStatement($normalizedQuery)) {
            return ['valid' => false, 'message' => 'Multiple statements are not allowed'];
        }

        $firstKeyword = $this->getFirstKeyword($normalizedQuery);
        if (!$firstKeyword || !in_array(strtoupper($firstKeyword), $this->allowedStatements)) {
            return ['valid' => false, 'message' => 'Only SELECT and WITH statements are allowed'];
        }

        if ($this->containsBlockedKeywords($normalizedQuery)) {
            return ['valid' => false, 'message' => 'Query contains disallowed keywords'];
        }

        if ($this->containsDangerousPatterns($normalizedQuery)) {
            return ['valid' => false, 'message' => 'Query contains potentially dangerous patterns'];
        }

        if ($this->containsComments($normalizedQuery)) {
            return ['valid' => false, 'message' => 'Comments are not allowed in queries'];
        }

        return ['valid' => true];
    }

    private function normalizeQuery(string $query): string
    {
        $query = preg_replace('/\s+/', ' ', $query);
        return trim($query);
    }

    private function isSingleStatement(string $query): bool
    {
        $semicolonCount = substr_count($query, ';');
        if ($semicolonCount === 0) return true;
        if ($semicolonCount === 1 && substr(rtrim($query), -1) === ';') return true;
        return false;
    }

    private function getFirstKeyword(string $query): ?string
    {
        $query = ltrim($query);
        if (preg_match('/^\(\s*WITH\s/i', $query)) {
            return 'WITH';
        }
        if (preg_match('/^WITH\s/i', $query)) {
            return 'WITH';
        }
        if (preg_match('/^SELECT\s/i', $query)) {
            return 'SELECT';
        }
        $words = explode(' ', $query, 2);
        return $words[0] ?? null;
    }

    private function containsBlockedKeywords(string $query): bool
    {
        $upperQuery = strtoupper($query);

        foreach ($this->blockedKeywords as $keyword) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $upperQuery)) {
                return true;
            }
        }

        return false;
    }

    private function containsDangerousPatterns(string $query): bool
    {
        $dangerousPatterns = [
            '/\bUNION\s+(ALL\s+)?SELECT\b/i',
            '/\bINTO\s+(OUTFILE|DUMPFILE)\b/i',
            '/\bLOAD\s+DATA\s+INFILE\b/i',
            '/\bSELECT\s+.*\bFROM\s+.*\bINTO\b/i',
            '/\bEXEC\s*\(/i',
            '/\bEXECUTE\s*\(/i',
            '/\bPREPARE\s+/i',
            '/\bDEALLOCATE\s+/i',
            '/\bSHOW\s+(TABLES|DATABASES|COLUMNS)\b/i',
            '/\bDESCRIBE\s+/i',
            '/\bEXPLAIN\s+/i',
            '/\bUSE\s+/i',
            '/\bSET\s+@/i',
            '/\bCALL\s+/i',
            '/\bSLEEP\s*\(/i',
            '/\bBENCHMARK\s*\(/i',
            '/\bINFORMATION_SCHEMA\b/i',
            '/\bMYSQL\b/i',
            '/\bPERFORMANCE_SCHEMA\b/i',
            '/\bSYS\b/i',
            '/\bPROCESSLIST\b/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }

    private function containsComments(string $query): bool
    {
        if (str_contains($query, '--')) return true;
        if (str_contains($query, '/*')) return true;
        if (str_contains($query, '#')) return true;
        return false;
    }
}