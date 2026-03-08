<?php
/* /domains/icebeats.io/public_html/_partials/creator_lib.php*/
declare(strict_types=1);

/**
 * iceBeats creator helpers
 * Grund för:
 * - creators
 * - creator claims
 * - creator edit tokens
 */

if (!function_exists('ij_creator_data_dir')) {
    function ij_creator_data_dir(): string {
        return realpath(__DIR__ . '/../data') ?: (__DIR__ . '/../data');
    }
}

if (!function_exists('ij_creator_creators_path')) {
    function ij_creator_creators_path(): string {
        return ij_creator_data_dir() . '/creators.json';
    }
}

if (!function_exists('ij_creator_claims_path')) {
    function ij_creator_claims_path(): string {
        return ij_creator_data_dir() . '/creator_claims.json';
    }
}

if (!function_exists('ij_creator_tokens_path')) {
    function ij_creator_tokens_path(): string {
        return ij_creator_data_dir() . '/creator_tokens.json';
    }
}

if (!function_exists('ij_creator_ensure_storage')) {
    function ij_creator_ensure_storage(): void {
        $dir = ij_creator_data_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $files = [
            ij_creator_creators_path() => "{}\n",
            ij_creator_claims_path()   => "[]\n",
            ij_creator_tokens_path()   => "[]\n",
        ];

        foreach ($files as $path => $default) {
            if (!is_file($path)) {
                @file_put_contents($path, $default, LOCK_EX);
            }
        }
    }
}

if (!function_exists('ij_creator_read_json_file')) {
    function ij_creator_read_json_file(string $path, mixed $fallback): mixed {
        if (!is_file($path)) return $fallback;

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') return $fallback;

        $json = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $json : $fallback;
    }
}

if (!function_exists('ij_creator_write_json_file')) {
    function ij_creator_write_json_file(string $path, mixed $data): bool {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return false;
        }

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($json === false) return false;

        return @file_put_contents($path, $json . "\n", LOCK_EX) !== false;
    }
}

if (!function_exists('ij_creator_slugify')) {
    function ij_creator_slugify(string $name): string {
        $s = trim(mb_strtolower($name, 'UTF-8'));
        if ($s === '') return '';

        $map = [
            'å' => 'a',
            'ä' => 'a',
            'ö' => 'o',
            '&' => 'and',
            '+' => 'plus',
        ];
        $s = strtr($s, $map);

        $s = preg_replace('~[^a-z0-9]+~u', '-', $s) ?? $s;
        $s = preg_replace('~\-+~', '-', $s) ?? $s;
        $s = trim($s, '-');

        return $s;
    }
}

if (!function_exists('ij_creator_norm_name')) {
    function ij_creator_norm_name(string $name): string {
        $s = trim(mb_strtolower($name, 'UTF-8'));
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }
}

if (!function_exists('ij_creator_new_id')) {
    function ij_creator_new_id(string $prefix): string {
        return $prefix . '_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(6)), 0, 12);
    }
}

