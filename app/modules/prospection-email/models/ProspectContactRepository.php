<?php

declare(strict_types=1);

class ProspectContactRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $sql = sprintf(
            'INSERT INTO prospect_contacts (%s) VALUES (%s)',
            implode(', ', $fields),
            implode(', ', array_map(static fn (string $field): string => ':' . $field, $fields))
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_combine(
            array_map(static fn (string $field): string => ':' . $field, $fields),
            array_values($data)
        ));

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM prospect_contacts WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => mb_strtolower(trim($email))]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listForValidation(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['validation_status'])) {
            $where[] = 'validation_status = :validation_status';
            $params[':validation_status'] = $filters['validation_status'];
        }

        if (!empty($filters['source_type'])) {
            $where[] = 'source_type = :source_type';
            $params[':source_type'] = $filters['source_type'];
        }

        $sql = 'SELECT * FROM prospect_contacts WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $contactId, string $status, bool $blacklisted = false): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE prospect_contacts
             SET validation_status = :status,
                 blacklist_status = :blacklisted,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':status' => $status,
            ':blacklisted' => (int) $blacklisted,
            ':id' => $contactId,
        ]);
    }

    public function findValidBySegment(array $segment): array
    {
        $where = ['validation_status = "valid"', 'blacklist_status = 0'];
        $params = [];

        if (!empty($segment['city'])) {
            $where[] = 'city = :city';
            $params[':city'] = $segment['city'];
        }

        if (!empty($segment['company_network'])) {
            $where[] = 'company_network = :company_network';
            $params[':company_network'] = $segment['company_network'];
        }

        $sql = 'SELECT * FROM prospect_contacts WHERE ' . implode(' AND ', $where) . ' ORDER BY id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
