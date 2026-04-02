<?php
session_start();

define("CHARGE_AUTOLOAD", true);
require_once "inc/poo.inc.php";
define("CHARGE_BD", true);
require_once "inc/bd.inc.php";
require_once "inc/config.inc.php";

Langue::init();

$utilisateur = new Utilisateur($cnx);
$tag = new Tag($cnx);
$ami = new Ami($cnx);
$message = new Message($cnx);
$evenement = new Evenement($cnx);
$vue = new Vues();

$action = $_GET['action'] ?? 'accueil';
$connecte = $utilisateur->estConnecte();
$userId = $_SESSION['user_id'] ?? null;

switch ($action) {

    case 'inscription_submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $utilisateur->inscrire($_POST, $_POST['tags'] ?? []);
            if ($result['ok']) {
                $_SESSION['flash_success'] = $result['msg'];
                header("Location: index.php?action=connexion&lang=" . Langue::getLang());
                exit();
            } else {
                $_SESSION['flash_error'] = $result['msg'];
                header("Location: index.php?action=inscription&lang=" . Langue::getLang());
                exit();
            }
        }
        header("Location: index.php");
        exit();

    case 'connexion_submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($utilisateur->connecter($_POST['email'] ?? '', $_POST['mot_de_passe'] ?? '')) {
                header("Location: index.php?action=feed&lang=" . Langue::getLang());
                exit();
            } else {
                $_SESSION['flash_error'] = 'error_login';
                header("Location: index.php?action=connexion&lang=" . Langue::getLang());
                exit();
            }
        }
        header("Location: index.php");
        exit();

    case 'deconnexion':
        $utilisateur->deconnecter();
        header("Location: index.php?lang=" . Langue::getLang());
        exit();

    case 'profil_update':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $utilisateur->mettreAJour($userId, $_POST, $_POST['tags'] ?? []);
            $_SESSION['flash_success'] = 'success_profile';
        }
        header("Location: index.php?action=profil&lang=" . Langue::getLang());
        exit();

    case 'demande_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->demanderAmi($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=profil_voir&id=" . (int)($_GET['id'] ?? 0) . "&lang=" . Langue::getLang());
        exit();

    case 'accepter_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->accepter($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=amis&lang=" . Langue::getLang());
        exit();

    case 'refuser_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->refuser($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=amis&lang=" . Langue::getLang());
        exit();

    case 'supprimer_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->supprimer($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=amis&lang=" . Langue::getLang());
        exit();

    case 'envoyer_msg':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['dest'])) {
            $destId = (int)$_GET['dest'];
            $result = $message->envoyer($userId, $destId, $_POST['contenu'] ?? '', $ami, $_FILES['piece_jointe'] ?? null);
            if (!empty($result['msg'])) {
                $_SESSION['flash_info'] = $result['msg'];
            }
        }
        header("Location: index.php?action=ecrire&dest=" . (int)($_GET['dest'] ?? 0) . "&lang=" . Langue::getLang());
        exit();

    case 'creer_event':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $evenement->creer($userId, $_POST, $_POST['participants'] ?? []);
        }
        header("Location: index.php?action=agenda&lang=" . Langue::getLang());
        exit();

    case 'suppr_event':
        if ($connecte && !empty($_GET['id'])) {
            $evenement->supprimer((int)$_GET['id'], $userId);
        }
        header("Location: index.php?action=agenda&lang=" . Langue::getLang());
        exit();

    case 'repondre_event':
        if ($connecte && !empty($_GET['id']) && !empty($_GET['rep'])) {
            $rep = in_array($_GET['rep'], ['accepte', 'refuse']) ? $_GET['rep'] : 'refuse';
            $evenement->repondreInvitation((int)$_GET['id'], $userId, $rep);
        }
        header("Location: index.php?action=agenda&lang=" . Langue::getLang());
        exit();

    case 'toggle_abo':
        if ($connecte && !empty($_GET['id']) && (int)$_GET['id'] !== $userId) {
            $utilisateur->toggleAbonnement($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=profil_voir&id=" . (int)$_GET['id'] . "&lang=" . Langue::getLang());
        exit();

    case 'supprimer_pub':
        if ($connecte && !empty($_GET['id'])) {
            $utilisateur->supprimerPublication((int)$_GET['id'], $userId);
        }
        header("Location: index.php?action=feed&lang=" . Langue::getLang());
        exit();

    case 'supprimer_image_pub':
        if ($connecte && !empty($_GET['id'])) {
            $utilisateur->supprimerImagePublication((int)$_GET['id'], $userId);
        }
        header("Location: index.php?action=publication&id=" . (int)$_GET['id'] . "&lang=" . Langue::getLang());
        exit();

    case 'liker':
        if ($connecte && !empty($_GET['id'])) {
            $utilisateur->toggleLike($userId, (int)$_GET['id']);
            if (!empty($_GET['ajax'])) {
                $pid = (int)$_GET['id'];
                $req = $cnx->prepare("SELECT COUNT(*) AS nb FROM likes WHERE publication_id = :pid");
                $req->execute([':pid' => $pid]);
                $nb = $req->fetch()['nb'];
                $req2 = $cnx->prepare("SELECT COUNT(*) AS liked FROM likes WHERE publication_id = :pid AND utilisateur_id = :uid");
                $req2->execute([':pid' => $pid, ':uid' => $userId]);
                $liked = $req2->fetch()['liked'];
                header('Content-Type: application/json');
                echo json_encode(['nb' => (int)$nb, 'liked' => (bool)$liked]);
                exit();
            }
        }
        header("Location: index.php?action=feed&lang=" . Langue::getLang());
        exit();

    case 'publier':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $utilisateur->publier($userId, $_POST['contenu'] ?? '', $_FILES['image'] ?? null);
        }
        header("Location: index.php?action=feed&lang=" . Langue::getLang());
        exit();

    case 'commenter':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['publication_id'])) {
            $utilisateur->commenter($userId, (int)$_POST['publication_id'], $_POST['contenu'] ?? '');
        }
        header("Location: index.php?action=feed&lang=" . Langue::getLang());
        exit();

    case 'get_commentaires':
        if ($connecte && !empty($_GET['id'])) {
            $comments = $utilisateur->getCommentaires((int)$_GET['id']);
            $result = [];
            foreach ($comments as $c) {
                $f = $c['photo_profil'] ?: 'default.webp';
                $path = __DIR__ . '/img/avatars/' . $f;
                if (!file_exists($path)) $f = 'default.webp';
                $result[] = [
                    'auteur_id' => $c['auteur_id'],
                    'pseudonyme' => htmlspecialchars($c['pseudonyme']),
                    'photo' => $f,
                    'contenu' => nl2br(htmlspecialchars($c['contenu'])),
                    'date' => date('d/m H:i', strtotime($c['date_commentaire']))
                ];
            }
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
        header('Content-Type: application/json');
        echo '[]';
        exit();

    case 'commenter_ajax':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['publication_id'])) {
            $utilisateur->commenter($userId, (int)$_POST['publication_id'], $_POST['contenu'] ?? '');
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit();
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => false]);
        exit();
}

