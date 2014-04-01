<?php
/** 
 * Regroupe les fonctions utilitaires et de gestion des erreurs.
 * @package default
 * @todo  fonction estMoisValide Ã  dÃ©finir complÃ¨tement ou Ã  supprimer
 */

/** 
 * Fournit le libellÃ© en franÃ§ais correspondant Ã  un numÃ©ro de mois.                     
 *
 * Fournit le libellÃ© franÃ§ais du mois de numÃ©ro $unNoMois.
 * Retourne une chaÃ®ne vide si le numÃ©ro n'est pas compris dans l'intervalle [1,12].
 * @param int numÃ©ro de mois
 * @return string identifiant de connexion
 */
function obtenirLibelleMois($unNoMois) {
    $tabLibelles = array(1=>"Janvier", 
                            "F&eacutevrier", "Mars", "Avril", "Mai", "Juin", "Juillet",
                            "Ao&ucirct", "Septembre", "Octobre", "Novembre", "D&eacutecembre");
    $libelle="";
    if ( $unNoMois >=1 && $unNoMois <= 12 ) {
        $libelle = $tabLibelles[$unNoMois];
    }
    return $libelle;
}

/** 
 * VÃ©rifie si une chaÃ®ne fournie est bien une date valide, au format JJ/MM/AAAA.                     
 * 
 * Retrourne true si la chaÃ®ne $date est une date valide, au format JJ/MM/AAAA, false sinon.
 * @param string date Ã  vÃ©rifier
 * @return boolean succÃ¨s ou Ã©chec
 */ 
function estDate($date) {
	$tabDate = explode('/',$date);
	if (count($tabDate) != 3) {
	    $dateOK = false;
    }
    elseif (!verifierEntiersPositifs($tabDate)) {
        $dateOK = false;
    }
    elseif (!checkdate($tabDate[1], $tabDate[0], $tabDate[2])) {
        $dateOK = false;
    }
    else {
        $dateOK = true;
    }
	return $dateOK;
}

/**
 * Transforme une date au format franÃ§ais jj/mm/aaaa vers le format anglais aaaa-mm-jj
 * @param $date au format  jj/mm/aaaa
 * @return string la date au format anglais aaaa-mm-jj
*/
function convertirDateFrancaisVersAnglais($date){
	@list($jour,$mois,$annee) = explode('/',$date);
	return date("Y-m-d", mktime(0, 0, 0, $mois, $jour, $annee));
}

/**
 * Transforme une date au format format anglais aaaa-mm-jj vers le format 
 * franÃ§ais jj/mm/aaaa 
 * @param $date au format  aaaa-mm-jj
 * @return string la date au format format franÃ§ais jj/mm/aaaa
*/
function convertirDateAnglaisVersFrancais($date){
    @list($annee,$mois,$jour) = explode('-',$date);
	return date("d/m/Y", mktime(0, 0, 0, $mois, $jour, $annee));
}

/**
 * Indique si une date est incluse ou non dans l'annÃ©e Ã©coulÃ©e.
 * 
 * Retourne true si la date $date est comprise entre la date du jour moins un an et la 
 * la date du jour. False sinon.   
 * @param $date date au format jj/mm/aaaa
 * @return boolean succÃ¨s ou Ã©chec
*/
function estDansAnneeEcoulee($date) {
	$dateAnglais = convertirDateFrancaisVersAnglais($date);
	$dateDuJourAnglais = date("Y-m-d");
	$dateDuJourMoinsUnAnAnglais = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));
	return ($dateAnglais >= $dateDuJourMoinsUnAnAnglais) && ($dateAnglais <= $dateDuJourAnglais);
}

/** 
 * VÃ©rifie si une chaÃ®ne fournie est bien numÃ©rique entiÃ¨re positive.                     
 * 
 * Retrourne true si la valeur transmise $valeur ne contient pas d'autres 
 * caractÃ¨res que des chiffres, false sinon.
 * @param string chaÃ®ne Ã  vÃ©rifier
 * @return boolean succÃ¨s ou Ã©chec
 */ 
