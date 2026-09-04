<?php

namespace App\Services\AcademicAdvisor;

use InvalidArgumentException;

class AdvisorGuards
{
    /**
     * Result of a guard check
     */
    public const GUARD_PASS = 'pass';
    public const GUARD_FAIL = 'fail';
    public const GUARD_RETRY = 'retry';

    /**
     * Forbidden assumption phrases from config
     */
    protected array $forbiddenPhrases;

    public function __construct()
    {
        $this->forbiddenPhrases = config('academic_rules.forbidden_assumption_phrases', [
            'biasanya',
            'umumnya',
            'tergantung',
            'pada umumnya',
            'lazimnya',
            'seringkali',
            'mungkin sekitar',
        ]);
    }

    /**
     * Assert that required rules are present in context
     *
     * @throws InvalidArgumentException
     */
    public function assertRulesPresent(array $context): void
    {
        $requiredRules = [
            'graduation_total_sks',
            'thesis_min_sks',
        ];

        foreach ($requiredRules as $rule) {
            if (!isset($context['prodi_rules'][$rule]) || $context['prodi_rules'][$rule] <= 0) {
                throw new InvalidArgumentException(
                    "Required academic rule '{$rule}' is missing or invalid in context."
                );
            }
        }
    }

    /**
     * Validate that context has minimum required data
     *
     * @throws InvalidArgumentException
     */
    public function validateContext(array $context): void
    {
        $requiredKeys = ['student', 'prodi_rules', 'academic_summary'];

        foreach ($requiredKeys as $key) {
            if (!isset($context[$key]) || empty($context[$key])) {
                throw new InvalidArgumentException(
                    "Required context key '{$key}' is missing or empty."
                );
            }
        }

        if (!isset($context['student']['nim'])) {
            throw new InvalidArgumentException(
                "Student NIM is required in context."
            );
        }
    }

    /**
     * Check if output contains generic assumption phrases
     *
     * @return array{status: string, violations: array, sanitized_output: string|null, retry_prompt: string|null}
     */
    public function preventGenericAssumptions(string $output): array
    {
        $violations = [];
        $outputLower = strtolower($output);

        foreach ($this->forbiddenPhrases as $phrase) {
            if (str_contains($outputLower, strtolower($phrase))) {
                $violations[] = $phrase;
            }
        }

        if (empty($violations)) {
            return [
                'status' => self::GUARD_PASS,
                'violations' => [],
                'sanitized_output' => null,
                'retry_prompt' => null,
            ];
        }

        // Generate retry prompt for the model
        $retryPrompt = $this->generateRetryPrompt($violations);

        // Also provide a sanitized version as fallback
        $sanitizedOutput = $this->sanitizeOutput($output, $violations);

        return [
            'status' => self::GUARD_RETRY,
            'violations' => $violations,
            'sanitized_output' => $sanitizedOutput,
            'retry_prompt' => $retryPrompt,
        ];
    }

    /**
     * Check attendance guard - prevent low attendance conclusions when data unavailable
     *
     * @return array{status: string, issue: string|null, recommended_response: string|null}
     */
    public function attendanceGuard(array $context, string $output): array
    {
        // Check if attendance data is available
        $attendanceDataAvailable = $context['attendance']['data_available'] ?? false;
        $allZeroOrNull = $context['attendance']['all_zero_or_null'] ?? true;

        // If data is available and valid, pass the guard
        if ($attendanceDataAvailable && !$allZeroOrNull) {
            return [
                'status' => self::GUARD_PASS,
                'issue' => null,
                'recommended_response' => null,
            ];
        }

        // Check if output mentions low attendance
        $lowAttendancePhrases = [
            'presensi rendah',
            'kehadiran rendah',
            'jarang hadir',
            'sering tidak hadir',
            'tingkat kehadiran rendah',
            'absensi tinggi',
            'banyak alpa',
            'sering alpa',
            'kehadiran kurang',
            'presensi kurang',
        ];

        $outputLower = strtolower($output);

        foreach ($lowAttendancePhrases as $phrase) {
            if (str_contains($outputLower, $phrase)) {
                return [
                    'status' => self::GUARD_FAIL,
                    'issue' => "Output menyebutkan '{$phrase}' tetapi data presensi belum tersedia/valid.",
                    'recommended_response' => $this->generateAttendanceUnavailableResponse(),
                ];
            }
        }

        return [
            'status' => self::GUARD_PASS,
            'issue' => null,
            'recommended_response' => null,
        ];
    }

