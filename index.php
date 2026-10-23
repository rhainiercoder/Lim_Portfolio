<?php
$name = "Rhainier I. Lim";
$title = "BSIT Student";
$tagline = "I build practical software and thoughtful digital experiences.";
$about = "I'm a self-taught developer who likes turning messy ideas into simple, working pages. I enjoy PHP, clean HTML, CSS, and adding just enough JavaScript to make things feel alive.";
$email = "rhainierlim07@gmail.com";
$skills = ["PHP", "HTML", "CSS", "JavaScript", "Java", "MySQL", "SQLite", "Git"];

$systems = [
    [
        "id" => "dental", "title" => "Dental Clinic Management System", "kind" => "Full-stack web application",
        "description" => "A patient-first clinic booking system with separate patient, dentist, and admin workflows, backed by Express and MySQL.",
        "stack" => ["HTML", "CSS", "JavaScript", "Node.js", "Express", "MySQL"],
        "features" => ["Patient registration and appointment booking", "Dentist schedules and appointment status", "Admin tools for patients, services, and schedules"],
        "image" => "assets/images/portfolio/Dental%20Clinic%20Management%20System.jpg", "alt" => "ZNS Dental Clinic homepage with appointment call to action",
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
        "demo" => "demos/recipe-manager.html",
        "files" => [["label" => "RecipeManagerSwing.java", "path" => "assets/Source%20Codes/RecipeManager/RecipeManagerSwing.java", "lang" => "java"]],
    ],
    [
        "id" => "hanoi", "title" => "Tower of Hanoi", "kind" => "Puzzle game · Java Swing",
        "description" => "A configurable Tower of Hanoi puzzle with legal-move checks, move tracking, hints, and an animated solver.",
        "stack" => ["Java", "Swing", "Stacks", "SQLite"],
        "features" => ["Choose disk and rod counts", "Move disks with rule validation", "Watch or step through an automatic solution"],
        "image" => "assets/images/Programming%20Works/Tower%20of%20Hanoi.png", "alt" => "Tower of Hanoi game with colored disks on three rods",
        "demo" => "demos/tower-of-hanoi.html",
        "files" => [["label" => "TowerOfHanoiSwing.java", "path" => "assets/Source%20Codes/TowerofHanoiStack/TowerOfHanoiSwing.java", "lang" => "java"]],
    ],
    [
        "id" => "trains", "title" => "Train Route Station Manager", "kind" => "Desktop application · Java Swing",
        "description" => "Manage rail lines, station order, train availability, and station-to-station moves in a visual desktop interface.",
        "stack" => ["Java", "Swing", "Linked lists", "SQLite"],
        "features" => ["Browse stations by rail line", "Update train availability", "Move between stations and manage route data"],
        "image" => "assets/images/Programming%20Works/Train%20Route%20Station%20Manager.png", "alt" => "Train Route Station Manager showing rail line stations and availability",
        "demo" => "demos/train-station-manager.html",
        "files" => [["label" => "TrainRouteStationSwing.java", "path" => "assets/Source%20Codes/TrainRouteStationLinkedList/TrainRouteStationSwing.java", "lang" => "java"]],
    ],
];

$artworks = [
    ["title" => "Flyer Mockup", "image" => "assets/images/portfolio/Flyer Mockup.jpg", "full" => "assets/images/Multimedia%20Works/Flyer%20Mockup.png", "alt" => "Promotional flyer mockup"],
    ["title" => "PETA Flyer", "image" => "assets/images/portfolio/Flyer PETA.jpg", "full" => "assets/images/Multimedia%20Works/Flyer%20PETA.png", "alt" => "Portrait-format PETA flyer artwork"],
    ["title" => "Harbinger Poster", "image" => "assets/images/portfolio/Harbinger Poster.jpg", "full" => "assets/images/Multimedia%20Works/Harbinger%20Poster.png", "alt" => "Harbinger poster design"],
    ["title" => "Midterm Artwork", "image" => "assets/images/portfolio/Midtern Part 2.jpg", "full" => "assets/images/Multimedia%20Works/Midtern%20Part%202.png", "alt" => "Midterm multimedia artwork"],
];