if (!function_exists('ij_creator_load_all')) {
    function ij_creator_load_all(): array {
        ij_creator_ensure_storage();

        $data = ij_creator_read_json_file(ij_creator_creators_path(), []);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('ij_creator_save_all')) {
    function ij_creator_save_all(array $creators): bool {
        ij_creator_ensure_storage();
        ksort($creators);
        return ij_creator_write_json_file(ij_creator_creators_path(), $creators);
    }
}

if (!function_exists('ij_creator_claims_load_all')) {
    function ij_creator_claims_load_all(): array {
        ij_creator_ensure_storage();

        $data = ij_creator_read_json_file(ij_creator_claims_path(), []);
        return is_array($data) ? array_values($data) : [];
    }
}

if (!function_exists('ij_creator_claims_save_all')) {
    function ij_creator_claims_save_all(array $claims): bool {
        ij_creator_ensure_storage();
        return ij_creator_write_json_file(ij_creator_claims_path(), array_values($claims));
    }
}

if (!function_exists('ij_creator_tokens_load_all')) {
    function ij_creator_tokens_load_all(): array {
        ij_creator_ensure_storage();

        $data = ij_creator_read_json_file(ij_creator_tokens_path(), []);
        return is_array($data) ? array_values($data) : [];
    }
}

if (!function_exists('ij_creator_tokens_save_all')) {
    function ij_creator_tokens_save_all(array $tokens): bool {
        ij_creator_ensure_storage();
        return ij_creator_write_json_file(ij_creator_tokens_path(), array_values($tokens));
    }
}

if (!function_exists('ij_creator_empty_profile')) {
    function ij_creator_empty_profile(string $name, ?string $slug = null): array {
        $slug = $slug !== null && $slug !== '' ? $slug : ij_creator_slugify($name);
        $now  = time();

        return [
            'slug'        => $slug,
            'name'        => trim($name),
            'bio_sv'      => '',
            'bio_en'      => '',
            'image'       => '',
            'links'       => [
                'website'   => '',
                'instagram' => '',
                'spotify'   => '',
            ],
            'verified'    => false,
            'owner_email' => '',
            'claimed_at'  => 0,
            'updated_at'  => $now,
            'created_at'  => $now,
        ];
    }
}

if (!function_exists('ij_creator_find_by_slug')) {
    function ij_creator_find_by_slug(string $slug): ?array {
        $slug = trim($slug);
        if ($slug === '') return null;

        $all = ij_creator_load_all();
        return isset($all[$slug]) && is_array($all[$slug]) ? $all[$slug] : null;
    }
}

if (!function_exists('ij_creator_find_by_name')) {
    function ij_creator_find_by_name(string $name): ?array {
        $needle = ij_creator_norm_name($name);
        if ($needle === '') return null;

        $all = ij_creator_load_all();

        foreach ($all as $profile) {
            if (!is_array($profile)) continue;
            $candidate = ij_creator_norm_name((string)($profile['name'] ?? ''));
            if ($candidate === $needle) return $profile;
        }

        return null;
    }
}

if (!function_exists('ij_creator_find_or_create_by_name')) {
    function ij_creator_find_or_create_by_name(string $name): array {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Creator name missing');
        }

        $existing = ij_creator_find_by_name($name);
        if ($existing) return $existing;

        $slug = ij_creator_slugify($name);
        if ($slug === '') {
            throw new RuntimeException('Could not create creator slug');
        }

        $all = ij_creator_load_all();

        $baseSlug = $slug;
        $i = 2;
        while (isset($all[$slug])) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        $profile = ij_creator_empty_profile($name, $slug);
        $all[$slug] = $profile;
        ij_creator_save_all($all);

        return $profile;
    }
}

if (!function_exists('ij_creator_upsert_profile')) {
    function ij_creator_upsert_profile(array $profile): bool {
        $slug = trim((string)($profile['slug'] ?? ''));
        $name = trim((string)($profile['name'] ?? ''));

        if ($slug === '' || $name === '') return false;

        $all = ij_creator_load_all();

        if (!isset($profile['links']) || !is_array($profile['links'])) {
            $profile['links'] = [
                'website'   => '',
                'instagram' => '',
                'spotify'   => '',
            ];
        }

        if (!isset($profile['created_at']) || !is_numeric($profile['created_at'])) {
            $profile['created_at'] = time();
        }

        $profile['updated_at'] = time();

        $all[$slug] = $profile;
        return ij_creator_save_all($all);
    }
}

if (!function_exists('ij_creator_create_claim')) {
    function ij_creator_create_claim(array $input): array {
        $creatorName = trim((string)($input['creator_name'] ?? ''));
        $name        = trim((string)($input['name'] ?? ''));
        $email       = trim((string)($input['email'] ?? ''));
        $proofUrl    = trim((string)($input['proof_url'] ?? ''));
        $message     = trim((string)($input['message'] ?? ''));

        if ($creatorName === '' || $name === '' || $email === '') {
            throw new InvalidArgumentException('Missing required claim fields');
        }

        $creator = ij_creator_find_or_create_by_name($creatorName);
        $slug = (string)($creator['slug'] ?? '');

        $claims = ij_creator_claims_load_all();

        $claim = [
            'id'           => ij_creator_new_id('clm'),
            'creator_slug' => $slug,
            'creator_name' => (string)($creator['name'] ?? $creatorName),
            'name'         => $name,
            'email'        => $email,
            'proof_url'    => $proofUrl,
            'message'      => $message,
            'status'       => 'pending',
            'created_at'   => time(),
        ];

        $claims[] = $claim;
        ij_creator_claims_save_all($claims);

        return $claim;
    }
}

