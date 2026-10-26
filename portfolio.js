(function () {
  "use strict";

  const root = document.body;
  const carousel = document.getElementById("systems-carousel");
  const track = document.getElementById("carousel-track");
  const slides = Array.from(document.querySelectorAll(".system-slide"));
  const count = document.getElementById("slide-count");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  let current = 0;
  let pointerStart = null;

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
    slides.forEach(function (slide, i) {
      slide.setAttribute("aria-hidden", i === current ? "false" : "true");
      slide.inert = i !== current;
      if (i !== current) closePanels(slide);
    });
    count.textContent = String(current + 1).padStart(2, "0") + " / " + String(slides.length).padStart(2, "0");
    window.dispatchEvent(new CustomEvent("portfolio:project-change", { detail: { scope: carousel } }));
  }

  document.getElementById("carousel-prev").addEventListener("click", function () { showSlide(current - 1); });
  document.getElementById("carousel-next").addEventListener("click", function () { showSlide(current + 1); });
  carousel.addEventListener("keydown", function (event) {
    if (event.key === "ArrowLeft") showSlide(current - 1);
    if (event.key === "ArrowRight") showSlide(current + 1);
  });
  carousel.addEventListener("pointerdown", function (event) {
    if (event.target.closest("button, iframe, input, textarea, a")) return;
    pointerStart = { x: event.clientX, y: event.clientY };
  });
  carousel.addEventListener("pointerup", function (event) {
    if (!pointerStart) return;
    const dx = event.clientX - pointerStart.x;
    const dy = event.clientY - pointerStart.y;
    if (Math.abs(dx) > 55 && Math.abs(dx) > Math.abs(dy) * 1.2) {
      showSlide(current + (dx < 0 ? 1 : -1));
    }
    pointerStart = null;
  });
  carousel.addEventListener("pointercancel", function () { pointerStart = null; });

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
  const portraitToggle = document.querySelector("[data-portrait-toggle]");
  if (portraitToggle) {
    portraitToggle.addEventListener("click", function () {
      const showingArtwork = portraitToggle.getAttribute("aria-pressed") !== "true";
      portraitToggle.setAttribute("aria-pressed", showingArtwork ? "true" : "false");
      portraitToggle.setAttribute("aria-label", showingArtwork ? "Show portrait photo" : "Show illustrated portrait");
    });
  }
  document.querySelectorAll("[data-lightbox]").forEach(function (button) {
    button.addEventListener("click", function () {
      const selectableSlide = button.closest(".character-figure, .gallery-slide");
      if (selectableSlide && !selectableSlide.classList.contains("is-active")) {
        selectableSlide.dispatchEvent(new CustomEvent("portfolio:select", { bubbles: true }));
        return;
      }
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
    if (themeButton) {
      themeButton.setAttribute("aria-pressed", dark ? "true" : "false");
      themeButton.setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
      themeButton.setAttribute("title", dark ? "Switch to light mode" : "Switch to dark mode");
    }
    try { localStorage.setItem("lim-portfolio-theme", dark ? "dark" : "light"); } catch (_) { /* storage can be disabled */ }
  }
  let savedTheme = "light";
  try { savedTheme = localStorage.getItem("lim-portfolio-theme") || "light"; } catch (_) { /* storage can be disabled */ }
  setTheme(savedTheme === "dark");
  if (themeButton) themeButton.addEventListener("click", function () { setTheme(!root.classList.contains("dark")); });

  const contactForm = document.querySelector("[data-gmail-compose]");
  if (contactForm) {
    contactForm.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!contactForm.reportValidity()) return;
      const formData = new FormData(contactForm);
      const senderName = String(formData.get("name") || "").trim();
      const senderEmail = String(formData.get("email") || "").trim();
      const message = String(formData.get("message") || "").trim();
      const subject = "Portfolio inquiry from " + senderName;
      const body = "Name: " + senderName + "\nReply email: " + senderEmail + "\n\n" + message;
      const gmailUrl = new URL("https://mail.google.com/mail/");
      gmailUrl.search = new URLSearchParams({ view: "cm", fs: "1", to: contactForm.dataset.recipient, su: subject, body: body }).toString();
      window.location.assign(gmailUrl.toString());
    });
  }

  document.querySelectorAll("[data-gallery-carousel]").forEach(function (gallery) {
    const track = gallery.querySelector(".gallery-track");
    const slides = Array.from(gallery.querySelectorAll(".gallery-slide"));
    const count = gallery.querySelector(".gallery-count");
    let active = Math.floor(slides.length / 2), startX = null, suppressClick = false;
    function layout() {
      const width = gallery.clientWidth;
      const slideWidth = width * (window.matchMedia("(max-width: 700px)").matches ? 0.86 : 0.68);
      track.style.paddingInline = Math.max(0, (width - slideWidth) / 2) + "px";
      slides.forEach(function (slide) { slide.style.flexBasis = slideWidth + "px"; });
      show(active);
    }
    function show(index) {
      active = (index + slides.length) % slides.length;
      const width = gallery.clientWidth;
      const slideWidth = parseFloat(slides[active].style.flexBasis) || slides[active].getBoundingClientRect().width;
      const offset = active * slideWidth;
      track.style.transform = "translateX(-" + Math.max(0, offset) + "px)";
      slides.forEach(function (slide, i) {
        const selected = i === active;
        slide.classList.toggle("is-active", selected);
        slide.setAttribute("aria-current", selected ? "true" : "false");
      });
      if (count) count.textContent = String(active + 1).padStart(2, "0") + " / " + String(slides.length).padStart(2, "0");
      window.dispatchEvent(new CustomEvent("portfolio:project-change", { detail: { scope: gallery } }));
    }
    gallery.addEventListener("portfolio:select", function (event) {
      const slide = event.target.closest(".gallery-slide");
      if (slide) show(slides.indexOf(slide));
    });
    gallery.querySelector(".gallery-prev").addEventListener("click", function () { show(active - 1); });
    gallery.querySelector(".gallery-next").addEventListener("click", function () { show(active + 1); });
    gallery.addEventListener("pointerdown", function (event) { if (event.target.closest(".gallery-prev, .gallery-next")) return; startX = { x: event.clientX, y: event.clientY }; });
    gallery.addEventListener("pointerup", function (event) {
      if (!startX) return;
      const dx = event.clientX - startX.x, dy = event.clientY - startX.y;
      if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.2) { show(active + (dx < 0 ? 1 : -1)); suppressClick = true; window.setTimeout(function () { suppressClick = false; }, 400); }
      startX = null;
    });
    gallery.addEventListener("pointercancel", function () { startX = null; });
    gallery.addEventListener("click", function (event) {
      if (!suppressClick) return;
      event.preventDefault(); event.stopImmediatePropagation(); suppressClick = false;
    }, true);
    window.addEventListener("resize", layout, { passive: true });
    layout();
  });

  document.querySelectorAll("[data-character-carousel]").forEach(function (carousel) {
    const track = carousel.querySelector(".character-track");
    const slides = Array.from(carousel.querySelectorAll(".character-figure"));
    const count = carousel.querySelector(".character-count");
    let active = Math.floor(slides.length / 2), step = 0, startX = null, suppressClick = false;
    function layout() {
      const width = carousel.clientWidth;
      const slideWidth = width * 0.34;
      track.style.paddingInline = Math.max(0, (width - slideWidth) / 2) + "px";
      slides.forEach(function (slide) { slide.style.flexBasis = slideWidth + "px"; });
      step = slides[1] ? slides[1].offsetLeft - slides[0].offsetLeft : slideWidth;
      show(active);
    }
    function show(index) {
      active = (index + slides.length) % slides.length;
      const width = carousel.clientWidth;
      const slideWidth = parseFloat(slides[active].style.flexBasis) || slides[active].getBoundingClientRect().width;
      const offset = active * slideWidth;
      track.style.transform = "translateX(-" + Math.max(0, offset) + "px)";
      slides.forEach(function (slide, i) {
        const selected = i === active;
        slide.classList.toggle("is-active", selected);
        slide.setAttribute("aria-current", selected ? "true" : "false");
      });
      count.textContent = String(active + 1).padStart(2, "0") + " / " + String(slides.length).padStart(2, "0");
      window.dispatchEvent(new CustomEvent("portfolio:project-change", { detail: { scope: carousel } }));
    }
    carousel.addEventListener("portfolio:select", function (event) {
      const slide = event.target.closest(".character-figure");
      if (slide) show(slides.indexOf(slide));
    });
    carousel.querySelector(".character-prev").addEventListener("click", function () { show(active - 1); });
    carousel.querySelector(".character-next").addEventListener("click", function () { show(active + 1); });
    carousel.addEventListener("pointerdown", function (event) { if (event.target.closest(".character-prev, .character-next")) return; startX = { x: event.clientX, y: event.clientY }; });
    carousel.addEventListener("pointerup", function (event) {
      if (!startX) return;
      const dx = event.clientX - startX.x, dy = event.clientY - startX.y;
      if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.2) { show(active + (dx < 0 ? 1 : -1)); suppressClick = true; window.setTimeout(function () { suppressClick = false; }, 400); }
      startX = null;
    });
    carousel.addEventListener("pointercancel", function () { startX = null; });
    carousel.addEventListener("click", function (event) {
      if (!suppressClick) return;
      event.preventDefault(); event.stopImmediatePropagation(); suppressClick = false;
    }, true);
    window.addEventListener("resize", layout, { passive: true });
    layout();
  });

  // Progressive reveal uses IntersectionObserver, so scrolling itself stays free of layout work.
  if ("IntersectionObserver" in window && !reducedMotion.matches) {
    const titleItems = Array.from(document.querySelectorAll(".section-heading h2"));
    titleItems.forEach(function (heading) {
      const walker = document.createTreeWalker(heading, NodeFilter.SHOW_TEXT);
      const textNodes = [];
      while (walker.nextNode()) textNodes.push(walker.currentNode);
      let wordIndex = 0;
      textNodes.forEach(function (node) {
        const parts = node.nodeValue.match(/\s+|[^\s]+/g) || [];
        if (!parts.some(function (part) { return /\S/.test(part); })) return;
        const fragment = document.createDocumentFragment();
        parts.forEach(function (part) {
          if (/^\s+$/.test(part)) fragment.append(document.createTextNode(part));
          else {
            const word = document.createElement("span");
            word.className = "motion-word";
            word.style.setProperty("--word-delay", Math.min(wordIndex++, 8) * 42 + "ms");
            word.textContent = part;
            fragment.append(word);
          }
        });
        node.parentNode.replaceChild(fragment, node);
      });
      heading.dataset.wordReveal = "";
    });
    const revealItems = Array.from(document.querySelectorAll(
      ".hero-copy, .portrait-frame, .about .section-heading, .about > div:nth-child(2), .systems-heading, .system-visual, .system-content, .artwork-section .section-heading, .artwork-section .gallery-carousel, .gallery-slide .screenshot-button, .gallery-slide h3, .character-carousel, .character-button, .character-figure figcaption, .contact-section .section-heading, .contact-form-wrap, .section-heading h2[data-word-reveal]"
    ));
    const typeLabel = document.querySelector("[data-typewriter]");
    if (typeLabel) typeLabel.style.setProperty("--type-width", Math.min(typeLabel.scrollWidth, typeLabel.parentElement.clientWidth) + "px");
    revealItems.forEach(function (item, index) {
      item.dataset.reveal = "stagger";
      item.style.setProperty("--reveal-delay", Math.min(index % 4, 3) * 70 + "ms");
    });
    document.body.classList.add("motion-ready");
    const revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        entry.target.classList.toggle("is-visible", entry.isIntersecting);
        if (entry.target.classList.contains("hero-copy") && typeLabel) {
          if (entry.isIntersecting) {
            typeLabel.classList.remove("is-typing");
            void typeLabel.offsetWidth;
            typeLabel.classList.add("is-typing");
          } else typeLabel.classList.remove("is-typing");
        }
      });
    }, { rootMargin: "0px 0px -12% 0px", threshold: 0.12 });
    revealItems.forEach(function (item) { revealObserver.observe(item); });

    window.addEventListener("portfolio:project-change", function (event) {
      const scope = event.detail && event.detail.scope;
      if (!scope) return;
      const project = scope.id === "systems-carousel"
        ? scope.querySelector(".system-slide[aria-hidden='false']")
        : scope.querySelector(".gallery-slide.is-active, .character-figure.is-active");
      if (!project) return;
      const animatedParts = Array.from(project.querySelectorAll("[data-reveal]"));
      animatedParts.forEach(function (part) { part.classList.remove("is-visible"); });
      requestAnimationFrame(function () {
        animatedParts.forEach(function (part) {
          const bounds = part.getBoundingClientRect();
          const inView = bounds.bottom > 0 && bounds.top < window.innerHeight && bounds.right > 0 && bounds.left < window.innerWidth;
          if (inView) part.classList.add("is-visible");
        });
      });
    });
  }

  // Keep the thumb dock and category bar in sync with the section nearest the reading line.
  if ("IntersectionObserver" in window) {
    const navLinks = Array.from(document.querySelectorAll(".category-bar a"));
    const sections = Array.from(document.querySelectorAll("main section[id]"));
    function syncCategory() {
      const readingLine = window.innerHeight * 0.35;
      const activeSection = sections.find(function (section) {
        const rect = section.getBoundingClientRect();
        return rect.top <= readingLine && rect.bottom > readingLine;
      });
      if (!activeSection) return;
      navLinks.forEach(function (link) {
        const active = link.getAttribute("href") === "#" + activeSection.id;
        if (active) link.setAttribute("aria-current", "location");
        else link.removeAttribute("aria-current");
      });
    }
    const sectionObserver = new IntersectionObserver(syncCategory, { rootMargin: "-25% 0px -65% 0px", threshold: 0 });
    sections.forEach(function (section) { sectionObserver.observe(section); });
    let categoryFrame = 0;
    window.addEventListener("scroll", function () {
      if (categoryFrame) return;
      categoryFrame = requestAnimationFrame(function () { syncCategory(); categoryFrame = 0; });
    }, { passive: true });
    syncCategory();
  }
}());
