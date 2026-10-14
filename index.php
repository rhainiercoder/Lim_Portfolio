<?php
// ============ EDIT YOUR DETAILS HERE ============
$name     = "Rhainier I. Lim";
$title    = "BSIT Student";
$tagline  = "I build fast, readable websites for small businesses and student projects.";
$about    = "I'm a self-taught developer who likes turning messy ideas into simple, working pages. "
          . "I enjoy PHP, clean HTML, CSS, and adding just enough JavaScript to make things feel alive.";
$email    = "rhainierlim07@gmail.com";
$skills   = ["PHP", "HTML", "CSS", "MySQL", "Git"];

$projects = [
    ["title" => "Bakery Ordering Site", "type" => "PHP",        "desc" => "Menu and order form that saves requests to a file.", "link" => "#"],
    ["title" => "Quiz Game",            "type" => "JavaScript", "desc" => "Timed multiple-choice quiz with a running score.",   "link" => "#"],
    ["title" => "Class Schedule Page",  "type" => "HTML",       "desc" => "Responsive timetable built with plain HTML.",       "link" => "#"],
    ["title" => "Guestbook",            "type" => "PHP",        "desc" => "Visitors leave notes, stored and shown by PHP.",    "link" => "#"],
    ["title" => "Budget Calculator",    "type" => "JavaScript", "desc" => "Adds up expenses live as you type.",                "link" => "#"],
];
// ================================================

// Contact form handling (plain PHP, no database)
$status = "";
$errors = [];
$old = ["name" => "", "email" => "", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["name"]    = trim($_POST["name"] ?? "");
    $old["email"]   = trim($_POST["email"] ?? "");
    $old["message"] = trim($_POST["message"] ?? "");

    if ($old["name"] === "")                                   $errors[] = "Enter your name.";
    if (!filter_var($old["email"], FILTER_VALIDATE_EMAIL))     $errors[] = "Enter a valid email address.";
    if (strlen($old["message"]) < 10)                          $errors[] = "Write a message of at least 10 characters.";

    if (!$errors) {
        $line = date("Y-m-d H:i") . " | " . str_replace(["\r", "\n", "|"], " ", $old["name"])
              . " | " . $old["email"] . " | " . str_replace(["\r", "\n"], " ", $old["message"]) . "\n";
        if (file_put_contents(__DIR__ . "/messages.txt", $line, FILE_APPEND | LOCK_EX) !== false) {
            $status = "Thanks, your message was sent.";
            $old = ["name" => "", "email" => "", "message" => ""];
        } else {
            $errors[] = "Could not save your message. Check that the folder is writable.";
        }
    }
}

// Helper to safely print text
function e($s) { return htmlspecialchars($s, ENT_QUOTES, "UTF-8"); }

