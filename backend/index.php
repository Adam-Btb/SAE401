<?php
session_start();

define("CHARGE_AUTOLOAD", true);
require_once "inc/poo.inc.php";
define("CHARGE_BD", true);
require_once "inc/bd.inc.php";
require_once "inc/config.inc.php";

// Initialiser la langue
Langue::init();

// Instancier les classes
$utilisateur = new Utilisateur($cnx);
$tag = new Tag($cnx);
$ami = new Ami($cnx);
$message = new Message($cnx);
$evenement = new Evenement($cnx);
$vue = new Vues();

$action = $_GET['action'] ?? 'accueil';
$connecte = $utilisateur->estConnecte();
$userId = $_SESSION['user_id'] ?? null;

// ============================================
// ACTIONS QUI REDIRIGENT (POST / traitements)
// ============================================

switch ($action) {

    // --- INSCRIPTION (traitement POST) ---
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

    // --- CONNEXION (traitement POST) ---
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

    // --- DÉCONNEXION ---
    case 'deconnexion':
        $utilisateur->deconnecter();
        header("Location: index.php?lang=" . Langue::getLang());
        exit();

    // --- MISE À JOUR PROFIL (POST) ---
    case 'profil_update':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $utilisateur->mettreAJour($userId, $_POST, $_POST['tags'] ?? []);
            $_SESSION['flash_success'] = 'success_profile';
        }
        header("Location: index.php?action=profil&lang=" . Langue::getLang());
        exit();

    // --- DEMANDE D'AMI ---
    case 'demande_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->demanderAmi($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=profil_voir&id=" . (int)($_GET['id'] ?? 0) . "&lang=" . Langue::getLang());
        exit();

    // --- ACCEPTER AMI ---
    case 'accepter_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->accepter($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=amis&lang=" . Langue::getLang());
        exit();

    // --- REFUSER AMI ---
    case 'refuser_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->refuser($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=amis&lang=" . Langue::getLang());
        exit();

    // --- SUPPRIMER AMI ---
    case 'supprimer_ami':
        if ($connecte && !empty($_GET['id'])) {
            $ami->supprimer($userId, (int)$_GET['id']);
        }
        header("Location: index.php?action=amis&lang=" . Langue::getLang());
        exit();

    // --- ENVOYER MESSAGE (POST) ---
    case 'envoyer_msg':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['dest'])) {
            $destId = (int)$_GET['dest'];
            $result = $message->envoyer($userId, $destId, $_POST['contenu'] ?? '', $ami);
            if (!empty($result['msg'])) {
                $_SESSION['flash_info'] = $result['msg'];
            }
        }
        header("Location: index.php?action=ecrire&dest=" . (int)($_GET['dest'] ?? 0) . "&lang=" . Langue::getLang());
        exit();

    // --- CRÉER ÉVÉNEMENT (POST) ---
    case 'creer_event':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $evenement->creer($userId, $_POST, $_POST['participants'] ?? []);
        }
        header("Location: index.php?action=agenda&lang=" . Langue::getLang());
        exit();

    // --- SUPPRIMER ÉVÉNEMENT ---
    case 'suppr_event':
        if ($connecte && !empty($_GET['id'])) {
            $evenement->supprimer((int)$_GET['id'], $userId);
        }
        header("Location: index.php?action=agenda&lang=" . Langue::getLang());
        exit();

    // --- RÉPONDRE INVITATION ÉVÉNEMENT ---
    case 'repondre_event':
        if ($connecte && !empty($_GET['id']) && !empty($_GET['rep'])) {
            $rep = in_array($_GET['rep'], ['accepte', 'refuse']) ? $_GET['rep'] : 'refuse';
            $evenement->repondreInvitation((int)$_GET['id'], $userId, $rep);
        }
        header("Location: index.php?action=agenda&lang=" . Langue::getLang());
        exit();

    // --- PUBLIER (fil d'actualité) ---
    case 'publier':
        if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $utilisateur->publier($userId, $_POST['contenu'] ?? '');
        }
        header("Location: index.php?action=feed&lang=" . Langue::getLang());
        exit();
}

// ============================================
// ACTIONS QUI AFFICHENT UNE PAGE
// ============================================

$vue->debutHtml('MotuStar', $connecte);