function estEntierPositif($valeur) {
    return preg_match("/[^0-9]/", $valeur) == 0;
}

/** 
 * VÃ©rifie que chaque valeur est bien renseignÃ©e et numÃ©rique entiÃ¨re positive.
 *  
 * Renvoie la valeur boolÃ©enne true si toutes les valeurs sont bien renseignÃ©es et
 * numÃ©riques entiÃ¨res positives. False si l'une d'elles ne l'est pas.
 * @param array $lesValeurs tableau des valeurs
 * @return boolÃ©en succÃ¨s ou Ã©chec
 */ 
function verifierEntiersPositifs($lesValeurs){
    $ok = true;     
    foreach ( $lesValeurs as $val ) {
        if ($val=="" || ! estEntierPositif($val) ) {
            $ok = false;
        }
    }
    return $ok; 
}

/** 
 * Fournit la valeur d'une donnÃ©e transmise par la mÃ©thode get (url).                    
 * 
 * Retourne la valeur de la donnÃ©e portant le nom $nomDonnee reÃ§ue dans l'url, 
 * $valDefaut si aucune donnÃ©e de nom $nomDonnee dans l'url 
 * @param string nom de la donnÃ©e
 * @param string valeur par dÃ©faut 
 * @return string valeur de la donnÃ©e
 */ 
function lireDonneeUrl($nomDonnee, $valDefaut="") {
    if ( isset($_GET[$nomDonnee]) ) {
        $val = $_GET[$nomDonnee];
    }
    else {
        $val = $valDefaut;
    }
    return $val;
}

/** 
 * Fournit la valeur d'une donnÃ©e transmise par la mÃ©thode post 
 *  (corps de la requÃªte HTTP).                    
 * 
 * Retourne la valeur de la donnÃ©e portant le nom $nomDonnee reÃ§ue dans le corps de la requÃªte http, 
 * $valDefaut si aucune donnÃ©e de nom $nomDonnee dans le corps de requÃªte
 * @param string nom de la donnÃ©e
 * @param string valeur par dÃ©faut 
 * @return string valeur de la donnÃ©e
 */ 
function lireDonneePost($nomDonnee, $valDefaut="") {
    if ( isset($_POST[$nomDonnee]) ) {
        $val = $_POST[$nomDonnee];
    }
    else {
        $val = $valDefaut;
    }
    return $val;
}

/** 
 * Fournit la valeur d'une donnÃ©e transmise par la mÃ©thode get (url) ou post 
 *  (corps de la requÃªte HTTP).                    
 * 
 * Retourne la valeur de la donnÃ©e portant le nom $nomDonnee
 * reÃ§ue dans l'url ou corps de requÃªte, 
 * $valDefaut si aucune donnÃ©e de nom $nomDonnee ni dans l'url, ni dans corps.
 * Si le mÃªme nom a Ã©tÃ© transmis Ã  la fois dans l'url et le corps de la requÃªte,
 * c'est la valeur transmise par l'url qui est retournÃ©e.  
 * @param string nom de la donnÃ©e
 * @param string valeur par dÃ©faut 
 * @return string valeur de la donnÃ©e
 */ 
function lireDonnee($nomDonnee, $valDefaut="") {
    if ( isset($_GET[$nomDonnee]) ) {
        $val = $_GET[$nomDonnee];
    }
    elseif ( isset($_POST[$nomDonnee]) ) {
        $val = $_POST[$nomDonnee];
    }
    else {
        $val = $valDefaut;
    }
    return $val;
}

/** 
 * Ajoute un message dans le tableau des messages d'erreurs.                    
 * 
 * Ajoute le message $msg en fin de tableau $tabErr. Ce tableau est passÃ© par 
 * rÃ©fÃ©rence afin que les modifications sur ce tableau soient visibles de l'appelant.  
 * @param array $tabErr  
 * @param string message
 * @return void
 */ 
