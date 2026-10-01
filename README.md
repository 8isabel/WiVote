# Stemwebsite

Een website waarop jongeren kunnen stemmen op hun favoriete dingen, zoals eten, muziek, games, films en sport.

## Functies

* Stemmen op verschillende keuzes
* Verschillende categorieën
* Ratings en resultaten bekijken
* Responsive design voor mobiel en computer
* Profielpagina met een echte, aanpasbare Mii-avatar (opgeslagen in de database)

## Team

Tobias vd Ende & Isabel de Heer

## Profile page / Mii avatar setup

The profile page (`profile.php`) now has a working Mii-style avatar builder.

1. **Database**: the `miis` table must exist with the structure in `sql/miis.sql`
   (it already does on the live WiVote database - that file is just for
   reference / setting up a fresh copy).
2. **No database changes needed**: the profile page works with the existing
   `users` table (id, username, email, password, role, created_at) as-is.
   Optionally, you can run `sql/users_profile_fields.sql` once to add gender,
   birthday, favorite_color and is_single - the page detects automatically
   whether those columns exist and only shows/saves those fields if they do.
3. Log in, open `profile.php`, click one of the 9 icons in the top bar
   (face, hair, eyebrows, eyes, nose, mouth, facial hair, glasses, shirt),
   pick a style/color from the row that appears under the avatar, and hit
   **Confirm** to save it. Your Mii is loaded automatically next time you
   open the page.

### Key files

* `includes/avatar_options.php` - the single source of truth for how many
  styles each body part has, the color palettes, and the default Mii.
* `mii_api.php` - JSON API used by the profile page (`load`, `save`,
  `save_profile`, `change_password`), with full server-side validation.
* `js/avatar.js` - draws the Mii as layered SVG and handles the UI.
* `style/auth.css` - shared styling for the login and register pages.
