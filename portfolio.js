(function () {
  "use strict";

  const root = document.body;
  const carousel = document.getElementById("systems-carousel");
  const track = document.getElementById("carousel-track");
  const slides = Array.from(document.querySelectorAll(".system-slide"));
  const dots = Array.from(document.querySelectorAll(".carousel-dot"));
  const count = document.getElementById("slide-count");
  const autoplayButton = document.getElementById("autoplay-toggle");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  let current = 0;
  let timer = null;
  let pointerStart = null;
  let pointerMoved = false;

  function closePanels(slide) {
    slide.querySelectorAll(".demo-panel, .source-panel").forEach(function (panel) {
      panel.hidden = true;
    });
    slide.querySelectorAll(".demo-toggle, .source-toggle").forEach(function (button) {
      button.setAttribute("aria-expanded", "false");
    });
  }

  function showSlide(index) {
    current = (index + slides.length) % slides.length;
    track.style.transform = "translateX(-" + (current * 100) + "%)";
    dots.forEach(function (dot, i) {
      const active = i === current;
      dot.classList.toggle("active", active);
      dot.setAttribute("aria-current", active ? "true" : "false");
    });
    slides.forEach(function (slide, i) {
      slide.setAttribute("aria-hidden", i === current ? "false" : "true");
      slide.inert = i !== current;
      if (i !== current) closePanels(slide);
    });
    count.textContent = String(current + 1).padStart(2, "0") + " / " + String(slides.length).padStart(2, "0");
  }

  document.getElementById("carousel-prev").addEventListener("click", function () { showSlide(current - 1); });
  document.getElementById("carousel-next").addEventListener("click", function () { showSlide(current + 1); });
  dots.forEach(function (dot) {
    dot.addEventListener("click", function () { showSlide(Number(dot.dataset.go)); });
  });
  carousel.addEventListener("keydown", function (event) {
    if (event.key === "ArrowLeft") showSlide(current - 1);
    if (event.key === "ArrowRight") showSlide(current + 1);
  });
  carousel.addEventListener("pointerdown", function (event) {
    if (event.target.closest("button, iframe, input, textarea, a")) return;
    pointerStart = { x: event.clientX, y: event.clientY };
    pointerMoved = false;
  });
  carousel.addEventListener("pointerup", function (event) {
    if (!pointerStart) return;
    const dx = event.clientX - pointerStart.x;
    const dy = event.clientY - pointerStart.y;
    if (Math.abs(dx) > 55 && Math.abs(dx) > Math.abs(dy) * 1.2) {
      pointerMoved = true;
      showSlide(current + (dx < 0 ? 1 : -1));
    }
    pointerStart = null;
  });
  carousel.addEventListener("pointercancel", function () { pointerStart = null; });

  function stopAutoplay() {
    if (timer) window.clearInterval(timer);
    timer = null;
    autoplayButton.setAttribute("aria-pressed", "false");
    autoplayButton.setAttribute("aria-label", "Turn autoplay on");
    autoplayButton.innerHTML = 'Autoplay <span aria-hidden="true">▶</span>';
  }
  function startAutoplay() {
    if (reducedMotion.matches) return;
    stopAutoplay();
    autoplayButton.setAttribute("aria-pressed", "true");
    autoplayButton.setAttribute("aria-label", "Turn autoplay off");
    autoplayButton.innerHTML = 'Autoplay <span aria-hidden="true">Ⅱ</span>';
    timer = window.setInterval(function () { showSlide(current + 1); }, 6500);
  }
  autoplayButton.addEventListener("click", function () {
    if (timer) stopAutoplay(); else startAutoplay();
  });
  carousel.addEventListener("mouseenter", function () { if (timer) window.clearInterval(timer); });
  carousel.addEventListener("mouseleave", function () { if (autoplayButton.getAttribute("aria-pressed") === "true" && !reducedMotion.matches) timer = window.setInterval(function () { showSlide(current + 1); }, 6500); });
  carousel.addEventListener("focusin", function () { if (timer) window.clearInterval(timer); });
  carousel.addEventListener("focusout", function (event) {
    if (!carousel.contains(event.relatedTarget) && autoplayButton.getAttribute("aria-pressed") === "true" && !reducedMotion.matches) timer = window.setInterval(function () { showSlide(current + 1); }, 6500);
  });
  reducedMotion.addEventListener("change", function (event) { if (event.matches) stopAutoplay(); });
  showSlide(0);

  document.querySelectorAll(".demo-toggle").forEach(function (button) {
    button.addEventListener("click", function () {
      const slide = button.closest(".system-slide");
      const panel = slide.querySelector(".demo-panel");
      const opening = panel.hidden;
      closePanels(slide);
      if (opening) {
        panel.hidden = false;
        button.setAttribute("aria-expanded", "true");
        const frame = panel.querySelector("iframe");
        if (!frame.getAttribute("src")) frame.src = button.dataset.demo;
        panel.scrollIntoView({ behavior: reducedMotion.matches ? "auto" : "smooth", block: "nearest" });
      }
    });
  });
  document.querySelectorAll(".source-toggle").forEach(function (button) {
    button.addEventListener("click", function () {
      const slide = button.closest(".system-slide");
      const panel = slide.querySelector(".source-panel");
      const opening = panel.hidden;
      closePanels(slide);
      if (opening) {
        panel.hidden = false;
        button.setAttribute("aria-expanded", "true");
        const first = panel.querySelector(".source-file");
        if (first) loadSource(panel, first);
        panel.scrollIntoView({ behavior: reducedMotion.matches ? "auto" : "smooth", block: "nearest" });
      }
    });
  });
  document.querySelectorAll(".close-panel").forEach(function (button) {
    button.addEventListener("click", function () {
      const panel = button.closest(".demo-panel, .source-panel");
      panel.hidden = true;
      const trigger = panel.parentElement.querySelector(panel.classList.contains("demo-panel") ? ".demo-toggle" : ".source-toggle");
      if (trigger) { trigger.setAttribute("aria-expanded", "false"); trigger.focus(); }
    });
  });

  function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function (char) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[char];
    });
  }
  function renderHighlighted(text, lang) {
    const escaped = escapeHtml(text);
    const patterns = lang === "sql"
      ? /(<!--[\s\S]*?-->|--[^\n]*|\/\*[\s\S]*?\*\/|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|\b(?:SELECT|FROM|WHERE|CREATE|TABLE|PRIMARY|KEY|NOT|NULL|INSERT|UPDATE|DELETE|JOIN|ORDER|BY|INDEX|INT|VARCHAR|AUTO_INCREMENT|REFERENCES|FOREIGN|CONSTRAINT|AND|OR|AS|ON|INTO|VALUES)\b|\b\d+\b)/gi
      : /(\/\/[^\n]*|\/\*[\s\S]*?\*\/|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|`(?:\\.|[^`\\])*`|\b(?:public|private|protected|static|final|class|interface|extends|implements|new|return|if|else|for|while|try|catch|throw|const|let|var|function|async|await|import|export|from|require|module|this|super|true|false|null|undefined|void|int|long|boolean|String|return|switch|case|break|continue|do|instanceof|enum|package)\b|\b\d+\b)/g;
    return escaped.replace(patterns, function (token) {
      let kind = "tok-keyword";
      if (/^(\/\/|\/\*|--|&lt;!--)/.test(token)) kind = "tok-comment";
      else if (/^("|'|`)/.test(token)) kind = "tok-string";
      else if (/^\d/.test(token)) kind = "tok-number";
      return '<span class="' + kind + '">' + token + "</span>";
    });
  }
  async function loadSource(panel, button) {
    panel.querySelectorAll(".source-file").forEach(function (item) {
      const active = item === button;
      item.classList.toggle("selected", active);
      item.setAttribute("aria-pressed", active ? "true" : "false");
    });
    const code = panel.querySelector(".code-content");
    const filename = panel.querySelector(".code-filename");
    const copyButton = panel.querySelector(".copy-code");
    filename.textContent = button.textContent;
    code.textContent = "Loading source…";
    code.dataset.raw = "";
    copyButton.disabled = true;
    try {
      const response = await fetch(button.dataset.path, { credentials: "same-origin", headers: { "Accept": "text/plain" } });
      if (!response.ok) throw new Error("HTTP " + response.status);
      const source = await response.text();
      code.dataset.raw = source;
      code.innerHTML = renderHighlighted(source, button.dataset.lang);
      copyButton.disabled = false;
    } catch (error) {
      code.textContent = "Could not load this source file (" + error.message + "). Open the project file from the assets folder instead.";
    }
  }
  document.querySelectorAll(".source-file").forEach(function (button) {
    button.addEventListener("click", function () { loadSource(button.closest(".source-panel"), button); });
  });
  document.querySelectorAll(".copy-code").forEach(function (button) {
    button.addEventListener("click", async function () {
      const code = button.closest(".source-panel").querySelector(".code-content");
      try {
        await navigator.clipboard.writeText(code.dataset.raw || code.textContent);
        button.textContent = "Copied";
        window.setTimeout(function () { button.textContent = "Copy"; }, 1400);
      } catch (_) { button.textContent = "Select code to copy"; }
    });
  });

  const lightbox = document.getElementById("lightbox");
  const lightboxImage = lightbox.querySelector("img");
  const lightboxCaption = lightbox.querySelector("p");
  document.querySelectorAll("[data-lightbox]").forEach(function (button) {
    button.addEventListener("click", function () {
      lightboxImage.src = button.dataset.lightbox;
      lightboxImage.alt = button.dataset.caption;
      lightboxCaption.textContent = button.dataset.caption;
      lightbox.showModal();
    });
  });
  lightbox.addEventListener("click", function (event) {
    if (event.target === lightbox) lightbox.close();
  });

  const themeButton = document.getElementById("theme-toggle");
  function setTheme(dark) {
    root.classList.toggle("dark", dark);
    themeButton.textContent = dark ? "Light mode" : "Dark mode";
    themeButton.setAttribute("aria-pressed", dark ? "true" : "false");
    try { localStorage.setItem("lim-portfolio-theme", dark ? "dark" : "light"); } catch (_) { /* storage can be disabled */ }
  }
  let savedTheme = "light";
  try { savedTheme = localStorage.getItem("lim-portfolio-theme") || "light"; } catch (_) { /* storage can be disabled */ }
  setTheme(savedTheme === "dark");
  themeButton.addEventListener("click", function () { setTheme(!root.classList.contains("dark")); });
}());