    /**
     * Run all post-output guards
     *
     * @return array{passed: bool, issues: array, should_retry: bool, retry_prompt: string|null, replacement_output: string|null}
     */
    public function runPostGuards(array $context, string $output): array
    {
        $issues = [];
        $shouldRetry = false;
        $retryPrompt = null;
        $replacementOutput = null;

        // Check out-of-context guard FIRST (highest priority)
        $outOfContextResult = $this->outOfContextGuard($output);
        if ($outOfContextResult['status'] !== self::GUARD_PASS) {
            // Immediately return replacement - don't process further
            return [
                'passed' => false,
                'issues' => [['guard' => 'out_of_context', 'issue' => $outOfContextResult['issue']]],
                'should_retry' => false,
                'retry_prompt' => null,
                'replacement_output' => $outOfContextResult['recommended_response'],
            ];
        }

        // Check assumption guard
        $assumptionResult = $this->preventGenericAssumptions($output);
        if ($assumptionResult['status'] !== self::GUARD_PASS) {
            $issues[] = [
                'guard' => 'assumption',
                'violations' => $assumptionResult['violations'],
            ];
            $shouldRetry = true;
            $retryPrompt = $assumptionResult['retry_prompt'];
            $replacementOutput = $assumptionResult['sanitized_output'];
        }

        // Check attendance guard
        $attendanceResult = $this->attendanceGuard($context, $output);
        if ($attendanceResult['status'] !== self::GUARD_PASS) {
            $issues[] = [
                'guard' => 'attendance',
                'issue' => $attendanceResult['issue'],
            ];
            if ($replacementOutput === null) {
                $replacementOutput = $attendanceResult['recommended_response'];
            }
        }

        // Check course reference guard
        $courseResult = $this->courseReferenceGuard($context, $output);
        if ($courseResult['status'] !== self::GUARD_PASS) {
            $issues[] = [
                'guard' => 'course_reference',
                'invalid_courses' => $courseResult['invalid_courses'],
            ];
            $shouldRetry = true;
            if ($retryPrompt === null) {
                $retryPrompt = $courseResult['retry_prompt'];
            }
        }

        // Check numeric validation guard
        $numericResult = $this->numericValidationGuard($context, $output);
        if ($numericResult['status'] !== self::GUARD_PASS) {
            $issues[] = [
                'guard' => 'numeric_validation',
                'mismatches' => $numericResult['mismatches'],
            ];
            $shouldRetry = true;
            if ($retryPrompt === null) {
                $retryPrompt = $numericResult['retry_prompt'];
            }
        }

        return [
            'passed' => empty($issues),
            'issues' => $issues,
            'should_retry' => $shouldRetry,
            'retry_prompt' => $retryPrompt,
            'replacement_output' => $replacementOutput,
        ];
    }

    /**
     * Generate retry prompt for assumption violations
     */
    protected function generateRetryPrompt(array $violations): string
    {
        $violationsList = implode(', ', $violations);

        return <<<PROMPT
PERINGATAN: Respons sebelumnya mengandung frasa asumsi yang terlarang: {$violationsList}.

ATURAN KETAT:
1. Jangan gunakan kata-kata seperti {$violationsList} dalam konteks aturan akademik.
2. Gunakan HANYA data yang ada dalam context JSON.
3. Jika data tidak tersedia, katakan "data belum tersedia" bukan asumsi.
4. Berikan jawaban yang sama tapi tanpa frasa asumsi.

Ulangi jawaban tanpa frasa asumsi:
PROMPT;
    }

