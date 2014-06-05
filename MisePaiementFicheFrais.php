<?php
/**
 * Script de contrôle et d'affichage du cas d'utilisation "Suivre le paiement fiche de frais"
 * @package default
 * @todo  RAS
 */
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si utilisateur non connectÃ©
if (!estUtilisateurConnecte()) {
    header("Location: Seconnecter.php");
}
require($repInclude . "entete.inc.html");
require($repInclude . "_sommaire.inc.php");

// acquisition des données entrées, ici l'id de visiteur, le mois et l'étape du traitement
$idVisiteur = lireDonnee("lstVisiteur", "");
$idMois = lireDonnee("lstMois", "");
$etape = lireDonnee("etape", "");

// structure de décision sur les différentes étapes du cas d'utilisation
if ($etape == "mettreEnPaiementFicheFrais") {
    modifierEtatFicheFrais($idConnexion, $idMois, $idVisiteur, 'MP');
}
?>

<!-- Division principale -->
<div id="contenu">
    <?php
    $lgVisiteur = obtenirDetailUtilisateur($idConnexion, $idVisiteur);
    $noMois = intval(substr($idMois, 4, 2));
    $annee = intval(substr($idMois, 0, 4));
    // Gestion des messages d'informations
    if ($etape == "mettreEnPaiementFicheFrais") {
        ?>
        <p class="info">La fiche de frais de <?php echo $lgVisiteur['nom'] . ' ' . $lgVisiteur['prenom']; ?> de <?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?> a bien Ã©tÃ© mise en paiement</p>        
        <?php
    }
    ?>
    <h1>Suivi des paiement des fiches de frais</h1>
    <?php
    $req = "SELECT utilisateur.id, nom, prenom, ficheFrais.mois, SUM(lignefraisforfait.quantite * fraisForfait.montant) AS montantForfait,";
    $req .= " (ficheFrais.montantValide - SUM(lignefraisforfait.quantite * fraisForfait.montant)) AS montantHorsForfait, ficheFrais.montantValide AS totalFicheFrais";
    $req .= " FROM utilisateur INNER JOIN ficheFrais ON utilisateur.id=ficheFrais.idVisiteur";
    $req .= "                  INNER JOIN lignefraisforfait ON (ficheFrais.idVisiteur = lignefraisforfait.idVisiteur  AND ficheFrais.mois = lignefraisforfait.mois)";
    $req .= "                  INNER JOIN fraisForfait ON lignefraisforfait.idFraisForfait = fraisForfait.id";
    $req .= " WHERE ficheFrais.idEtat = 'VA'";
    $req .= " GROUP BY nom, prenom, ficheFrais.mois";
    $idJeuFicheAPayer = mysql_query($req, $idConnexion);
    ?>
    <form id="formChoixFichesAPayer" method="post" action="">
        <p>
            <input type="hidden" id="etape" name="etape" value="mettreEnPaiementFicheFrais" />
            <input type="hidden" id="lstVisiteur" name="lstVisiteur" value="" />
            <input type="hidden" id="lstMois" name="lstMois" value="" />
        </p>
        <div style="clear:left;"><h2>Fiches de frais valid&eacutees</h2></div>
        <table style="color:white;" border="1">
            <tr><th rowspan="2" style="vertical-align:middle;">Visiteur&nbsp;m&eacutedical</th><th rowspan="2" style="vertical-align:middle;">Mois</th><th colspan="3">Fiches de frais</th><th rowspan="2" style="vertical-align:middle;">Actions</th></tr>
            <tr><th>Forfait</th><th>Hors forfait</th><th>Total</th></tr>
            <?php
            while ($lgFicheAPayer = mysql_fetch_array($idJeuFicheAPayer)) {
                $mois = $lgFicheAPayer['mois'];
                $noMois = intval(substr($mois, 4, 2));
                $annee = intval(substr($mois, 0, 4));
                ?>
                <tr align="center">
                    <td style="width:80px;white-space:nowrap;color:black;"><?php echo $lgFicheAPayer['nom'] . ' ' . $lgFicheAPayer['prenom']; ?></td>
                    <td style="width:80px;white-space:nowrap;color:black;"><?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?></td>
                    <td style="width:80px;white-space:nowrap;color:black;text-align:right;"><?php echo $lgFicheAPayer['montantForfait']; ?></td>
                    <td style="width:80px;white-space:nowrap;color:black;text-align:right;"><?php echo $lgFicheAPayer['montantHorsForfait']; ?></td>
                    <td style="width:80px;white-space:nowrap;color:black;text-align:right;"><?php echo $lgFicheAPayer['totalFicheFrais']; ?></td>
                    <td style="width:200px;white-space:nowrap;color:black;">
                        <div id="actionsFicheFrais" class="actions">
                            <a class="actionsCritiques" onclick="mettreEnPaiementFicheFrais('<?php echo $lgFicheAPayer['id']; ?>',<?php echo $lgFicheAPayer['mois']; ?>);" title="Mettre en paiement la fiche de frais">&nbsp;<img src="images/mettreEnPaiementIcon.png" class="icon" alt="icone Mettre en paiment" />&nbsp;Mettre en paiement&nbsp;</a>
                        </div>
                    </td>
                </tr>

                <?php
            }
            ?>
        </table>
    </form>
</div>
<script type="text/javascript">
    function mettreEnPaiementFicheFrais(idVisiteur,idMois) {
        document.getElementById('lstVisiteur').value = idVisiteur;
        document.getElementById('lstMois').value = idMois;
        document.getElementById('formChoixFichesAPayer').submit();
    }
</script>
<?php
require($repInclude . "pied.inc.html");
require($repInclude . "_fin.inc.php");
?>