switch ($action) {

    // --- ACCUEIL PUBLIC ---
    case 'accueil':
        $vue->afficherAccueil();
        break;

    // --- INSCRIPTION (formulaire) ---
    case 'inscription':
        $erreur = $_SESSION['flash_error'] ?? '';
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        $vue->afficherInscription($tag->getTous(), $erreur, $success);
        break;

    // --- CONNEXION (formulaire) ---
    case 'connexion':
        $erreur = $_SESSION['flash_error'] ?? '';
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        if ($success) {
            echo '<div class="alert alert-success">' . Langue::t($success) . '</div>';
        }
        $vue->afficherConnexion($erreur);
        break;

    // --- MON PROFIL ---
    case 'profil':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $user = $utilisateur->getById($userId);
        $userTags = $utilisateur->getTagsUtilisateur($userId);
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);
        if ($success) echo '<div class="alert alert-success">' . Langue::t($success) . '</div>';
        $vue->afficherProfil($user, $userTags, true);
        break;

    // --- ÉDITER PROFIL ---
    case 'profil_edit':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $user = $utilisateur->getById($userId);
        $userTags = $utilisateur->getTagsUtilisateur($userId);
        $vue->afficherEditProfil($user, $userTags, $tag->getTous());
        break;

    // --- VOIR PROFIL D'UN AUTRE ---
    case 'profil_voir':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $cibleId = (int)($_GET['id'] ?? 0);
        if ($cibleId === $userId) { header("Location: index.php?action=profil"); exit(); }
        $user = $utilisateur->getById($cibleId);
        if (!$user) { echo '<p>Utilisateur introuvable.</p>'; break; }
        $userTags = $utilisateur->getTagsUtilisateur($cibleId);
        $relation = $ami->getStatut($userId, $cibleId);
        $vue->afficherProfil($user, $userTags, false, $relation);
        break;

    // --- FIL D'ACTUALITÉ ---
    case 'feed':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $mesAmis = $ami->getAmis($userId);
        $amisIds = array_column($mesAmis, 'id');
        $publications = $utilisateur->getPublications($userId, $amisIds);
        $nouveaux = $utilisateur->getDerniersInscrits(6);
        $eventsPublics = $evenement->getEvenementsPublicsConnectes($userId, $amisIds);
        $vue->afficherFeed($publications, $nouveaux, $eventsPublics);
        break;

    // --- RECHERCHE ---
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

    // --- AMIS ---
    case 'amis':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $mesAmis = $ami->getAmis($userId);
        $demandes = $ami->getDemandesRecues($userId);
        $vue->afficherAmis($mesAmis, $demandes);
        break;

    // --- LISTE CONVERSATIONS ---
    case 'messages':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $convs = $message->getConversations($userId);
        $vue->afficherConversations($convs);
        break;

    // --- CONVERSATION AVEC UN CONTACT ---
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

    // --- AGENDA ---
    case 'agenda':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $mesEvents = $evenement->getMesEvenements($userId);
        $invitations = $evenement->getInvitationsEnAttente($userId);
        $mesAmis = $ami->getAmis($userId);
        // Ajouter événements publics des amis
        $amisIds = array_column($mesAmis, 'id');
        $eventsPublics = $evenement->getEvenementsPublicsConnectes($userId, $amisIds);
        $tousEvents = array_merge($mesEvents, $eventsPublics);
        // Trier par date
        usort($tousEvents, function($a, $b) { return strtotime($a['date_debut']) - strtotime($b['date_debut']); });
        $vue->afficherAgenda($tousEvents, $invitations, $mesAmis);
        break;

    // --- LEARN ---
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

    // --- COMMUNITY ---
    case 'forum':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherForum();
        break;
    case 'showcases':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $vue->afficherShowcases();
        break;

    // --- PORTFOLIO ---
    case 'portfolio':
        if (!$connecte) { header("Location: index.php?action=connexion"); exit(); }
        $user = $utilisateur->getById($userId);
        $userTags = $utilisateur->getTagsUtilisateur($userId);
        $vue->afficherPortfolio($user, $userTags);
        break;

    // --- ABOUT ---
    case 'equipe':
        $vue->afficherEquipe();
        break;
    case 'contact':
        $vue->afficherContact();
        break;

    // --- PAGES STATIQUES ---
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

    // --- DÉFAUT ---
    default:
        $vue->afficherAccueil();
        break;
}

$vue->finHtml();
