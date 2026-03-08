<?php
// /_partials/footer.php
declare(strict_types=1);

require_once __DIR__ . '/i18n.php';
$lang = ij_lang();
$year = (int)date('Y');

$T = [
  'sv' => [
    'desc' => 'Publikt ljudbibliotek för arenaljud, jinglar och musik – byggt för att fungera tillsammans med IceJockey.app.',
    'explore' => 'Utforska',
    'platform' => 'Plattform',
    'partner' => 'Kopplat till',
    'partnerText' => 'Spela upp tracks, jinglar och arenaljud direkt i IceJockey.',
    'openPartner' => 'Öppna IceJockey.app',
    'admin' => 'Admin',
  ],
  'en' => [
    'desc' => 'Public audio library for arena sounds, jingles and music – built to work together with IceJockey.app.',
    'explore' => 'Explore',
    'platform' => 'Platform',
    'partner' => 'Connected to',
    'partnerText' => 'Play tracks, jingles and arena audio directly in IceJockey.',
    'openPartner' => 'Open IceJockey.app',
    'admin' => 'Admin',
  ],
];

$L = $T[$lang] ?? $T['sv'];
?>

  </main>

  <footer class="ij-footer">
    <div class="ij-footer-inner ij-footer-rich">

      <div class="ij-footer-brand">
        <div class="ij-footer-title"><?= htmlspecialchars(ij_t('brand', $lang), ENT_QUOTES, 'UTF-8') ?></div>

        <div class="ij-footer-muted">
          <?= htmlspecialchars($L['desc'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="ij-footer-copy">
          © <?= $year ?> • <?= htmlspecialchars(ij_t('copyright', $lang), ENT_QUOTES, 'UTF-8') ?>
        </div>
      </div>

      <div class="ij-footer-promo">
        <div class="ij-footer-col-title"><?= htmlspecialchars($L['partner'], ENT_QUOTES, 'UTF-8') ?></div>

        <a class="ij-footer-promo-card" href="https://icejockey.app" target="_blank" rel="noopener" data-no-ajax="1">
          <img
            class="ij-footer-promo-img"
            src="<?= htmlspecialchars(ij_url('/assets/ij-logo.webp'), ENT_QUOTES, 'UTF-8') ?>"
            alt="IceJockey.app"
            loading="lazy"
          >
          <div class="ij-footer-promo-body">
            <div class="ij-footer-promo-title">IceJockey.app</div>
            <div class="ij-footer-promo-text"><?= htmlspecialchars($L['partnerText'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="ij-footer-promo-link"><?= htmlspecialchars($L['openPartner'], ENT_QUOTES, 'UTF-8') ?> →</div>
          </div>
        </a>
      </div>

      <div class="ij-footer-links">

        <div class="ij-footer-col">
          <div class="ij-footer-col-title"><?= htmlspecialchars($L['explore'], ENT_QUOTES, 'UTF-8') ?></div>

          <a href="<?= htmlspecialchars(ij_url('/top/'), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars(ij_t('top', $lang), ENT_QUOTES, 'UTF-8') ?>
          </a>

          <a href="<?= htmlspecialchars(ij_url('/library/'), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars(ij_t('library', $lang), ENT_QUOTES, 'UTF-8') ?>
          </a>

          <a href="<?= htmlspecialchars(ij_url('/upload/'), ENT_QUOTES, 'UTF-8') ?>" data-no-ajax="1">
            <?= htmlspecialchars(ij_t('upload', $lang), ENT_QUOTES, 'UTF-8') ?>
          </a>
        </div>

        <div class="ij-footer-col">
          <div class="ij-footer-col-title"><?= htmlspecialchars($L['platform'], ENT_QUOTES, 'UTF-8') ?></div>

          <a href="<?= htmlspecialchars(ij_url('/about/'), ENT_QUOTES, 'UTF-8') ?>" data-no-ajax="1">
            <?= htmlspecialchars($lang === 'en' ? 'About' : 'Om oss', ENT_QUOTES, 'UTF-8') ?>
          </a>

          <a href="<?= htmlspecialchars(ij_url('/api/'), ENT_QUOTES, 'UTF-8') ?>" data-no-ajax="1">
            <?= htmlspecialchars(ij_t('api', $lang), ENT_QUOTES, 'UTF-8') ?>
          </a>

          <a href="<?= htmlspecialchars(ij_url('/status/'), ENT_QUOTES, 'UTF-8') ?>" data-no-ajax="1">
            <?= htmlspecialchars(ij_t('status', $lang), ENT_QUOTES, 'UTF-8') ?>
          </a>

          <a href="<?= htmlspecialchars(ij_url('/contact/'), ENT_QUOTES, 'UTF-8') ?>" data-no-ajax="1">
            <?= htmlspecialchars(ij_t('contact', $lang), ENT_QUOTES, 'UTF-8') ?>
          </a>

          <a href="<?= htmlspecialchars(ij_url('/admin/'), ENT_QUOTES, 'UTF-8') ?>" class="ij-footer-admin" data-no-ajax="1">
            <?= htmlspecialchars($L['admin'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        </div>

      </div>

    </div>
  </footer>
</body>
</html>