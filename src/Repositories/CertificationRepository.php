<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Class CertificationRepository
 * 
 * DAO component managing automated student certifications.
 * 
 * @package App\Repositories
 */
class CertificationRepository extends BaseRepository
{
    /**
     * Issue a certification for a completed course (idempotent).
     *
     * @param int $userId
     * @param int $courseId
     * @return string Certificate unique code
     */
    public function issueCertification(int $userId, int $courseId): string
    {
        // Check if certificate already exists
        $stmtCheck = $this->db->prepare('SELECT certificate_code FROM certifications WHERE user_id = :user_id AND course_id = :course_id LIMIT 1');
        $stmtCheck->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
        $existingCode = $stmtCheck->fetchColumn();

        if ($existingCode) {
            return (string) $existingCode;
        }

        // Generate unique certificate code: KL-CERT-YYYY-HEX
        $certificateCode = 'KL-CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(6)));

        $sql = 'INSERT INTO certifications (user_id, course_id, certificate_code, created_by, updated_by)
                VALUES (:user_id, :course_id, :certificate_code, :created_by, :updated_by)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'certificate_code' => $certificateCode,
            'created_by' => 'certification_engine',
            'updated_by' => 'certification_engine'
        ]);

        return $certificateCode;
    }

    /**
     * Find all certifications earned by a user, including course and theme metadata.
     *
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function findUserCertifications(int $userId): array
    {
        $sql = 'SELECT cert.*, c.title as course_title, c.slug as course_slug, t.name as theme_name
                FROM certifications cert
                JOIN courses c ON cert.course_id = c.id
                JOIN themes t ON c.theme_id = t.id
                WHERE cert.user_id = :user_id
                ORDER BY cert.issued_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