$status = "";
$errors = [];
$old = ["name" => "", "email" => "", "message" => ""];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["name"] = trim($_POST["name"] ?? "");
    $old["email"] = trim($_POST["email"] ?? "");
    $old["message"] = trim($_POST["message"] ?? "");
    if ($old["name"] === "") $errors[] = "Enter your name.";
    if (!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address.";
    if (strlen($old["message"]) < 10) $errors[] = "Write a message of at least 10 characters.";
    if (!$errors) {
        $line = date("Y-m-d H:i") . " | " . str_replace(["\r", "\n", "|"], " ", $old["name"]) . " | " . $old["email"] . " | " . str_replace(["\r", "\n"], " ", $old["message"]) . "\n";
        if (file_put_contents(__DIR__ . "/messages.txt", $line, FILE_APPEND | LOCK_EX) !== false) {
            $status = "Thanks, your message was sent.";
            $old = ["name" => "", "email" => "", "message" => ""];
        } else $errors[] = "Could not save your message. Check that the folder is writable.";
    }
}
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f7f4ef">
  <title><?= e($name) ?> | Portfolio</title>
  <link rel="stylesheet" href="portfolio.css">
</head>
<body>
<div class="site-shell">
  <header class="site-header">
    <a class="wordmark" href="#home" aria-label="<?= e($name) ?>, home"><?= e($name) ?><span class="wordmark-dot">.</span></a>
    <nav aria-label="Main navigation"><a href="#about">About</a><a href="#systems">Systems</a><a href="#creative">Creative</a><a href="#contact">Contact</a><button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Dark mode</button></nav>
  </header>

  <main>
    <section class="hero" id="home">
      <div class="hero-copy"><p class="eyebrow">Portfolio · <?= e($title) ?></p><h1>Ideas into <span>useful experiences.</span></h1><p class="hero-intro"><?= e($tagline) ?></p><a class="button button-primary" href="#systems">Explore the systems <span aria-hidden="true">↓</span></a></div>
      <figure class="portrait-frame"><img src="assets/images/lim.jpg" alt="Portrait of <?= e($name) ?>" width="273" height="350" fetchpriority="high"></figure>
      <div class="hero-orbit orbit-one" aria-hidden="true"></div><div class="hero-orbit orbit-two" aria-hidden="true"></div>
    </section>

    <section class="about section-block" id="about">
      <div class="section-heading"><p class="eyebrow">A little about me</p><h2>Curious by nature.<br><span>Practical by design.</span></h2></div>
      <div><p><?= e($about) ?></p><ul class="skill-list" aria-label="Skills"><?php foreach ($skills as $skill): ?><li><?= e($skill) ?></li><?php endforeach; ?></ul></div>
    </section>

    <section class="systems-section section-block" id="systems">
      <div class="section-heading systems-heading"><div><p class="eyebrow">Selected systems</p><h2>Built to be explored.</h2><p class="section-intro">Swipe through the projects, open a live browser prototype, or browse the source files.</p></div><div class="carousel-tools"><button class="icon-button" id="autoplay-toggle" type="button" aria-pressed="false" aria-label="Turn autoplay on">Autoplay <span aria-hidden="true">▶</span></button><span class="slide-count" id="slide-count" aria-live="polite">01 / 04</span></div></div>
      <div class="carousel" id="systems-carousel" aria-roledescription="carousel" aria-label="Featured systems">
        <div class="carousel-track" id="carousel-track">
          <?php foreach ($systems as $index => $system): ?>
          <article class="system-slide" data-slide="<?= $index ?>" aria-roledescription="slide" aria-label="<?= $index + 1 ?> of <?= count($systems) ?>">
            <div class="system-visual"><button type="button" class="screenshot-button" data-lightbox="<?= e($system["image"]) ?>" data-caption="<?= e($system["title"]) ?> — screenshot"><img src="<?= e($system["image"]) ?>" alt="<?= e($system["alt"]) ?>" loading="lazy" decoding="async"><span class="zoom-hint">View screenshot <span aria-hidden="true">↗</span></span></button><span class="visual-index">0<?= $index + 1 ?></span></div>
            <div class="system-content"><p class="eyebrow"><?= e($system["kind"]) ?></p><h3><?= e($system["title"]) ?></h3><p class="system-description"><?= e($system["description"]) ?></p>
              <div class="badge-list" aria-label="Technology stack"><?php foreach ($system["stack"] as $badge): ?><span><?= e($badge) ?></span><?php endforeach; ?></div>
              <h4>What it does</h4><ul class="feature-list"><?php foreach ($system["features"] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul>
              <div class="system-actions"><button class="button button-primary demo-toggle" type="button" aria-expanded="false" data-demo="<?= e($system["demo"]) ?>" data-title="<?= e($system["title"]) ?>">Try the live demo <span aria-hidden="true">↗</span></button><button class="button button-quiet source-toggle" type="button" aria-expanded="false">Explore source <span aria-hidden="true">⌘</span></button></div>
            </div>
            <div class="demo-panel" hidden><div class="panel-heading"><div><p class="eyebrow">Interactive preview</p><h4><?= e($system["title"]) ?></h4><p>This is a browser prototype for portfolio exploration. It does not connect to the project's database.</p></div><button type="button" class="close-panel" aria-label="Close demo">×</button></div><iframe title="Interactive <?= e($system["title"]) ?> demo" loading="lazy" sandbox="allow-scripts" referrerpolicy="no-referrer"></iframe></div>
            <div class="source-panel" hidden><div class="panel-heading"><div><p class="eyebrow">Source explorer</p><h4>Browse the project files</h4><p>Source is displayed as text; it does not execute in this viewer.</p></div><button type="button" class="close-panel" aria-label="Close source viewer">×</button></div><div class="source-browser"><div class="source-tree" role="group" aria-label="Source files"><?php foreach ($system["files"] as $fileIndex => $file): ?><button type="button" class="source-file<?= $fileIndex === 0 ? " selected" : "" ?>" aria-pressed="<?= $fileIndex === 0 ? "true" : "false" ?>" data-path="<?= e($file["path"]) ?>" data-lang="<?= e($file["lang"]) ?>"><?= e($file["label"]) ?></button><?php endforeach; ?></div><div class="code-wrap"><div class="code-toolbar"><span class="code-filename"><?= e($system["files"][0]["label"]) ?></span><button type="button" class="copy-code">Copy</button></div><pre><code class="code-content" tabindex="0">Select a source file to load it.</code></pre></div></div></div>
          </article>
          <?php endforeach; ?>
        </div>
        <div class="carousel-footer"><div class="carousel-nav"><button type="button" class="arrow-button" id="carousel-prev" aria-label="Previous project">←</button><div class="carousel-dots" id="carousel-dots" role="group" aria-label="Choose a project"><?php foreach ($systems as $index => $system): ?><button type="button" class="carousel-dot<?= $index === 0 ? " active" : "" ?>" data-go="<?= $index ?>" aria-label="Show <?= e($system["title"]) ?>" aria-current="<?= $index === 0 ? "true" : "false" ?>"></button><?php endforeach; ?></div><button type="button" class="arrow-button" id="carousel-next" aria-label="Next project">→</button></div><p class="swipe-note">Drag or swipe to explore</p></div>
      </div>
    </section>

    <section class="creative-section section-block" id="creative"><div class="section-heading"><p class="eyebrow">Visual experiments</p><h2>Beyond the code.</h2></div><div class="art-grid"><?php foreach ($artworks as $art): ?><article class="art-card"><button type="button" class="screenshot-button" data-lightbox="<?= e($art["full"]) ?>" data-caption="<?= e($art["title"]) ?>"><img src="<?= e($art["image"]) ?>" alt="<?= e($art["alt"]) ?>" loading="lazy" decoding="async"><span class="zoom-hint">View artwork <span aria-hidden="true">↗</span></span></button><h3><?= e($art["title"]) ?></h3></article><?php endforeach; ?></div></section>

    <section class="contact-section section-block" id="contact"><div class="section-heading"><p class="eyebrow">Have a project in mind?</p><h2>Let's make something<br><span>work beautifully.</span></h2><a href="mailto:<?= e($email) ?>" class="email-link"><?= e($email) ?> ↗</a></div><div class="contact-form-wrap">
      <?php if ($status): ?><p class="form-status" role="status"><?= e($status) ?></p><?php endif; ?>
      <?php if ($errors): ?><div class="form-errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="post" action="#contact"><label for="contact-name">Name</label><input id="contact-name" name="name" type="text" value="<?= e($old["name"]) ?>" autocomplete="name" required><label for="contact-email">Email</label><input id="contact-email" name="email" type="email" value="<?= e($old["email"]) ?>" autocomplete="email" required><label for="contact-message">Message</label><textarea id="contact-message" name="message" rows="4" required><?= e($old["message"]) ?></textarea><button class="button button-primary" type="submit">Send a message <span aria-hidden="true">↗</span></button></form>
    </div></section>
  </main>
  <footer><span>© <?= date("Y") ?> <?= e($name) ?></span><a href="#home">Back to top ↑</a><span>Made with PHP, HTML, CSS & JavaScript</span></footer>
</div>
<dialog class="lightbox" id="lightbox"><form method="dialog"><button class="lightbox-close" aria-label="Close image">×</button></form><img alt=""><p></p></dialog>
<script src="portfolio.js" defer></script>
</body>
</html>