$types = array_values(array_unique(array_column($projects, "type")));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($name) ?> | <?= e($title) ?></title>
<style>
  :root { --bg:#eef1f4; --ink:#1b2a3a; --muted:#566676; --card:#ffffff; --line:#c9d2db; --accent:#2f4bff; }
  body.dark { --bg:#111a24; --ink:#e6ecf2; --muted:#9fb0c0; --card:#1a2633; --line:#2e3d4d; --accent:#7f93ff; }
  * { box-sizing: border-box; }
  body { margin:0; background:var(--bg); color:var(--ink); font-family:system-ui,-apple-system,"Segoe UI",sans-serif; line-height:1.6; }
  a { color:var(--accent); }
  :focus-visible { outline:3px solid var(--accent); outline-offset:2px; }
  .wrap { max-width:960px; margin:0 auto; padding:0 20px; }
  header.top { display:flex; justify-content:space-between; align-items:center; padding:18px 0; border-bottom:1px solid var(--line); }
  nav a { margin-right:18px; text-decoration:none; color:var(--ink); font-weight:600; }
  nav a:hover { color:var(--accent); }
  button { font:inherit; cursor:pointer; border:1px solid var(--ink); background:transparent; color:var(--ink); padding:6px 14px; border-radius:6px; }
  button:hover { background:var(--ink); color:var(--bg); }
  .hero { padding:90px 0 70px; }
  .hero h1 { font-family:Georgia,"Times New Roman",serif; font-size:clamp(2.6rem,8vw,5.5rem); line-height:1.02; margin:0 0 18px; letter-spacing:-0.02em; }
  .hero p { max-width:52ch; font-size:1.2rem; color:var(--muted); margin:0 0 6px; }
  #typed { color:var(--accent); font-weight:700; }
  section { padding:50px 0; border-top:1px solid var(--line); }
  h2 { font-family:Georgia,serif; font-size:2rem; margin:0 0 20px; }
  .skills { display:flex; flex-wrap:wrap; gap:10px; padding:0; list-style:none; margin:20px 0 0; }
  .skills li { padding:4px 14px; border:1px solid var(--line); background:var(--card); border-radius:99px; }
  .filters { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; }
  .filters button.active { background:var(--accent); border-color:var(--accent); color:#fff; }
  .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:16px; }
  .project { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:18px; }
  .project h3 { margin:0 0 6px; }
  .project small { color:var(--muted); }
  form { display:grid; gap:12px; max-width:520px; }
  input, textarea { font:inherit; padding:10px; border:1px solid var(--line); border-radius:6px; background:var(--card); color:var(--ink); width:100%; }
  .ok { background:#d9f4e1; color:#14532d; padding:10px 14px; border-radius:6px; }
  .err { background:#fde2e2; color:#7f1d1d; padding:10px 14px; border-radius:6px; }
  .err ul { margin:0; padding-left:18px; }
  footer { padding:30px 0; color:var(--muted); border-top:1px solid var(--line); }
  @media (prefers-reduced-motion: reduce) { * { scroll-behavior:auto !important; } }
  html { scroll-behavior:smooth; }
</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <strong><?= e($name) ?></strong>
    <nav aria-label="Main">
      <a href="#about">About</a><a href="#projects">Projects</a><a href="#contact">Contact</a>
      <button id="themeBtn" type="button" aria-pressed="false">Dark mode</button>
    </nav>
  </header>

  <div class="hero">
    <h1><?= e($name) ?></h1>
    <p><?= e($tagline) ?></p>
    <p>Currently focused on: <span id="typed"></span></p>
  </div>

  <section id="about">
    <h2>About</h2>
    <p><?= e($about) ?></p>
    <ul class="skills">
      <?php foreach ($skills as $s): ?>
        <li><?= e($s) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section id="projects">
    <h2>Projects</h2>
    <div class="filters" role="group" aria-label="Filter projects">
      <button type="button" class="active" data-filter="all">All</button>
      <?php foreach ($types as $t): ?>
        <button type="button" data-filter="<?= e($t) ?>"><?= e($t) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="grid">
      <?php foreach ($projects as $p): ?>
        <article class="project" data-type="<?= e($p["type"]) ?>">
          <h3><?= e($p["title"]) ?></h3>
          <small><?= e($p["type"]) ?></small>
          <p><?= e($p["desc"]) ?></p>
          <a href="<?= e($p["link"]) ?>">View project</a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="contact">
    <h2>Contact</h2>
    <p>Prefer email? Write to <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>, or use the form.</p>

    <?php if ($status): ?><p class="ok" role="status"><?= e($status) ?></p><?php endif; ?>
    <?php if ($errors): ?>
      <div class="err" role="alert"><ul>
        <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
      </ul></div>
    <?php endif; ?>

    <form method="post" action="#contact">
      <label>Name <input type="text" name="name" value="<?= e($old["name"]) ?>" required></label>
      <label>Email <input type="email" name="email" value="<?= e($old["email"]) ?>" required></label>
      <label>Message <textarea name="message" rows="5" required><?= e($old["message"]) ?></textarea></label>
      <button type="submit">Send message</button>
    </form>
  </section>

  <footer>&copy; <?= date("Y") ?> <?= e($name) ?>. Built with PHP, HTML and JavaScript.</footer>
</div>

<script>
  // 1. Project filter
  var filterButtons = document.querySelectorAll(".filters button");
  var cards = document.querySelectorAll(".project");
  filterButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var f = btn.getAttribute("data-filter");
      filterButtons.forEach(function (b) { b.classList.remove("active"); });
      btn.classList.add("active");
      cards.forEach(function (c) {
        c.style.display = (f === "all" || c.getAttribute("data-type") === f) ? "" : "none";
      });
    });
  });

  // 2. Dark mode toggle (remembers choice)
  var themeBtn = document.getElementById("themeBtn");
  function setDark(on) {
    document.body.classList.toggle("dark", on);
    themeBtn.textContent = on ? "Light mode" : "Dark mode";
    themeBtn.setAttribute("aria-pressed", on);
    try { localStorage.setItem("dark", on ? "1" : "0"); } catch (e) {}
  }
  var saved = null;
  try { saved = localStorage.getItem("dark"); } catch (e) {}
  setDark(saved === "1");
  themeBtn.addEventListener("click", function () { setDark(!document.body.classList.contains("dark")); });

  // 3. Typing line in the hero (one moment of motion; static if reduced motion is on)
  var phrases = <?= json_encode($skills) ?>;
  var typed = document.getElementById("typed");
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    typed.textContent = phrases.join(", ");
  } else {
    var i = 0, j = 0, deleting = false;
    (function tick() {
      var word = phrases[i];
      typed.textContent = word.substring(0, j);
      if (!deleting && j < word.length) { j++; setTimeout(tick, 110); }
      else if (!deleting) { deleting = true; setTimeout(tick, 1200); }
      else if (j > 0) { j--; setTimeout(tick, 60); }
      else { deleting = false; i = (i + 1) % phrases.length; setTimeout(tick, 300); }
    })();
  }
</script>
</body>
</html>
