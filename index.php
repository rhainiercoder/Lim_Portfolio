<?php
$name = "Rhainier I. Lim";
$title = "BSIT Student";
$tagline = "I build practical software and thoughtful digital experiences.";
$about = "I'm Rhainier Lim, and my main focus is digital design and art. I’m self-taught in design and artistic practice, where I’ve developed my eye for composition, color, and visual storytelling. My programming knowledge comes from what I’ve learned in school, and I use that foundation to bring my design ideas to life.";
$email = "rhainierlim07@gmail.com";
$skills = ["PHP", "HTML", "CSS", "JavaScript", "Java", "MySQL", "SQLite", "Git"];

$systems = [
    [
        "id" => "dental", "title" => "Dental Clinic Management System", "kind" => "Full-stack web application",
        "description" => "A patient-first clinic booking system with separate patient, dentist, and admin workflows, backed by Express and MySQL.",
        "stack" => ["HTML", "CSS", "JavaScript", "Node.js", "Express", "MySQL"],
        "features" => ["Patient registration and appointment booking", "Dentist schedules and appointment status", "Admin tools for patients, services, and schedules"],
        "image" => "assets/images/Programming%20Works/Dental%20Clinic%20Management%20System.png", "alt" => "ZNS Dental Clinic homepage with appointment call to action",
        "demo" => "demos/dental-clinic.html",
        "files" => [
            ["label" => "server.js", "path" => "assets/Source%20Codes/Dental%20Clinic%20Management%20System/server.js", "lang" => "javascript"],
            ["label" => "Homepage", "path" => "assets/Source%20Codes/Dental%20Clinic%20Management%20System/pages/homepage.html", "lang" => "html"],
            ["label" => "Shared app logic", "path" => "assets/Source%20Codes/Dental%20Clinic%20Management%20System/js/app.js", "lang" => "javascript"],
            ["label" => "Authentication", "path" => "assets/Source%20Codes/Dental%20Clinic%20Management%20System/js/auth.js", "lang" => "javascript"],
            ["label" => "Database schema", "path" => "assets/Source%20Codes/Dental%20Clinic%20Management%20System/database/01DatabaseSchema.sql", "lang" => "sql"],
        ],
    ],
    [
        "id" => "recipes", "title" => "Recipe Manager", "kind" => "Desktop application · Java Swing",
        "description" => "Create, edit, search, and save recipes with ingredient lists, cooking steps, and photos using SQLite.",
        "stack" => ["Java", "Swing", "SQLite", "JDBC"],
        "features" => ["Build ingredient and instruction lists", "Search and browse saved recipes", "Edit or remove recipes stored in SQLite"],
        "image" => "assets/images/Programming%20Works/Recipe%20Manager.png", "alt" => "Recipe Manager create recipe screen",
        "demo" => "demos/recipe-core.html",
        "files" => [["label" => "RecipeManagerSwing.java", "path" => "assets/Source%20Codes/RecipeManager/RecipeManagerSwing.java", "lang" => "java"]],
    ],
    [
        "id" => "hanoi", "title" => "Tower of Hanoi", "kind" => "Puzzle game · Java Swing",
        "description" => "A configurable Tower of Hanoi puzzle with legal-move checks, move tracking, hints, and an animated solver.",
        "stack" => ["Java", "Swing", "Stacks", "SQLite"],
        "features" => ["Choose disk and rod counts", "Move disks with rule validation", "Watch or step through an automatic solution"],
        "image" => "assets/images/Programming%20Works/Tower%20of%20Hanoi.png", "alt" => "Tower of Hanoi game with colored disks on three rods",
        "demo" => "demos/hanoi-core.html",
        "files" => [["label" => "TowerOfHanoiSwing.java", "path" => "assets/Source%20Codes/TowerofHanoiStack/TowerOfHanoiSwing.java", "lang" => "java"]],
    ],
    [
        "id" => "trains", "title" => "Train Route Station Manager", "kind" => "Desktop application · Java Swing",
        "description" => "Manage rail lines, station order, train availability, and station-to-station moves in a visual desktop interface.",
        "stack" => ["Java", "Swing", "Linked lists", "SQLite"],
        "features" => ["Browse stations by rail line", "Update train availability", "Move between stations and manage route data"],
        "image" => "assets/images/Programming%20Works/Train%20Route%20Station%20Manager.png", "alt" => "Train Route Station Manager showing rail line stations and availability",
        "demo" => "demos/station-core.html",
        "files" => [["label" => "TrainRouteStationSwing.java", "path" => "assets/Source%20Codes/TrainRouteStationLinkedList/TrainRouteStationSwing.java", "lang" => "java"]],
    ],
];

