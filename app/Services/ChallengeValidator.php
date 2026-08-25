<?php

namespace App\Services;

use App\Core\Application;

class ChallengeValidator
{
    private \PDO $db;
    private \PDO $investigationDb;

    public function __construct()
    {
        $this->db = Application::getInstance()->db();
        $this->investigationDb = Application::getInstance()->investigationDb();
    }

    public function validate(int $userId, int $challengeId, string $query): array
    {
        $stmt = $this->db->prepare("SELECT * FROM challenges WHERE id = ?");
        $stmt->execute([$challengeId]);
        $challenge = $stmt->fetch();
        if (!$challenge) {
            return ['success' => false, 'message' => 'Challenge not found', 'xp_earned' => 0];
        }

        $stmt = $this->db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$challenge['case_id']]);
        $case = $stmt->fetch();
        if (!$case) {
            return ['success' => false, 'message' => 'Case not found', 'xp_earned' => 0];
        }

        $stmt = $this->db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
        $stmt->execute([$case['id']]);
        $databases = $stmt->fetchAll();
        $databaseId = $databases[0]['id'] ?? null;

        if (!$databaseId) {
            return ['success' => false, 'message' => 'No investigation database configured', 'xp_earned' => 0];
        }

        $startTime = microtime(true);
        try {
            $stmt = $this->investigationDb->prepare($query);
            $stmt->execute();
            $userRows = $stmt->fetchAll();
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $rowCount = count($userRows);
        } catch (\PDOException $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->recordAttempt($userId, $challengeId, $query, 'error', $executionTime, 0, $e->getMessage());
            return ['success' => false, 'message' => 'Query execution failed: ' . $this->sanitizeError($e->getMessage()), 'xp_earned' => 0, 'type' => 'sql_error'];
        }

        $validationRules = json_decode($challenge['validation_rules'] ?? '{}', true);

        $isCorrect = false;
        $message = '';

        if (!empty($validationRules)) {
            $isCorrect = $this->validateAgainstRules($userRows, $validationRules);
        } else {
            $isCorrect = true;
        }

        $xpEarned = 0;
        if ($isCorrect) {
            $hintsUsed = $this->getHintsUsed($userId, $challengeId);
            $xpEarned = max(0, $challenge['xp_reward'] - ($hintsUsed * 20));
            $message = 'Challenge completed successfully!';
            $status = 'success';
        } else {
            $message = 'Query executed but result does not match expected outcome.';
            $status = 'wrong_result';
        }

        $this->recordAttempt($userId, $challengeId, $query, $status, $executionTime, $rowCount, $isCorrect ? null : 'Result mismatch');

        if ($isCorrect) {
            $this->updateProgress($userId, $challengeId);
        }

        return [
            'success' => $isCorrect,
            'message' => $message,
            'xp_earned' => $xpEarned,
            'execution_time_ms' => $executionTime,
            'rows_returned' => $rowCount,
        ];
    }

    private function validateAgainstRules(array $rows, array $rules): bool
    {
        if (isset($rules['min_rows']) && count($rows) < $rules['min_rows']) return false;
        if (isset($rules['max_rows']) && count($rows) > $rules['max_rows']) return false;

        if (isset($rules['required_columns'])) {
            if (empty($rows)) return false;
            $columns = array_keys($rows[0]);
            foreach ($rules['required_columns'] as $col) {
                if (!in_array($col, $columns)) return false;
            }
        }

        if (isset($rules['required_values'])) {
            foreach ($rules['required_values'] as $col => $value) {
                $found = false;
                foreach ($rows as $row) {
                    if (isset($row[$col]) && $row[$col] == $value) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) return false;
            }
        }

        if (isset($rules['column_conditions'])) {
            foreach ($rules['column_conditions'] as $col => $condition) {
                foreach ($rows as $row) {
                    if (isset($row[$col])) {
                        $val = $row[$col];
                        if (isset($condition['min']) && $val < $condition['min']) return false;
                        if (isset($condition['max']) && $val > $condition['max']) return false;
                        if (isset($condition['in']) && !in_array($val, $condition['in'])) return false;
                        if (isset($condition['not_in']) && in_array($val, $condition['not_in'])) return false;
                    }
                }
            }
        }

        return true;
    }

    private function hashResult(array $rows): string
    {
        $normalized = [];
        foreach ($rows as $row) {
            ksort($row);
            $normalized[] = $row;
        }
        return hash('sha256', json_encode($normalized));
    }

    private function getHintsUsed(int $userId, int $challengeId): int
    {
        $stmt = $this->db->prepare("SELECT id FROM hints WHERE challenge_id = ?");
        $stmt->execute([$challengeId]);
        $hints = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($hints)) return 0;

        $placeholders = implode(',', array_fill(0, count($hints), '?'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM hint_usage WHERE user_id = ? AND hint_id IN ($placeholders)");
        $stmt->execute(array_merge([$userId], $hints));
        return (int)$stmt->fetchColumn();
    }

    private function recordAttempt(int $userId, int $challengeId, string $query, string $status, float $executionTime, int $rowCount, ?string $errorMessage): void
    {
        $this->db->prepare("
            INSERT INTO challenge_attempts (user_id, challenge_id, submitted_query, result_status, execution_time_ms, rows_returned, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$userId, $challengeId, $query, $status, $executionTime, $rowCount, $errorMessage]);
    }

    private function updateProgress(int $userId, int $challengeId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM challenges WHERE id = ?");
            $stmt->execute([$challengeId]);
            $challenge = $stmt->fetch();
            $caseId = $challenge['case_id'];

            $stmt = $this->db->prepare("
                SELECT * FROM challenges
                WHERE case_id = ? AND display_order > ?
                ORDER BY display_order ASC
                LIMIT 1
            ");
            $stmt->execute([$caseId, $challenge['display_order']]);
            $nextChallenge = $stmt->fetch();

            $stmt = $this->db->prepare("SELECT * FROM user_case_progress WHERE user_id = ? AND case_id = ?");
            $stmt->execute([$userId, $caseId]);
            $progress = $stmt->fetch();

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM challenges WHERE case_id = ?");
            $stmt->execute([$caseId]);
            $totalChallenges = (int)$stmt->fetchColumn();

            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT challenge_id) FROM challenge_attempts
                WHERE user_id = ? AND challenge_id IN (SELECT id FROM challenges WHERE case_id = ?) AND result_status = 'success'
            ");
            $stmt->execute([$userId, $caseId]);
            $solvedChallenges = (int)$stmt->fetchColumn();

            $progressPercent = $totalChallenges > 0 ? round(($solvedChallenges / $totalChallenges) * 100, 2) : 0;
            $completed = $solvedChallenges >= $totalChallenges && $totalChallenges > 0;

            if ($progress) {
                $updateData = [
                    'current_challenge_id' => $nextChallenge['id'] ?? null,
                    'progress_percentage' => $progressPercent,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($completed && !$progress['completed']) {
                    $updateData['completed'] = 1;
                    $updateData['completed_at'] = date('Y-m-d H:i:s');
                }
                $this->buildUpdate($progress['id'], $updateData);
            } else {
                $this->db->prepare("
                    INSERT INTO user_case_progress (user_id, case_id, current_challenge_id, progress_percentage, completed, completed_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ")->execute([
                    $userId,
                    $caseId,
                    $nextChallenge['id'] ?? null,
                    $progressPercent,
                    $completed ? 1 : 0,
                    $completed ? date('Y-m-d H:i:s') : null,
                ]);
            }

            if ($completed) {
                $this->completeCase($userId, $caseId, $challenge['xp_reward']);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function buildUpdate(int $id, array $data): void
    {
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "$col = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $this->db->prepare("UPDATE user_case_progress SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
    }

    private function completeCase(int $userId, int $caseId, int $challengeXpReward): void
    {
        $stmt = $this->db->prepare("SELECT xp_reward FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        $caseXp = $case['xp_reward'] ?? 0;

        $stmt = $this->db->prepare("UPDATE user_case_progress SET xp_earned = ?, completed = 1, completed_at = NOW() WHERE user_id = ? AND case_id = ?");
        $stmt->execute([$caseXp, $userId, $caseId]);

        $this->db->prepare("UPDATE users SET xp = xp + ? WHERE id = ?")->execute([$caseXp, $userId]);
        $this->updateUserLevel($userId);
    }

    private function updateUserLevel(int $userId): void
    {
        $stmt = $this->db->prepare("SELECT xp, level FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $newLevel = $this->calculateLevel($user['xp']);
        if ($newLevel > $user['level']) {
            $rank = $this->getRankForLevel($newLevel);
            $this->db->prepare("UPDATE users SET level = ?, detective_rank = ? WHERE id = ?")->execute([$newLevel, $rank, $userId]);
        }
    }

    private function calculateLevel(int $xp): int
    {
        if ($xp < 100) return 1;
        $level = 1;
        $required = 100;
        while ($xp >= $required) {
            $level++;
            $required += 100 * $level;
        }
        return $level;
    }

    private function getRankForLevel(int $level): string
    {
        if ($level >= 30) return 'SQL Master';
        if ($level >= 20) return 'Senior Investigator';
        if ($level >= 10) return 'Database Detective';
        if ($level >= 5) return 'Query Analyst';
        return 'SQL Rookie';
    }

    private function sanitizeError(string $message): string
    {
        $patterns = [
            '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/',
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
            '/password/i',
            '/secret/i',
            '/token/i',
        ];
        foreach ($patterns as $pattern) {
            $message = preg_replace($pattern, '[REDACTED]', $message);
        }
        return $message;
    }
}