<?php
class Vues
{
    private function t($k)
    {
        return Langue::t($k);
    }
    private function lang()
    {
        return Langue::getLang();
    }
    private function esc($s)
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
    private function avatar($photo)
    {
        $f = $photo ?: 'default.webp';
        $path = __DIR__ . '/../img/avatars/' . $f;
        return file_exists($path) ? $f : 'default.webp';
    }

    public function debutHtml($titre, $connecte = false)
    {
        $lang = $this->lang();
        $action = $_GET['action'] ?? 'accueil';
        $nonLus = 0;
        if ($connecte && isset($_SESSION['user_id'])) {
            $nonLus = (new Message($GLOBALS['cnx']))->compterNonLus($_SESSION['user_id']);
        }

        echo '<!DOCTYPE html><html lang="' . $lang . '">';
        echo '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>' . $this->esc($titre) . '</title>';
        echo '<script>if(localStorage.getItem("theme")==="light" || (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: light)").matches)) document.documentElement.classList.add("light-theme");</script>';
        echo '<link rel="stylesheet" href="css/style.css">';
        echo '</head><body>';

        echo '<div class="bg-fixed">';
        echo '<img src="https://images.unsplash.com/photo-1503289408281-f8314bf417c3?w=1920&q=80" alt="">';
        echo '<div class="bg-overlay"></div>';
        echo '</div>';

        echo '<div class="app-shell">';

        echo '<header class="app-topnav">';
        echo '<div class="topnav-inner">';

        echo '<a href="index.php?lang=' . $lang . '" class="topnav-logo">';
        echo '<img src="img/LOGO.png" alt="Crilay Logo" style="height: 32px; width: auto; margin-right: 8px; vertical-align: middle;">';
        echo '<span class="topnav-logo-name">Crilay</span>';
        echo '</a>';

        echo '<nav class="topnav-nav">';
        if ($connecte) {
            $navItems = [
                ['action' => 'feed', 'label' => $this->t('feed'), 'icon' => ''],
                ['action' => 'messages', 'label' => $this->t('messages'), 'icon' => ''],
                ['action' => 'agenda', 'label' => $this->t('events'), 'icon' => ''],
                ['action' => 'profil', 'label' => '', 'icon' => '<img src="img/profile.png" alt="Profile" style="height: 24px;">'],
            ];
            foreach ($navItems as $item) {
                $isActive = ($action === $item['action']) ? ' active' : '';
                echo '<a href="index.php?action=' . $item['action'] . '&lang=' . $lang . '" class="tnav-item' . $isActive . '">';
                if ($item['icon']) echo '<span class="tnav-icon">' . $item['icon'] . '</span>';
                if ($item['label']) echo '<span>' . $item['label'] . '</span>';
                if ($item['action'] === 'messages' && $nonLus > 0) {
                    echo '<span class="tnav-badge">' . $nonLus . '</span>';
                }
                echo '</a>';
            }
        } else {
            $pubItems = [
                ['action' => 'apropos', 'label' => $this->t('nav_about')],
                ['action' => 'equipe', 'label' => $this->t('team')],
                ['action' => 'faq', 'label' => $this->t('faq')],
            ];
            foreach ($pubItems as $item) {
                $isActive = ($action === $item['action']) ? ' active' : '';
                echo '<a href="index.php?action=' . $item['action'] . '&lang=' . $lang . '" class="tnav-item' . $isActive . '">';
                echo '<span class="tnav-icon">' . $item['icon'] . '</span>';
                echo '<span>' . $item['label'] . '</span>';
                echo '</a>';
            }
        }
        echo '</nav>';

        echo '<div class="topnav-actions">';

        echo '<div class="lang-switcher">';
        echo '<img src="img/language.png" alt="Language" class="lang-icon" onclick="document.getElementById(\'lang-dropdown\').classList.toggle(\'show\'); event.stopPropagation();">';
        echo '<div id="lang-dropdown" class="lang-dropdown">';
        foreach (Langue::getAll() as $l) {
            $cls = ($l === $lang) ? ' lang-active' : '';
            echo '<a href="index.php?lang=' . $l . '&action=' . $this->esc($action) . '" class="lang-option' . $cls . '">' . strtoupper($l) . '</a>';
        }
        echo '</div>';
        echo '<script>document.addEventListener("click", function(event) { var d = document.getElementById("lang-dropdown"); if(d && !event.target.closest(".lang-switcher")) d.classList.remove("show"); });</script>';
        echo '</div>';

        if ($connecte) {

            echo '<form action="index.php" method="GET" class="topnav-search">';
            echo '<input type="hidden" name="action" value="recherche">';
            echo '<input type="hidden" name="lang" value="' . $lang . '">';
            echo '<span class="topnav-search-icon">&#128269;</span>';
            echo '<input type="text" name="q" placeholder="' . $this->t('search') . '...">';
            echo '</form>';

            echo '<a href="index.php?action=deconnexion" class="btn-logout" title="' . $this->t('logout') . '" style="display:flex; align-items:center; background:none; border:none; padding:0; outline:none;"><img src="img/se-deco.png" alt="Logout" style="height:24px;"></a>';
        } else {
            echo '<a href="index.php?action=inscription&lang=' . $lang . '" class="topnav-cta"><span>' . $this->t('signup') . '</span></a>';
            echo '<a href="index.php?action=connexion&lang=' . $lang . '" class="btn-secondary btn-pill" style="padding:8px 18px;font-size:14px;min-height:42px;">' . $this->t('login') . '</a>';
        }
        echo '<button id="theme-toggle" class="btn-theme-toggle" aria-label="Toggle theme">&#9728;</button>';
        echo '</div>';

        echo '</div></header>';

        if ($connecte) {
            echo '<nav class="app-bottomnav">';
            $mobileNav = [
                ['action' => 'feed', 'icon' => '&#127968;', 'label' => $this->t('feed')],
                ['action' => 'messages', 'icon' => '&#9993;', 'label' => $this->t('messages')],
                ['action' => 'agenda', 'icon' => '&#128197;', 'label' => $this->t('events')],
                ['action' => 'profil', 'icon' => '<img src="img/profile.png" alt="Profile" style="height:24px; vertical-align:middle;">', 'label' => ''],
            ];
            foreach ($mobileNav as $item) {
                $isActive = ($action === $item['action']) ? ' active' : '';
                echo '<a href="index.php?action=' . $item['action'] . '&lang=' . $lang . '" class="bnav-item' . $isActive . '">';
                echo '<span style="font-size:22px;">' . $item['icon'] . '</span>';
                if ($item['label']) echo '<span>' . $item['label'] . '</span>';
                echo '</a>';
            }
            echo '</nav>';
        }

        echo '<main class="contenu-principal">';
    }

