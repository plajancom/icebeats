<?php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – About'
  : 'iceBeats.io – Om oss';

$pageDescText = ($lang === 'en')
  ? 'Learn more about iceBeats.io, the public arena audio library built to work with IceJockey.app.'
  : 'Lär dig mer om iceBeats.io, det publika ljudbiblioteket för arenaljud som är byggt för att fungera med IceJockey.app.';

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/about/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

$T = [
  'sv' => [
    'heroTitle' => 'Om iceBeats.io',
    'heroText' => 'iceBeats.io är ett publikt ljudbibliotek för arenaljud, jinglar, intros och musik som kan användas tillsammans med IceJockey.app.',
    'pill1' => 'Publikt bibliotek',
    'pill2' => 'Arena audio',
    'pill3' => 'Kopplat till IceJockey',
    'whatTitle' => 'Vad är iceBeats?',
    'whatText1' => 'iceBeats är en plats där musik och arenaljud kan samlas, spelas upp och delas vidare. Målet är att göra det enklare att hitta rätt ljud för matcher, events och sportsammanhang.',
    'whatText2' => 'Biblioteket innehåller allt från mål-ljud och orgel/charge till jinglar, intros och annan musik som passar i arenamiljö.',
    'worksTitle' => 'Så fungerar det',
    'works1Title' => 'Utforska biblioteket',
    'works1Text' => 'Bläddra bland låtar, topplista och kategorier för att hitta rätt sound.',
    'works2Title' => 'Dela eller ladda upp',
    'works2Text' => 'Skicka in egna tracks, jinglar eller effekter och gör dem tillgängliga i biblioteket.',
    'works3Title' => 'Spela i IceJockey',
    'works3Text' => 'Använd spåren direkt tillsammans med IceJockey.app för snabb och enkel uppspelning i live-miljö.',
    'whyTitle' => 'Varför finns detta?',
    'whyText' => 'iceBeats skapades för att göra det lättare att samla ljud på ett ställe och bygga en tydligare koppling mellan ljudbiblioteket och själva uppspelningsupplevelsen i IceJockey.app.',
    'connectTitle' => 'Byggt för IceJockey.app',
    'connectText' => 'iceBeats och IceJockey kompletterar varandra. iceBeats fungerar som bibliotek och delningsyta, medan IceJockey.app är verktyget för att spela upp ljuden i rätt ögonblick.',
    'ctaTitle' => 'Redo att utforska eller bidra?',
    'ctaText' => 'Öppna biblioteket för att hitta nya tracks, eller ladda upp eget material till plattformen.',
    'ctaLibrary' => 'Öppna Låtar',
    'ctaUpload' => 'Ladda upp',
    'ctaIceJockey' => 'Öppna IceJockey.app',
  ],
  'en' => [
    'heroTitle' => 'About iceBeats.io',
    'heroText' => 'iceBeats.io is a public audio library for arena sounds, jingles, intros and music designed to work together with IceJockey.app.',
    'pill1' => 'Public library',
    'pill2' => 'Arena audio',
    'pill3' => 'Connected to IceJockey',
    'whatTitle' => 'What is iceBeats?',
    'whatText1' => 'iceBeats is a place where music and arena audio can be collected, played and shared. The goal is to make it easier to find the right sound for games, events and sports environments.',
    'whatText2' => 'The library includes everything from goal sounds and organ/charge tracks to jingles, intros and other music that fits the arena atmosphere.',
    'worksTitle' => 'How it works',
    'works1Title' => 'Explore the library',
    'works1Text' => 'Browse tracks, top lists and categories to find the right sound.',
    'works2Title' => 'Share or upload',
    'works2Text' => 'Submit your own tracks, jingles or effects and make them available in the library.',
    'works3Title' => 'Play in IceJockey',
    'works3Text' => 'Use the tracks directly together with IceJockey.app for quick and simple live playback.',
    'whyTitle' => 'Why does this exist?',
    'whyText' => 'iceBeats was created to make it easier to collect audio in one place and build a clearer bridge between the audio library and the actual playback experience inside IceJockey.app.',
    'connectTitle' => 'Built for IceJockey.app',
    'connectText' => 'iceBeats and IceJockey complement each other. iceBeats works as the library and sharing hub, while IceJockey.app is the tool for playing the sounds at exactly the right moment.',
    'ctaTitle' => 'Ready to explore or contribute?',
    'ctaText' => 'Open the library to find new tracks, or upload your own content to the platform.',
    'ctaLibrary' => 'Open Tracks',
    'ctaUpload' => 'Upload',
    'ctaIceJockey' => 'Open IceJockey.app',
  ],
];