    /**
     * Sanitize output by replacing assumption phrases
     */
    protected function sanitizeOutput(string $output, array $violations): string
    {
        $sanitized = $output;

        $replacements = [
            'biasanya' => 'berdasarkan aturan yang berlaku',
            'umumnya' => 'sesuai ketentuan',
            'tergantung' => 'ditentukan oleh',
            'pada umumnya' => 'sesuai aturan',
            'lazimnya' => 'berdasarkan ketentuan',
            'seringkali' => 'dalam banyak kasus tercatat',
            'mungkin sekitar' => 'data menunjukkan',
            'kira-kira' => 'tepatnya',
            'sekitar' => 'tepatnya',
            'kurang lebih' => 'tepatnya',
        ];

        foreach ($violations as $phrase) {
            $phraseLower = strtolower($phrase);
            if (isset($replacements[$phraseLower])) {
                // Case-insensitive replacement
                $pattern = '/\b' . preg_quote($phrase, '/') . '\b/iu';
                $sanitized = preg_replace($pattern, $replacements[$phraseLower], $sanitized);
            }
        }

        return $sanitized;
    }

    /**
     * Generate response when attendance data is unavailable
     */
    protected function generateAttendanceUnavailableResponse(): string
    {
        return "Mohon maaf, **data presensi belum tersedia** dalam sistem. " .
               "Data presensi belum diinput atau masih dalam proses pencatatan. " .
               "Saya tidak dapat memberikan analisis kehadiran tanpa data yang valid. " .
               "Silakan hubungi bagian akademik atau dosen pengampu untuk informasi lebih lanjut.";
    }

    /**
     * Get list of forbidden phrases
     */
    public function getForbiddenPhrases(): array
    {
        return $this->forbiddenPhrases;
    }

