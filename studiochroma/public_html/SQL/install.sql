CREATE DATABASE IF NOT EXISTS studiochroma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studiochroma;

CREATE TABLE IF NOT EXISTS langues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(5) NOT NULL UNIQUE,
    nom VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO langues (code, nom) VALUES
('fr', 'Français'),
('sq', 'Shqip'),
('vi', 'Tiếng Việt');

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudonyme VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(191) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    photo_profil VARCHAR(255) DEFAULT 'default.webp',
    langues_parlees VARCHAR(255) DEFAULT '',
    nationalite VARCHAR(100) DEFAULT '',
    date_naissance DATE DEFAULT NULL,
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    ville VARCHAR(100) DEFAULT '',
    bio TEXT DEFAULT NULL,
    experiences_texte TEXT DEFAULT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    cgu_acceptees TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags_traductions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_id INT NOT NULL,
    langue_code VARCHAR(5) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tag_langue (tag_id, langue_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS utilisateur_tags (
    utilisateur_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (utilisateur_id, tag_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS amis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id_1 INT NOT NULL,
    utilisateur_id_2 INT NOT NULL,
    statut ENUM('en_attente', 'accepte', 'refuse') DEFAULT 'en_attente',
    demandeur_id INT NOT NULL,
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_reponse DATETIME DEFAULT NULL,
    FOREIGN KEY (utilisateur_id_1) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id_2) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (demandeur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_amitie (utilisateur_id_1, utilisateur_id_2),
    CHECK (utilisateur_id_1 < utilisateur_id_2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    contenu TEXT NOT NULL,
    piece_jointe VARCHAR(255) DEFAULT NULL,
    lu TINYINT(1) DEFAULT 0,
    est_invitation TINYINT(1) DEFAULT 0,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisateur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    type ENUM('prive', 'partage', 'public') NOT NULL DEFAULT 'prive',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evenement_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    statut ENUM('en_attente', 'accepte', 'refuse') DEFAULT 'en_attente',
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (evenement_id, utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auteur_id INT NOT NULL,
    contenu TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publication_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_like DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (publication_id, utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publication_id INT NOT NULL,
    auteur_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS abonnements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    abonne_id INT NOT NULL,
    cible_id INT NOT NULL,
    date_abonnement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (abonne_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cible_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_abo (abonne_id, cible_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tags (cle) VALUES
('oil_painting'), ('3d_modelling'), ('origami'), ('storyboarding'), ('pottery'),
('vector_art'), ('sculpting'), ('motion_design'), ('woodwork'), ('character_design'),
('ink_line_art'), ('mixed_crafts'), ('watercolor'), ('paper_quilling'), ('journalling'),
('realism'), ('photography'), ('caricature'), ('digital_illustration'), ('manga'),
('digital_painting'), ('abstract'), ('acrylic_gouache'), ('pencil_sketching'), ('animation'),
('charcoal'), ('collage'), ('printmaking'), ('textile'), ('comics'),
('handicrafts'), ('perspective'), ('stopmotion'), ('anatomy'), ('mixed_media'),
('landscapes'), ('logo_design');

INSERT INTO tags_traductions (tag_id, langue_code, nom) VALUES
((SELECT id FROM tags WHERE cle='oil_painting'), 'fr', 'Peinture à l''huile'),
((SELECT id FROM tags WHERE cle='3d_modelling'), 'fr', 'Modélisation 3D'),
((SELECT id FROM tags WHERE cle='origami'), 'fr', 'Origami'),
((SELECT id FROM tags WHERE cle='storyboarding'), 'fr', 'Storyboarding'),
((SELECT id FROM tags WHERE cle='pottery'), 'fr', 'Poterie'),
((SELECT id FROM tags WHERE cle='vector_art'), 'fr', 'Art vectoriel'),
((SELECT id FROM tags WHERE cle='sculpting'), 'fr', 'Sculpture'),
((SELECT id FROM tags WHERE cle='motion_design'), 'fr', 'Motion Design'),
((SELECT id FROM tags WHERE cle='woodwork'), 'fr', 'Travail du bois'),
((SELECT id FROM tags WHERE cle='character_design'), 'fr', 'Character Design'),
((SELECT id FROM tags WHERE cle='ink_line_art'), 'fr', 'Encre et trait'),
((SELECT id FROM tags WHERE cle='mixed_crafts'), 'fr', 'Artisanat mixte'),
((SELECT id FROM tags WHERE cle='watercolor'), 'fr', 'Aquarelle'),
((SELECT id FROM tags WHERE cle='paper_quilling'), 'fr', 'Quilling papier'),
((SELECT id FROM tags WHERE cle='journalling'), 'fr', 'Journalling'),
((SELECT id FROM tags WHERE cle='realism'), 'fr', 'Réalisme'),
((SELECT id FROM tags WHERE cle='photography'), 'fr', 'Photographie'),
((SELECT id FROM tags WHERE cle='caricature'), 'fr', 'Caricature'),
((SELECT id FROM tags WHERE cle='digital_illustration'), 'fr', 'Illustration numérique'),
((SELECT id FROM tags WHERE cle='manga'), 'fr', 'Manga'),
((SELECT id FROM tags WHERE cle='digital_painting'), 'fr', 'Peinture numérique'),
((SELECT id FROM tags WHERE cle='abstract'), 'fr', 'Abstrait'),
((SELECT id FROM tags WHERE cle='acrylic_gouache'), 'fr', 'Acrylique / Gouache'),
((SELECT id FROM tags WHERE cle='pencil_sketching'), 'fr', 'Dessin au crayon'),
((SELECT id FROM tags WHERE cle='animation'), 'fr', 'Animation'),
((SELECT id FROM tags WHERE cle='charcoal'), 'fr', 'Fusain'),
((SELECT id FROM tags WHERE cle='collage'), 'fr', 'Collage'),
((SELECT id FROM tags WHERE cle='printmaking'), 'fr', 'Gravure / Impression'),
((SELECT id FROM tags WHERE cle='textile'), 'fr', 'Textile'),
((SELECT id FROM tags WHERE cle='comics'), 'fr', 'Bande dessinée'),
((SELECT id FROM tags WHERE cle='handicrafts'), 'fr', 'Artisanat'),
((SELECT id FROM tags WHERE cle='perspective'), 'fr', 'Perspective'),
((SELECT id FROM tags WHERE cle='stopmotion'), 'fr', 'Stop Motion'),
((SELECT id FROM tags WHERE cle='anatomy'), 'fr', 'Anatomie'),
((SELECT id FROM tags WHERE cle='mixed_media'), 'fr', 'Techniques mixtes'),
((SELECT id FROM tags WHERE cle='landscapes'), 'fr', 'Paysages'),
((SELECT id FROM tags WHERE cle='logo_design'), 'fr', 'Création de logo');

INSERT INTO tags_traductions (tag_id, langue_code, nom) VALUES
((SELECT id FROM tags WHERE cle='oil_painting'), 'sq', 'Pikturë me vaj'),
((SELECT id FROM tags WHERE cle='3d_modelling'), 'sq', 'Modelim 3D'),
((SELECT id FROM tags WHERE cle='origami'), 'sq', 'Origami'),
((SELECT id FROM tags WHERE cle='storyboarding'), 'sq', 'Storyboarding'),
((SELECT id FROM tags WHERE cle='pottery'), 'sq', 'Poçari'),
((SELECT id FROM tags WHERE cle='vector_art'), 'sq', 'Art vektorial'),
((SELECT id FROM tags WHERE cle='sculpting'), 'sq', 'Skulpturë'),
((SELECT id FROM tags WHERE cle='motion_design'), 'sq', 'Motion Design'),
((SELECT id FROM tags WHERE cle='woodwork'), 'sq', 'Punë me dru'),
((SELECT id FROM tags WHERE cle='character_design'), 'sq', 'Dizajn personazhi'),
((SELECT id FROM tags WHERE cle='ink_line_art'), 'sq', 'Bojë dhe vizë'),
((SELECT id FROM tags WHERE cle='mixed_crafts'), 'sq', 'Artizanat i përzier'),
((SELECT id FROM tags WHERE cle='watercolor'), 'sq', 'Akuarel'),
((SELECT id FROM tags WHERE cle='paper_quilling'), 'sq', 'Quilling letre'),
((SELECT id FROM tags WHERE cle='journalling'), 'sq', 'Journalling'),
((SELECT id FROM tags WHERE cle='realism'), 'sq', 'Realizëm'),
((SELECT id FROM tags WHERE cle='photography'), 'sq', 'Fotografi'),
((SELECT id FROM tags WHERE cle='caricature'), 'sq', 'Karikaturë'),
((SELECT id FROM tags WHERE cle='digital_illustration'), 'sq', 'Ilustrim dixhital'),
((SELECT id FROM tags WHERE cle='manga'), 'sq', 'Manga'),
((SELECT id FROM tags WHERE cle='digital_painting'), 'sq', 'Pikturë dixhitale'),
((SELECT id FROM tags WHERE cle='abstract'), 'sq', 'Abstrakt'),
((SELECT id FROM tags WHERE cle='acrylic_gouache'), 'sq', 'Akrilik / Guash'),
((SELECT id FROM tags WHERE cle='pencil_sketching'), 'sq', 'Vizatim me laps'),
((SELECT id FROM tags WHERE cle='animation'), 'sq', 'Animacion'),
((SELECT id FROM tags WHERE cle='charcoal'), 'sq', 'Qymyr'),
((SELECT id FROM tags WHERE cle='collage'), 'sq', 'Kolazh'),
((SELECT id FROM tags WHERE cle='printmaking'), 'sq', 'Printim artistik'),
((SELECT id FROM tags WHERE cle='textile'), 'sq', 'Tekstil'),
((SELECT id FROM tags WHERE cle='comics'), 'sq', 'Komike'),
((SELECT id FROM tags WHERE cle='handicrafts'), 'sq', 'Punë dore'),
((SELECT id FROM tags WHERE cle='perspective'), 'sq', 'Perspektivë'),
((SELECT id FROM tags WHERE cle='stopmotion'), 'sq', 'Stop Motion'),
((SELECT id FROM tags WHERE cle='anatomy'), 'sq', 'Anatomi'),
((SELECT id FROM tags WHERE cle='mixed_media'), 'sq', 'Teknika të përziera'),
((SELECT id FROM tags WHERE cle='landscapes'), 'sq', 'Peizazhe'),
((SELECT id FROM tags WHERE cle='logo_design'), 'sq', 'Dizajn logoje');

INSERT INTO tags_traductions (tag_id, langue_code, nom) VALUES
((SELECT id FROM tags WHERE cle='oil_painting'), 'vi', 'Tranh sơn dầu'),
((SELECT id FROM tags WHERE cle='3d_modelling'), 'vi', 'Mô hình 3D'),
((SELECT id FROM tags WHERE cle='origami'), 'vi', 'Nghệ thuật gấp giấy'),
((SELECT id FROM tags WHERE cle='storyboarding'), 'vi', 'Kịch bản hình ảnh'),
((SELECT id FROM tags WHERE cle='pottery'), 'vi', 'Gốm sứ'),
((SELECT id FROM tags WHERE cle='vector_art'), 'vi', 'Nghệ thuật vector'),
((SELECT id FROM tags WHERE cle='sculpting'), 'vi', 'Điêu khắc'),
((SELECT id FROM tags WHERE cle='motion_design'), 'vi', 'Thiết kế chuyển động'),
((SELECT id FROM tags WHERE cle='woodwork'), 'vi', 'Nghề mộc'),
((SELECT id FROM tags WHERE cle='character_design'), 'vi', 'Thiết kế nhân vật'),
((SELECT id FROM tags WHERE cle='ink_line_art'), 'vi', 'Mực và nét vẽ'),
((SELECT id FROM tags WHERE cle='mixed_crafts'), 'vi', 'Thủ công hỗn hợp'),
((SELECT id FROM tags WHERE cle='watercolor'), 'vi', 'Màu nước'),
((SELECT id FROM tags WHERE cle='paper_quilling'), 'vi', 'Quilling giấy'),
((SELECT id FROM tags WHERE cle='journalling'), 'vi', 'Nhật ký sáng tạo'),
((SELECT id FROM tags WHERE cle='realism'), 'vi', 'Chủ nghĩa hiện thực'),
((SELECT id FROM tags WHERE cle='photography'), 'vi', 'Nhiếp ảnh'),
((SELECT id FROM tags WHERE cle='caricature'), 'vi', 'Biếm họa'),
((SELECT id FROM tags WHERE cle='digital_illustration'), 'vi', 'Minh họa kỹ thuật số'),
((SELECT id FROM tags WHERE cle='manga'), 'vi', 'Manga'),
((SELECT id FROM tags WHERE cle='digital_painting'), 'vi', 'Vẽ kỹ thuật số'),
((SELECT id FROM tags WHERE cle='abstract'), 'vi', 'Trừu tượng'),
((SELECT id FROM tags WHERE cle='acrylic_gouache'), 'vi', 'Acrylic / Gouache'),
((SELECT id FROM tags WHERE cle='pencil_sketching'), 'vi', 'Phác thảo chì'),
((SELECT id FROM tags WHERE cle='animation'), 'vi', 'Hoạt hình'),
((SELECT id FROM tags WHERE cle='charcoal'), 'vi', 'Than chì'),
((SELECT id FROM tags WHERE cle='collage'), 'vi', 'Cắt dán nghệ thuật'),
((SELECT id FROM tags WHERE cle='printmaking'), 'vi', 'In ấn nghệ thuật'),
((SELECT id FROM tags WHERE cle='textile'), 'vi', 'Dệt may'),
((SELECT id FROM tags WHERE cle='comics'), 'vi', 'Truyện tranh'),
((SELECT id FROM tags WHERE cle='handicrafts'), 'vi', 'Thủ công mỹ nghệ'),
((SELECT id FROM tags WHERE cle='perspective'), 'vi', 'Phối cảnh'),
((SELECT id FROM tags WHERE cle='stopmotion'), 'vi', 'Stop Motion'),
((SELECT id FROM tags WHERE cle='anatomy'), 'vi', 'Giải phẫu học'),
((SELECT id FROM tags WHERE cle='mixed_media'), 'vi', 'Kỹ thuật hỗn hợp'),
((SELECT id FROM tags WHERE cle='landscapes'), 'vi', 'Phong cảnh'),
((SELECT id FROM tags WHERE cle='logo_design'), 'vi', 'Thiết kế logo');
