<?php

declare(strict_types=1);

class ProspectRepository
{
    public function __construct(private readonly PDO $db) {}

    // ------------------------------------------------------------------
    // READ
    // ------------------------------------------------------------------

    public function findAll(int $userId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where  = ['deleted_at IS NULL', 'user_id = :user_id'];
        $params = [':user_id' => $userId];

        if (!empty($filters['search'])) {
            $where[]           = '(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR company LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[]          = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['email_status'])) {
            $where[]                 = 'email_status = :email_status';
            $params[':email_status'] = $filters['email_status'];
        }

        if (!empty($filters['source'])) {
            $where[]          = 'source = :source';
            $params[':source'] = $filters['source'];
        }

        $sql = 'SELECT * FROM prospect_contacts WHERE ' . implode(' AND ', $where)
             . ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(int $userId, array $filters = []): int
    {
        $where  = ['deleted_at IS NULL', 'user_id = :user_id'];
        $params = [':user_id' => $userId];

        if (!empty($filters['search'])) {
            $where[]           = '(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR company LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[]          = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql  = 'SELECT COUNT(*) FROM prospect_contacts WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM prospect_contacts WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findByEmail(string $email, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM prospect_contacts WHERE email = :email AND user_id = :user_id AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':email' => $email, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // WRITE
    // ------------------------------------------------------------------

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_contacts
                (user_id, first_name, last_name, email, phone, company, city, source, tags, email_status, status, notes)
             VALUES
                (:user_id, :first_name, :last_name, :email, :phone, :company, :city, :source, :tags, :email_status, :status, :notes)'
        );

        $stmt->execute([
            ':user_id'      => $data['user_id'],
            ':first_name'   => $data['first_name']   ?? '',
            ':last_name'    => $data['last_name']     ?? '',
            ':email'        => strtolower(trim($data['email'])),
            ':phone'        => $data['phone']         ?? null,
            ':company'      => $data['company']       ?? null,
            ':city'         => $data['city']          ?? null,
            ':source'       => $data['source']        ?? 'manual',
            ':tags'         => isset($data['tags']) ? json_encode($data['tags']) : null,
            ':email_status' => $data['email_status']  ?? 'unknown',
            ':status'       => $data['status']        ?? 'active',
            ':notes'        => $data['notes']         ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id, ':user_id' => $userId];

        $allowed = ['first_name','last_name','email','phone','company','city','source','tags','email_status','status','notes'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]          = "{$field} = :{$field}";
                $params[":{$field}"] = ($field === 'tags' && is_array($data[$field]))
                    ? json_encode($data[$field])
                    : $data[$field];
            }
        }

        if ($fields === []) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE prospect_contacts SET ' . implode(', ', $fields)
            . ' WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL'
        );

        return $stmt->execute($params);
    }

    public function softDelete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE prospect_contacts SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE prospect_contacts SET status = :status WHERE id = :id'
        );
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    // ------------------------------------------------------------------
    // STATS
    // ------------------------------------------------------------------

    public function statsByStatus(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, COUNT(*) AS total
             FROM prospect_contacts
             WHERE user_id = :user_id AND deleted_at IS NULL
             GROUP BY status'
        );
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['total'];
        }
        return $out;
    }
}