    public function finHtml()
    {
        echo '</main>';
        $lang = $this->lang();

        echo '<footer class="footer">';
        echo '<div class="footer-inner">';
        echo '<div class="footer-columns">';

        echo '<div class="footer-col">';
        echo '<h4>' . $this->t('nav_about') . '</h4>';
        echo '<a href="index.php?action=apropos&lang=' . $lang . '">' . $this->t('about') . '</a>';
        echo '<a href="index.php?action=equipe&lang=' . $lang . '">' . $this->t('team') . '</a>';
        echo '<a href="index.php?action=contact&lang=' . $lang . '">' . $this->t('contact') . '</a>';
        echo '<a href="index.php?action=faq&lang=' . $lang . '">' . $this->t('faq') . '</a>';
        echo '</div>';

        echo '<div class="footer-col">';
        echo '<h4>' . $this->t('legal') . '</h4>';
        echo '<a href="index.php?action=mentions&lang=' . $lang . '">' . $this->t('legal') . '</a>';
        echo '<a href="index.php?action=cgu&lang=' . $lang . '">' . $this->t('cgu') . '</a>';
        echo '<a href="index.php?action=confidentialite&lang=' . $lang . '">' . $this->t('privacy') . '</a>';
        echo '</div>';

        echo '</div>';
        echo '<p class="footer-copy">' . $this->t('footer_copy') . '</p>';
        echo '</div></footer>';

        echo '</div>';
        
        echo '<button id="btn-scroll-top" class="btn-scroll-top" aria-label="Remonter en haut" title="Remonter">&#8679;</button>';
        
        echo '<script>
        document.getElementById("theme-toggle")?.addEventListener("click", () => {
            document.documentElement.classList.toggle("light-theme");
            localStorage.setItem("theme", document.documentElement.classList.contains("light-theme") ? "light" : "dark");
        });
        
        const btnScrollTop = document.getElementById("btn-scroll-top");
        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                btnScrollTop?.classList.add("visible");
            } else {
                btnScrollTop?.classList.remove("visible");
            }
        });
        btnScrollTop?.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
        </script>';
        echo '</body></html>';
    }

    public function afficherAccueil()
    {
        $lang = $this->lang();

        echo '<section class="hero-split">';
        echo '<div class="hero-content">';
        echo '<h1>' . $this->t('hero_title') . '</h1>';
        echo '<p class="hero-subtitle">' . $this->t('hero_subtitle') . '</p>';
        echo '<div class="hero-buttons">';
        echo '<a href="index.php?action=inscription&lang=' . $lang . '" class="btn-primary btn-lg">' . $this->t('signup') . '</a>';
        echo '<a href="index.php?action=connexion&lang=' . $lang . '" class="btn-secondary btn-lg">' . $this->t('login') . '</a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="hero-video">';
        echo '<div class="video-container">';
        echo '<video controls width="100%" poster="img/video-poster.jpg">';
        echo '<source src="img/motustar-intro.mp4" type="video/mp4">';
        echo '<track kind="subtitles" src="img/subs_fr.vtt" srclang="fr" label="Français"' . ($lang === 'fr' ? ' default' : '') . '>';
        echo '<track kind="subtitles" src="img/subs_sq.vtt" srclang="sq" label="Shqip"' . ($lang === 'sq' ? ' default' : '') . '>';
        echo '<track kind="subtitles" src="img/subs_vi.vtt" srclang="vi" label="Tiếng Việt"' . ($lang === 'vi' ? ' default' : '') . '>';
        echo '</video></div>';
        echo '</div>';
        echo '</section>';

        echo '<section class="section-why">';
        echo '<h2>' . $this->t('why_title') . '</h2>';
        echo '<div class="why-grid">';
        $whyIcons = ['&#129309;', '&#127793;', '&#127758;'];
        for ($i = 1; $i <= 3; $i++) {
            echo '<div class="why-card">';
            echo '<div class="why-icon">' . $whyIcons[$i - 1] . '</div>';
            echo '<div class="why-text">';
            echo '<h3>' . $this->t("why_{$i}_title") . '</h3>';
            echo '<p>' . $this->t("why_{$i}_desc") . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div></section>';

        echo '<section class="section-how">';
        echo '<h2>' . $this->t('how_title') . '</h2>';
        echo '<div class="how-grid">';
        $icons = ['&#127912;', '&#128269;', '&#129309;'];
        $colors = ['#DE6B48', '#7DA27E', '#E3B505'];
        for ($i = 1; $i <= 3; $i++) {
            echo '<div class="how-step">';
            echo '<div class="how-icon">' . $icons[$i - 1] . '</div>';
            echo '<div class="how-text">';
            echo '<h3>' . $this->t("how_{$i}_title") . '</h3>';
            echo '<p>' . $this->t("how_{$i}_desc") . '</p>';
            echo '</div>';
            echo '<div class="how-bar" style="background:' . $colors[$i - 1] . ';box-shadow:0 2px 10px ' . $colors[$i - 1] . '60;"></div>';
            echo '</div>';
        }
        echo '</div></section>';

        echo '<section class="section-cta">';
        echo '<h2>' . $this->t('join_cta') . '</h2>';
        echo '<p>' . $this->t('member_count_text') . '</p>';
        echo '<a href="index.php?action=inscription&lang=' . $lang . '" class="btn-primary btn-lg">' . $this->t('signup') . '</a>';
        echo '</section>';
    }

    public function afficherInscription($tags, $erreur = '', $success = '')
    {
        echo '<section class="form-section">';
        echo '<h2>' . $this->t('register_title') . '</h2>';

        if ($erreur)
            echo '<div class="alert alert-error">' . $this->t($erreur) . '</div>';
        if ($success)
            echo '<div class="alert alert-success">' . $this->t($success) . '</div>';

        // Progress bar
        echo '<div class="signup-progress">';
        echo '<div class="progress-step active" data-step="1"><span class="step-num">1</span><span class="step-label">' . $this->t('step_account') . '</span></div>';
        echo '<div class="progress-line"></div>';
        echo '<div class="progress-step" data-step="2"><span class="step-num">2</span><span class="step-label">' . $this->t('step_profile') . '</span></div>';
        echo '<div class="progress-line"></div>';
        echo '<div class="progress-step" data-step="3"><span class="step-num">3</span><span class="step-label">' . $this->t('step_finish') . '</span></div>';
        echo '</div>';

        echo '<form method="POST" action="index.php?action=inscription_submit" enctype="multipart/form-data" class="form-card">';

        // Step 1
        echo '<div class="signup-step" id="signup-step-1">';
        $this->champTexte('pseudonyme', 'pseudo', true);
        $this->champTexte('email', 'email', true, 'email');
        $this->champTexte('mot_de_passe', 'password', true, 'password');
        $this->champTexte('mot_de_passe_confirm', 'password_confirm', true, 'password');
        echo '<button type="button" class="btn-primary btn-full" onclick="signupNext(2)">' . $this->t('next') . '</button>';
        echo '</div>';

        // Step 2
        echo '<div class="signup-step" id="signup-step-2" style="display:none;">';
        $this->champPays();
        $this->champTexte('date_naissance', 'birthdate', false, 'date');

        echo '<div class="form-group">';
        echo '<label>' . $this->t('spoken_languages') . '</label>';
        echo '<div class="checkbox-grid">';
        $languesOptions = ['Français', 'English', 'Shqip', 'Tiếng Việt', 'Deutsch', 'Español', 'العربية', '中文', '日本語'];
        foreach ($languesOptions as $lo) {
            echo '<label class="checkbox-label"><input type="checkbox" name="langues_parlees[]" value="' . $this->esc($lo) . '"> ' . $this->esc($lo) . '</label>';
        }
        echo '</div></div>';

        echo '<div class="form-group">';
        echo '<label for="bio">' . $this->t('bio') . '</label>';
        echo '<textarea name="bio" id="bio" rows="3"></textarea>';
        echo '</div>';

        echo '<div class="form-group">';
        echo '<label for="experiences_texte">' . $this->t('experiences_texte') . '</label>';
        echo '<textarea name="experiences_texte" id="experiences_texte" rows="3" placeholder="' . $this->t('experiences_placeholder') . '"></textarea>';
        echo '</div>';

        echo '<div class="signup-nav">';
        echo '<button type="button" class="btn-secondary btn-full" onclick="signupPrev(1)">' . $this->t('previous') . '</button>';
        echo '<button type="button" class="btn-primary btn-full" onclick="signupNext(3)">' . $this->t('next') . '</button>';
        echo '</div>';
        echo '</div>';

        // Step 3
        echo '<div class="signup-step" id="signup-step-3" style="display:none;">';

        echo '<div class="form-group">';
        echo '<label for="photo">' . $this->t('photo') . '</label>';
        echo '<input type="file" name="photo" id="photo" accept="image/*">';
        echo '</div>';

        echo '<div class="form-group">';
        echo '<label>' . $this->t('select_tags') . '</label>';
        echo '<div class="tags-grid">';
        foreach ($tags as $tag) {
            echo '<label class="tag-label"><input type="checkbox" name="tags[]" value="' . $tag['id'] . '"> ' . $this->esc($tag['nom']) . '</label>';
        }
        echo '</div></div>';

        echo '<input type="hidden" name="latitude" id="latitude">';
        echo '<input type="hidden" name="longitude" id="longitude">';

        echo '<div class="form-group cgu-group">';
        echo '<label class="checkbox-label"><input type="checkbox" name="cgu" value="1" required> <span class="required-star">*</span> ' . $this->t('accept_cgu');
        echo ' (<a href="index.php?action=cgu" target="_blank">' . $this->t('cgu') . '</a>)';
        echo '</label></div>';

        echo '<div class="signup-nav">';
        echo '<button type="button" class="btn-secondary btn-full" onclick="signupPrev(2)">' . $this->t('previous') . '</button>';
        echo '<button type="submit" class="btn-primary btn-full">' . $this->t('register_btn') . '</button>';
        echo '</div>';
        echo '</div>';

        echo '</form>';

        echo '<p class="form-footer">' . $this->t('already_account') . ' <a href="index.php?action=connexion">' . $this->t('login') . '</a></p>';

        echo '<script>
        function signupNext(step) {
            var current = step - 1;
            var currentDiv = document.getElementById("signup-step-" + current);
            var inputs = currentDiv.querySelectorAll("[required]");
            for (var i = 0; i < inputs.length; i++) {
                if (!inputs[i].value.trim()) { inputs[i].focus(); inputs[i].reportValidity(); return; }
            }
            currentDiv.style.display = "none";
            document.getElementById("signup-step-" + step).style.display = "block";
            document.querySelectorAll(".progress-step").forEach(function(el) {
                el.classList.toggle("active", parseInt(el.dataset.step) <= step);
            });
            document.querySelectorAll(".progress-line").forEach(function(el, i) {
                el.classList.toggle("active", i < step - 1);
            });
        }
        function signupPrev(step) {
            var current = step + 1;
            document.getElementById("signup-step-" + current).style.display = "none";
            document.getElementById("signup-step-" + step).style.display = "block";
            document.querySelectorAll(".progress-step").forEach(function(el) {
                el.classList.toggle("active", parseInt(el.dataset.step) <= step);
            });
            document.querySelectorAll(".progress-line").forEach(function(el, i) {
                el.classList.toggle("active", i < step - 1);
            });
        }
        </script>';
        echo '</section>';

        echo '<script>
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
                document.getElementById("latitude").value=pos.coords.latitude;
                document.getElementById("longitude").value=pos.coords.longitude;
            });
        }
        </script>';
    }

    public function afficherConnexion($erreur = '')
    {
        echo '<section class="form-section">';
        echo '<h2>' . $this->t('login_title') . '</h2>';

        if ($erreur)
            echo '<div class="alert alert-error">' . $this->t($erreur) . '</div>';

        echo '<form method="POST" action="index.php?action=connexion_submit" class="form-card">';
        $this->champTexte('email', 'email', true, 'email');
        $this->champTexte('mot_de_passe', 'password', true, 'password');
        echo '<button type="submit" class="btn-primary btn-full">' . $this->t('login_btn') . '</button>';
        echo '</form>';
        echo '<p class="form-footer">' . $this->t('no_account') . ' <a href="index.php?action=inscription">' . $this->t('signup') . '</a></p>';
        echo '</section>';
    }

    public function afficherProfil($user, $tags, $estMoi = false, $relationAmitie = null)
    {
        $age = (new Utilisateur(null))->calculerAge($user['date_naissance'] ?? null);

        echo '<section class="profil-section">';
        echo '<div class="profil-card">';
        echo '<div class="profil-header">';
        $photo = $this->avatar($user['photo_profil'] ?? null);
        echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="Avatar" class="profil-avatar">';
        echo '<div class="profil-info">';
        echo '<h2>' . $this->esc($user['pseudonyme']) . '</h2>';
        if ($age !== null)
            echo '<p>' . $age . ' ' . $this->t('years_old') . '</p>';
        if (!empty($user['ville']))
            echo '<p>' . $this->esc($user['ville']) . '</p>';
        if (!empty($user['nationalite']))
            echo '<p>' . $this->esc($user['nationalite']) . '</p>';
        if (!empty($user['langues_parlees']))
            echo '<p>' . $this->esc($user['langues_parlees']) . '</p>';
        echo '<p class="profil-date">' . $this->t('member_since') . ' ' . date('d/m/Y', strtotime($user['date_inscription'])) . '</p>';
        echo '</div></div>';

        if (!empty($user['bio'])) {
            echo '<div class="profil-bio"><p>' . nl2br($this->esc($user['bio'])) . '</p></div>';
        }

        if (!empty($user['experiences_texte'])) {
            echo '<div class="profil-experiences">';
            echo '<h3>' . $this->t('experiences_section') . '</h3>';
            echo '<p>' . nl2br($this->esc($user['experiences_texte'])) . '</p>';
            echo '</div>';
        }

        if (!empty($tags)) {
            echo '<div class="profil-tags"><h3>' . $this->t('skills') . '</h3><div class="tags-list">';
            foreach ($tags as $t) {
                echo '<span class="tag-badge">' . $this->esc($t['nom']) . '</span>';
            }
            echo '</div></div>';
        }

        if ($estMoi) {
            echo '<div style="display:flex; gap:10px; margin-top:20px;">';
            echo '<a href="index.php?action=profil_edit" class="btn-primary">' . $this->t('edit_profile') . '</a>';
            echo '<a href="index.php?action=amis" class="btn-secondary" style="padding:12px 22px;">' . $this->t('friends') . '</a>';
            echo '</div>';
        } else {
            if ($relationAmitie === null) {
                echo '<a href="index.php?action=demande_ami&id=' . $user['id'] . '" class="btn-primary">' . $this->t('add_friend') . '</a>';
            } elseif ($relationAmitie['statut'] === 'en_attente') {
                echo '<span class="badge-pending">' . $this->t('pending') . '</span>';
            }
            echo ' <a href="index.php?action=ecrire&dest=' . $user['id'] . '" class="btn-secondary" style="padding:12px 22px;">' . $this->t('send_message') . '</a>';
        }

        echo '</div></section>';
    }

    public function afficherEditProfil($user, $userTags, $allTags)
    {
        echo '<section class="form-section">';
        echo '<h2>' . $this->t('edit_profile') . '</h2>';
        echo '<form method="POST" action="index.php?action=profil_update" enctype="multipart/form-data" class="form-card">';

        $this->champTexte('pseudonyme', 'pseudo', true, 'text', $user['pseudonyme']);

        $this->champPays($user['nationalite'] ?? '');

        $this->champTexte('date_naissance', 'birthdate', false, 'date', $user['date_naissance']);
        $this->champTexte('ville', 'city', false, 'text', $user['ville']);
        $this->champTexte('mot_de_passe', 'password', false, 'password');

        echo '<div class="form-group">';
        echo '<label>' . $this->t('spoken_languages') . '</label>';
        echo '<div class="checkbox-grid">';
        $languesActuelles = explode(',', $user['langues_parlees']);
        $languesOptions = ['Français', 'English', 'Shqip', 'Tiếng Việt', 'Deutsch', 'Español', 'العربية', '中文', '日本語'];
        foreach ($languesOptions as $lo) {
            $checked = in_array($lo, $languesActuelles) ? ' checked' : '';
            echo '<label class="checkbox-label"><input type="checkbox" name="langues_parlees[]" value="' . $this->esc($lo) . '"' . $checked . '> ' . $this->esc($lo) . '</label>';
        }
        echo '</div></div>';

        echo '<div class="form-group"><label for="bio">' . $this->t('bio') . '</label>';
        echo '<textarea name="bio" id="bio" rows="3">' . $this->esc($user['bio'] ?? '') . '</textarea></div>';

        echo '<div class="form-group"><label for="experiences_texte">' . $this->t('experiences_texte') . '</label>';
        echo '<textarea name="experiences_texte" id="experiences_texte" rows="3" placeholder="' . $this->t('experiences_placeholder') . '">' . $this->esc($user['experiences_texte'] ?? '') . '</textarea></div>';

        echo '<div class="form-group"><label>' . $this->t('photo') . '</label>';
        echo '<input type="file" name="photo" accept="image/*"></div>';

        echo '<input type="hidden" name="latitude" id="latitude" value="' . ($user['latitude'] ?? '') . '">';
        echo '<input type="hidden" name="longitude" id="longitude" value="' . ($user['longitude'] ?? '') . '">';

        $userTagIds = array_column($userTags, 'id');
        echo '<div class="form-group"><label>' . $this->t('select_tags') . '</label><div class="tags-grid">';
        foreach ($allTags as $tag) {
            $checked = in_array($tag['id'], $userTagIds) ? ' checked' : '';
            echo '<label class="tag-label"><input type="checkbox" name="tags[]" value="' . $tag['id'] . '"' . $checked . '> ' . $this->esc($tag['nom']) . '</label>';
        }
        echo '</div></div>';

        echo '<div class="form-actions">';
        echo '<button type="submit" class="btn-primary">' . $this->t('save') . '</button>';
        echo ' <a href="index.php?action=profil" class="btn-secondary" style="padding:12px 22px;">' . $this->t('cancel') . '</a>';
        echo '</div></form></section>';

        echo '<script>
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
                document.getElementById("latitude").value=pos.coords.latitude;
                document.getElementById("longitude").value=pos.coords.longitude;
            });
        }
        </script>';
    }

    public function afficherRecherche($resultats, $tags, $filtres)
    {
        echo '<section class="recherche-section">';
        echo '<h2>' . $this->t('search_title') . '</h2>';

        echo '<form method="GET" action="index.php" class="filtres-form">';
        echo '<input type="hidden" name="action" value="recherche">';
        echo '<div class="filtres-grid">';
        echo '<input type="text" name="q" placeholder="' . $this->t('search_placeholder') . '" value="' . $this->esc($filtres['q'] ?? '') . '" class="input-search">';

        echo '<select name="langue" class="select-filtre">';
        echo '<option value="">' . $this->t('all_languages') . '</option>';
        $languesOptions = ['Français', 'English', 'Shqip', 'Tiếng Việt', 'Deutsch', 'Español'];
        foreach ($languesOptions as $lo) {
            $sel = (($filtres['langue'] ?? '') === $lo) ? ' selected' : '';
            echo '<option value="' . $this->esc($lo) . '"' . $sel . '>' . $this->esc($lo) . '</option>';
        }
        echo '</select>';

        echo '<select name="tag_id" class="select-filtre">';
        echo '<option value="">' . $this->t('all_tags') . '</option>';
        foreach ($tags as $tag) {
            $sel = (($filtres['tag_id'] ?? '') == $tag['id']) ? ' selected' : '';
            echo '<option value="' . $tag['id'] . '"' . $sel . '>' . $this->esc($tag['nom']) . '</option>';
        }
        echo '</select>';

        echo '<button type="submit" class="btn-primary btn-sm">' . $this->t('search') . '</button>';
        echo '</div></form>';

        echo '<div class="vue-toggle">';
        echo '<button type="button" id="btn-liste" class="btn-toggle active" onclick="toggleVue(\'liste\')">' . $this->t('list_view') . '</button>';
        echo '<button type="button" id="btn-carte" class="btn-toggle" onclick="toggleVue(\'carte\')">' . $this->t('map_view') . '</button>';
        echo '</div>';

        echo '<div id="vue-liste" class="resultats-liste">';
        if (empty($resultats)) {
            echo '<p class="no-results">' . $this->t('no_results') . '</p>';
        } else {
            echo '<div class="users-grid">';
            foreach ($resultats as $u) {
                echo '<div class="user-card">';
                $photo = $this->avatar($u['photo_profil'] ?? null);
                echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="user-card-avatar">';
                echo '<h3>' . $this->esc($u['pseudonyme']) . '</h3>';
                if (!empty($u['ville']))
                    echo '<p class="user-card-city">' . $this->esc($u['ville']) . '</p>';
                if (!empty($u['tags_noms']))
                    echo '<p class="user-card-tags">' . $this->esc($u['tags_noms']) . '</p>';
                echo '<a href="index.php?action=profil_voir&id=' . $u['id'] . '" class="btn-small">' . $this->t('view_profile') . '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';

        echo '<div id="vue-carte" class="carte-container" style="display:none;">';
        echo '<div id="map" style="width:100%;height:500px;border-radius:20px;"></div>';
        echo '</div>';

        echo '<script>';
        echo 'var utilisateursMap = ' . json_encode(array_map(function ($u) {
            return [
                'id' => $u['id'],
                'pseudo' => $u['pseudonyme'],
                'lat' => (float) $u['latitude'],
                'lng' => (float) $u['longitude'],
                'ville' => $u['ville'] ?? '',
                'photo' => $u['photo_profil'] ?: 'default.webp'
            ];
        }, array_filter($resultats, function ($u) {
            return !empty($u['latitude']);
        }))) . ';';

        echo '
        var map, markers = [];
        function toggleVue(mode) {
            document.getElementById("vue-liste").style.display = mode==="liste" ? "block" : "none";
            document.getElementById("vue-carte").style.display = mode==="carte" ? "block" : "none";
            document.getElementById("btn-liste").className = "btn-toggle" + (mode==="liste" ? " active" : "");
            document.getElementById("btn-carte").className = "btn-toggle" + (mode==="carte" ? " active" : "");
            if(mode==="carte" && !map) initMap();
        }
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 3, center: {lat: 30, lng: 10},
                styles: [{elementType:"geometry",stylers:[{color:"#1d2c4d"}]},{elementType:"labels.text.fill",stylers:[{color:"#8ec3b9"}]},{elementType:"labels.text.stroke",stylers:[{color:"#1a3646"}]}]
            });
            utilisateursMap.forEach(function(u) {
                if(u.lat && u.lng) {
                    var marker = new google.maps.Marker({
                        position: {lat: u.lat, lng: u.lng},
                        map: map, title: u.pseudo
                    });
                    var infowindow = new google.maps.InfoWindow({
                        content: \'<div style="text-align:center;padding:8px;"><img src="img/avatars/\'+u.photo+\'" style="width:44px;height:44px;border-radius:50%;"><br><strong style="color:#333;">\'+u.pseudo+\'</strong><br><span style="color:#666;">\'+u.ville+\'</span><br><a href="index.php?action=profil_voir&id=\'+u.id+\'" style="color:#DE6B48;font-weight:700;">Voir</a></div>\'
                    });
                    marker.addListener("click", function() { infowindow.open(map, marker); });
                    markers.push(marker);
                }
            });
        }
        </script>';
        $apiKey = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
        echo '<script src="https://maps.googleapis.com/maps/api/js?key=' . htmlspecialchars($apiKey) . '&callback=Function.prototype" async defer></script>';
        echo '</section>';
    }

    public function afficherAmis($amis, $demandes)
    {
        echo '<section class="amis-section">';

        if (!empty($demandes)) {
            echo '<div class="demandes-box">';
            echo '<h3>' . $this->t('friend_requests') . ' (' . count($demandes) . ')</h3>';
            foreach ($demandes as $d) {
                echo '<div class="demande-card">';
                $photo = $this->avatar($d['photo_profil'] ?? null);
                echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="mini-avatar">';
                echo '<span>' . $this->esc($d['pseudonyme']) . '</span>';
                echo '<a href="index.php?action=accepter_ami&id=' . $d['id'] . '" class="btn-accept">' . $this->t('accept') . '</a>';
                echo '<a href="index.php?action=refuser_ami&id=' . $d['id'] . '" class="btn-decline">' . $this->t('decline') . '</a>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '<h2>' . $this->t('friends') . '</h2>';
        if (empty($amis)) {
            echo '<p class="no-results">' . $this->t('no_friends') . '</p>';
        } else {
            echo '<div class="users-grid">';
            foreach ($amis as $a) {
                echo '<div class="user-card">';
                $photo = $this->avatar($a['photo_profil'] ?? null);
                echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="user-card-avatar">';
                echo '<h3>' . $this->esc($a['pseudonyme']) . '</h3>';
                echo '<div class="user-card-actions">';
                echo '<a href="index.php?action=ecrire&dest=' . $a['id'] . '" class="btn-small">' . $this->t('send_message') . '</a>';
                echo '<a href="index.php?action=profil_voir&id=' . $a['id'] . '" class="btn-small btn-outline">' . $this->t('view_profile') . '</a>';
                echo '</div></div>';
            }
            echo '</div>';
        }
        echo '</section>';
    }

    public function afficherConversations($conversations)
    {
        echo '<section class="messages-section">';
        echo '<h2>' . $this->t('conversations') . '</h2>';

        if (empty($conversations)) {
            echo '<p class="no-results">' . $this->t('no_messages') . '</p>';
        } else {
            echo '<div class="conversations-list">';
            foreach ($conversations as $c) {
                $classe = ($c['non_lus'] > 0) ? ' conv-unread' : '';
                echo '<a href="index.php?action=ecrire&dest=' . $c['contact_id'] . '" class="conv-item' . $classe . '">';
                $photo = $this->avatar($c['photo_profil'] ?? null);
                echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="mini-avatar">';
                echo '<div class="conv-info">';
                echo '<strong>' . $this->esc($c['pseudonyme']) . '</strong>';
                if ($c['non_lus'] > 0)
                    echo ' <span class="badge-notif">' . $c['non_lus'] . '</span>';
                echo '<p class="conv-preview">' . mb_strimwidth($this->esc($c['contenu']), 0, 60, '...') . '</p>';
                echo '</div>';
                echo '<span class="conv-date">' . date('d/m H:i', strtotime($c['date_envoi'])) . '</span>';
                echo '</a>';
            }
            echo '</div>';
        }
        echo '</section>';
    }

    public function afficherConversation($messages, $contact, $amiObj, $userId, $infoMsg = '')
    {
        echo '<section class="chat-section">';
        echo '<div class="chat-header">';
        echo '<a href="index.php?action=messages" class="btn-back">&larr;</a>';
        $photo = $this->avatar($contact['photo_profil'] ?? null);
        echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="mini-avatar">';
        echo '<h3>' . $this->esc($contact['pseudonyme']) . '</h3>';
        echo '</div>';

        if ($infoMsg) {
            echo '<div class="alert alert-info">' . $this->t($infoMsg) . '</div>';
        }

        echo '<div class="chat-messages">';
        foreach ($messages as $m) {
            $moi = ($m['expediteur_id'] == $userId) ? ' msg-moi' : ' msg-autre';
            echo '<div class="msg-bulle' . $moi . '">';
            if ($m['est_invitation'])
                echo '<div class="msg-invitation-badge">&#9733;</div>';
            if (!empty(trim($m['contenu']))) {
                echo '<p>' . nl2br($this->esc($m['contenu'])) . '</p>';
            }
            if (!empty($m['piece_jointe'])) {
                echo '<img src="img/uploads/messages/' . $this->esc($m['piece_jointe']) . '" class="msg-image" style="max-width:100%; border-radius: 8px; margin-top: 5px; cursor: pointer;" onclick="window.open(this.src,\'_blank\')">';
            }
            echo '<span class="msg-date">' . date('d/m H:i', strtotime($m['date_envoi'])) . '</span>';
            echo '</div>';
        }
        echo '</div>';

        $sontAmis = $amiObj->sontAmis($userId, $contact['id']);
        $peutEcrire = $sontAmis;

        if (!$sontAmis) {
            $dejaEnvoye = false;
            foreach ($messages as $m) {
                if ($m['expediteur_id'] == $userId) {
                    $dejaEnvoye = true;
                    break;
                }
            }
            $peutEcrire = !$dejaEnvoye;
        }

        if ($peutEcrire) {
            echo '<form method="POST" action="index.php?action=envoyer_msg&dest=' . $contact['id'] . '" class="chat-form" enctype="multipart/form-data">';
            echo '<textarea name="contenu" placeholder="' . $this->t('type_message') . '"></textarea>';
            echo '<label for="file_msg" style="cursor:pointer; display:flex; align-items:center; justify-content:center; padding: 0 15px; font-size: 28px; font-weight: bold; color: var(--primary);">+</label>';
            echo '<input type="file" id="file_msg" name="piece_jointe" accept="image/*" style="display:none;" onchange="previewImg(this, \'preview_msg\')">';
            echo '<button type="submit" class="btn-primary">' . $this->t('send') . '</button>';
            echo '</form>';
            echo '<div id="preview_msg" style="display:none; margin-top:10px; text-align:right;"><img src="" style="max-height:100px; border-radius:8px; border: 2px solid var(--primary);"></div>';
            echo '<script>
            if (typeof previewImg !== "function") {
                function previewImg(input, previewId) {
                    var previewDiv = document.getElementById(previewId);
                    var img = previewDiv.querySelector("img");
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) { img.src = e.target.result; previewDiv.style.display = "block"; }
                        reader.readAsDataURL(input.files[0]);
                    } else { img.src = ""; previewDiv.style.display = "none"; }
                }
            }
            </script>';
        } else {
            echo '<div class="alert alert-info">' . $this->t('must_connect_first') . '</div>';
        }

        echo '</section>';
    }

    public function afficherAgenda($evenements, $invitations, $amis)
    {
        echo '<section class="agenda-section">';
        echo '<h2>' . $this->t('agenda_title') . '</h2>';

        if (!empty($invitations)) {
            echo '<div class="invitations-box">';
            echo '<h3>' . $this->t('event_pending') . '</h3>';
            foreach ($invitations as $inv) {
                echo '<div class="invitation-card">';
                echo '<strong>' . $this->esc($inv['titre']) . '</strong>';
                echo '<p>' . $this->esc($inv['organisateur_pseudo']) . ' - ' . date('d/m/Y H:i', strtotime($inv['date_debut'])) . '</p>';
                echo '<a href="index.php?action=repondre_event&id=' . $inv['id'] . '&rep=accepte" class="btn-accept">' . $this->t('accept_event') . '</a>';
                echo '<a href="index.php?action=repondre_event&id=' . $inv['id'] . '&rep=refuse" class="btn-decline">' . $this->t('decline_event') . '</a>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '<details class="create-event-box">';
        echo '<summary class="btn-primary">' . $this->t('create_event') . '</summary>';
        echo '<form method="POST" action="index.php?action=creer_event" class="form-card">';
        $this->champTexte('titre', 'event_title', true);
        echo '<div class="form-group"><label>' . $this->t('event_desc') . '</label><textarea name="description" rows="2"></textarea></div>';
        $this->champTexte('date_debut', 'event_start', true, 'datetime-local');
        $this->champTexte('date_fin', 'event_end', true, 'datetime-local');
        echo '<div class="form-group"><label>' . $this->t('event_type') . '</label>';
        echo '<select name="type" id="event-type-select" onchange="toggleParticipants()">';
        echo '<option value="prive">' . $this->t('event_private') . '</option>';
        echo '<option value="partage">' . $this->t('event_shared') . '</option>';
        echo '<option value="public">' . $this->t('event_public') . '</option>';
        echo '</select></div>';

        echo '<div class="form-group" id="participants-group" style="display:none;">';
        echo '<label>' . $this->t('event_participants') . '</label>';
        echo '<div class="checkbox-grid">';
        foreach ($amis as $a) {
            echo '<label class="checkbox-label"><input type="checkbox" name="participants[]" value="' . $a['id'] . '"> ' . $this->esc($a['pseudonyme']) . '</label>';
        }
        echo '</div></div>';

        echo '<button type="submit" class="btn-primary">' . $this->t('create_event') . '</button>';
        echo '</form></details>';

        echo '<div class="agenda-events">';
        if (empty($evenements)) {
            echo '<p class="no-results">' . $this->t('no_events') . '</p>';
        }
        foreach ($evenements as $e) {
            $typeClass = 'event-' . $e['type'];
            echo '<div class="event-card ' . $typeClass . '">';
            echo '<div class="event-date-badge">' . date('d/m', strtotime($e['date_debut'])) . '</div>';
            echo '<div class="event-details">';
            echo '<h4>' . ($e['type'] === 'prive' && ($e['role'] ?? '') === 'participant' ? $this->t('event_busy') : $this->esc($e['titre'])) . '</h4>';
            if ($e['type'] !== 'prive' || ($e['role'] ?? '') === 'organisateur') {
                echo '<p>' . date('H:i', strtotime($e['date_debut'])) . ' - ' . date('H:i', strtotime($e['date_fin'])) . '</p>';
                if (!empty($e['description']))
                    echo '<p class="event-desc">' . $this->esc($e['description']) . '</p>';
            }
            echo '<span class="event-type-badge">' . $this->t('event_' . $e['type']) . '</span>';
            if (($e['role'] ?? '') === 'organisateur') {
                echo ' <a href="index.php?action=suppr_event&id=' . $e['id'] . '" class="btn-small btn-danger">' . $this->t('delete_event') . '</a>';
            }
            echo '</div></div>';
        }
        echo '</div>';

        echo '<script>
        function toggleParticipants() {
            var sel = document.getElementById("event-type-select").value;
            document.getElementById("participants-group").style.display = sel==="partage" ? "block" : "none";
        }
        </script>';
        echo '</section>';
    }

    public function afficherFeed($publications, $nouveaux, $eventsPublics)
    {
        echo '<section class="feed-section">';
        echo '<div class="feed-layout">';

        echo '<div class="feed-main">';

        echo '<form method="POST" action="index.php?action=publier" class="post-form" enctype="multipart/form-data">';
        echo '<textarea name="contenu" placeholder="' . $this->t('write_post') . '" rows="2"></textarea>';
        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-top: 10px;">';
        echo '<label for="file_feed" style="cursor:pointer; font-size:28px; font-weight:bold; color:var(--primary); padding: 0 10px;">+</label>';
        echo '<input type="file" id="file_feed" name="image" accept="image/*" style="display:none;" onchange="previewImg(this, \'preview_feed\')">';
        echo '<button type="submit" class="btn-primary btn-sm">' . $this->t('publish') . '</button>';
        echo '</div>';
        echo '<div id="preview_feed" style="display:none; margin-top:10px;"><img src="" style="max-height:150px; border-radius:8px; border: 2px solid var(--primary);"></div>';
        echo '</form>';
        echo '<script>
        if (typeof previewImg !== "function") {
            function previewImg(input, previewId) {
                var previewDiv = document.getElementById(previewId);
                var img = previewDiv.querySelector("img");
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) { img.src = e.target.result; previewDiv.style.display = "block"; }
                    reader.readAsDataURL(input.files[0]);
                } else { img.src = ""; previewDiv.style.display = "none"; }
            }
        }
        </script>';

        if (empty($publications)) {
            echo '<p class="no-results">' . $this->t('no_posts') . '</p>';
        }
        foreach ($publications as $p) {
            echo '<div class="post-card">';
            echo '<div class="post-header">';
            $photo = $this->avatar($p['photo_profil'] ?? null);
            echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="mini-avatar">';
            echo '<div>';
            echo '<a href="index.php?action=profil_voir&id=' . $p['auteur_id'] . '" class="post-author">' . $this->esc($p['pseudonyme']) . '</a>';
            echo '<span class="post-date">' . date('d/m/Y H:i', strtotime($p['date_publication'])) . '</span>';
            echo '</div></div>';
            if (!empty(trim($p['contenu']))) {
                echo '<p class="post-content">' . nl2br($this->esc($p['contenu'])) . '</p>';
            }
            if (!empty($p['image_url'])) {
                echo '<img src="img/uploads/publications/' . $this->esc($p['image_url']) . '" class="post-image" style="width:100%; border-radius:12px; margin-top:12px; cursor:pointer;" onclick="window.open(this.src,\'_blank\')">';
            } elseif (!empty($p['photo_publication'])) {
                echo '<img src="img/uploads/publications/' . $this->esc($p['photo_publication']) . '" class="post-image" style="width:100%; border-radius:12px; margin-top:12px; cursor:pointer;" onclick="window.open(this.src,\'_blank\')">';
            }
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="feed-sidebar">';

        echo '<div class="sidebar-box">';
        echo '<h3>' . $this->t('new_members') . '</h3>';
        foreach ($nouveaux as $n) {
            echo '<a href="index.php?action=profil_voir&id=' . $n['id'] . '" class="sidebar-user">';
            $photo = $this->avatar($n['photo_profil'] ?? null);
            echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="mini-avatar" style="width:36px;height:36px;">';
            echo '<span>' . $this->esc($n['pseudonyme']) . '</span>';
            echo '</a>';
        }
        echo '</div>';

        if (!empty($eventsPublics)) {
            echo '<div class="sidebar-box">';
            echo '<h3>' . $this->t('upcoming_events') . '</h3>';
            foreach (array_slice($eventsPublics, 0, 5) as $e) {
                echo '<div class="sidebar-event">';
                echo '<strong>' . $this->esc($e['titre']) . '</strong>';
                echo '<span>' . date('d/m H:i', strtotime($e['date_debut'])) . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</section>';
    }

    public function afficherTutoriels()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('tutorials') . '</h2>';
        echo '<p class="section-intro">' . $this->t('tutorials_intro') . '</p>';
        echo '<div class="placeholder-grid">';
        $exemples = ['tutorials_ex1', 'tutorials_ex2', 'tutorials_ex3'];
        foreach ($exemples as $ex) {
            echo '<div class="placeholder-card">';
            echo '<div class="placeholder-icon">&#127891;</div>';
            echo '<h3>' . $this->t($ex . '_title') . '</h3>';
            echo '<p>' . $this->t($ex . '_desc') . '</p>';
            echo '</div>';
        }
        echo '</div></section>';
    }

    public function afficherAteliers()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('workshops') . '</h2>';
        echo '<p class="section-intro">' . $this->t('workshops_intro') . '</p>';
        echo '<div class="placeholder-grid">';
        $exemples = ['workshops_ex1', 'workshops_ex2', 'workshops_ex3'];
        foreach ($exemples as $ex) {
            echo '<div class="placeholder-card">';
            echo '<div class="placeholder-icon">&#128736;</div>';
            echo '<h3>' . $this->t($ex . '_title') . '</h3>';
            echo '<p>' . $this->t($ex . '_desc') . '</p>';
            echo '</div>';
        }
        echo '</div></section>';
    }

    public function afficherRessources()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('resources') . '</h2>';
        echo '<p class="section-intro">' . $this->t('resources_intro') . '</p>';
        echo '<div class="placeholder-grid">';
        $exemples = ['resources_ex1', 'resources_ex2', 'resources_ex3'];
        foreach ($exemples as $ex) {
            echo '<div class="placeholder-card">';
            echo '<div class="placeholder-icon">&#128218;</div>';
            echo '<h3>' . $this->t($ex . '_title') . '</h3>';
            echo '<p>' . $this->t($ex . '_desc') . '</p>';
            echo '</div>';
        }
        echo '</div></section>';
    }

    public function afficherForum()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('forum') . '</h2>';
        echo '<p class="section-intro">' . $this->t('forum_intro') . '</p>';
        echo '<div class="forum-coming-soon">';
        echo '<div class="coming-soon-icon">&#128172;</div>';
        echo '<p>' . $this->t('coming_soon') . '</p>';
        echo '</div></section>';
    }

    public function afficherShowcases()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('showcases') . '</h2>';
        echo '<p class="section-intro">' . $this->t('showcases_intro') . '</p>';
        echo '<div class="forum-coming-soon">';
        echo '<div class="coming-soon-icon">&#127912;</div>';
        echo '<p>' . $this->t('coming_soon') . '</p>';
        echo '</div></section>';
    }

    public function afficherPortfolio($user, $tags)
    {
        echo '<section class="page-section">';
        echo '<div class="portfolio-header">';
        $photo = $this->avatar($user['photo_profil'] ?? null);
        echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="profil-avatar">';
        echo '<h2>' . $this->esc($user['pseudonyme']) . '</h2>';
        echo '</div>';

        if (!empty($user['experiences_texte'])) {
            echo '<div class="profil-experiences">';
            echo '<h3>' . $this->t('experiences_section') . '</h3>';
            echo '<p>' . nl2br($this->esc($user['experiences_texte'])) . '</p>';
            echo '</div>';
        }

        if (!empty($tags)) {
            echo '<div class="profil-tags"><h3>' . $this->t('skills') . '</h3><div class="tags-list">';
            foreach ($tags as $t) {
                echo '<span class="tag-badge">' . $this->esc($t['nom']) . '</span>';
            }
            echo '</div></div>';
        }

        echo '<p class="portfolio-hint">' . $this->t('portfolio_hint') . '</p>';
        echo '</section>';
    }

    public function afficherEquipe()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('team') . '</h2>';
        echo '<p class="section-intro">' . $this->t('team_intro') . '</p>';
        echo '<div class="team-grid">';
        $roles = ['team_dev', 'team_design', 'team_comm'];
        $icons = ['&#128187;', '&#127912;', '&#128227;'];
        foreach ($roles as $i => $role) {
            echo '<div class="team-card">';
            echo '<div class="team-icon">' . $icons[$i] . '</div>';
            echo '<h3>' . $this->t($role . '_title') . '</h3>';
            echo '<p>' . $this->t($role . '_desc') . '</p>';
            echo '</div>';
        }
        echo '</div></section>';
    }

    public function afficherContact()
    {
        echo '<section class="page-section">';
        echo '<h2>' . $this->t('contact') . '</h2>';
        echo '<p class="section-intro">' . $this->t('contact_intro') . '</p>';
        echo '<div class="contact-info">';
        echo '<p>' . $this->t('contact_email_label') . ' : <strong>contact@crilay.org</strong></p>';
        echo '<p>' . $this->t('contact_discord') . '</p>';
        echo '</div></section>';
    }

    public function afficherPageStatique($titreCle, $texteCle)
    {
        echo '<section class="page-statique">';
        echo '<h2>' . $this->t($titreCle) . '</h2>';
        echo '<div class="static-content"><p>' . $this->t($texteCle) . '</p></div>';
        echo '</section>';
    }

    public function afficherFAQ()
    {
        echo '<section class="page-statique">';
        echo '<h2>' . $this->t('faq_title') . '</h2>';
        echo '<div class="faq-list">';

        $faqs = $this->getFAQData();
        foreach ($faqs as $faq) {
            echo '<details class="faq-item"><summary>' . $this->esc($faq['q']) . '</summary>';
            echo '<p>' . $this->esc($faq['a']) . '</p></details>';
        }
        echo '</div></section>';
    }

    private function getFAQData()
    {
        $lang = $this->lang();
        $faqs = [
            'en' => [
                ['q' => 'How does Crilay work?', 'a' => 'Sign up, describe your skills and experiences, then find other people to exchange with — whether it\'s cooking, DIY, languages or life stories.'],
                ['q' => 'Is it free?', 'a' => 'Yes, Crilay is completely free.'],
                ['q' => 'How do I contact someone?', 'a' => 'Send a first message which will serve as a connection request. If the person accepts, you will be able to exchange freely.'],
                ['q' => 'Is my email visible?', 'a' => 'No, your email address is never visible. The internal messaging system protects your contact details.'],
                ['q' => 'How do I organize an online meeting?', 'a' => 'Go to your calendar, create a "Shared" event and invite people from your network. You can also organize public events visible to everyone.'],
                ['q' => 'Who is it for?', 'a' => 'For everyone! Youth, adults, seniors — the goal is exactly to connect generations together.'],
            ],
            'fr' => [
                ['q' => 'Comment fonctionne Crilay ?', 'a' => 'Inscrivez-vous, décrivez vos compétences et expériences, puis trouvez d\'autres personnes pour échanger — que ce soit de la cuisine, du bricolage, des langues ou des récits de vie.'],
                ['q' => 'Est-ce gratuit ?', 'a' => 'Oui, Crilay est entièrement gratuit.'],
                ['q' => 'Comment contacter quelqu\'un ?', 'a' => 'Envoyez un premier message qui servira de demande de connexion. Si la personne accepte, vous pourrez échanger librement.'],
                ['q' => 'Mon email est-il visible ?', 'a' => 'Non, votre adresse email n\'est jamais visible. La messagerie interne protège vos coordonnées.'],
                ['q' => 'Comment organiser une rencontre en ligne ?', 'a' => 'Allez dans votre agenda, créez un événement "Partagé" et invitez des personnes de votre réseau. Vous pouvez aussi organiser des événements publics visibles par tous.'],
                ['q' => 'C\'est pour qui ?', 'a' => 'Pour tout le monde ! Jeunes, adultes, aînés — l\'objectif est justement de connecter les générations entre elles.'],
            ],
            'sq' => [
                ['q' => 'Si funksionon Crilay?', 'a' => 'Regjistrohuni, përshkruani aftësitë dhe përvojat tuaja, pastaj gjeni persona të tjera për shkëmbim — gatim, riparime, gjuhë ose tregime jete.'],
                ['q' => 'A është falas?', 'a' => 'Po, Crilay është plotësisht falas.'],
                ['q' => 'Si të kontaktoj dikë?', 'a' => 'Dërgoni një mesazh të parë si kërkesë lidhje. Nëse personi pranon, mund të shkëmbeni lirisht.'],
                ['q' => 'A është email im i dukshëm?', 'a' => 'Jo, adresa juaj email nuk është kurrë e dukshme. Mesazhet e brendshme mbrojnë të dhënat tuaja.'],
                ['q' => 'Si të organizoj një takim online?', 'a' => 'Shkoni në axhendë, krijoni një ngjarje "Të ndarë" dhe ftoni persona nga rrjeti juaj.'],
                ['q' => 'Për kë është?', 'a' => 'Për të gjithë! Të rinj, të rritur, të moshuar — qëllimi është pikërisht lidhja e brezave.'],
            ],
            'vi' => [
                ['q' => 'Crilay hoạt động như thế nào?', 'a' => 'Đăng ký, mô tả kỹ năng và kinh nghiệm, rồi tìm người để trao đổi — nấu ăn, sửa chữa, ngôn ngữ hay câu chuyện cuộc sống.'],
                ['q' => 'Có miễn phí không?', 'a' => 'Có, Crilay hoàn toàn miễn phí.'],
                ['q' => 'Làm sao liên hệ ai đó?', 'a' => 'Gửi tin nhắn đầu tiên như yêu cầu kết nối. Nếu họ chấp nhận, bạn trao đổi tự do.'],
                ['q' => 'Email có hiển thị không?', 'a' => 'Không. Tin nhắn nội bộ bảo vệ thông tin cá nhân của bạn.'],
                ['q' => 'Làm sao tổ chức gặp mặt trực tuyến?', 'a' => 'Vào lịch, tạo sự kiện "Chia sẻ" và mời người từ mạng lưới. Bạn cũng có thể tạo sự kiện công khai.'],
                ['q' => 'Dành cho ai?', 'a' => 'Cho tất cả mọi người! Trẻ, trưởng thành, người lớn tuổi — mục tiêu là kết nối các thế hệ.'],
            ]
        ];
        return $faqs[$lang] ?? $faqs['fr'];
    }

    private function champPays($selected = '')
    {
        $pays = ["Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Central African Republic","Chad","Chile","China","Colombia","Comoros","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Democratic Republic of the Congo","Denmark","Djibouti","Dominica","Dominican Republic","East Timor","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Ivory Coast","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway","Oman","Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Republic of the Congo","Romania","Russia","Rwanda","Saint Kitts and Nevis","Saint Lucia","Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Sweden","Switzerland","Syria","Tajikistan","Tanzania","Thailand","Togo","Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"];
        echo '<div class="form-group">';
        echo '<label for="nationalite">' . $this->t('nationality') . '</label>';
        echo '<select name="nationalite" id="nationalite">';
        echo '<option value="">—</option>';
        foreach ($pays as $p) {
            $sel = ($selected === $p) ? ' selected' : '';
            echo '<option value="' . $this->esc($p) . '"' . $sel . '>' . $this->esc($p) . '</option>';
        }
        echo '</select></div>';
    }

    private function champTexte($name, $labelCle, $required = false, $type = 'text', $value = '')
    {
        $req = $required ? ' required' : '';
        $star = $required ? ' <span class="required-star">*</span>' : '';
        echo '<div class="form-group">';
        echo '<label for="' . $name . '">' . $this->t($labelCle) . $star . '</label>';
        echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" value="' . $this->esc($value) . '"' . $req . '>';
        echo '</div>';
    }
}
