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

    private function renderTagsTree($tags, $checkedIds = [])
    {
        $tree = [
            'tagcat_plastic' => [
                'tagsub_painting' => ['oil_painting', 'watercolor', 'acrylic_gouache', 'digital_painting'],
                'tagsub_drawing' => ['pencil_sketching', 'charcoal', 'ink_line_art', 'realism', 'perspective', 'anatomy', 'landscapes'],
                'tagsub_sculpture' => ['sculpting', 'pottery', 'woodwork'],
            ],
            'tagcat_narrative' => [
                'tagsub_graphic_narr' => ['comics', 'manga', 'caricature', 'storyboarding'],
                'tagsub_animation' => ['animation', 'motion_design', 'stopmotion'],
            ],
            'tagcat_design' => [
                'tagsub_graphic_design' => ['logo_design', 'vector_art', 'digital_illustration'],
                'tagsub_technical' => ['3d_modelling', 'character_design'],
            ],
            'tagcat_craft' => [
                'tagsub_paper' => ['origami', 'paper_quilling', 'journalling'],
                'tagsub_textile' => ['textile', 'handicrafts', 'mixed_crafts'],
                'tagsub_assembly' => ['collage', 'mixed_media', 'printmaking'],
            ],
            'tagcat_visual' => [
                'tagsub_captured' => ['photography'],
                'tagsub_conceptual' => ['abstract'],
            ],
        ];

        $tagsByKey = [];
        foreach ($tags as $t) $tagsByKey[$t['cle']] = $t;

        echo '<div class="tags-tree">';
        foreach ($tree as $catKey => $subcats) {
            echo '<details class="tag-category"><summary>' . $this->esc($this->t($catKey)) . '</summary>';
            foreach ($subcats as $subKey => $keys) {
                echo '<div class="tag-subcategory"><span class="tag-subcat-title">' . $this->esc($this->t($subKey)) . '</span>';
                echo '<div class="tags-grid">';
                foreach ($keys as $key) {
                    if (!isset($tagsByKey[$key])) continue;
                    $t = $tagsByKey[$key];
                    $checked = in_array($t['id'], $checkedIds) ? ' checked' : '';
                    echo '<label class="tag-label"><input type="checkbox" name="tags[]" value="' . $t['id'] . '"' . $checked . '> ' . $this->esc($t['nom']) . '</label>';
                }
                echo '</div></div>';
            }
            echo '</details>';
        }
        echo '</div>';
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

        $navClass = $connecte ? 'connected' : 'disconnected';
        echo '<header class="app-topnav ' . $navClass . '">';
        echo '<div class="topnav-inner">';

        echo '<a href="index.php?lang=' . $lang . '" class="topnav-logo" style="margin-left: -15px;">';
        echo '<img src="img/LOGO.png" alt="Crilay Logo" style="height: 32px; width: auto; margin-right: 8px; vertical-align: middle;">';
        echo '<span class="topnav-logo-name">Crilay</span>';
        echo '</a>';

        echo '<nav class="topnav-nav">';
        if ($connecte) {
            $navItems = [
                ['action' => 'feed', 'label' => $this->t('feed'), 'icon' => ''],
                ['action' => 'messages', 'label' => $this->t('messages'), 'icon' => ''],
                ['action' => 'agenda', 'label' => $this->t('events'), 'icon' => ''],
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

            echo '<form action="index.php" method="GET" class="topnav-search" style="margin-left:8px;">';
            echo '<input type="hidden" name="action" value="recherche">';
            echo '<input type="hidden" name="lang" value="' . $lang . '">';
            echo '<span class="topnav-search-icon"><img src="img/loupe.png" alt=""></span>';
            echo '<input type="text" name="q" placeholder="' . $this->t('search') . '...">';
            echo '</form>';
        } else {
            $pubItems = [
                ['action' => 'apropos', 'label' => $this->t('nav_about')],
                ['action' => 'equipe', 'label' => $this->t('team')],
                ['action' => 'faq', 'label' => $this->t('faq')],
            ];
            foreach ($pubItems as $item) {
                $isActive = ($action === $item['action']) ? ' active' : '';
                echo '<a href="index.php?action=' . $item['action'] . '&lang=' . $lang . '" class="tnav-item' . $isActive . '">';
                echo '<span>' . $item['label'] . '</span>';
                echo '</a>';
            }
        }
        echo '</nav>';

        echo '<div class="topnav-actions">';

        if ($connecte) {
            $isActive = ($action === 'profil') ? ' active' : '';
            echo '<a href="index.php?action=profil&lang=' . $lang . '" class="tnav-item' . $isActive . '" style="padding: 0 10px; min-height: 42px;">';
            echo '<img src="img/profile.png" alt="Profile" style="height: 24px;">';
            echo '</a>';
        }

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
            echo '<div class="mobile-menu-btn" onclick="document.getElementById(\'mobile-connected-nav\').classList.toggle(\'show\')" style="display:none;font-size:24px;cursor:pointer;margin-right:8px;">&#9776;</div>';
            echo '<a href="index.php?action=deconnexion" class="btn-logout" title="' . $this->t('logout') . '" style="display:flex; align-items:center; justify-content:center; background:none; border:none; padding:8px; outline:none; height:42px;"><img src="img/se-deco.png" alt="Logout" style="height:24px;"></a>';
        } else {
            echo '<div class="mobile-menu-btn" onclick="document.getElementById(\'mobile-pub-nav\').classList.toggle(\'show\')" style="display:none;font-size:24px;cursor:pointer;margin-right:8px;">&#9776;</div>';

            echo '<a href="index.php?action=inscription&lang=' . $lang . '" class="topnav-cta"><span>' . $this->t('signup') . '</span></a>';
            echo '<a href="index.php?action=connexion&lang=' . $lang . '" class="btn-secondary btn-pill" style="padding:8px 18px;font-size:14px;min-height:42px;">' . $this->t('login') . '</a>';
        }

        echo '<button id="theme-toggle" class="btn-theme-toggle" aria-label="Toggle theme">&#9728;</button>';
        echo '</div>';

        if (!$connecte) {
            echo '<div id="mobile-pub-nav" class="mobile-pub-nav">';
            foreach ($pubItems as $item) {
                echo '<a href="index.php?action=' . $item['action'] . '&lang=' . $lang . '">' . $item['label'] . '</a>';
            }
            echo '</div>';
        }

        if ($connecte) {
            echo '<div id="mobile-connected-nav" class="mobile-pub-nav">';
            $mobileItems = [
                ['action' => 'feed', 'label' => $this->t('feed')],
                ['action' => 'messages', 'label' => $this->t('messages') . ($nonLus > 0 ? ' <span class="tnav-badge">' . $nonLus . '</span>' : '')],
                ['action' => 'agenda', 'label' => $this->t('events')],
                ['action' => 'profil', 'label' => $this->t('profile') ?? 'Profil'],
                ['action' => 'recherche', 'label' => $this->t('search')],
            ];
            foreach ($mobileItems as $item) {
                echo '<a href="index.php?action=' . $item['action'] . '&lang=' . $lang . '">' . $item['label'] . '</a>';
            }
            echo '</div>';
        }

        echo '</div></header>';

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
        $icons = ['<img src="img/peinture.png" alt="" style="height:1.4em;vertical-align:middle;">', '<img src="img/loupe.png" alt="" style="height:2em;vertical-align:middle;">', '&#129309;'];
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
        echo '<div id="signup-error-1" class="alert alert-error" style="display:none;"></div>';
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
        $this->renderTagsTree($tags);
        echo '</div>';

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
            if (current === 1) {
                var pw = document.querySelector("[name=mot_de_passe]");
                var pwc = document.querySelector("[name=mot_de_passe_confirm]");
                var errDiv = document.getElementById("signup-error-1");
                errDiv.style.display = "none";
                if (pw.value.length < 8) { errDiv.textContent = "' . $this->t('error_password_short') . '"; errDiv.style.display = "block"; pw.focus(); return; }
                if (pw.value !== pwc.value) { errDiv.textContent = "' . $this->t('error_password_mismatch') . '"; errDiv.style.display = "block"; pwc.focus(); return; }
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

    public function afficherProfil($user, $tags, $estMoi = false, $relationAmitie = null, $amis = [], $publications = [], $estAbonne = false, $nbAbonnes = 0)
    {
        $age = (new Utilisateur(null))->calculerAge($user['date_naissance'] ?? null);
        $lang = $this->lang();
        $photo = $this->avatar($user['photo_profil'] ?? null);
        $nbConnexions = count($amis);
        $nbPublications = count($publications);

        echo '<section class="profil-section">';

        // ── Card principale ──
        echo '<div class="profil-card profil-card-new">';

        // Header: avatar + infos + boutons
        echo '<div class="profil-header-new">';
        echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="Avatar" class="profil-avatar-new">';
        echo '<div class="profil-header-content">';

        // Nom + boutons d'action sur la même ligne
        echo '<div class="profil-name-actions">';
        echo '<h2 class="profil-name">' . $this->esc($user['pseudonyme']) . '</h2>';
        echo '<div class="profil-actions">';
        if ($estMoi) {
            echo '<a href="index.php?action=profil_edit&lang=' . $lang . '" class="profil-btn-secondary">&#9998; ' . $this->t('edit_profile') . '</a>';
        } else {
            if ($estAbonne) {
                echo '<a href="index.php?action=toggle_abo&id=' . $user['id'] . '&lang=' . $lang . '" class="profil-btn-follow following"><svg viewBox="0 0 24 24" width="16" height="16" style="vertical-align:middle;margin-right:4px;"><path fill="#fff" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>' . $this->t('following') . '</a>';
            } else {
                echo '<a href="index.php?action=toggle_abo&id=' . $user['id'] . '&lang=' . $lang . '" class="profil-btn-follow"><svg viewBox="0 0 24 24" width="16" height="16" style="vertical-align:middle;margin-right:4px;"><path fill="#fff" d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z"/></svg>' . $this->t('follow') . '</a>';
            }
            echo '<a href="index.php?action=ecrire&dest=' . $user['id'] . '&lang=' . $lang . '" class="profil-btn-secondary">&#128172; ' . $this->t('send_message') . '</a>';
            if ($relationAmitie === null) {
                echo '<a href="index.php?action=demande_ami&id=' . $user['id'] . '&lang=' . $lang . '" class="profil-btn-primary">&#128279; ' . $this->t('add_friend') . '</a>';
            } elseif ($relationAmitie['statut'] === 'en_attente') {
                echo '<span class="profil-btn-pending">&#9203; ' . $this->t('pending') . '</span>';
            } elseif ($relationAmitie['statut'] === 'accepte') {
                echo '<span class="profil-btn-connected">&#10003; ' . $this->t('friends') . '</span>';
            }
        }
        echo '</div>'; // .profil-actions
        echo '</div>'; // .profil-name-actions

        // Métadonnées : langues, âge
        echo '<div class="profil-meta">';
        if (!empty($user['nationalite']))
            echo '<span class="profil-meta-item"><svg viewBox="0 0 24 24" width="14" height="14" class="profil-meta-icon"><path fill="#fff" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg> ' . $this->esc($user['nationalite']) . '</span>';
        if (!empty($user['langues_parlees'])) {
            $languesList = explode(',', $user['langues_parlees']);
            $languesAbbr = [];
            foreach ($languesList as $l) {
                $l = trim($l);
                if ($l) $languesAbbr[] = mb_strtoupper(mb_substr($l, 0, 2));
            }
            echo '<span class="profil-meta-item"><svg viewBox="0 0 24 24" width="14" height="14" class="profil-meta-icon"><path fill="#fff" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2s.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 0 1 5.08 16zm2.95-8H5.08a7.987 7.987 0 0 1 4.33-3.56A15.65 15.65 0 0 0 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2s.07-1.35.16-2h4.68c.09.65.16 1.32.16 2s-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2s-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/></svg> ' . implode(' ', $languesAbbr) . '</span>';
        }
        if ($age !== null)
            echo '<span class="profil-meta-item"><svg viewBox="0 0 24 24" width="14" height="14" class="profil-meta-icon"><path fill="#fff" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg> ' . $age . ' ' . $this->t('years_old') . '</span>';
        echo '</div>'; // .profil-meta

        echo '</div>'; // .profil-header-content
        echo '</div>'; // .profil-header-new

        // Bio
        if (!empty($user['bio'])) {
            echo '<div class="profil-bio-new"><p>' . nl2br($this->esc($user['bio'])) . '</p></div>';
        }

        // Tags
        if (!empty($tags)) {
            echo '<div class="profil-tags-new">';
            $tagColors = [
                ['color' => '#E68641', 'bg' => 'rgba(230, 134, 65, 0.15)', 'border' => 'rgba(230, 134, 65, 0.4)'],
                ['color' => '#7DA27E', 'bg' => 'rgba(125, 162, 126, 0.15)', 'border' => 'rgba(125, 162, 126, 0.4)'],
                ['color' => '#E3B505', 'bg' => 'rgba(227, 181, 5, 0.15)', 'border' => 'rgba(227, 181, 5, 0.4)'],
                ['color' => '#c4b0f5', 'bg' => 'rgba(196, 176, 245, 0.15)', 'border' => 'rgba(196, 176, 245, 0.4)'],
                ['color' => '#EF7B73', 'bg' => 'rgba(239, 123, 115, 0.15)', 'border' => 'rgba(239, 123, 115, 0.4)'],
                ['color' => '#6CC4E0', 'bg' => 'rgba(108, 196, 224, 0.15)', 'border' => 'rgba(108, 196, 224, 0.4)'],
            ];
            foreach ($tags as $i => $t) {
                $c = $tagColors[$i % count($tagColors)];
                echo '<span class="profil-tag-pill" style="color:' . $c['color'] . '; background:' . $c['bg'] . '; border-color:' . $c['border'] . ';">' . $this->esc($t['nom']) . '</span>';
            }
            echo '</div>';
        }

        // Stats row
        echo '<div class="profil-stats-row">';
        echo '<div class="profil-stat-item">';
        echo '<span class="profil-stat-val">' . $nbPublications . '</span>';
        echo '<span class="profil-stat-label">Publications</span>';
        echo '</div>';
        echo '<a href="index.php?action=followers&id=' . $user['id'] . '&lang=' . $lang . '" class="profil-stat-item profil-stat-link">';
        echo '<span class="profil-stat-val">' . $nbAbonnes . '</span>';
        echo '<span class="profil-stat-label">' . $this->t('followers') . '</span>';
        echo '</a>';
        echo '</div>'; // .profil-stats-row

        echo '</div>'; // .profil-card-new

        // ── Tabs Galerie / Texte / À propos ──
        $mediaPubs = array_filter($publications, function($p) { return !empty($p['image_url']); });
        $textPubs = array_filter($publications, function($p) { return empty($p['image_url']); });

        echo '<div class="profil-tabs-card">';
        echo '<div class="profil-tabs-nav">';
        echo '<button class="profil-tab active" data-tab="gallery" onclick="switchProfilTab(\'gallery\')">' . $this->t('gallery') . '</button>';
        echo '<button class="profil-tab" data-tab="textposts" onclick="switchProfilTab(\'textposts\')">' . $this->t('text_posts') . '</button>';
        echo '<button class="profil-tab" data-tab="apropos" onclick="switchProfilTab(\'apropos\')">' . $this->t('nav_about') . '</button>';
        echo '</div>';

        // Tab: Galerie (médias uniquement)
        echo '<div class="profil-tab-content" id="profil-tab-gallery">';
        if (empty($mediaPubs)) {
            echo '<p class="no-results">' . $this->t('no_results') . '</p>';
        } else {
            echo '<div class="profil-gallery-grid">';
            foreach ($mediaPubs as $p) {
                echo '<a href="index.php?action=publication&id=' . $p['id'] . '&lang=' . $lang . '" class="profil-gallery-item">';
                echo '<img src="img/uploads/publications/' . $this->esc($p['image_url']) . '" alt="">';
                echo '<div class="profil-gallery-overlay"><span>&#10084; ' . ($p['nb_likes'] ?? 0) . '</span></div>';
                echo '</a>';
            }
            echo '</div>';
        }
        echo '</div>';

        // Tab: Posts texte
        echo '<div class="profil-tab-content" id="profil-tab-textposts" style="display:none;">';
        if (empty($textPubs)) {
            echo '<p class="no-results">' . $this->t('no_results') . '</p>';
        } else {
            foreach ($textPubs as $p) {
                echo '<a href="index.php?action=publication&id=' . $p['id'] . '&lang=' . $lang . '" class="profil-textpost">';
                echo '<p>' . mb_strimwidth($this->esc($p['contenu']), 0, 200, '...') . '</p>';
                echo '<div class="profil-textpost-meta">';
                echo '<span>&#10084; ' . ($p['nb_likes'] ?? 0) . '</span>';
                echo '<span>' . date('d/m/Y', strtotime($p['date_publication'])) . '</span>';
                echo '</div>';
                echo '</a>';
            }
        }
        echo '</div>';

        // Tab: À propos
        echo '<div class="profil-tab-content" id="profil-tab-apropos" style="display:none;">';
        echo '<div class="profil-about-content">';

        if (!empty($user['bio'])) {
            echo '<div class="profil-about-block">';
            echo '<h3><svg viewBox="0 0 24 24" width="18" height="18" class="about-icon"><path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg> Bio</h3>';
            echo '<p>' . nl2br($this->esc($user['bio'])) . '</p>';
            echo '</div>';
        }

        if (!empty($user['experiences_texte'])) {
            echo '<div class="profil-about-block">';
            echo '<h3><svg viewBox="0 0 24 24" width="18" height="18" class="about-icon"><path fill="currentColor" d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg> ' . $this->t('experiences_section') . '</h3>';
            echo '<p>' . nl2br($this->esc($user['experiences_texte'])) . '</p>';
            echo '</div>';
        }

        echo '<div class="profil-about-block">';
        echo '<h3><svg viewBox="0 0 24 24" width="18" height="18" class="about-icon"><path fill="currentColor" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/></svg> ' . $this->t('member_since') . '</h3>';
        echo '<p>' . date('d/m/Y', strtotime($user['date_inscription'])) . '</p>';
        echo '</div>';

        if (!empty($user['langues_parlees'])) {
            echo '<div class="profil-about-block">';
            echo '<h3><svg viewBox="0 0 24 24" width="18" height="18" class="about-icon"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2s.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 0 1 5.08 16zm2.95-8H5.08a7.987 7.987 0 0 1 4.33-3.56A15.65 15.65 0 0 0 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2s.07-1.35.16-2h4.68c.09.65.16 1.32.16 2s-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2s-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/></svg> ' . $this->t('spoken_languages') . '</h3>';
            $languesList = explode(',', $user['langues_parlees']);
            echo '<div class="profil-about-langues">';
            foreach ($languesList as $l) {
                $l = trim($l);
                if ($l) echo '<span class="profil-about-langue-badge">' . $this->esc($l) . '</span>';
            }
            echo '</div>';
            echo '</div>';
        }

        echo '</div>'; // .profil-about-content
        echo '</div>'; // #profil-tab-apropos

        echo '</div>'; // .profil-tabs-card

        // Tab switching JS
        echo '<script>
        function switchProfilTab(tab) {
            document.querySelectorAll(".profil-tab-content").forEach(function(el) { el.style.display = "none"; });
            document.querySelectorAll(".profil-tab").forEach(function(el) { el.classList.remove("active"); });
            document.getElementById("profil-tab-" + tab).style.display = "block";
            document.querySelector(".profil-tab[data-tab=\'" + tab + "\']").classList.add("active");
        }
        </script>';

        echo '</section>';
    }

    public function afficherEditProfil($user, $userTags, $allTags)
    {
        echo '<section class="form-section">';
        echo '<h2>' . $this->t('edit_profile') . '</h2>';
        echo '<form method="POST" action="index.php?action=profil_update" enctype="multipart/form-data" class="form-card">';

        echo '<div class="form-row">';
        $this->champTexte('pseudonyme', 'pseudo', true, 'text', $user['pseudonyme']);
        $this->champPays($user['nationalite'] ?? '');
        echo '</div>';

        echo '<div class="form-row">';
        $this->champTexte('date_naissance', 'birthdate', false, 'date', $user['date_naissance']);
        $this->champTexte('mot_de_passe', 'password', false, 'password');
        echo '</div>';

        echo '<div class="form-row">';
        echo '<div class="form-group"><label for="bio">' . $this->t('bio') . '</label>';
        echo '<textarea name="bio" id="bio" rows="3">' . $this->esc($user['bio'] ?? '') . '</textarea></div>';

        echo '<div class="form-group"><label for="experiences_texte">' . $this->t('experiences_texte') . '</label>';
        echo '<textarea name="experiences_texte" id="experiences_texte" rows="3" placeholder="' . $this->t('experiences_placeholder') . '">' . $this->esc($user['experiences_texte'] ?? '') . '</textarea></div>';
        echo '</div>';

        echo '<div class="form-row">';
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

        echo '<div class="form-group"><label>' . $this->t('photo') . '</label>';
        echo '<input type="file" name="photo" accept="image/*"></div>';
        echo '</div>';

        echo '<input type="hidden" name="latitude" id="latitude" value="' . ($user['latitude'] ?? '') . '">';
        echo '<input type="hidden" name="longitude" id="longitude" value="' . ($user['longitude'] ?? '') . '">';

        $userTagIds = array_column($userTags, 'id');
        echo '<div class="form-group"><label>' . $this->t('select_tags') . '</label>';
        $this->renderTagsTree($allTags, $userTagIds);
        echo '</div>';

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

    public function afficherFollowers($abonnes, $cibleId, $userId)
    {
        $lang = $this->lang();
        echo '<section class="amis-section">';
        echo '<h2>' . $this->t('followers') . ' (' . count($abonnes) . ')</h2>';
        if (empty($abonnes)) {
            echo '<p class="no-results">' . $this->t('no_followers') . '</p>';
        } else {
            echo '<div class="users-grid">';
            foreach ($abonnes as $a) {
                echo '<div class="user-card">';
                $photo = $this->avatar($a['photo_profil'] ?? null);
                echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="user-card-avatar">';
                echo '<h3>' . $this->esc($a['pseudonyme']) . '</h3>';
                echo '<div class="user-card-actions">';
                echo '<a href="index.php?action=profil_voir&id=' . $a['id'] . '&lang=' . $lang . '" class="btn-small btn-outline">' . $this->t('view_profile') . '</a>';
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
            echo '<div id="preview_msg" style="display:none; padding:8px 24px; flex-shrink:0;"><img src="" style="max-height:80px; border-radius:8px; border: 2px solid var(--orange);"></div>';
            echo '<form method="POST" action="index.php?action=envoyer_msg&dest=' . $contact['id'] . '" class="chat-form" enctype="multipart/form-data">';
            echo '<label for="file_msg" style="cursor:pointer; display:flex; align-items:center; font-size:22px; color:var(--orange);">+</label>';
            echo '<input type="file" id="file_msg" name="piece_jointe" accept="image/*" style="display:none;" onchange="previewImg(this, \'preview_msg\')">';
            echo '<textarea name="contenu" placeholder="' . $this->t('type_message') . '"></textarea>';
            echo '<button type="submit" class="btn-primary">' . $this->t('send') . '</button>';
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
            var chatBox = document.querySelector(".chat-messages");
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
            </script>';
        } else {
            echo '<div class="alert alert-info" style="margin:0 24px 12px; flex-shrink:0;">' . $this->t('must_connect_first') . '</div>';
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

    public function afficherPublication($pub, $commentaires, $userId)
    {
        $lang = $this->lang();
        $photo = $this->avatar($pub['photo_profil'] ?? null);
        $liked = !empty($pub['user_liked']);
        $nbLikes = (int)($pub['nb_likes'] ?? 0);
        $heart = $liked
            ? '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="#E68641" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
            : '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="#ccc" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>';

        echo '<section class="pub-detail-section">';
        echo '<a href="javascript:history.back()" class="btn-back">&larr;</a>';

        echo '<div class="pub-detail-card">';
        // Header
        echo '<div class="pub-detail-header">';
        echo '<img src="img/avatars/' . $this->esc($photo) . '" alt="" class="mini-avatar">';
        echo '<div>';
        echo '<a href="index.php?action=profil_voir&id=' . $pub['auteur_id'] . '" class="post-author">' . $this->esc($pub['pseudonyme']) . '</a>';
        echo '<span class="post-date">' . date('d/m/Y H:i', strtotime($pub['date_publication'])) . '</span>';
        echo '</div></div>';

        // Media
        if (!empty($pub['image_url'])) {
            echo '<img src="img/uploads/publications/' . $this->esc($pub['image_url']) . '" class="pub-detail-img">';
        }

        // Content
        if (!empty(trim($pub['contenu']))) {
            echo '<p class="pub-detail-content">' . nl2br($this->esc($pub['contenu'])) . '</p>';
        }

        // Like + actions
        echo '<div class="pub-detail-actions">';
        echo '<a href="#" onclick="toggleLike(this, ' . $pub['id'] . '); return false;" style="text-decoration:none; display:inline-flex; transition:transform 0.2s;">' . $heart . '</a>';
        echo '<span class="like-count-' . $pub['id'] . '">' . $nbLikes . '</span>';
        if ($pub['auteur_id'] == $userId) {
            echo '<div style="margin-left:auto; display:flex; gap:8px;">';
            if (!empty($pub['image_url'])) {
                echo '<a href="index.php?action=supprimer_image_pub&id=' . $pub['id'] . '&lang=' . $lang . '" class="btn-delete-small" onclick="return confirm(\'' . $this->t('confirm_delete') . '\')">' . $this->t('delete_image') . '</a>';
            }
            echo '<a href="index.php?action=supprimer_pub&id=' . $pub['id'] . '&lang=' . $lang . '" class="btn-delete-small btn-delete-danger" onclick="return confirm(\'' . $this->t('confirm_delete') . '\')">' . $this->t('delete') . '</a>';
            echo '</div>';
        }
        echo '</div>';

        // Commentaires
        echo '<div class="pub-detail-comments">';
        foreach ($commentaires as $c) {
            $cPhoto = $this->avatar($c['photo_profil'] ?? null);
            echo '<div class="pub-detail-comment">';
            echo '<img src="img/avatars/' . $this->esc($cPhoto) . '" alt="" class="mini-avatar" style="width:28px;height:28px;">';
            echo '<div>';
            echo '<a href="index.php?action=profil_voir&id=' . $c['auteur_id'] . '" class="comment-author">' . $this->esc($c['pseudonyme']) . '</a>';
            echo '<p>' . nl2br($this->esc($c['contenu'])) . '</p>';
            echo '<span class="msg-date">' . date('d/m H:i', strtotime($c['date_commentaire'])) . '</span>';
            echo '</div></div>';
        }
        echo '</div>';

        // Form commentaire
        echo '<form method="POST" action="index.php?action=commenter" class="pub-detail-comment-form">';
        echo '<input type="hidden" name="publication_id" value="' . $pub['id'] . '">';
        echo '<input type="text" name="contenu" placeholder="' . $this->t('type_message') . '..." class="comment-input" style="flex:1;">';
        echo '<button type="submit" class="comment-send-btn">&#10148;</button>';
        echo '</form>';

        echo '</div>'; // .pub-detail-card
        echo '</section>';

        echo '<script>
        function toggleLike(el, id) {
            el.style.transform = "scale(1.4)";
            setTimeout(function(){ el.style.transform = "scale(1)"; }, 250);
            fetch("index.php?action=liker&id=" + id + "&ajax=1")
            .then(function(r){ return r.json(); })
            .then(function(d){
                var svg = d.liked
                    ? \'<svg viewBox="0 0 24 24" width="24" height="24"><path fill="#E68641" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>\'
                    : \'<svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="#ccc" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>\';
                el.innerHTML = svg;
                document.querySelector(".like-count-" + id).textContent = d.nb;
            });
        }
        </script>';
    }

    public function afficherFeed($publications, $nouveaux, $eventsPublics, $commentCounts = [])
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

        echo '<script>
        function toggleLike(el, id) {
            el.style.transform = "scale(1.4)";
            setTimeout(function(){ el.style.transform = "scale(1)"; }, 250);
            fetch("index.php?action=liker&id=" + id + "&ajax=1")
            .then(function(r){ return r.json(); })
            .then(function(d){
                var svg = d.liked
                    ? \'<svg viewBox="0 0 24 24" width="28" height="28"><path fill="#E68641" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>\'
                    : \'<svg viewBox="0 0 24 24" width="28" height="28"><path fill="none" stroke="#ccc" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>\';
                el.innerHTML = svg;
                document.querySelector(".like-count-" + id).textContent = d.nb;
            });
        }

        function toggleComments(id) {
            var box = document.getElementById("comments-box-" + id);
            if (box.style.display === "none") {
                box.style.display = "block";
                loadComments(id);
            } else {
                box.style.display = "none";
            }
        }

        function loadComments(id) {
            var list = document.getElementById("comments-list-" + id);
            list.innerHTML = "<p style=\"text-align:center; color:var(--text-dim); font-size:13px;\">...</p>";
            fetch("index.php?action=get_commentaires&id=" + id + "&ajax=1")
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.length === 0) {
                    list.innerHTML = "<p style=\"text-align:center; color:var(--text-dim); font-size:13px; padding:8px 0;\">' . $this->t('no_results') . '</p>";
                    return;
                }
                var html = "";
                data.forEach(function(c){
                    html += "<div class=\"comment-item\">";
                    html += "<img src=\"img/avatars/" + c.photo + "\" alt=\"\" class=\"comment-avatar\">";
                    html += "<div class=\"comment-body\">";
                    html += "<a href=\"index.php?action=profil_voir&id=" + c.auteur_id + "\" class=\"comment-author\">" + c.pseudonyme + "</a>";
                    html += "<p class=\"comment-text\">" + c.contenu + "</p>";
                    html += "<span class=\"comment-date\">" + c.date + "</span>";
                    html += "</div></div>";
                });
                list.innerHTML = html;
            });
        }

        function submitComment(id) {
            var input = document.getElementById("comment-input-" + id);
            var contenu = input.value.trim();
            if (!contenu) return;
            input.disabled = true;
            var formData = new FormData();
            formData.append("publication_id", id);
            formData.append("contenu", contenu);
            fetch("index.php?action=commenter_ajax", { method: "POST", body: formData })
            .then(function(r){ return r.json(); })
            .then(function(d){
                input.value = "";
                input.disabled = false;
                loadComments(id);
                var countEl = document.querySelector(".comment-count-" + id);
                if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;
            })
            .catch(function(){ input.disabled = false; });
        }
        </script>';

        if (empty($publications)) {
            echo '<p class="no-results">' . $this->t('no_posts') . '</p>';
        }
        foreach ($publications as $p) {
            $nbComments = isset($commentCounts[$p['id']]) ? (int)$commentCounts[$p['id']] : 0;

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
            $liked = !empty($p['user_liked']);
            $nbLikes = (int)($p['nb_likes'] ?? 0);
            $heart = $liked
                ? '<svg viewBox="0 0 24 24" width="28" height="28"><path fill="#E68641" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
                : '<svg viewBox="0 0 24 24" width="28" height="28"><path fill="none" stroke="#ccc" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>';

            echo '<div class="post-actions" style="margin-top:10px; display:flex; align-items:center; justify-content:flex-end; gap:12px;">';

            // Comment button
            echo '<button class="comment-toggle-btn" onclick="toggleComments(' . $p['id'] . ')" title="Commentaires">';
            echo '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="#ccc" stroke-width="2" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
            echo '<span class="comment-count-' . $p['id'] . '">' . $nbComments . '</span>';
            echo '</button>';

            // Like button
            echo '<span class="like-count-' . $p['id'] . '" style="font-size:14px; color:#ccc;">' . $nbLikes . '</span>';
            echo '<a href="#" class="like-btn" data-id="' . $p['id'] . '" onclick="toggleLike(this, ' . $p['id'] . '); return false;" style="text-decoration:none; display:inline-flex; transition: transform 0.2s ease;">' . $heart . '</a>';

            // Delete button (own posts)
            if (isset($_SESSION['user_id']) && $p['auteur_id'] == $_SESSION['user_id']) {
                echo '<a href="index.php?action=supprimer_pub&id=' . $p['id'] . '&lang=' . $this->lang() . '" class="btn-delete-small btn-delete-danger" onclick="return confirm(\'' . $this->t('confirm_delete') . '\')" style="margin-left:4px;">&#128465;</a>';
            }
            echo '</div>';

            // Comments section (hidden by default)
            echo '<div id="comments-box-' . $p['id'] . '" class="comments-box" style="display:none;">';
            echo '<div id="comments-list-' . $p['id'] . '" class="comments-list"></div>';
            echo '<div class="comment-form-inline">';
            echo '<input type="text" id="comment-input-' . $p['id'] . '" class="comment-input" placeholder="' . $this->t('type_message') . '..." onkeydown="if(event.key===\'Enter\'){submitComment(' . $p['id'] . ');}">';
            echo '<button type="button" class="comment-send-btn" onclick="submitComment(' . $p['id'] . ')">&#10148;</button>';
            echo '</div>';
            echo '</div>';

            echo '</div>'; // .post-card
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
        echo '<div class="coming-soon-icon"><img src="img/peinture.png" alt="" style="height:1.4em;vertical-align:middle;"></div>';
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
        $icons = ['&#128187;', '<img src="img/peinture.png" alt="" style="height:1.4em;vertical-align:middle;">', '&#128227;'];
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
