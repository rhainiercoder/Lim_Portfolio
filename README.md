# Lim Portfolio

Personal portfolio website for **Rhainier I. Lim**, featuring digital artwork, character designs, multimedia work, and selected software projects.

The site combines a PHP-rendered portfolio page with responsive CSS and vanilla JavaScript interactions. It is designed to run locally with XAMPP or PHP's built-in development server.

## Highlights

- Hero section with portrait and illustrated-portrait toggle
- About section with skills and background
- Digital artwork gallery
- Multimedia design gallery
- Character design carousel
- Software project carousel with:
  - Project screenshots
  - Interactive browser demos
  - Source-code viewer
- Lightbox previews for artwork and project screenshots
- Light and dark theme toggle with saved browser preference
- Responsive layout for desktop, tablet, and mobile screens
- Contact form that sends messages through Gmail SMTP

## Featured software projects

The portfolio currently presents these projects:

| Project | Description | Technologies |
| --- | --- | --- |
| Dental Clinic Management System | Patient booking and clinic workflow application | HTML, CSS, JavaScript, Node.js, Express, MySQL |
| Recipe Manager | Desktop recipe creation and management application | Java Swing, SQLite, JDBC |
| Tower of Hanoi | Configurable puzzle game with legal-move validation and an automatic solver | Java Swing, stacks, SQLite |
| Train Route Station Manager | Desktop tool for managing rail lines, stations, and train availability | Java Swing, linked lists, SQLite |

The complete project files are stored in [`assets/Source Codes/`](<assets/Source Codes/>). The interactive previews in [`demos/`](demos/) are browser demonstrations and do not replace the original desktop or database-backed applications.

## Requirements

- PHP 8.0 or newer
- A web browser with JavaScript enabled
- XAMPP, or another local PHP web server
- Node.js and npm only if you need to install or maintain the JavaScript package dependency

## Run with XAMPP

1. Place or clone this repository inside the XAMPP `htdocs` directory.
2. Start **Apache** from the XAMPP Control Panel.
3. Open the portfolio at:

   <http://localhost/Lim%20Portfolio/>

If the folder is renamed, replace `Lim%20Portfolio` in the URL with the actual folder name.

## Run with PHP's built-in server

From the repository root, run:

```bash
php -S localhost:8000
```

Then open <http://localhost:8000/>.

## Optional JavaScript dependency setup

The root [`package.json`](package.json) declares GSAP for the project. Install the dependency with:

```bash
npm install
```

The portfolio itself is loaded from [`index.php`](index.php), [`portfolio.css`](portfolio.css), and [`portfolio.js`](portfolio.js).

## Contact form

The contact form submits directly from the browser to the Web3Forms HTTPS API, as required for its free plan. Configure `WEB3FORMS_ACCESS_KEY` in Render using the public access key from the Web3Forms form setup. PHP inserts the key into the rendered form, where visitors can see it; Web3Forms documents that this access key is public and safe to expose. Web3Forms sends submissions to the email address associated with that key. The visitor's email is included so you can reply.

## Project structure

```text
.
├── index.php                 # Main portfolio page and contact-form handler
├── portfolio.css             # Site styling, responsive layout, and themes
├── portfolio.js              # Gallery, carousel, demo, lightbox, and theme behavior
├── package.json              # Front-end package metadata
├── messages.txt              # Legacy local contact log; current form does not write here
├── assets/
│   ├── images/               # Portraits, artwork, and project images
│   └── Source Codes/         # Source files for featured projects
└── demos/                    # Standalone browser previews embedded in the portfolio
```

Artwork sections are populated dynamically by PHP from their categorized folders under `assets/images/`. To add artwork, place a supported image file (`.png`, `.jpg`, `.jpeg`, `.webp`, or `.gif`) in the appropriate folder and reload the page.

## Editing content

Most portfolio text and project metadata are defined near the top of [`index.php`](index.php), including:

- Name, title, tagline, biography, and email address
- Skills list
- Featured project descriptions, technologies, features, screenshots, demos, and source files

Use URL-encoded paths when adding filenames or folders containing spaces to links rendered by PHP.

## Credits

Built and maintained by **Rhainier I. Lim**.