function ajouterErreur(&$tabErr,$msg) {
    $tabErr[count($tabErr)]=$msg;
}

/** 
 * Retourne le nombre de messages d'erreurs enregistrÃ©s.                    
 * 
 * Retourne le nombre de messages d'erreurs enregistrÃ©s dans le tableau $tabErr. 
 * @param array $tabErr tableau des messages d'erreurs  
 * @return int nombre de messages d'erreurs
 */ 
function nbErreurs($tabErr) {
    return count($tabErr);
}
 
/** 
 * Fournit les messages d'erreurs sous forme d'une liste Ã  puces HTML.                    
 * 
 * Retourne le source HTML, division contenant une liste Ã  puces, d'aprÃ¨s les
 * messages d'erreurs contenus dans le tableau des messages d'erreurs $tabErr. 
 * @param array $tabErr tableau des messages d'erreurs  
 * @return string source html
 */ 
function toStringErreurs($tabErr) {
    $str = '<div class="erreur">';
    $str .= '<ul>';
    foreach($tabErr as $erreur){
        $str .= '<li>' . $erreur . '</li>';
	}
    $str .= '</ul>';
    $str .= '</div>';
    return $str;
} 

/** 
 * Echappe les caractÃ¨res considÃ©rÃ©s spÃ©ciaux en HTML par les entitÃ©s HTML correspondantes.
 *  
 * Renvoie une copie de la chaÃ®ne $str Ã  laquelle les caractÃ¨res considÃ©rÃ©s spÃ©ciaux
 * en HTML (tq la quote simple, le guillemet double, les chevrons), auront Ã©tÃ©
 * remplacÃ©s par les entitÃ©s HTML correspondantes. 
 * @param string $str chaÃ®ne Ã  Ã©chapper
 * @return string chaÃ®ne Ã©chappÃ©e 
 */ 
function filtrerChainePourNavig($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** 
 * Vérifie la validité des données d'une ligne de frais hors forfait.
 *  
 * Renseigne le tableau des messages d'erreurs d'aprÃ¨s les erreurs rencontrÃ©es
 * sur chaque donnÃ©e d'une ligne de frais hors forfait : vÃ©rifie que chaque 
 * donnÃ©e est bien renseignÃ©e, le montant est numÃ©rique positif, la date valide
 * et dans l'annÃ©e Ã©coulÃ©e.  
 * @param array $date date d'engagement de la ligne de frais HF
 * @param array $libelle libellÃ© de la ligne de frais HF
 * @param array $montant montant de la ligne de frais HF
 * @param array $tabErrs tableau des messages d'erreurs passÃ© par rÃ©fÃ©rence
 * @return void
 */ 
function verifierLigneFraisHF($date, $libelle, $montant, &$tabErrs) {
    // vérification du libellÃ© 
    if ($libelle == "") {
		ajouterErreur($tabErrs, "Le libell&eacute doit Ãªtre renseign&eacute.");
	}
	// vérification du montant
	if ($montant == "") {
		ajouterErreur($tabErrs, "Le montant doit &ecirctre renseign&eacute.");
	}
	elseif ( !is_numeric($montant) || $montant < 0 ) {
        ajouterErreur($tabErrs, "Le montant doit &ecirctre num&eacuterique positif.");
    }
    // vérification de la date d'engagement
	if ($date == "") {
		ajouterErreur($tabErrs, "La date d'engagement doit &ecirctre renseign&eacutee.");
	}
	elseif (!estDate($date)) {
		ajouterErreur($tabErrs, "La date d'engagement doit &ecirctre valide au format JJ/MM/AAAA");
	}	
	elseif (!estDansAnneeEcoulee($date)) {
	    ajouterErreur($tabErrs,"La date d'engagement doit se situer dans l'ann&eacutee &eacutecoul&eacutee");
    }
}
?>