$vue->debutHtml('Crilay', $connecte);

switch ($action) {

    case 'accueil':
        $vue->afficherAccueil();
        break;

    case 'inscription':
        $erreur = $_SESSION['flash_error'] ?? '';
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        $vue->afficherInscription($tag->getTous(), $erreur, $success);
        break;

    case 'connexion':
        $erreur = $_SESSION['flash_error'] ?? '';
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        if ($success) {
            echo '<div class="alert alert-success">' . Langue::t($success) . '</div>';
        }
        $vue->afficherConnexion($erreur);
        break;

    case 'profil':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $user = $utilisateur->getById($userId);
        $userTags = $utilisateur->getTagsUtilisateur($userId);
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);
        if ($success) echo '<div class="alert alert-success">' . Langue::t($success) . '</div>';
        $mesAmis = $ami->getAmis($userId);
        $mesPublications = $utilisateur->getPublications($userId, array_column($mesAmis, 'id'));
        $mesPublicationsPerso = array_filter($mesPublications, function($p) use ($userId) { return $p['auteur_id'] == $userId; });
        $nbAbonnes = $utilisateur->compterAbonnes($userId);
        $vue->afficherProfil($user, $userTags, true, null, $mesAmis, array_values($mesPublicationsPerso), false, $nbAbonnes);
        break;

    case 'profil_edit':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $user = $utilisateur->getById($userId);
        $userTags = $utilisateur->getTagsUtilisateur($userId);
        $vue->afficherEditProfil($user, $userTags, $tag->getTous());
        break;

    case 'profil_voir':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $cibleId = (int)($_GET['id'] ?? 0);
        if ($cibleId === $userId) { header("Location: index.php?action=profil"); exit(); }
        $user = $utilisateur->getById($cibleId);
        if (!$user) { echo '<p>Utilisateur introuvable.</p>'; break; }
        $userTags = $utilisateur->getTagsUtilisateur($cibleId);
        $relation = $ami->getStatut($userId, $cibleId);
        $amisCible = $ami->getAmis($cibleId);
        $pubsCible = $utilisateur->getPublications($cibleId, array_column($amisCible, 'id'));
        $pubsCiblePerso = array_filter($pubsCible, function($p) use ($cibleId) { return $p['auteur_id'] == $cibleId; });
        $estAbonne = $utilisateur->estAbonne($userId, $cibleId);
        $nbAbonnes = $utilisateur->compterAbonnes($cibleId);
        $vue->afficherProfil($user, $userTags, false, $relation, $amisCible, array_values($pubsCiblePerso), $estAbonne, $nbAbonnes);
        break;

    case 'publication':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $pub = $utilisateur->getPublicationById((int)($_GET['id'] ?? 0), $userId);
        if (!$pub) { echo '<p>Publication introuvable.</p>'; break; }
        $commentaires = $utilisateur->getCommentaires($pub['id']);
        $vue->afficherPublication($pub, $commentaires, $userId);
        break;

    case 'feed':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $mesAmis = $ami->getAmis($userId);
        $amisIds = array_column($mesAmis, 'id');
        $publications = $utilisateur->getPublications($userId, $amisIds);
        $pubIds = array_column($publications, 'id');
        $commentCounts = $utilisateur->compterCommentaires($pubIds);
        $nouveaux = $ami->getSuggestionsAmis($userId, 6);
        $eventsPublics = $evenement->getEvenementsPublicsConnectes($userId, $amisIds);
        $vue->afficherFeed($publications, $nouveaux, $eventsPublics, $commentCounts);
        break;

    case 'recherche':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $filtres = [
            'q' => $_GET['q'] ?? '',
            'langue' => $_GET['langue'] ?? '',
            'tag_id' => $_GET['tag_id'] ?? '',
            'exclude_id' => $userId
        ];
        $resultats = $utilisateur->rechercher($filtres);
        $vue->afficherRecherche($resultats, $tag->getTous(), $filtres);
        break;

    case 'amis':
    case 'followers':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $cibleId = (int)($_GET['id'] ?? $userId);
        $abonnes = $utilisateur->getAbonnes($cibleId);
        $vue->afficherFollowers($abonnes, $cibleId, $userId);
        break;

    case 'messages':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $convs = $message->getConversations($userId);
        $vue->afficherConversations($convs);
        break;

    case 'ecrire':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $destId = (int)($_GET['dest'] ?? 0);
        $contact = $utilisateur->getById($destId);
        if (!$contact) { echo '<p>Utilisateur introuvable.</p>'; break; }
        $message->marquerLu($userId, $destId);
        $msgs = $message->getConversation($userId, $destId);
        $info = $_SESSION['flash_info'] ?? '';
        unset($_SESSION['flash_info']);
        $vue->afficherConversation($msgs, $contact, $ami, $userId, $info);
        break;

    case 'agenda':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $mesEvents = $evenement->getMesEvenements($userId);
        $invitations = $evenement->getInvitationsEnAttente($userId);
        $mesAmis = $ami->getAmis($userId);

        $amisIds = array_column($mesAmis, 'id');
        $eventsPublics = $evenement->getEvenementsPublicsConnectes($userId, $amisIds);
        $tousEvents = array_merge($mesEvents, $eventsPublics);

        usort($tousEvents, function($a, $b) { return strtotime($a['date_debut']) - strtotime($b['date_debut']); });
        $vue->afficherAgenda($tousEvents, $invitations, $mesAmis);
        break;

    case 'tutoriels':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherTutoriels();
        break;
    case 'ateliers':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherAteliers();
        break;
    case 'ressources':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherRessources();
        break;

    case 'forum':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherForum();
        break;
    case 'showcases':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherShowcases();
        break;

    case 'portfolio':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $user = $utilisateur->getById($userId);
        $userTags = $utilisateur->getTagsUtilisateur($userId);
        $vue->afficherPortfolio($user, $userTags);
        break;

    case 'equipe':
        $vue->afficherEquipe();
        break;
    case 'contact':
        $vue->afficherContact();
        break;

    case 'apropos':
        $vue->afficherPageStatique('about_title', 'about_text');
        break;
    case 'faq':
        $vue->afficherFAQ();
        break;
    case 'mentions':
        $vue->afficherPageStatique('legal_title', 'legal_text');
        break;
    case 'cgu':
        $vue->afficherPageStatique('cgu_title', 'cgu_text');
        break;
    case 'confidentialite':
        $vue->afficherPageStatique('privacy_title', 'privacy_text');
        break;

    default:
        $vue->afficherAccueil();
        break;
}

$vue->finHtml();
