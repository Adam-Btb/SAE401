<?php
class Tag {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
    }

    public function getTous($langueCode = null) {
        $lang = $langueCode ?? Langue::getLang();
        $sql = "SELECT t.id, t.cle, COALESCE(tt.nom, t.cle) AS nom
                FROM tags t
                LEFT JOIN tags_traductions tt ON t.id = tt.tag_id AND tt.langue_code = :lang
                ORDER BY nom ASC";
        $req = $this->cnx->prepare($sql);
        $req->execute([':lang' => $lang]);
        return $req->fetchAll();
    }
}
