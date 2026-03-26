<?php
class Evenement {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
    }

    public function creer($organisateurId, $data, $participantIds = []) {
        $sql = "INSERT INTO evenements (organisateur_id, titre, description, date_debut, date_fin, type)
                VALUES (:org, :titre, :desc, :debut, :fin, :type)";
        $req = $this->cnx->prepare($sql);
        $req->execute([
            ':org' => $organisateurId,
            ':titre' => htmlspecialchars($data['titre']),
            ':desc' => htmlspecialchars($data['description'] ?? ''),
            ':debut' => $data['date_debut'],
            ':fin' => $data['date_fin'],
            ':type' => $data['type']
        ]);
        $eventId = $this->cnx->lastInsertId();

        // Ajouter les participants (pour événements partagés)
        if ($data['type'] === 'partage' && !empty($participantIds)) {
            $stmt = $this->cnx->prepare("INSERT INTO evenement_participants (evenement_id, utilisateur_id) VALUES (:eid, :uid)");
            foreach ($participantIds as $pid) {
                if ($pid != $organisateurId) {
                    $stmt->execute([':eid' => $eventId, ':uid' => (int)$pid]);
                }
            }
        }

        return $eventId;
    }

    public function supprimer($eventId, $organisateurId) {
        $req = $this->cnx->prepare("DELETE FROM evenements WHERE id = :id AND organisateur_id = :org");
        return $req->execute([':id' => $eventId, ':org' => $organisateurId]);
    }

    public function getMesEvenements($userId) {
        // Événements que j'organise
        $sql = "SELECT e.*, 'organisateur' AS role FROM evenements e WHERE e.organisateur_id = :uid
                UNION
                SELECT e.*, 'participant' AS role FROM evenements e
                JOIN evenement_participants ep ON e.id = ep.evenement_id
                WHERE ep.utilisateur_id = :uid2
                ORDER BY date_debut ASC";
        $req = $this->cnx->prepare($sql);
        $req->execute([':uid' => $userId, ':uid2' => $userId]);
        return $req->fetchAll();
    }

    public function getEvenementsPublicsConnectes($userId, $amisIds) {
        if (empty($amisIds)) return [];
        $placeholders = implode(',', array_fill(0, count($amisIds), '?'));
        $sql = "SELECT e.*, u.pseudonyme AS organisateur_pseudo
                FROM evenements e
                JOIN utilisateurs u ON e.organisateur_id = u.id
                WHERE e.type = 'public' AND e.organisateur_id IN ($placeholders)
                AND e.date_fin >= NOW()
                ORDER BY e.date_debut ASC";
        $req = $this->cnx->prepare($sql);
        $req->execute($amisIds);
        return $req->fetchAll();
    }

    public function getParticipants($eventId) {
        $sql = "SELECT u.id, u.pseudonyme, u.photo_profil, ep.statut
                FROM evenement_participants ep
                JOIN utilisateurs u ON ep.utilisateur_id = u.id
                WHERE ep.evenement_id = :eid";
        $req = $this->cnx->prepare($sql);
        $req->execute([':eid' => $eventId]);
        return $req->fetchAll();
    }

    public function repondreInvitation($eventId, $userId, $statut) {
        $sql = "UPDATE evenement_participants SET statut = :statut WHERE evenement_id = :eid AND utilisateur_id = :uid";
        return $this->cnx->prepare($sql)->execute([':statut' => $statut, ':eid' => $eventId, ':uid' => $userId]);
    }

    public function getInvitationsEnAttente($userId) {
        $sql = "SELECT e.*, u.pseudonyme AS organisateur_pseudo
                FROM evenement_participants ep
                JOIN evenements e ON ep.evenement_id = e.id
                JOIN utilisateurs u ON e.organisateur_id = u.id
                WHERE ep.utilisateur_id = :uid AND ep.statut = 'en_attente'
                ORDER BY e.date_debut ASC";
        $req = $this->cnx->prepare($sql);
        $req->execute([':uid' => $userId]);
        return $req->fetchAll();
    }

    public function getById($id) {
        $req = $this->cnx->prepare("SELECT * FROM evenements WHERE id = :id");
        $req->execute([':id' => $id]);
        return $req->fetch();
    }

    public function tousValides($eventId) {
        $req = $this->cnx->prepare(
            "SELECT COUNT(*) AS total, SUM(statut = 'accepte') AS acceptes FROM evenement_participants WHERE evenement_id = :eid"
        );
        $req->execute([':eid' => $eventId]);
        $r = $req->fetch();
        return $r['total'] > 0 && $r['total'] == $r['acceptes'];
    }
}
