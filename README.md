# Crumb & Cream — Graham Bars Landing Page

A single-product landing page for **Crumb & Cream**, built with PHP, HTML5, CSS3, and vanilla JavaScript. No frameworks, no page builder — just clean, editable code.

```
graham-bars/
│
├── index.php              ← Main page (all sections)
├── css/style.css           ← All styles (design tokens, layout, animations)
├── js/script.js            ← Nav, FAQ accordion, size/qty selectors, scroll reveal
├── images/graham-bars.svg  ← Placeholder product illustration (replace with a real photo)
├── config/database.php     ← MySQL (PDO) connection used by the order form
├── database/schema.sql     ← SQL to create the database + `orders` table
└── README.md
```

---

## 1. Replacing the product image

The hero and product sections both point to `images/graham-bars.svg`, a custom illustration used as a stand-in. To use your own photo:

1. Save your photo as `images/graham-bars.jpg` (or `.png`).
2. In `index.php`, find the two `<img src="images/graham-bars.svg" ...>` tags and change the path to your new file.

That's it — no other file needs to change.

---

## 2. Running the site in Laragon (no database needed yet)

1. Copy the whole `graham-bars` folder into Laragon's `www` directory, e.g. `C:\laragon\www\graham-bars`.
2. Open Laragon and click **Start All** (this starts Apache/Nginx and MySQL).
3. Visit `http://graham-bars.test` in your browser (Laragon auto-creates this domain), or `http://localhost/graham-bars/`.

At this point the whole page works — nav, FAQ, size/quantity selectors, animations — **except** the "Send an Order Inquiry" form in the Contact section, which needs the database set up below.

---

## 3. Connecting the database in Laragon

The order form saves inquiries (name, contact info, size, quantity, message) into a MySQL table called `orders`. Here's how to wire it up:

### Step 1 — Make sure MySQL is running
In Laragon, click **Start All**. The MySQL icon in the tray should turn green.

### Step 2 — Create the database and table
You have two easy options — pick whichever tool you're comfortable with.

**Option A: phpMyAdmin (comes with Laragon)**
1. In Laragon, click **Menu → MySQL → phpMyAdmin** (or visit `http://localhost/phpmyadmin`).
2. Log in with username `root` and an empty password (Laragon's default).
3. Click **Import** in the top menu.
4. Choose the file `database/schema.sql` from this project, then click **Go**.
5. You should now see a new database called `crumb_and_cream` with one table, `orders`.

**Option B: HeidiSQL (also bundled with Laragon)**
1. In Laragon, click **Menu → MySQL → HeidiSQL**.
2. Connect using host `127.0.0.1`, user `root`, empty password.
3. Go to **File → Load SQL file...**, select `database/schema.sql`.
4. Click the **Execute SQL** (▶) button to run it.

Either way, this creates:
```sql
CREATE DATABASE crumb_and_cream;

CREATE TABLE orders (
  id, customer_name, contact_info, size,
  quantity, message, status, created_at
);
```

### Step 3 — Check the connection settings
Open `config/database.php`. The defaults already match a fresh Laragon install:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'crumb_and_cream');
define('DB_USER', 'root');
define('DB_PASS', '');   // empty by default in Laragon
```

You only need to change something here if:
- You set a custom MySQL root password in Laragon → update `DB_PASS`.
- You renamed the database → update `DB_NAME`.
- You're running MySQL on a non-default port → update `DB_PORT`.

### Step 4 — Test it
1. Reload the page and scroll to the **Contact** section.
2. Fill in the **Send an Order Inquiry** form and submit.
3. You should see a green confirmation message.
4. Check phpMyAdmin/HeidiSQL — a new row should appear in the `orders` table.

If something's wrong, the form shows a friendly error instead of a crash (e.g. "database unavailable"), and the real error is written to Laragon's PHP error log (`Menu → Apache/Nginx → php-error.log` in Laragon) so you can debug without exposing details to visitors.

### Viewing submitted inquiries
There's no admin dashboard (kept out of scope on purpose), but you can view submissions any time by opening the `orders` table in phpMyAdmin or HeidiSQL, or by running:

```sql
SELECT * FROM orders ORDER BY created_at DESC;
```

---

## 4. Editing site content

Almost everything text-based is a PHP variable at the top of `index.php`, so you rarely need to touch HTML directly:

```php
$site = [ 'brand' => 'Crumb & Cream', 'tagline' => '...', ... ];
$product = [ 'name' => 'Graham Bars', 'price_from' => '45.00', 'sizes' => [...] ];
$contact = [ 'facebook' => '...', 'phone' => '...', 'email' => '...', ... ];
```

- **Prices/sizes**: edit the `$product['sizes']` array — the product section, order form, and FAQ price answer all update automatically.
- **Contact details**: edit the `$contact` array.
- **FAQ questions**: edit the `$faqs` array further down in `index.php`.

---

## 5. Notes on design & behavior

- **Signature motif**: the torn, crumbly zigzag edge used on dividers and the hero shapes is a nod to a graham cracker's broken edge — it repeats across the page as the visual signature.
- **Responsive**: mobile-first, with a slide-in hamburger menu below 980px width.
- **Animations**: fade/slide-up on scroll via `IntersectionObserver`, respecting `prefers-reduced-motion`.
- **Size/quantity selectors** on the product showcase are visual only (they don't submit anywhere) — the *order inquiry form* in the Contact section is the one connected to the database.
- **Security**: all dynamic output goes through `htmlspecialchars()`, and the database query uses a prepared statement (PDO), so it's protected against basic XSS and SQL injection.

---

## 6. Requirements

- PHP 7.4+ (uses PDO, no PHP 8-only syntax)
- MySQL/MariaDB (only needed for the order inquiry form)
- Any local server stack: Laragon, XAMPP, or standard PHP hosting

No Composer, Node.js, or build step required.

© 2026 Crumb & Cream. All Rights Reserved.