$L = $T[$lang] ?? $T['sv'];

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.wrap{max-width:1120px;margin:0 auto;padding:0 16px}

.card{
  background:#0f172a;
  border:1px solid #1f2a44;
  border-radius:16px;
  padding:20px;
  margin:14px 0;
}

.heroCard{
  position:relative;
  overflow:hidden;
}
.heroCard::after{
  content:"";
  position:absolute;
  right:-80px;
  top:-80px;
  width:240px;
  height:240px;
  background:radial-gradient(circle, rgba(96,165,250,.16), transparent 68%);
  pointer-events:none;
}

.heroTitle{
  margin:0 0 10px;
  font-size:30px;
  line-height:1.08;
  letter-spacing:.2px;
  color:#e5e7eb;
}
.heroText{
  max-width:760px;
  color:#94a3b8;
  font-size:14px;
  line-height:1.8;
}

.pills{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:16px;
}
.pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #24324f;
  background:#0b1220;
  color:#cbd5e1;
  font-size:12px;
  font-weight:800;
}

.grid2{
  display:grid;
  grid-template-columns:1.1fr .9fr;
  gap:14px;
}
@media (max-width: 900px){
  .grid2{grid-template-columns:1fr}
}

.sectionTitle{
  margin:0 0 10px;
  font-size:18px;
  color:#e5e7eb;
  font-weight:950;
}
.sectionText{
  color:#94a3b8;
  font-size:14px;
  line-height:1.8;
}
.sectionText p{
  margin:0 0 12px;
}

.steps{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:12px;
}
@media (max-width: 900px){
  .steps{grid-template-columns:1fr}
}
.step{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:16px;
  position:relative;
  overflow:hidden;
}
.step::after{
  content:"";
  position:absolute;
  right:-22px;
  bottom:-22px;
  width:90px;
  height:90px;
  background:radial-gradient(circle, rgba(96,165,250,.09), transparent 70%);
  pointer-events:none;
}
.stepNum{
  width:32px;
  height:32px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  background:#2563eb;
  color:#fff;
  font-size:13px;
  font-weight:900;
  margin-bottom:12px;
}
.stepTitle{
  color:#e5e7eb;
  font-size:15px;
  font-weight:900;
  margin-bottom:6px;
}
.stepText{
  color:#94a3b8;
  font-size:13px;
  line-height:1.7;
}

.featureCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:16px;
}
.featureTitle{
  color:#e5e7eb;
  font-size:15px;
  font-weight:900;
  margin-bottom:8px;
}
.featureText{
  color:#94a3b8;
  font-size:13px;
  line-height:1.75;
}

.ctaCard{
  display:grid;
  grid-template-columns:1.1fr .9fr;
  gap:14px;
  align-items:center;
}
@media (max-width: 900px){
  .ctaCard{grid-template-columns:1fr}
}
.ctaTitle{
  margin:0 0 8px;
  font-size:22px;
  color:#e5e7eb;
  font-weight:950;
}
.ctaText{
  color:#94a3b8;
  font-size:14px;
  line-height:1.8;
}
.ctaButtons{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  justify-content:flex-start;
}
.ctaButtons .ij-btn,
.ctaButtons .ij-btnGhost{
  text-decoration:none;
}

.visualCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  overflow:hidden;
}
.visualCard img{
  display:block;
  width:100%;
  height:auto;
}
.visualBody{
  padding:14px;
}
.visualTitle{
  color:#e5e7eb;
  font-size:15px;
  font-weight:900;
}
.visualSub{
  margin-top:6px;
  color:#94a3b8;
  font-size:12px;
  line-height:1.65;
}
</style>

