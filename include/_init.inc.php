<?php
/** 
 * Initialise les ressources nÃƒÂ©cessaires au fonctionnement de l'application
 * @package default
 * @todo  RAS
 */
  require("_bdgestionDonnee.lib.php");
  require("_gestionSession.lib.php");
  require("_utilitairesEtgestionerreurs.lib.php");
  // dÃ©marrage ou reprise de la session
  initSession();
  // initialement, aucune erreur ...
  $tabErreurs = array();
    
  // Demande-t-on une dÃ©connexion ?
  $demandeDeconnexion = lireDonneeUrl("cmdDeconnecter");
  if ( $demandeDeconnexion == "on") {
      deconnecterUtilisateur();
      header("Location: cAccueil.php");
  }
    
  // établissement d'une connexion avec le serveur de données 
  // puis sélection de la BD qui contient les données des visiteurs et de leurs frais
  $idConnexion=connecterServeurBD();
  if (!$idConnexion) {
      ajouterErreur($tabErreurs, "Echec de la connexion au serveur MySql");
  }
  elseif (!activerBD($idConnexion)) {
      ajouterErreur($tabErreurs, "La base de donn&eacutees gsb_frais est inexistante ou non accessible");
  }
  
?>