<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Class CertificationRepository
 *
 * DAO component managing automated student certifications (one per theme).
 *
 * @package App\Repositories
 */
class CertificationRepository extends BaseRepository
{
    /**
     * Issue a certification for a completed theme (idempotent).
     *
     * @param int $userId
     * @param int $themeId
     * @return string Certificate unique code
     */
    public function issueCertification(int $userId, int $themeId): string
    {
        // Check if certificate already exists for this theme
        $stmtCheck = $this->db->prepare('SELECT certificate_code FROM certifications WHERE user_id = :user_id AND theme_id = :theme_id LIMIT 1');
        $stmtCheck->execute([
            'user_id' => $userId,
            'theme_id' => $themeId
        ]);
        $existingCode = $stmtCheck->fetchColumn();

        if ($existingCode) {
            return (string) $existingCode;
        }

        // Generate unique certificate code: KL-CERT-YYYY-HEX
        $certificateCode = 'KL-CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(6)));

        $sql = 'INSERT INTO certifications (user_id, theme_id, certificate_code, created_by, updated_by)
                VALUES (:user_id, :theme_id, :certificate_code, :created_by, :updated_by)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'theme_id' => $themeId,
            'certificate_code' => $certificateCode,
            'created_by' => 'certification_engine',
            'updated_by' => 'certification_engine'
        ]);

        return $certificateCode;
    }

    /**
     * Find all certifications earned by a user, with the theme metadata.
     *
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function findUserCertifications(int $userId): array
    {
        $sql = 'SELECT cert.*, t.name as theme_name, t.slug as theme_slug
                FROM certifications cert
                JOIN themes t ON cert.theme_id = t.id
                WHERE cert.user_id = :user_id
                ORDER BY cert.issued_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
