<?php
require("_gestionSession.lib.php");

// démarrage ou reprise de la session
initSession();

// page inaccessible si utilisateur non connecté
if (!estUtilisateurConnecte()) {
    header("Location:Seconnecter.php");
}
require("_utilitairesEtgestionerreurs.lib.php");
require('fpdf.php');

class PDF extends FPDF {

    // En-tête
    function Header() {
        // Logo
        $this->Image('../images/logo.jpg', 90, 6, 30);
    }

    // Pied de page
    function Footer() {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        $this->SetFont('Times', 'I', 8);
        // Numérotation des pages
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function enteteFicheFrais($bdd, $idMois, $idVisiteur) {
        $this->SetTextColor(31, 73, 125);
        $this->SetFont('Times', 'B', 15);
        // Saut de ligne + Décalage à droite + Texte + Saut de ligne
        $this->Ln(30);
        $this->Cell(10);
        $this->Cell(170, 10, utf8_decode('REMBOURSEMENT DE FRAIS ENGAGÉS'), 0, 0, 'C');
        $idJeuFicheDeFrais = $bdd->query('select nom, prenom from utilisateur join fichefrais on id = idVisiteur where id="' . $idVisiteur . '" and mois="' . $idMois . '";');
        $lgFicheFrais = $idJeuFicheDeFrais->fetch();
        $idJeuFicheDeFrais->closeCursor();
        $this->Ln(15);
        $this->Cell(10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Times', '', 12);
        $this->Cell(50, 7, "Visiteur", 0);
        $this->Cell(40, 7, $idVisiteur, 0);
        $this->Cell(80, 7, utf8_decode($lgFicheFrais['prenom']) . " " . strtoupper(utf8_decode($lgFicheFrais['nom'])), 0);
        $this->Ln(10);
        $this->Cell(10);
        $this->Cell(50, 7, "Mois", 0);
        $noMois = intval(substr($idMois, 4, 2));
        $annee = intval(substr($idMois, 0, 4));
        $this->Cell(40, 7, obtenirLibelleMois($noMois) . ' ' . $annee, 0);
    }

    function tabFraisForfaits($bdd, $idMois, $idVisiteur) {
        // Entêtes de colonnes
        $this->Ln(15);
        $this->Cell(10);
        $this->SetTextColor(31, 73, 125);
        $this->SetFont('Times', 'BI', 12);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(50, 7, 'Frais forfaitaires', 'LTB', 0, 'C', true);
        $this->Cell(40, 7, utf8_decode('Quantité'), 'TB', 0, 'C', true);
        $this->Cell(40, 7, 'Montant unitaire', 'TB', 0, 'C', true);
        $this->Cell(40, 7, 'Total', 'TRB', 0, 'C', true);
        // Données
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Times', '', 12);
        $idJeuFraisForfait = $bdd->query("select libelle, quantite, montant, (quantite*montant) as total from LigneFraisForfait inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait where idVisiteur='" . $idVisiteur . "' and mois='" . $idMois . "'");
        while ($lgFraisForfait = $idJeuFraisForfait->fetch()) {
            $this->Cell(10);
            $this->Cell(50, 7, $lgFraisForfait['libelle'], 1, 0, 'L', true);
            $this->Cell(40, 7, $lgFraisForfait['quantite'], 1, 0, 'R', true);
            $this->Cell(40, 7, $lgFraisForfait['montant'], 1, 0, 'R', true);
            $this->Cell(40, 7, $lgFraisForfait['total'], 1, 0, 'R', true);
            $this->Ln();
        }
        $idJeuFraisForfait->closeCursor();
    }

    function tabFraisHorsForfaits($bdd, $idMois, $idVisiteur) {
        $this->Ln(5);
        $this->Cell(10);
        $this->SetTextColor(31, 73, 125);
        $this->SetFont('Times', 'BI', 12);
        $this->Cell(170, 10, 'Autres frais', 0, 0, 'C');
        // Entêtes de colonnes
        $this->Ln(10);
        $this->Cell(10);
        $this->SetTextColor(31, 73, 125);
        $this->SetFont('Times', 'BI', 12);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(50, 7, 'Date', 'LTB', 0, 'C', true);
        $this->Cell(80, 7, utf8_decode('Libellé'), 'TB', 0, 'C', true);
        $this->Cell(40, 7, 'Montant', 'TRB', 0, 'C', true);
        // Données
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Times', '', 12);
        $idJeuFraisHorsForfait = $bdd->query("select id, date, libelle, montant from LigneFraisHorsForfait where idVisiteur='" . $idVisiteur . "' and mois='" . $idMois . "'");
        while ($lgFraisHorsForfait = $idJeuFraisHorsForfait->fetch()) {
            $this->Cell(10);
            $this->Cell(50, 7, convertirDateAnglaisVersFrancais($lgFraisHorsForfait['date']), 1, 0, 'L', true);
            $this->Cell(80, 7, $lgFraisHorsForfait['libelle'], 1, 0, 'L', true);
            $this->Cell(40, 7, $lgFraisHorsForfait['montant'], 1, 0, 'R', true);
            $this->Ln();
        }
        $idJeuFraisHorsForfait->closeCursor();
    }

    function afficheTotal($bdd, $idMois, $idVisiteur) {
        $this->Ln();
        $this->Cell(100);
        $idJeuFicheFrais = $bdd->query("select montantValide from ficheFrais where idVisiteur='" . $idVisiteur . "' and mois='" . $idMois . "'");
        $lgFicheFrais = $idJeuFicheFrais->fetch();
        $idJeuFicheFrais->closeCursor();
        $noMois = intval(substr($idMois, 4, 2));
        $annee = intval(substr($idMois, 0, 4));
        $this->Cell(40, 7, 'TOTAL ' . $noMois . '/' . $annee, 1, 0, 'L', true);
        $this->Cell(40, 7, $lgFicheFrais['montantValide'], 1, 0, 'R', true);
    }

    function afficheSignature($bdd, $idMois, $idVisiteur) {
        $this->Ln(20);
        $this->Cell(100);
        $idJeuFicheFrais = $bdd->query("select dateModif from ficheFrais where idVisiteur='" . $idVisiteur . "' and mois='" . $idMois . "'");
        $lgFicheFrais = $idJeuFicheFrais->fetch();
        $idJeuFicheFrais->closeCursor();
        $noJour = intval(substr($lgFicheFrais['dateModif'], 8, 2));
        $noMois = intval(substr($lgFicheFrais['dateModif'], 5, 2));
        $annee = intval(substr($lgFicheFrais['dateModif'], 0, 4));
        $this->Cell(80, 7, utf8_decode('Fait à Paris, le ' . $noJour . ' ' . obtenirLibelleMois($noMois) . ' ' . $annee), 0, 0, 'L', true);
        $this->Ln(10);
        $this->Cell(100);
        $this->Cell(80, 7, 'Vu l\'agent comptable', 0, 0, 'L', true);
        $this->Ln(10);
        $this->Cell(100);
        $this->Image('../images/signatureComptable.png', null, null, 70);
    }

    function afficheFicheFrais($idMois, $idVisiteur) {
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetFont('Times', '', 12);
        // Connexion à la BDD en PDO
        try {
            $bdd = new PDO('mysql:host=localhost;dbname=gsb_frais', 'adminGsb', 'akayah');
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        // Affichage de l'entête de la fiche de frais
        $this->enteteFicheFrais($bdd, $idMois, $idVisiteur);
        // Affichage des frais forfaitisés
        $this->tabFraisForfaits($bdd, $idMois, $idVisiteur);
        // Affichage des frais hors forfaits
        $this->tabFraisHorsForfaits($bdd, $idMois, $idVisiteur);
        // Affichage du total
        $this->afficheTotal($bdd, $idMois, $idVisiteur);
        // Affichage de la date et de la signature du document
        $this->afficheSignature($bdd, $idMois, $idVisiteur);
    }

}

$mois = lireDonneePost("idMois", "");
$visiteur = lireDonneePost("idVisiteur", "");
$fichier = '../pdf/' . $mois . $visiteur . '.pdf';

// Si l'adresse de l'utilitaire d'export a été tapé manuellement
if (empty($mois) or empty($visiteur)) {
    header("Location: ../cAccueil.php");
}

// Si le fichier n'existe pas encore, on le génère
if (!file_exists($fichier)) {
    // Instanciation de la classe dérivée
    $pdf = new PDF();
    $pdf->afficheFicheFrais($mois, $visiteur);
    $pdf->Output($fichier);
}
// Connexion à la BDD en PDO
try {
    $bdd = new PDO('mysql:host=localhost;dbname=gsb_frais', 'adminGsb', 'akayah');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
$idJeuFicheFrais = $bdd->query('select nom, prenom from utilisateur join fichefrais on id = idVisiteur where id="' . $visiteur . '" and mois="' . $mois . '";');
$lgFicheFrais = $idJeuFicheFrais->fetch();
$idJeuFicheFrais->closeCursor();
$noMois = intval(substr($mois, 4, 2));
$annee = intval(substr($mois, 0, 4));
header('Content-Type: application/x-download');
header('Content-Disposition: inline; filename="Fiche_de_frais_' . utf8_decode($lgFicheFrais['prenom']) . '_' . strtoupper(utf8_decode($lgFicheFrais['nom'])) . '_' . obtenirLibelleMois($noMois) . '_' . $annee . '.pdf');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
readfile($fichier);
?>