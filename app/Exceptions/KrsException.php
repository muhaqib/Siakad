<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

/**
 * Exception for KRS-related errors
 */
class KrsException extends SiakadException
{
    // Error codes for different KRS scenarios
    public const NO_ACTIVE_SEMESTER = 'KRS_NO_ACTIVE_SEMESTER';
    public const ALREADY_SUBMITTED = 'KRS_ALREADY_SUBMITTED';
    public const ALREADY_LOCKED = 'KRS_LOCKED';
    public const CLASS_FULL = 'KRS_CLASS_FULL';
    public const COURSE_ALREADY_TAKEN = 'KRS_COURSE_TAKEN';
    public const SKS_LIMIT_EXCEEDED = 'KRS_SKS_EXCEEDED';
    public const EMPTY_KRS = 'KRS_EMPTY';
    public const INVALID_STATUS = 'KRS_INVALID_STATUS';
    public const NOT_FOUND = 'KRS_NOT_FOUND';

    protected string $errorCode = 'KRS_ERROR';
    protected int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY;

    /**
     * Create exception for no active semester
     */
    public static function noActiveSemester(): self
    {
        $e = new self('Tidak ada tahun akademik yang aktif.');
        $e->errorCode = self::NO_ACTIVE_SEMESTER;
        $e->httpStatus = Response::HTTP_SERVICE_UNAVAILABLE;
        return $e;
    }

    /**
     * Create exception for already submitted KRS
     */
    public static function alreadySubmitted(): self
    {
        $e = new self('KRS sudah disubmit/disetujui. Tidak dapat diubah.');
        $e->errorCode = self::ALREADY_SUBMITTED;
        return $e;
    }

    /**
     * Create exception for locked KRS
     */
    public static function locked(): self
    {
        $e = new self('KRS terkunci. Tidak dapat diubah.');
        $e->errorCode = self::ALREADY_LOCKED;
        return $e;
    }

    /**
     * Create exception for full class
     */
    public static function classFull(string $className, int $capacity): self
    {
        $e = new self("Kelas {$className} penuh! Kapasitas: {$capacity}");
        $e->errorCode = self::CLASS_FULL;
        $e->context = ['class' => $className, 'capacity' => $capacity];
        return $e;
    }

    /**
     * Create exception for already taken course
     */
    public static function courseAlreadyTaken(string $courseName): self
    {
        $e = new self("Mata kuliah {$courseName} sudah diambil.");
        $e->errorCode = self::COURSE_ALREADY_TAKEN;
        $e->context = ['course' => $courseName];
        return $e;
    }

    /**
     * Create exception for SKS limit exceeded
     */
    public static function sksLimitExceeded(int $currentSks, int $newSks, int $maxSks): self
    {
        $total = $currentSks + $newSks;
        $e = new self("Melebihi batas SKS ({$maxSks}). Total SKS akan menjadi: {$total}");
        $e->errorCode = self::SKS_LIMIT_EXCEEDED;
        $e->context = [
            'current_sks' => $currentSks,
            'new_sks' => $newSks,
            'max_sks' => $maxSks,
            'total' => $total,
        ];
        return $e;
    }

    /**
     * Create exception for empty KRS submission
     */
    public static function emptyKrs(): self
    {
        $e = new self('KRS kosong tidak dapat diajukan.');
        $e->errorCode = self::EMPTY_KRS;
        return $e;
    }

    /**
     * Create exception for invalid status transition
     */
    public static function invalidStatus(string $currentStatus, string $requiredStatus): self
    {
        $e = new self("KRS tidak dalam status {$requiredStatus}. Status saat ini: {$currentStatus}");
        $e->errorCode = self::INVALID_STATUS;
        $e->context = ['current' => $currentStatus, 'required' => $requiredStatus];
        return $e;
    }

    /**
     * Create exception for KRS not found
     */
    public static function notFound(int $krsId): self
    {
        $e = new self("KRS dengan ID {$krsId} tidak ditemukan.");
        $e->errorCode = self::NOT_FOUND;
        $e->httpStatus = Response::HTTP_NOT_FOUND;
        $e->context = ['krs_id' => $krsId];
        return $e;
    }
}