// Discover image-based work directly from the portfolio's categorized asset folders.
function scanArtwork($folder, $category) {
    $items = [];
    $base = __DIR__ . "/assets/images/" . $folder;
    foreach (glob($base . "/*.{png,jpg,jpeg,webp,gif}", GLOB_BRACE) ?: [] as $file) {
        $title = pathinfo($file, PATHINFO_FILENAME);
        $title = preg_replace('/[_-]+/', ' ', $title);
        $title = preg_replace('/\s+\d{8,}$/', '', $title);
        $relative = "assets/images/" . rawurlencode($folder) . "/" . rawurlencode(basename($file));
        $items[] = ["title" => $title, "image" => $relative, "category" => $category];
    }
    return $items;
}
$artworkGroups = [
    "digital" => scanArtwork("Digital Artworks", "Digital Artworks"),
    "game-art" => scanArtwork("Game Development Designing Works", "Game Development Design"),
    "creative" => scanArtwork("Multimedia Photoshop Works", "Multimedia Design"),
];

function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f7f4ef">
  <title><?= e($name) ?> | Portfolio</title>
  <link rel="stylesheet" href="portfolio.css?v=20261026a08">
</head>
<body>
<div class="site-shell">
  <header class="site-header">
    <a class="wordmark" href="#home" aria-label="<?= e($name) ?>, home"><?= e($name) ?><span class="wordmark-dot">.</span></a>
    <div class="header-actions">
      <nav class="main-nav" aria-label="Main navigation">
        <a href="#about">About</a>
        <details class="projects-menu"><summary>Skills <span aria-hidden="true">⌄</span></summary><div class="projects-dropdown"><<a href="#digital">Digital Artworks</a><a href="#game-art">Character Designing</a><a href="#creative">Multimedia Designing</a><a href="#systems">Software Developing</a></div></details>
        <a href="#contact">Contact</a>
      </nav>
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false" aria-label="Switch to dark mode" title="Switch to dark mode"><svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.2A8.3 8.3 0 0 1 8.8 3.8 8.5 8.5 0 1 0 20.2 15.2Z"/></svg><svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg></button>
    </div>
  </header>

  <main>
    <section class="hero" id="home">
      <div class="hero-copy"><p class="eyebrow hero-label" data-typewriter>Digital artist · Visual designer</p><h1><span class="hero-name-word hero-name-first">Rhainier</span> <span class="hero-name-word hero-name-last">Lim</span></h1><p class="hero-intro">I create expressive visuals, characters, and digital experiences, with programming as a tool to bring ideas to life.</p><a class="button button-primary" href="#digital">Explore the work <span aria-hidden="true">↓</span></a></div>
      <figure class="portrait-frame">
        <button class="portrait-toggle" type="button" data-portrait-toggle aria-pressed="false" aria-label="Show illustrated portrait">
          <span class="portrait-photo"><img src="assets/images/limrhainier-neutral.png" alt="Portrait cutout of <?= e($name) ?>" width="910" height="1134" fetchpriority="high"></span>
          <span class="portrait-artwork" aria-hidden="true"><img src="assets/images/limrhainierart.png" alt="" width="1530" height="2019" loading="lazy"></span>
        </button>
      </figure>
      <div class="hero-orbit orbit-one" aria-hidden="true"></div><div class="hero-orbit orbit-two" aria-hidden="true"></div>
    </section>

    <nav class="category-bar" aria-label="Browse portfolio categories"><a href="#digital">Digital Artworks<span>↘</span></a><a href="#creative">Multimedia Designing<span>↘</span></a><a href="#game-art">Character Designing<span>↘</span></a><a href="#systems">Software Developing<span>↘</span></a></nav>

    <section class="about section-block" id="about">
      <div class="section-heading"><p class="eyebrow">A little about me</p><h2>Digital art first.<br><span>Ideas in motion.</span></h2></div>
      <div><p><?= e($about) ?></p><ul class="skill-list" aria-label="Skills"><?php foreach ($skills as $skill): ?><li><?= e($skill) ?></li><?php endforeach; ?></ul></div>
    </section>

    <?php foreach (["digital" => "Digital Artworks", "creative" => "Multimedia Designing"] as $groupId => $groupTitle): $groupKey = $groupId === "creative" ? "creative" : "digital"; ?>
    <section class="creative-section section-block artwork-section" id="<?= e($groupId) ?>"><div class="section-heading"><div><p class="eyebrow">Scanned collection · <?= count($artworkGroups[$groupKey]) ?> works</p><h2><?= e($groupTitle) ?>.</h2></div></div><div class="gallery-carousel" data-gallery-carousel><div class="gallery-track"><?php foreach ($artworkGroups[$groupKey] as $art): ?><article class="art-card gallery-slide"><button type="button" class="screenshot-button" data-lightbox="<?= e($art["image"]) ?>" data-caption="<?= e($art["title"]) ?>"><img src="<?= e($art["image"]) ?>" alt="<?= e($art["title"]) ?>" loading="lazy" decoding="async"><span class="zoom-hint">View artwork <span aria-hidden="true">↗</span></span><span class="gallery-selected">Selected work</span></button><h3><?= e($art["title"]) ?></h3><p class="eyebrow"><?= e($art["category"]) ?></p></article><?php endforeach; ?></div><div class="gallery-controls"><button class="arrow-button gallery-prev" type="button" aria-label="Previous image">←</button><button class="arrow-button gallery-next" type="button" aria-label="Next image">→</button><span class="gallery-count">01 / <?= str_pad((string)count($artworkGroups[$groupKey]), 2, "0", STR_PAD_LEFT) ?></span></div></div></section>
    <?php endforeach; ?>

    <section class="character-section section-block artwork-section" id="game-art"><div class="section-heading"><div><p class="eyebrow">Character roster · <?= count($artworkGroups["game-art"]) ?> designs</p><h2>Character Designing.</h2><p class="section-intro">Browse the roster. Select a character to view the full artwork.</p></div></div><div class="character-carousel" data-character-carousel><div class="character-track"><?php foreach ($artworkGroups["game-art"] as $index => $character): ?><figure class="character-figure"><button type="button" class="character-button" data-lightbox="<?= e($character["image"]) ?>" data-caption="<?= e($character["title"]) ?>"><span class="character-glow" aria-hidden="true"></span><img src="<?= e($character["image"]) ?>" alt="<?= e($character["title"]) ?>" loading="lazy" decoding="async"><span class="character-open">View character ↗</span></button><figcaption><span class="character-number">CHARACTER · <?= str_pad((string)($index + 1), 2, "0", STR_PAD_LEFT) ?></span><span class="character-selected" aria-hidden="true">Selected</span><h3><?= e($character["title"]) ?></h3></figcaption></figure><?php endforeach; ?></div><div class="character-controls"><button class="arrow-button character-prev" type="button" aria-label="Previous characters">←</button><button class="arrow-button character-next" type="button" aria-label="Next characters">→</button><span class="character-count" aria-live="polite"></span></div></div></section>

    <section class="systems-section section-block" id="systems">
      <div class="section-heading systems-heading"><div><p class="eyebrow">Selected interactive studies</p><h2>Software Developing</h2><p class="section-intro">A small collection of systems where programming supports the design and interaction.</p></div></div>
      <div class="carousel" id="systems-carousel" aria-roledescription="carousel" aria-label="Featured systems">
        <div class="carousel-track" id="carousel-track">
          <?php foreach ($systems as $index => $system): ?>
          <article class="system-slide" data-slide="<?= $index ?>" aria-roledescription="slide" aria-label="<?= $index + 1 ?> of <?= count($systems) ?>">
            <div class="system-visual"><button type="button" class="screenshot-button" data-lightbox="<?= e($system["image"]) ?>" data-caption="<?= e($system["title"]) ?> — screenshot"><img src="<?= e($system["image"]) ?>" alt="<?= e($system["alt"]) ?>" loading="lazy" decoding="async"><span class="zoom-hint">View screenshot <span aria-hidden="true">↗</span></span></button><span class="visual-index">0<?= $index + 1 ?></span></div>
            <div class="system-content"><p class="eyebrow">Rhainier Lim · Project 0<?= $index + 1 ?> · <?= e($system["kind"]) ?></p><h3><?= e($system["title"]) ?></h3><p class="system-description"><?= e($system["description"]) ?></p>
              <div class="badge-list" aria-label="Technology stack"><?php foreach ($system["stack"] as $badge): ?><span><?= e($badge) ?></span><?php endforeach; ?></div>
              <h4>What it does</h4><ul class="feature-list"><?php foreach ($system["features"] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul>
              <div class="system-actions"><button class="button button-primary demo-toggle" type="button" aria-expanded="false" data-demo="<?= e($system["demo"]) ?>" data-title="<?= e($system["title"]) ?>">Try the live demo <span aria-hidden="true">↗</span></button><button class="button button-quiet source-toggle" type="button" aria-expanded="false">Explore source <span aria-hidden="true">⌘</span></button></div>
            </div>
            <div class="demo-panel" hidden><div class="panel-heading"><div><p class="eyebrow">Core feature · single-screen preview</p><h4><?= e($system["title"]) ?></h4><p><?= $system["id"] === "dental" ? "A focused patient booking preview with sample dentists and times. Submissions stay in this browser demo and are not sent to the clinic." : "Browser preview follows the project's source logic. Demo data stays in this browser session and does not connect to the desktop app's SQLite database." ?></p></div><button type="button" class="close-panel" aria-label="Close demo">×</button></div><iframe data-project="<?= e($system["id"]) ?>" title="Interactive <?= e($system["title"]) ?> demo" loading="lazy" referrerpolicy="no-referrer"></iframe></div>
            <div class="source-panel" hidden><div class="panel-heading"><div><p class="eyebrow">Source explorer</p><h4>Browse the project files</h4><p>Source is displayed as text; it does not execute in this viewer.</p></div><button type="button" class="close-panel" aria-label="Close source viewer">×</button></div><div class="source-browser"><div class="source-tree" role="group" aria-label="Source files"><?php foreach ($system["files"] as $fileIndex => $file): ?><button type="button" class="source-file<?= $fileIndex === 0 ? " selected" : "" ?>" aria-pressed="<?= $fileIndex === 0 ? "true" : "false" ?>" data-path="<?= e($file["path"]) ?>" data-lang="<?= e($file["lang"]) ?>"><?= e($file["label"]) ?></button><?php endforeach; ?></div><div class="code-wrap"><div class="code-toolbar"><span class="code-filename"><?= e($system["files"][0]["label"]) ?></span><button type="button" class="copy-code">Copy</button></div><pre><code class="code-content" tabindex="0">Select a source file to load it.</code></pre></div></div></div>
          </article>
          <?php endforeach; ?>
        </div>
        <div class="carousel-footer"><div class="carousel-nav"><button type="button" class="arrow-button" id="carousel-prev" aria-label="Previous project">←</button><button type="button" class="arrow-button" id="carousel-next" aria-label="Next project">→</button><span class="slide-count" id="slide-count" aria-live="polite">01 / 04</span></div></div>
      </div>
    </section>

    <section class="contact-section section-block" id="contact"><div class="section-heading"><p class="eyebrow">Have a project in mind?</p><h2>Let's make something<br><span>work beautifully.</span></h2><div class="contact-links" aria-label="Contact channels">
      <a class="contact-link contact-email" href="mailto:<?= e($email) ?>" aria-label="Email <?= e($email) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg><span><strong>Gmail</strong><small><?= e($email) ?></small></span><span class="contact-arrow" aria-hidden="true">↗</span></a>
      <a class="contact-link" href="https://www.instagram.com/ryuu_064/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle class="icon-fill" cx="17.5" cy="6.5" r="1"/></svg><span><strong>Instagram</strong><small>@ryuu_064</small></span><span class="contact-arrow" aria-hidden="true">↗</span></a>
      <a class="contact-link" href="https://www.facebook.com/rhainier.lim" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="icon-fill" d="M13.4 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.4v2H7.2V13H10v8z"/></svg><span><strong>Facebook</strong><small>Rhainier Lim</small></span><span class="contact-arrow" aria-hidden="true">↗</span></a>
    </div></div><div class="contact-form-wrap">
      <form method="post" action="https://api.web3forms.com/submit">
        <input type="hidden" name="access_key" value="<?= e(getenv("WEB3FORMS_ACCESS_KEY") ?: "") ?>">
        <input type="hidden" name="subject" value="New portfolio inquiry">
        <input type="checkbox" name="botcheck" class="contact-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
        <p class="form-status" role="status" id="contact-status" hidden></p>
        <label for="contact-name">Name</label><input id="contact-name" name="name" type="text" autocomplete="name" required>
        <label for="contact-email">Email</label><input id="contact-email" name="email" type="email" autocomplete="email" required>
        <label for="contact-message">Message</label><textarea id="contact-message" name="message" rows="4" minlength="10" required></textarea>
        <button class="button button-primary" type="submit">Send message <span aria-hidden="true">↗</span></button>
      </form>
    </div></section>
  </main>
  <footer><span>© <?= date("Y") ?> <?= e($name) ?></span><span>Made with PHP, HTML, CSS & JavaScript</span></footer>
</div>
<dialog class="lightbox" id="lightbox"><form method="dialog"><button class="lightbox-close" aria-label="Close image">×</button></form><img alt=""><p></p></dialog>
<a class="back-to-top" href="#home" aria-label="Back to top"><span aria-hidden="true">↑</span><span>Back to top</span></a>
<script src="portfolio.js?v=20261026a19" defer></script>
</body>
</html>
