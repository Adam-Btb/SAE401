<?php
class Jeu {
    private $cnx;

    public function __construct($cnx) {
        $this->cnx = $cnx;
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    public function nouvellePartie($dico) {
        $mot = $dico->motAleatoire();
        $_SESSION['mot_secret'] = $mot;
        $_SESSION['taille'] = strlen($mot);
        $_SESSION['essais'] = []; 
        $_SESSION['max_tentatives'] = 6;
        $_SESSION['etat'] = 'en_cours'; 
        $_SESSION['msg'] = "Trouvez le mot !";

        $_SESSION['input_actuel'] = $mot[0]; 

        $_SESSION['clavier_status'] = [];
        foreach (range('A', 'Z') as $char) {
            $_SESSION['clavier_status'][$char] = null;
        }
    }

    public function ajouterLettre($lettre) {
        if ($_SESSION['etat'] != 'en_cours') return;

        if (strlen($_SESSION['input_actuel']) < $_SESSION['taille']) {
            $_SESSION['input_actuel'] .= $lettre;
        }
    }

    public function effacerLettre() {
        if ($_SESSION['etat'] != 'en_cours') return;

        if (strlen($_SESSION['input_actuel']) > 1) {
            $_SESSION['input_actuel'] = substr($_SESSION['input_actuel'], 0, -1);
        }
    }

    public function validerInput() {
        return $this->jouer($_SESSION['input_actuel']);
    }

    public function jouer($proposition) {
        if ($_SESSION['etat'] != 'en_cours') return;

        $proposition = strtoupper(trim($proposition));
        $secret = $_SESSION['mot_secret'];
        $taille = $_SESSION['taille'];

        if (strlen($proposition) != $taille) {
            $_SESSION['msg'] = "Mot incomplet !";
            return;
        }

        $resultat = array_fill(0, $taille, 'bleu');
        $tabSecret = str_split($secret);
        $tabProp = str_split($proposition);
        $secretRestant = $tabSecret;

        for ($i = 0; $i < $taille; $i++) {
            $lettre = $tabProp[$i];
            if ($lettre == $tabSecret[$i]) {
                $resultat[$i] = 'rouge';
                $secretRestant[$i] = null;
                $_SESSION['clavier_status'][$lettre] = 'rouge';
            }
        }

        for ($i = 0; $i < $taille; $i++) {
            $lettre = $tabProp[$i];
            if ($resultat[$i] != 'rouge') {
                $pos = array_search($lettre, $secretRestant);
                if ($pos !== false && $secretRestant[$pos] !== null) {
                    $resultat[$i] = 'jaune';
                    $secretRestant[$pos] = null;
                    if ($_SESSION['clavier_status'][$lettre] != 'rouge') {
                        $_SESSION['clavier_status'][$lettre] = 'jaune';
                    }
                } else {
                    if ($_SESSION['clavier_status'][$lettre] == null) {
                        $_SESSION['clavier_status'][$lettre] = 'bleu';
                    }
                }
            }
        }

        $_SESSION['essais'][] = ['lettres' => $tabProp, 'couleurs' => $resultat];

        $_SESSION['input_actuel'] = $secret[0];

        if ($proposition == $secret) {
            $_SESSION['etat'] = 'gagne';
            $_SESSION['msg'] = "BRAVO !";
        } elseif (count($_SESSION['essais']) >= $_SESSION['max_tentatives']) {
            $_SESSION['etat'] = 'perdu';
            $_SESSION['msg'] = "PERDU... Le mot était $secret.";
        }
    }

    public function sauverScore($pseudo) {
        if ($_SESSION['etat'] == 'gagne') {
            $score = (($_SESSION['max_tentatives'] + 1) - count($_SESSION['essais'])) * $_SESSION['taille'] * 10;
            $req = $this->cnx->prepare("INSERT INTO scores (pseudo, points) VALUES (:p, :s)");
            $req->execute([':p' => $pseudo, ':s' => $score]);
            return $score;
        }
        return 0;
    }

    public function getTopScores() {
        return $this->cnx->query("SELECT * FROM scores ORDER BY points DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    }
}
