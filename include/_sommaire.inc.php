<?php
/** 
 * Contient la division pour le sommaire, sujet à  des variations suivant la 
 * connexion ou non d'un utilisateur, suivant le type de cet utilisateur 
 * @todo  RAS
 */

?>
    <!-- Division pour le sommaire -->
    <div id="menuGauche">
     <div id="infosUtil">
    <?php      
      if (estUtilisateurConnecte() ) {
          $idUser = obtenirIdUserConnecte() ;
          $lgUser = obtenirDetailUtilisateur($idConnexion, $idUser);
          $nom = $lgUser['nom'];
          $prenom = $lgUser['prenom'];
          $libelleType = $lgUser['libelleType'];
    ?>
        <h2>
    <?php  
            echo $nom . " " . $prenom ;
    ?>
        </h2>
        <h3>
            <?php 
               echo $libelleType ;
          ?>
             </h3>        
    <?php
       }
    ?>  
      </div>  
<?php      
  if (estUtilisateurConnecte() ) {
?>
        <ul id="menuList">
           <li class="smenu">
              <a href="cAccueil.php" title="Page d'accueil">Accueil</a>
           </li>
           <li class="smenu">
              <a href="SeDeconnecter.php" title="Se d&eacuteconnecter">Se d&eacuteconnecter</a>
           </li>
           <?php 
            if ($libelleType == "Visiteur médical") 
                {
            ?>
              <li class="smenu">
              <a href="SaisieFicheFrais.php" title="Saisie fiche de frais du mois courant">Saisie fiche de frais</a>
           </li>
           <li class="smenu">
              <a href="ConsultFicheFrais.php" title="Consultation de mes fiches de frais">Mes fiches de frais</a>
           </li>
        <?php 
            }
            if ($libelleType == "Comptable") 
                {
            ?>
                <li class="smenu">
                    <a href="cValideFicheFrais.php" title="Validation des fiches de frais du mois pr&eacutec&eacutedent">Validation des fiches</a>
                </li>
                <li class="smenu">
                    <a href="MisePaiementFicheFrais.php" tilte="Mise en paiement des fiches frais">mois pr&eacutec&eacutedent</a>
                </li>
                <a href="consulterfichefrais.php" tilte="voir les statiques">voir les statistiques des fiches frais</a>
                <?php 
                }
            ?>
        </ul>
        <?php
          // affichage des éventuelles erreurs déjà  détectées
          if ( nbErreurs($tabErreurs) > 0 ) {
              echo toStringErreurs($tabErreurs) ;
          }
  }
        ?>
    </div>