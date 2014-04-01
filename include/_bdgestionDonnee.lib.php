<<?php
/** 
 * Regroupe les fonctions d'accès aux données.
 * @package default
 * @author Yvette Boidi
 * @todo Fonctions retournant plusieurs lignes sont à réécrire.
 */

/**
 * Se connecte au serveur de donnÃ©es MySql.                      
 * Se connecte au serveur de donnÃ©es MySql Ã  partir de valeurs
 * prÃ©dÃ©finies de connexion (hÃ´te, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succÃ¨s obtenu, le boolÃ©en false 
 * si problÃ¨me de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "adminGsb";
    $mdp = "akayah";
    return mysql_connect($hote, $login, $mdp);
}

/**
 * SÃ©lectionne (rend active) la base de donnÃ©es.
 * SÃ©lectionne (rend active) la BD prÃ©dÃ©finie gsb_frais sur la connexion
 * identifiÃ©e par $idCnx. Retourne true si succÃ¨s, false sinon.
 * @param resource $idCnx identifiant de connexion
 * @return boolean succÃ¨s ou Ã©chec de sÃ©lection BD 
 */
function activerBD($idCnx) {
    $bd = "gsb_frais";
    $query = "SET CHARACTER SET utf8";
    // Modification du jeu de caractÃ¨res de la connexion
    $res = mysql_query($query, $idCnx);
    $ok = mysql_select_db($bd, $idCnx);
    return $ok;
}

/**
 * Ferme la connexion au serveur de donnÃ©es.
 * Ferme la connexion au serveur de donnÃ©es identifiÃ©e par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    mysql_close($idCnx);
}

/**
 * Echappe les caractÃ¨res spÃ©ciaux d'une chaÃ®ne.
 * Envoie la chaÃ®ne $str Ã©chappÃ©e, cÃ d avec les caractÃ¨res considÃ©rÃ©s spÃ©ciaux
 * par MySql (tq la quote simple) prÃ©cÃ©dÃ©s d'un \, ce qui annule leur effet spÃ©cial
 * @param string $str chaÃ®ne Ã  Ã©chapper
 * @return string chaÃ®ne Ã©chappÃ©e 
 */
function filtrerChainePourBD($str) {
    if (!get_magic_quotes_gpc()) {
        // si la directive de configuration magic_quotes_gpc est activÃ©e dans php.ini,
        // toute chaÃ®ne reÃ§ue par get, post ou cookie est dÃ©jÃ  Ã©chappÃ©e 
        // par consÃ©quent, il ne faut pas Ã©chapper la chaÃ®ne une seconde fois                              
        $str = mysql_real_escape_string($str);
    }
    return $str;
}

/**
 * Fournit les informations sur un utilisateur demandÃ©. 
 * Retourne les informations du utilisateur d'id $unId sous la forme d'un tableau
 * associatif dont les clÃ©s sont les noms des colonnes(id, nom, prenom).
 * @param resource $idCnx identifiant de connexion
 * @param string $unId id de l'utilisateur
 * @return array  tableau associatif du utilisateur
 */
function obtenirDetailUtilisateur($idCnx, $unId) {
    $id = filtrerChainePourBD($unId);
    $requete = "select utilisateur.id, nom, prenom, libelleType from utilisateur inner join type on utilisateur.idType=type.id where utilisateur.id='" . $unId . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    $ligne = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne;
}

/**
 * Fournit les informations d'une fiche de frais. 
 * Retourne les informations de la fiche de frais du mois de $unMois (MMAAAA)
 * sous la forme d'un tableau associatif dont les clÃ©s sont les noms des colonnes
 * (nbJustitificatifs, idEtat, libelleEtat, dateModif, montantValide).
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandÃ© (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return array tableau associatif de la fiche de frais
 */
function obtenirDetailFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $ligne = false;
    $requete = "select IFNULL(nbJustificatifs,0) as nbJustificatifs, Etat.id as idEtat, libelle as libelleEtat, dateModif, montantValide
    from FicheFrais inner join Etat on idEtat = Etat.id 
    where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
    }
    mysql_free_result($idJeuRes);

    return $ligne;
}

/**
 * VÃ©rifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandÃ© (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return boolÃ©en existence ou non de la fiche de frais
 */
function existeFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idVisiteur from FicheFrais where idVisiteur='" . $unIdVisiteur .
            "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    $ligne = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }

    // si $ligne est un tableau, la fiche de frais existe, sinon elle n'exsite pas
    return is_array($ligne);
}

