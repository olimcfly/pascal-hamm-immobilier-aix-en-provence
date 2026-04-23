<?php

declare(strict_types=1);

require_once MODULES_PATH . '/prospection/repositories/ProspectRepository.php';

class ProspectService
{
    public function __construct(
        private readonly ProspectRepository $repo,
        private readonly int $userId
    ) {}

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    public function getList(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $offset   = ($page - 1) * $perPage;
        $contacts = $this->repo->findAll($this->userId, $filters, $perPage, $offset);
        $total    = $this->repo->countAll($this->userId, $filters);

        return [
            'contacts'    => $contacts,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function getById(int $id): ?array
    {
        return $this->repo->findById($id, $this->userId);
    }

    public function create(array $input): array
    {
        $errors = $this->validateInput($input);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        // Doublon ?
        if ($this->repo->findByEmail($input['email'], $this->userId)) {
            return ['ok' => false, 'errors' => ['email' => 'Un contact avec cet email existe déjà.']];
        }

        $data = $this->sanitize($input);
        $data['user_id'] = $this->userId;
        $id = $this->repo->create($data);

        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        $contact = $this->repo->findById($id, $this->userId);
        if (!$contact) {
            return ['ok' => false, 'errors' => ['global' => 'Contact introuvable.']];
        }

        $errors = $this->validateInput($input, isUpdate: true);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        // Email déjà utilisé par un autre contact ?
        if (!empty($input['email'])) {
            $existing = $this->repo->findByEmail($input['email'], $this->userId);
            if ($existing && (int) $existing['id'] !== $id) {
                return ['ok' => false, 'errors' => ['email' => 'Un autre contact utilise déjà cet email.']];
            }
        }

        $this->repo->update($id, $this->userId, $this->sanitize($input));

        return ['ok' => true];
    }

    public function delete(int $id): bool
    {
        return $this->repo->softDelete($id, $this->userId);
    }

    public function getStats(): array
    {
        return $this->repo->statsByStatus($this->userId);
    }

    // ------------------------------------------------------------------
    // IMPORT CSV
    // ------------------------------------------------------------------

    /**
     * Traite un fichier CSV uploadé.
     * Colonnes attendues (ordre libre, identifiées par entête) :
     *   email, first_name, last_name, phone, company, city, notes
     *
     * @return array{imported:int, skipped:int, errors:string[]}
     */
    public function importCsv(string $filePath, string $source = 'csv'): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Fichier inaccessible.']];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Impossible d\'ouvrir le fichier.']];
        }

        // Détection du séparateur (virgule ou point-virgule)
        $firstLine = fgets($handle);
        rewind($handle);
        $sep = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        // Entêtes
        $headers = fgetcsv($handle, 1000, $sep);
        if ($headers === false || $headers === null) {
            fclose($handle);
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Fichier vide ou illisible.']];
        }

        $headers = array_map(fn($h) => strtolower(trim(str_replace(['"', "'"], '', $h))), $headers);
        $colMap  = array_flip($headers);

        $get = static function (array $row, string $key, array $map): string {
            return isset($map[$key], $row[$map[$key]]) ? trim($row[$map[$key]]) : '';
        };

        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $line     = 1;

        while (($row = fgetcsv($handle, 1000, $sep)) !== false) {
            $line++;
            if (count($row) < 1) {
                continue;
            }

            $email = strtolower(trim($get($row, 'email', $colMap)));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Ligne {$line} ignorée : email invalide ({$email}).";
                $skipped++;
                continue;
            }

            // Doublon silencieux
            if ($this->repo->findByEmail($email, $this->userId)) {
                $skipped++;
                continue;
            }

            $this->repo->create([
                'user_id'    => $this->userId,
                'email'      => $email,
                'first_name' => $get($row, 'first_name', $colMap) ?: $get($row, 'prenom', $colMap),
                'last_name'  => $get($row, 'last_name',  $colMap) ?: $get($row, 'nom',    $colMap),
                'phone'      => $get($row, 'phone',      $colMap) ?: $get($row, 'telephone', $colMap),
                'company'    => $get($row, 'company',    $colMap) ?: $get($row, 'societe',   $colMap),
                'city'       => $get($row, 'city',       $colMap) ?: $get($row, 'ville',     $colMap),
                'notes'      => $get($row, 'notes',      $colMap),
                'source'     => $source,
            ]);
            $imported++;
        }

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    // ------------------------------------------------------------------
    // VALIDATION / SANITIZE
    // ------------------------------------------------------------------

    private function validateInput(array $input, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate || array_key_exists('email', $input)) {
            $email = trim($input['email'] ?? '');
            if ($email === '') {
                $errors['email'] = 'L\'email est obligatoire.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Adresse email invalide.';
            }
        }

        if (!$isUpdate && empty($input['first_name']) && empty($input['last_name'])) {
            $errors['name'] = 'Prénom ou nom obligatoire.';
        }

        return $errors;
    }

    private function sanitize(array $input): array
    {
        $clean = [];
        $textFields = ['first_name','last_name','phone','company','city','notes','source'];
        foreach ($textFields as $f) {
            if (array_key_exists($f, $input)) {
                $clean[$f] = htmlspecialchars(trim((string) $input[$f]), ENT_QUOTES, 'UTF-8');
            }
        }

        if (array_key_exists('email', $input)) {
            $clean['email'] = strtolower(trim((string) $input['email']));
        }

        foreach (['status','email_status'] as $f) {
            if (array_key_exists($f, $input)) {
                $clean[$f] = (string) $input[$f];
            }
        }

        if (array_key_exists('tags', $input)) {
            if (is_string($input['tags'])) {
                $clean['tags'] = array_filter(array_map('trim', explode(',', $input['tags'])));
            } else {
                $clean['tags'] = (array) $input['tags'];
            }
        }

        return $clean;
    }
}
