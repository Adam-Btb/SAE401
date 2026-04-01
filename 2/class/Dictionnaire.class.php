<?php
class Dictionnaire {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
    }

    public function motAleatoire() {
        $sql = "SELECT mot FROM mots ORDER BY RAND() LIMIT 1";
        $req = $this->cnx->query($sql);
        $res = $req->fetch(PDO::FETCH_ASSOC);
        return $res ? strtoupper($res['mot']) : "GOKU";
    }

    public function ajouter($mot) {
        $mot = strtoupper(trim($mot));
        if (strlen($mot) < 3) return false;
        try {
            $req = $this->cnx->prepare("INSERT INTO mots (mot) VALUES (:m)");
            $req->execute([':m' => $mot]);
            return true;
        } catch (Exception $e) { return false; }
    }

    public function lireTout() {
        return $this->cnx->query("SELECT * FROM mots ORDER BY mot")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function supprimer($id) {
        $req = $this->cnx->prepare("DELETE FROM mots WHERE id = :id");
        $req->execute([':id' => $id]);
    }
}