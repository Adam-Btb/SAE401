<?php
class Message {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
    }

    public function envoyer($expediteurId, $destinataireId, $contenu, $ami) {
        $sontAmis = $ami->sontAmis($expediteurId, $destinataireId);
        $estInvitation = 0;

        if (!$sontAmis) {

            $req = $this->cnx->prepare(
                "SELECT COUNT(*) as nb FROM messages WHERE expediteur_id = :exp AND destinataire_id = :dest"
            );
            $req->execute([':exp' => $expediteurId, ':dest' => $destinataireId]);
            $count = $req->fetch()['nb'];
            if ($count > 0) {
                return ['ok' => false, 'msg' => 'must_connect_first'];
            }

            $estInvitation = 1;
            $ami->demanderAmi($expediteurId, $destinataireId);
        }

        $sql = "INSERT INTO messages (expediteur_id, destinataire_id, contenu, est_invitation) VALUES (:exp, :dest, :msg, :inv)";
        $req = $this->cnx->prepare($sql);
        $req->execute([
            ':exp' => $expediteurId,
            ':dest' => $destinataireId,
            ':msg' => htmlspecialchars($contenu),
            ':inv' => $estInvitation
        ]);

        if ($estInvitation) {
            return ['ok' => true, 'msg' => 'invitation_sent'];
        }
        return ['ok' => true, 'msg' => ''];
    }

    public function getConversation($userId1, $userId2) {
        $sql = "SELECT m.*, u.pseudonyme AS pseudo_exp, u.photo_profil AS photo_exp
                FROM messages m
                JOIN utilisateurs u ON m.expediteur_id = u.id
                WHERE (m.expediteur_id = :a AND m.destinataire_id = :b)
                   OR (m.expediteur_id = :c AND m.destinataire_id = :d)
                ORDER BY m.date_envoi ASC";
        $req = $this->cnx->prepare($sql);
        $req->execute([':a' => $userId1, ':b' => $userId2, ':c' => $userId2, ':d' => $userId1]);
        return $req->fetchAll();
    }

    public function getConversations($userId) {
        $sql = "SELECT m2.*, u.pseudonyme, u.photo_profil,
                (SELECT COUNT(*) FROM messages WHERE expediteur_id = u.id AND destinataire_id = :uid3 AND lu = 0) AS non_lus
                FROM (
                    SELECT
                        CASE WHEN expediteur_id = :uid THEN destinataire_id ELSE expediteur_id END AS contact_id,
                        MAX(id) AS dernier_msg_id
                    FROM messages
                    WHERE expediteur_id = :uid1 OR destinataire_id = :uid2
                    GROUP BY contact_id
                ) AS conv
                JOIN messages m2 ON m2.id = conv.dernier_msg_id
                JOIN utilisateurs u ON u.id = conv.contact_id
                ORDER BY m2.date_envoi DESC";
        $req = $this->cnx->prepare($sql);
        $req->execute([':uid' => $userId, ':uid1' => $userId, ':uid2' => $userId, ':uid3' => $userId]);
        return $req->fetchAll();
    }

    public function marquerLu($userId, $contactId) {
        $sql = "UPDATE messages SET lu = 1 WHERE expediteur_id = :contact AND destinataire_id = :moi AND lu = 0";
        $this->cnx->prepare($sql)->execute([':contact' => $contactId, ':moi' => $userId]);
    }

    public function compterNonLus($userId) {
        $req = $this->cnx->prepare("SELECT COUNT(*) AS nb FROM messages WHERE destinataire_id = :uid AND lu = 0");
        $req->execute([':uid' => $userId]);
        return $req->fetch()['nb'];
    }
}
