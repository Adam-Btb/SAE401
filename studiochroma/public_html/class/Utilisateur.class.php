<?php
class Utilisateur {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
    }

    public function inscrire($data, $tags = []) {
        if (empty($data['pseudonyme']) || empty($data['email']) || empty($data['mot_de_passe'])) {
            return ['ok' => false, 'msg' => 'error_fields'];
        }
        if (strlen($data['mot_de_passe']) < 8) {
            return ['ok' => false, 'msg' => 'error_password_short'];
        }
        if ($data['mot_de_passe'] !== $data['mot_de_passe_confirm']) {
            return ['ok' => false, 'msg' => 'error_password_mismatch'];
        }
        if (empty($data['cgu'])) {
            return ['ok' => false, 'msg' => 'error_cgu'];
        }

        $req = $this->cnx->prepare("SELECT id FROM utilisateurs WHERE pseudonyme = :p");
        $req->execute([':p' => $data['pseudonyme']]);
        if ($req->fetch()) return ['ok' => false, 'msg' => 'error_pseudo_taken'];

        $req = $this->cnx->prepare("SELECT id FROM utilisateurs WHERE email = :e");
        $req->execute([':e' => $data['email']]);
        if ($req->fetch()) return ['ok' => false, 'msg' => 'error_email_taken'];

        $photo = 'default.webp';
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $newName = uniqid('avatar_') . '.' . $ext;
                $targetDir = __DIR__ . '/../img/avatars';
                if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . '/' . $newName)) {
                    $photo = $newName;
                } else {
                    error_log('[StudioChroma] Avatar upload failed for ' . $_FILES['photo']['name'] . ' → ' . $targetDir);
                }
            }
        }

        $hash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        $langues = is_array($data['langues_parlees'] ?? null) ? implode(',', $data['langues_parlees']) : ($data['langues_parlees'] ?? '');

        $sql = "INSERT INTO utilisateurs (pseudonyme, email, mot_de_passe, photo_profil, langues_parlees, nationalite, date_naissance, ville, bio, experiences_texte, cgu_acceptees, latitude, longitude)
                VALUES (:pseudo, :email, :mdp, :photo, :langues, :nat, :naissance, :ville, :bio, :exp, 1, :lat, :lng)";
        $req = $this->cnx->prepare($sql);
        $req->execute([
            ':pseudo' => htmlspecialchars($data['pseudonyme']),
            ':email' => $data['email'],
            ':mdp' => $hash,
            ':photo' => $photo,
            ':langues' => $langues,
            ':nat' => htmlspecialchars($data['nationalite'] ?? ''),
            ':naissance' => $data['date_naissance'] ?? null,
            ':ville' => htmlspecialchars($data['ville'] ?? ''),
            ':bio' => htmlspecialchars($data['bio'] ?? ''),
            ':exp' => htmlspecialchars($data['experiences_texte'] ?? ''),
            ':lat' => $data['latitude'] ?? null,
            ':lng' => $data['longitude'] ?? null
        ]);

        $userId = $this->cnx->lastInsertId();

        if (!empty($tags)) {
            $stmtTag = $this->cnx->prepare("INSERT INTO utilisateur_tags (utilisateur_id, tag_id) VALUES (:uid, :tid)");
            foreach ($tags as $tagId) {
                $stmtTag->execute([':uid' => $userId, ':tid' => (int)$tagId]);
            }
        }

        return ['ok' => true, 'msg' => 'success_register'];
    }

    public function connecter($email, $mdp) {
        $req = $this->cnx->prepare("SELECT * FROM utilisateurs WHERE email = :e");
        $req->execute([':e' => $email]);
        $user = $req->fetch();
        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_pseudo'] = $user['pseudonyme'];
            $_SESSION['user_photo'] = $user['photo_profil'];
            return true;
        }
        return false;
    }

    public function deconnecter() {
        unset($_SESSION['user_id'], $_SESSION['user_pseudo'], $_SESSION['user_photo']);
        session_destroy();
    }

    public function estConnecte() {
        return isset($_SESSION['user_id']);
    }

    public function getById($id) {
        $req = $this->cnx->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $req->execute([':id' => (int)$id]);
        return $req->fetch();
    }

    public function getTagsUtilisateur($userId) {
        $lang = Langue::getLang();
        $req = $this->cnx->prepare(
            "SELECT t.id, t.cle, COALESCE(tt.nom, t.cle) AS nom
             FROM utilisateur_tags ut
             JOIN tags t ON ut.tag_id = t.id
             LEFT JOIN tags_traductions tt ON t.id = tt.tag_id AND tt.langue_code = :lang
             WHERE ut.utilisateur_id = :uid"
        );
        $req->execute([':uid' => (int)$userId, ':lang' => $lang]);
        return $req->fetchAll();
    }

    public function mettreAJour($userId, $data, $tags = []) {
        $photoSQL = '';
        $params = [
            ':pseudo' => htmlspecialchars($data['pseudonyme']),
            ':langues' => is_array($data['langues_parlees'] ?? null) ? implode(',', $data['langues_parlees']) : ($data['langues_parlees'] ?? ''),
            ':nat' => htmlspecialchars($data['nationalite'] ?? ''),
            ':naissance' => $data['date_naissance'] ?? null,
            ':ville' => htmlspecialchars($data['ville'] ?? ''),
            ':bio' => htmlspecialchars($data['bio'] ?? ''),
            ':exp' => htmlspecialchars($data['experiences_texte'] ?? ''),
            ':lat' => $data['latitude'] ?? null,
            ':lng' => $data['longitude'] ?? null,
            ':id' => (int)$userId
        ];

        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $newName = uniqid('avatar_') . '.' . $ext;
                $targetDir = __DIR__ . '/../img/avatars';
                if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . '/' . $newName)) {
                    $photoSQL = ', photo_profil = :photo';
                    $params[':photo'] = $newName;
                    $_SESSION['user_photo'] = $newName;
                } else {
                    error_log('[StudioChroma] Avatar update failed → ' . $targetDir);
                }
            }
        }

        $mdpSQL = '';
        if (!empty($data['mot_de_passe']) && strlen($data['mot_de_passe']) >= 8) {
            $mdpSQL = ', mot_de_passe = :mdp';
            $params[':mdp'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE utilisateurs SET pseudonyme = :pseudo, langues_parlees = :langues, nationalite = :nat,
                date_naissance = :naissance, ville = :ville, bio = :bio, experiences_texte = :exp, latitude = :lat, longitude = :lng
                $photoSQL $mdpSQL WHERE id = :id";
        $this->cnx->prepare($sql)->execute($params);
        $_SESSION['user_pseudo'] = htmlspecialchars($data['pseudonyme']);

        $this->cnx->prepare("DELETE FROM utilisateur_tags WHERE utilisateur_id = :uid")->execute([':uid' => (int)$userId]);
        if (!empty($tags)) {
            $stmt = $this->cnx->prepare("INSERT INTO utilisateur_tags (utilisateur_id, tag_id) VALUES (:uid, :tid)");
            foreach ($tags as $tid) {
                $stmt->execute([':uid' => (int)$userId, ':tid' => (int)$tid]);
            }
        }

        return true;
    }

    public function rechercher($filtres = []) {
        $lang = Langue::getLang();
        $where = ["1=1"];
        $params = [':lang' => $lang];

        if (!empty($filtres['q'])) {
            $where[] = "u.pseudonyme LIKE :q";
            $params[':q'] = '%' . $filtres['q'] . '%';
        }
        if (!empty($filtres['langue'])) {
            $where[] = "u.langues_parlees LIKE :langue";
            $params[':langue'] = '%' . $filtres['langue'] . '%';
        }
        if (!empty($filtres['tag_id'])) {
            $where[] = "u.id IN (SELECT utilisateur_id FROM utilisateur_tags WHERE tag_id = :tag_id)";
            $params[':tag_id'] = (int)$filtres['tag_id'];
        }
        if (!empty($filtres['exclude_id'])) {
            $where[] = "u.id != :exclude_id";
            $params[':exclude_id'] = (int)$filtres['exclude_id'];
        }

        $sql = "SELECT u.*, GROUP_CONCAT(DISTINCT COALESCE(tt.nom, t.cle) SEPARATOR ', ') AS tags_noms
                FROM utilisateurs u
                LEFT JOIN utilisateur_tags ut ON u.id = ut.utilisateur_id
                LEFT JOIN tags t ON ut.tag_id = t.id
                LEFT JOIN tags_traductions tt ON t.id = tt.tag_id AND tt.langue_code = :lang
                WHERE " . implode(' AND ', $where) . "
                GROUP BY u.id
                ORDER BY u.pseudonyme ASC";
        $req = $this->cnx->prepare($sql);
        $req->execute($params);
        return $req->fetchAll();
    }

    public function calculerAge($dateNaissance) {
        if (empty($dateNaissance)) return null;
        $naissance = new DateTime($dateNaissance);
        $now = new DateTime();
        return $naissance->diff($now)->y;
    }

    public function getDerniersInscrits($limit = 6) {
        $req = $this->cnx->prepare("SELECT id, pseudonyme, photo_profil, ville, date_inscription FROM utilisateurs ORDER BY date_inscription DESC LIMIT :lim");
        $req->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    public function getPublications($userId, $amisIds = []) {
        $sql = "SELECT p.*, u.pseudonyme, u.photo_profil
                FROM publications p
                JOIN utilisateurs u ON p.auteur_id = u.id
                ORDER BY p.date_publication DESC
                LIMIT 50";
        $req = $this->cnx->prepare($sql);
        $req->execute();
        return $req->fetchAll();
    }

    public function publier($userId, $contenu, $fileData = null) {
        if (empty(trim($contenu)) && empty($fileData['name'])) return false;

        $imageUrl = null;
        if (!empty($fileData['name']) && $fileData['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $imageUrl = uniqid('post_') . '.' . $ext;
                $targetDir = __DIR__ . '/../img/uploads/publications';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }
                if (!move_uploaded_file($fileData['tmp_name'], $targetDir . '/' . $imageUrl)) {
                    error_log('[StudioChroma] Publication image upload failed → ' . $targetDir . '/' . $imageUrl);
                    $imageUrl = null;
                }
            }
        } elseif (!empty($fileData['error']) && $fileData['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log('[StudioChroma] Upload error code: ' . $fileData['error']);
        }

        $req = $this->cnx->prepare("INSERT INTO publications (auteur_id, contenu, image_url) VALUES (:uid, :contenu, :img)");
        $req->execute([':uid' => (int)$userId, ':contenu' => htmlspecialchars($contenu), ':img' => $imageUrl]);
        return true;
    }
}
