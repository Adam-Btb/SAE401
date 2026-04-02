<?php
class Ami {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
    }

    public function getSuggestionsAmis($userId, $limit = 6) {
        $req = $this->cnx->prepare("
            SELECT u.id, u.pseudonyme, u.photo_profil, u.ville
            FROM utilisateurs u
            WHERE u.id != :uid
              AND u.id NOT IN (
                SELECT utilisateur_id_1 FROM amis WHERE utilisateur_id_2 = :uid2
                UNION
                SELECT utilisateur_id_2 FROM amis WHERE utilisateur_id_1 = :uid3
              )
            ORDER BY RAND() LIMIT :lim
        ");
        $req->bindValue(':uid', $userId, PDO::PARAM_INT);
        $req->bindValue(':uid2', $userId, PDO::PARAM_INT);
        $req->bindValue(':uid3', $userId, PDO::PARAM_INT);
        $req->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    private function ordonner($id1, $id2) {
        return $id1 < $id2 ? [$id1, $id2] : [$id2, $id1];
    }

    public function getStatut($userId1, $userId2) {
        list($a, $b) = $this->ordonner($userId1, $userId2);
        $req = $this->cnx->prepare("SELECT * FROM amis WHERE utilisateur_id_1 = :a AND utilisateur_id_2 = :b");
        $req->execute([':a' => $a, ':b' => $b]);
        return $req->fetch();
    }

    public function sontAmis($userId1, $userId2) {
        $rel = $this->getStatut($userId1, $userId2);
        return $rel && $rel['statut'] === 'accepte';
    }

    public function demanderAmi($demandeurId, $cibleId) {
        if ($demandeurId == $cibleId) return false;
        $existant = $this->getStatut($demandeurId, $cibleId);
        if ($existant) return false;

        list($a, $b) = $this->ordonner($demandeurId, $cibleId);
        $req = $this->cnx->prepare(
            "INSERT INTO amis (utilisateur_id_1, utilisateur_id_2, demandeur_id, statut) VALUES (:a, :b, :d, 'en_attente')"
        );
        return $req->execute([':a' => $a, ':b' => $b, ':d' => $demandeurId]);
    }

    public function accepter($userId, $amiId) {
        list($a, $b) = $this->ordonner($userId, $amiId);
        $req = $this->cnx->prepare(
            "UPDATE amis SET statut = 'accepte', date_reponse = NOW() WHERE utilisateur_id_1 = :a AND utilisateur_id_2 = :b AND demandeur_id != :moi AND statut = 'en_attente'"
        );
        return $req->execute([':a' => $a, ':b' => $b, ':moi' => $userId]);
    }

    public function refuser($userId, $amiId) {
        list($a, $b) = $this->ordonner($userId, $amiId);
        $req = $this->cnx->prepare(
            "UPDATE amis SET statut = 'refuse', date_reponse = NOW() WHERE utilisateur_id_1 = :a AND utilisateur_id_2 = :b AND demandeur_id != :moi AND statut = 'en_attente'"
        );
        return $req->execute([':a' => $a, ':b' => $b, ':moi' => $userId]);
    }

    public function supprimer($userId, $amiId) {
        list($a, $b) = $this->ordonner($userId, $amiId);
        $req = $this->cnx->prepare("DELETE FROM amis WHERE utilisateur_id_1 = :a AND utilisateur_id_2 = :b");
        return $req->execute([':a' => $a, ':b' => $b]);
    }

    public function getAmis($userId) {
        $sql = "SELECT u.* FROM utilisateurs u
                JOIN amis a ON (
                    (a.utilisateur_id_1 = :uid AND a.utilisateur_id_2 = u.id)
                    OR (a.utilisateur_id_2 = :uid2 AND a.utilisateur_id_1 = u.id)
                )
                WHERE a.statut = 'accepte'
                ORDER BY u.pseudonyme";
        $req = $this->cnx->prepare($sql);
        $req->execute([':uid' => $userId, ':uid2' => $userId]);
        return $req->fetchAll();
    }

    public function getDemandesRecues($userId) {
        $sql = "SELECT u.*, a.date_demande FROM utilisateurs u
                JOIN amis a ON (
                    (a.utilisateur_id_1 = u.id AND a.utilisateur_id_2 = :uid)
                    OR (a.utilisateur_id_2 = u.id AND a.utilisateur_id_1 = :uid2)
                )
                WHERE a.demandeur_id = u.id AND a.statut = 'en_attente'
                ORDER BY a.date_demande DESC";
        $req = $this->cnx->prepare($sql);
        $req->execute([':uid' => $userId, ':uid2' => $userId]);
        return $req->fetchAll();
    }
}