if (!function_exists('ij_creator_claim_find_by_id')) {
    function ij_creator_claim_find_by_id(string $id): ?array {
        $id = trim($id);
        if ($id === '') return null;

        $claims = ij_creator_claims_load_all();
        foreach ($claims as $claim) {
            if ((string)($claim['id'] ?? '') === $id) return $claim;
        }
        return null;
    }
}

if (!function_exists('ij_creator_claim_set_status')) {
    function ij_creator_claim_set_status(string $id, string $status): bool {
        $claims = ij_creator_claims_load_all();
        $changed = false;

        foreach ($claims as &$claim) {
            if ((string)($claim['id'] ?? '') !== $id) continue;
            $claim['status'] = $status;
            $claim['updated_at'] = time();
            $changed = true;
            break;
        }
        unset($claim);

        return $changed ? ij_creator_claims_save_all($claims) : false;
    }
}

if (!function_exists('ij_creator_approve_claim')) {
    function ij_creator_approve_claim(string $claimId): bool {
        $claims = ij_creator_claims_load_all();
        $found = null;

        foreach ($claims as &$claim) {
            if ((string)($claim['id'] ?? '') !== $claimId) continue;
            $claim['status'] = 'approved';
            $claim['updated_at'] = time();
            $found = $claim;
            break;
        }
        unset($claim);

        if (!$found || !is_array($found)) return false;

        $slug = (string)($found['creator_slug'] ?? '');
        $email = trim((string)($found['email'] ?? ''));

        if ($slug === '' || $email === '') return false;

        $all = ij_creator_load_all();
        if (!isset($all[$slug]) || !is_array($all[$slug])) {
            return false;
        }

        $all[$slug]['owner_email'] = $email;
        $all[$slug]['verified'] = true;
        $all[$slug]['claimed_at'] = time();
        $all[$slug]['updated_at'] = time();

        if (!ij_creator_save_all($all)) return false;
        return ij_creator_claims_save_all($claims);
    }
}

if (!function_exists('ij_creator_reject_claim')) {
    function ij_creator_reject_claim(string $claimId): bool {
        return ij_creator_claim_set_status($claimId, 'rejected');
    }
}

if (!function_exists('ij_creator_generate_edit_token')) {
    function ij_creator_generate_edit_token(string $creatorSlug, string $email, int $ttlSeconds = 1800): array {
        $creatorSlug = trim($creatorSlug);
        $email = trim($email);

        if ($creatorSlug === '' || $email === '') {
            throw new InvalidArgumentException('Missing creator slug or email');
        }

        $plain = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $plain);
        $now   = time();

        $tokens = ij_creator_tokens_load_all();

        // ogiltigförklara gamla aktiva tokens för samma creator
        foreach ($tokens as &$t) {
            if ((string)($t['creator_slug'] ?? '') === $creatorSlug && (int)($t['used_at'] ?? 0) === 0) {
                $t['used_at'] = $now;
            }
        }
        unset($t);

        $row = [
            'id'           => ij_creator_new_id('tok'),
            'creator_slug' => $creatorSlug,
            'email'        => $email,
            'token_hash'   => $hash,
            'expires_at'   => $now + max(300, $ttlSeconds),
            'used_at'      => 0,
            'created_at'   => $now,
        ];

        $tokens[] = $row;
        ij_creator_tokens_save_all($tokens);

        return [
            'plain_token' => $plain,
            'record'      => $row,
        ];
    }
}

if (!function_exists('ij_creator_find_valid_token')) {
    function ij_creator_find_valid_token(string $plainToken): ?array {
        $plainToken = trim($plainToken);
        if ($plainToken === '') return null;

        $hash = hash('sha256', $plainToken);
        $now = time();

        $tokens = ij_creator_tokens_load_all();
        foreach ($tokens as $row) {
            if ((string)($row['token_hash'] ?? '') !== $hash) continue;
            if ((int)($row['used_at'] ?? 0) > 0) continue;
            if ((int)($row['expires_at'] ?? 0) < $now) continue;
            return $row;
        }

        return null;
    }
}

if (!function_exists('ij_creator_mark_token_used')) {
    function ij_creator_mark_token_used(string $plainToken): bool {
        $hash = hash('sha256', trim($plainToken));
        $tokens = ij_creator_tokens_load_all();
        $changed = false;

        foreach ($tokens as &$row) {
            if ((string)($row['token_hash'] ?? '') !== $hash) continue;
            $row['used_at'] = time();
            $changed = true;
            break;
        }
        unset($row);

        return $changed ? ij_creator_tokens_save_all($tokens) : false;
    }
}