<div class="wrap">

  <div class="card heroCard">
    <h1 class="heroTitle"><?= h($L['heroTitle']) ?></h1>
    <div class="heroText"><?= h($L['heroText']) ?></div>

    <div class="pills">
      <span class="pill"><?= h($L['pill1']) ?></span>
      <span class="pill"><?= h($L['pill2']) ?></span>
      <span class="pill"><?= h($L['pill3']) ?></span>
    </div>
  </div>

  <div class="grid2">
    <div class="card">
      <h2 class="sectionTitle"><?= h($L['whatTitle']) ?></h2>
      <div class="sectionText">
        <p><?= h($L['whatText1']) ?></p>
        <p><?= h($L['whatText2']) ?></p>
      </div>
    </div>

    <div class="card">
      <h2 class="sectionTitle"><?= h($L['whyTitle']) ?></h2>
      <div class="sectionText">
        <p><?= h($L['whyText']) ?></p>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="sectionTitle"><?= h($L['worksTitle']) ?></h2>

    <div class="steps">
      <div class="step">
        <div class="stepNum">1</div>
        <div class="stepTitle"><?= h($L['works1Title']) ?></div>
        <div class="stepText"><?= h($L['works1Text']) ?></div>
      </div>

      <div class="step">
        <div class="stepNum">2</div>
        <div class="stepTitle"><?= h($L['works2Title']) ?></div>
        <div class="stepText"><?= h($L['works2Text']) ?></div>
      </div>

      <div class="step">
        <div class="stepNum">3</div>
        <div class="stepTitle"><?= h($L['works3Title']) ?></div>
        <div class="stepText"><?= h($L['works3Text']) ?></div>
      </div>
    </div>
  </div>

  <div class="grid2">
    <div class="card featureCard">
      <div class="featureTitle"><?= h($L['connectTitle']) ?></div>
      <div class="featureText"><?= h($L['connectText']) ?></div>
    </div>

    <div class="visualCard">
      <img src="<?= h(ij_url('/share/icejockey-ad.webp')) ?>" alt="IceJockey.app" loading="lazy">
      <div class="visualBody">
        <div class="visualTitle">IceJockey.app</div>
        <div class="visualSub"><?= h($lang === 'en'
          ? 'The playback side of the platform for live arena use.'
          : 'Själva uppspelningssidan av plattformen för live användning i arena.') ?></div>
      </div>
    </div>
  </div>

<div class="card useCases">

  <h2 class="sectionTitle">
    <?= h($lang === 'en' ? 'Who is iceBeats for?' : 'Vem är iceBeats för?') ?>
  </h2>

  <div class="useCaseGrid">

    <div class="useCase">
      <div class="useCaseIcon">🎧</div>

      <div class="useCaseTitle">
        <?= h($lang === 'en' ? 'Creators & producers' : 'Creators & producenter') ?>
      </div>

      <div class="useCaseText">
        <?= h($lang === 'en'
          ? 'Upload and share arena-style music, jingles and sound effects. iceBeats helps creators get their tracks discovered and used in real arena environments.'
          : 'Ladda upp och dela arenamusik, jinglar och effekter. iceBeats gör det möjligt för creators att få sina tracks upptäckta och använda i riktiga arenor.') ?>
      </div>

    </div>


    <div class="useCase">
      <div class="useCaseIcon">🏒</div>

      <div class="useCaseTitle">
        <?= h($lang === 'en' ? 'Arena DJs & events' : 'Arena-DJ:s & events') ?>
      </div>

      <div class="useCaseText">
        <?= h($lang === 'en'
          ? 'Browse the library and quickly find sounds for goals, intros, hype moments and arena atmosphere.'
          : 'Bläddra i biblioteket och hitta snabbt ljud till mål, intros, hype-moment och arenastämning.') ?>
      </div>

    </div>


    <div class="useCase">
      <div class="useCaseIcon">🎛️</div>

      <div class="useCaseTitle">
        <?= h($lang === 'en' ? 'IceJockey.app users' : 'IceJockey-användare') ?>
      </div>

      <div class="useCaseText">
        <?= h($lang === 'en'
          ? 'Tracks from iceBeats can be used together with IceJockey.app to trigger audio instantly during games and live events.'
          : 'Tracks från iceBeats kan användas tillsammans med IceJockey.app för att trigga ljud direkt under matcher och live-events.') ?>
      </div>

    </div>

  </div>

</div>

  <div class="card ctaCard">
    <div>
      <h2 class="ctaTitle"><?= h($L['ctaTitle']) ?></h2>
      <div class="ctaText"><?= h($L['ctaText']) ?></div>
    </div>

    <div class="ctaButtons">
      <a class="ij-btnGhost" href="<?= h(ij_url('/library/?lang=' . $lang)) ?>">
        <?= h($L['ctaLibrary']) ?>
      </a>

      <a class="ij-btn" href="<?= h(ij_url('/upload/?lang=' . $lang)) ?>">
        <?= h($L['ctaUpload']) ?>
      </a>

      <a class="ij-btnGhost" href="https://icejockey.app" target="_blank" rel="noopener">
        <?= h($L['ctaIceJockey']) ?>
      </a>
    </div>
  </div>

</div>

<?php require __DIR__ . '/../_partials/footer.php'; ?>