    /**
     * Check if a specific phrase is forbidden
     */
    public function isForbiddenPhrase(string $phrase): bool
    {
        $phraseLower = strtolower($phrase);

        foreach ($this->forbiddenPhrases as $forbidden) {
            if (str_contains($phraseLower, strtolower($forbidden))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Guard: Detect out-of-context/non-academic topics
     */
    public function outOfContextGuard(string $output): array
    {
        // Only check for truly non-academic topics (not "pribadi" which causes false positives)
        $nonAcademicPatterns = [
            'harga iphone' => 'produk/harga',
            'harga hp' => 'produk/harga',
            'harga laptop' => 'produk/harga',
            'bitcoin' => 'cryptocurrency',
            'crypto' => 'cryptocurrency',
            'saham' => 'investasi',
            'forex' => 'trading',
            'pacaran' => 'hubungan personal',
        ];

        $outputLower = strtolower($output);
        
        // Skip if output is clearly a rejection message
        $rejectionPhrases = [
            'tidak dapat',
            'di luar',
            'belum tersedia',
            'tidak relevan',
            'mohon maaf',
            'di luar lingkup',
            'tidak tersedia dalam sistem'
        ];
        
        foreach ($rejectionPhrases as $phrase) {
            if (str_contains($outputLower, $phrase)) {
                return [
                    'status' => self::GUARD_PASS,
                    'issue' => null,
                    'recommended_response' => null,
                ];
            }
        }
        
        // Check for non-academic patterns
        foreach ($nonAcademicPatterns as $keyword => $topic) {
            if (str_contains($outputLower, $keyword)) {
                return [
                    'status' => self::GUARD_FAIL,
                    'issue' => "AI merespons topik non-akademik: {$topic}",
                    'recommended_response' => $this->generateOutOfContextResponse(),
                ];
            }
        }

        return [
            'status' => self::GUARD_PASS,
            'issue' => null,
            'recommended_response' => null,
        ];
    }

    /**
     * Guard: Validate course references exist in context
     */
    public function courseReferenceGuard(array $context, string $output): array
    {
        // Extract valid course codes from context
        $validCourses = [];
        foreach ($context['course_statuses'] ?? [] as $course) {
            $validCourses[strtoupper($course['kode'])] = $course['nama'];
        }

        // Pattern to find course codes in output (format: XXX000 or similar)
        preg_match_all('/\b([A-Z]{2,4}[0-9]{3,4})\b/i', $output, $matches);
        
        $invalidCourses = [];
        foreach ($matches[1] as $code) {
            $codeUpper = strtoupper($code);
            if (!isset($validCourses[$codeUpper])) {
                $invalidCourses[] = $code;
            }
        }

        if (!empty($invalidCourses)) {
            return [
                'status' => self::GUARD_RETRY,
                'invalid_courses' => $invalidCourses,
                'retry_prompt' => $this->generateCourseRetryPrompt($invalidCourses),
            ];
        }

        return [
            'status' => self::GUARD_PASS,
            'invalid_courses' => [],
            'retry_prompt' => null,
        ];
    }

    /**
     * Guard: Validate numeric data matches context
     */
    public function numericValidationGuard(array $context, string $output): array
    {
        $mismatches = [];
        
        // Get key numbers from context
        $contextSksLulus = $context['academic_summary']['total_sks_lulus'] ?? null;
        $contextIpk = $context['academic_summary']['ipk'] ?? null;
        $contextGraduationSks = $context['prodi_rules']['graduation_total_sks'] ?? null;

        // Check for SKS claims in output
        if ($contextSksLulus !== null) {
            // Pattern: "XX SKS" or "menyelesaikan XX"
            if (preg_match('/menyelesaikan\s+(\d+)\s*SKS/i', $output, $match)) {
                $claimedSks = (int) $match[1];
                if ($claimedSks !== $contextSksLulus && abs($claimedSks - $contextSksLulus) > 2) {
                    $mismatches[] = [
                        'field' => 'total_sks_lulus',
                        'claimed' => $claimedSks,
                        'actual' => $contextSksLulus,
                    ];
                }
            }
        }

        // Check for graduation SKS requirement claims
        if ($contextGraduationSks !== null) {
            if (preg_match('/lulus.*?(\d{3})\s*SKS|minimal\s*(\d{3})\s*SKS/i', $output, $match)) {
                $claimedGrad = (int) ($match[1] ?: $match[2]);
                if ($claimedGrad !== $contextGraduationSks) {
                    $mismatches[] = [
                        'field' => 'graduation_sks',
                        'claimed' => $claimedGrad,
                        'actual' => $contextGraduationSks,
                    ];
                }
            }
        }

        if (!empty($mismatches)) {
            return [
                'status' => self::GUARD_RETRY,
                'mismatches' => $mismatches,
                'retry_prompt' => $this->generateNumericRetryPrompt($mismatches),
            ];
        }

        return [
            'status' => self::GUARD_PASS,
            'mismatches' => [],
            'retry_prompt' => null,
        ];
    }

    /**
     * Generate response for out-of-context questions
     */
    protected function generateOutOfContextResponse(): string
    {
        return "Mohon maaf, pertanyaan tersebut **di luar lingkup layanan** AI Academic Advisor. " .
               "Saya hanya dapat membantu dengan pertanyaan seputar akademik, seperti:\n" .
               "- Progress kelulusan dan SKS\n" .
               "- Mata kuliah dan kurikulum\n" .
               "- Syarat skripsi dan kerja praktek\n" .
               "- Jadwal dan presensi\n\n" .
               "Silakan ajukan pertanyaan terkait akademik Anda.";
    }

    /**
     * Generate retry prompt for invalid course references
     */
    protected function generateCourseRetryPrompt(array $invalidCourses): string
    {
        $codes = implode(', ', $invalidCourses);
        return <<<PROMPT
PERINGATAN: Respons sebelumnya menyebutkan kode mata kuliah yang TIDAK ADA dalam data: {$codes}.

ATURAN KETAT:
1. HANYA gunakan kode mata kuliah yang ada dalam course_statuses dari context.
2. JANGAN mengarang kode mata kuliah.
3. Jika tidak ada data, katakan "data tidak tersedia".

Ulangi jawaban dengan kode mata kuliah yang benar dari context:
PROMPT;
    }

    /**
     * Generate retry prompt for numeric mismatches
     */
    protected function generateNumericRetryPrompt(array $mismatches): string
    {
        $details = [];
        foreach ($mismatches as $m) {
            $details[] = "{$m['field']}: Anda sebut {$m['claimed']}, seharusnya {$m['actual']}";
        }
        $detailStr = implode('; ', $details);

        return <<<PROMPT
PERINGATAN: Respons sebelumnya mengandung angka yang TIDAK SESUAI dengan data context:
{$detailStr}

ATURAN KETAT:
1. Gunakan angka PERSIS dari context JSON.
2. JANGAN membulatkan atau mengira-ngira.

Ulangi jawaban dengan angka yang benar:
PROMPT;
    }
}