/**
 * Fournit le mois de la derniÃ¨re fiche de frais d'un visiteur.
 * Retourne le mois de la derniÃ¨re fiche de frais du visiteur d'id $unIdVisiteur.
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur  
 * @return string dernier mois sous la forme AAAAMM
 */
function obtenirDernierMoisSaisi($idCnx, $unIdVisiteur) {
    $requete = "select max(mois) as dernierMois from FicheFrais where idVisiteur='" .
            $unIdVisiteur . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    $dernierMois = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        $dernierMois = $ligne["dernierMois"];
        mysql_free_result($idJeuRes);
    }
    return $dernierMois;
}

/**
 * Ajoute une nouvelle fiche de frais et les Ã©lÃ©ments forfaitisÃ©s associÃ©s, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur, avec les Ã©lÃ©ments forfaitisÃ©s associÃ©s dont la quantitÃ© initiale
 * est affectÃ©e Ã  0. ClÃ´t Ã©ventuellement la fiche de frais prÃ©cÃ©dente du visiteur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandÃ© (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    // modification de la derniÃ¨re fiche de frais du visiteur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdVisiteur);
    $laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdVisiteur);
    if (is_array($laDerniereFiche) && $laDerniereFiche['idEtat'] == 'CR') {
        modifierEtatFicheFrais($idCnx, $dernierMois, $unIdVisiteur, 'CL');
    }

    // ajout de la fiche de frais Ã  l'Ã©tat CrÃ©Ã©
    $requete = "insert into FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values ('"
            . $unIdVisiteur
            . "','" . $unMois . "',0,NULL, 'CR', '" . date("Y-m-d") . "')";
    mysql_query($requete, $idCnx);

    // ajout des Ã©lÃ©ments forfaitisÃ©s
    $requete = "select id from FraisForfait";
    $idJeuRes = mysql_query($requete, $idCnx);
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        while (is_array($ligne)) {
            $idFraisForfait = $ligne["id"];
            // insertion d'une ligne frais forfait dans la base
            $requete = "insert into LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite)
                        values ('" . $unIdVisiteur . "','" . $unMois . "','" . $idFraisForfait . "',0)";
            mysql_query($requete, $idCnx);
            // passage au frais forfait suivant
            $ligne = mysql_fetch_assoc($idJeuRes);
        }
        mysql_free_result($idJeuRes);
    }
}

/**
 * Retourne le texte de la requÃªte select concernant les mois pour lesquels un 
 * visiteur a une fiche de frais. 
 * 
 * La requÃªte de sÃ©lection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels le visiteur $unIdVisiteur a une fiche de frais. 
 * @param string $unIdVisiteur id visiteur  
 * @param string $unEtat (facultatif)
 * @return string texte de la requÃªte select
 */
function obtenirReqMoisFicheFrais($unIdVisiteur, $unEtat = "") {
    if ($unEtat == "") {
        // Utilisation originelle de la fonction
        $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='"
                . $unIdVisiteur . "' order by fichefrais.mois desc ";
    } else {
        // On applique une restriction sur l'Ã©tat
        $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='"
                . $unIdVisiteur . "' and fichefrais.idetat = '"
                . $unEtat . "' order by fichefrais.mois desc ";
    }
    return $req;
}

/**
 * Retourne le texte de la requÃªte select concernant les Ã©lÃ©ments forfaitisÃ©s 
 * d'un visiteur pour un mois donnÃ©s. 
 * 
 * La requÃªte de sÃ©lection fournie permettra d'obtenir l'id, le libellÃ© et la
 * quantitÃ© des Ã©lÃ©ments forfaitisÃ©s de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demandÃ© (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requÃªte select
 */
function obtenirReqEltsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idFraisForfait, libelle, quantite from LigneFraisForfait
              inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait
              where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Retourne le texte de la requÃªte select concernant les Ã©lÃ©ments hors forfait 
 * d'un visiteur pour un mois donnÃ©s. 
 * 
 * La requÃªte de sÃ©lection fournie permettra d'obtenir l'id, la date, le libellÃ© 
 * et le montant des Ã©lÃ©ments hors forfait de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demandÃ© (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requÃªte select
 */
function obtenirReqEltsHorsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select id, date, libelle, montant from LigneFraisHorsForfait
              where idVisiteur='" . $unIdVisiteur
            . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Supprime une ligne hors forfait.
 * Supprime dans la BD la ligne hors forfait d'id $unIdLigneHF
 * @param resource $idCnx identifiant de connexion
 * @param string $idLigneHF id de la ligne hors forfait
 * @return void
 */
function supprimerLigneHF($idCnx, $unIdLigneHF) {
    $requete = "delete from LigneFraisHorsForfait where id = " . $unIdLigneHF;
    mysql_query($requete, $idCnx);
}

/**
 * Ajoute une nouvelle ligne hors forfait.
 * InsÃ¨re dans la BD la ligne hors forfait de libellÃ© $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu Ã  la date $uneDateHF pour la fiche de frais du mois
 * $unMois du visiteur d'id $unIdVisiteur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandÃ© (AAMMMM)
 * @param string $unIdVisiteur id du visiteur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libellÃ© du frais hors forfait 
 * @param double $unMontantHF montant du frais hors forfait
 * @return void
 */
function ajouterLigneHF($idCnx, $unMois, $unIdVisiteur, $uneDateHF, $unLibelleHF, $unMontantHF) {
    $unLibelleHF = filtrerChainePourBD($unLibelleHF);
    $uneDateHF = filtrerChainePourBD(convertirDateFrancaisVersAnglais($uneDateHF));
    $unMois = filtrerChainePourBD($unMois);
    $requete = "insert into LigneFraisHorsForfait(idVisiteur, mois, date, libelle, montant) 
                values ('" . $unIdVisiteur . "','" . $unMois . "','" . $uneDateHF . "','" . $unLibelleHF . "'," . $unMontantHF . ")";
    mysql_query($requete, $idCnx);
}

/**
 * Modifie les quantitÃ©s des Ã©lÃ©ments forfaitisÃ©s d'une fiche de frais. 
 * Met Ã  jour les Ã©lÃ©ments forfaitisÃ©s contenus  
 * dans $desEltsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisForfait, aprÃ¨s avoir filtrÃ© 
 * (annulÃ© l'effet de certains caractÃ¨res considÃ©rÃ©s comme spÃ©ciaux par 
 *  MySql) chaque donnÃ©e   
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandÃ© (MMAAAA) 
 * @param string $unIdVisiteur  id visiteur
 * @param array $desEltsForfait tableau des quantitÃ©s des Ã©lÃ©ments hors forfait
 * avec pour clÃ©s les identifiants des frais forfaitisÃ©s 
 * @return void  
 */
function modifierEltsForfait($idCnx, $unMois, $unIdVisiteur, $desEltsForfait) {
    $unMois = filtrerChainePourBD($unMois);
    $unIdVisiteur = filtrerChainePourBD($unIdVisiteur);
    foreach ($desEltsForfait as $idFraisForfait => $quantite) {
        $requete = "update LigneFraisForfait set quantite = " . $quantite
                . " where idVisiteur = '" . $unIdVisiteur . "' and mois = '"
                . $unMois . "' and idFraisForfait='" . $idFraisForfait . "'";
        mysql_query($requete, $idCnx);
    }
}

/**
 * ContrÃ´le les informations de connexionn d'un utilisateur.
 * VÃ©rifie si les informations de connexion $unLogin, $unMdp sont ou non valides.
 * Retourne les informations de l'utilisateur sous forme de tableau associatif 
 * dont les clÃ©s sont les noms des colonnes (id, nom, prenom, login, mdp)
 * si login et mot de passe existent, le boolÃ©en false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unLogin login 
 * @param string $unMdp mot de passe 
 * @return array tableau associatif ou boolÃ©en false 
 */
function verifierInfosConnexion($idCnx, $unLogin, $unMdp) {
    $unLogin = filtrerChainePourBD($unLogin);
    $unMdp = filtrerChainePourBD($unMdp);
    // le mot de passe est cryptÃ© dans la base avec la fonction de hachage md5
    $req = "select id, nom, prenom, login, mdp, idType from utilisateur where login='" . $unLogin . "' and mdp='" . $unMdp . "'";
    $idJeuRes = mysql_query($req, $idCnx);
    $ligne = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne;
}

/**
 * Modifie l'Ã©tat et la date de modification d'une fiche de frais
 *
 * Met Ã  jour l'Ã©tat de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois Ã  la nouvelle valeur $unEtat et passe la date de modif Ã  
 * la date d'aujourd'hui
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @param string $unEtat
 * @return void 
 */
function modifierEtatFicheFrais($idCnx, $unMois, $unIdVisiteur, $unEtat) {
    $requete = "update FicheFrais set idEtat = '" . $unEtat .
            "', dateModif = now() where idVisiteur ='" .
            $unIdVisiteur . "' and mois = '" . $unMois . "'";
    mysql_query($requete, $idCnx) or die(mysql_error());
}

/**
 * Retourne la requete d'obtention de la liste des visiteurs mÃ©dicaux
 *
 * Retourne la requÃªte d'obtention de la liste des visiteurs mÃ©dicaux (id, nom et prenom)
 * @return string $requete
 */
function obtenirReqListeVisiteurs() {
    $requete = "select id, nom, prenom from utilisateur where idType='V' order by nom";
    return $requete;
}

/**
 * Modifie les quantitÃ©s des Ã©lÃ©ments non forfaitisÃ©s d'une fiche de frais. 
 * Met Ã  jour les Ã©lÃ©ments non forfaitisÃ©s contenus  
 * dans $desEltsHorsForfaits
 * @param resource $idCnx identifiant de connexion
 * @param array $desEltsHorsForfait tableau des Ã©lÃ©ments hors forfait
 * avec pour clÃ©s les identifiants des frais hors forfait
 * @return void  
 */
function modifierEltsHorsForfait($idCnx, $desEltsHorsForfait) {
    foreach ($desEltsHorsForfait as $cle => $val) {
        switch ($cle) {
            case 'id':
                $idFraisHorsForfait = $val;
                break;
            case 'libelle':
                $libelleFraisHorsForfait = $val;
                break;
            case 'date':
                $dateFraisHorsForfait = $val;
                break;
            case 'montant':
                $montantFraisHorsForfait = $val;
                break;
        }
    }
    $requete = "update LigneFraisHorsForfait"
            . " set libelle = '" . filtrerChainePourBD($libelleFraisHorsForfait) . "',"
            . " date = '" . convertirDateFrancaisVersAnglais($dateFraisHorsForfait) . "',"
            . " montant = " . $montantFraisHorsForfait
            . " where id = " . $idFraisHorsForfait;
    mysql_query($requete, $idCnx);
}

/**
 * Modifie le nombre de justificatifs d'une fiche de frais
 *
 * Met Ã  jour le nombre de justificatifs de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois Ã  la nouvelle valeur $nbJustificatifs
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @param integer $nbJustificatifs
 * @return void 
 */
function modifierNbJustificatifsFicheFrais($idCnx, $unMois, $unIdVisiteur, $nbJustificatifs) {
    $requete = "update FicheFrais set nbJustificatifs = " . $nbJustificatifs .
            " where idVisiteur ='" . $unIdVisiteur . "' and mois = '" . $unMois . "'";
    mysql_query($requete, $idCnx);
}

/**
 * Reporte d'un mois une ligne de frais hors forfait
 * 
 * 
 * @param resource $idCnx identifiant de connexion
 * @param int $unIdLigneHF identifiant de ligne hors forfait
 * @return void
 */
function reporterLigneHorsForfait($idCnx, $unIdLigneHF) {
    mysql_query('CALL reporterLigneFraisHF(' . $unIdLigneHF . ');', $idCnx);
}

/**
 * Cloture les fiches de frais antÃ©rieur au mois $unMois
 *
 * Cloture les fiches de frais antÃ©rieur au mois $unMois
 * et au besoin, crÃ©er une nouvelle de fiche de frais pour le mois courant
 * @param resource $idCnx identifiant de connexion
  * @param string $unMois mois sous la forme aaaamm
  * @return void 
 */
function cloturerFichesFrais($idCnx, $unMois) {
    $req = "SELECT idVisiteur, mois FROM ficheFrais WHERE idEtat = 'CR' AND CAST(mois AS unsigned) < $unMois ;";
    $idJeuFichesFrais = mysql_query($req, $idCnx);
    while ($lgFicheFrais = mysql_fetch_array($idJeuFichesFrais)) {
        modifierEtatFicheFrais($idCnx, $lgFicheFrais['mois'], $lgFicheFrais['idVisiteur'], 'CL');
        // VÃ©rification de l'existence de la fiche de frais pour le mois courant
        $existeFicheFrais = existeFicheFrais($idCnx, $unMois, $lgFicheFrais['idVisiteur']);
        // si elle n'existe pas, on la crÃ©e avec les Ã©lÃ©ments de frais forfaitisÃ©s Ã  0
        if (!$existeFicheFrais) {
            ajouterFicheFrais($idCnx, $unMois, $lgFicheFrais['idVisiteur']);
        }
    }
}