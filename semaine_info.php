<?php
require('steamauth/steamauth.php');
try {
    $devmod = false;
    if($devmod) {
        $bdd = new PDO('mysql:host=127.0.0.1;dbname=oilcompagny;charset=utf8', 'root', 'toor');
    }else {
        $bdd = new PDO('mysql:host=bw8wl.myd.infomaniak.com;dbname=bw8wl_oil;charset=utf8', 'bw8wl_oil', 'x7Btd-lwfL4');
    }
} catch (PDOException $e) {
    die('[BDD] Erreurs, Please contact administrator | Raphael Tax | raphtnt#2339');
}

$getSettings = $bdd->query("SELECT * FROM settings");
$gSettings = $getSettings->fetch();

if (isset($_GET['semaine_info']) and $_GET['semaine_info'] > 0) {

    $getid = $_GET['semaine_info'];

    $gTrajetInfo = $bdd->prepare("SELECT * FROM trajet WHERE semaine = ?");
    $gTrajetInfo->execute(array($getid));
    $gTInfo = $gTrajetInfo->fetch();

    $gVenteInfo = $bdd->prepare("SELECT * FROM vente WHERE semaine = ?");
    $gVenteInfo->execute(array($getid));
    $gVInfo = $gVenteInfo->fetch();

}


function getPermissionDecode($bdd)
{
    $permissions = $bdd->prepare("SELECT * FROM member INNER JOIN rank ON member.rank_member = rank.rankname WHERE steamid_member = '" . $_SESSION['steamid'] . "'");
    $permissions->execute();
    $result = $permissions->fetch();
    return json_decode($result["permissions"]) != null ? json_decode($result["permissions"]) : array();
}

if (!isset($_SESSION['steamid'])) {
    loginbutton();
} else { ?>

    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
        <title>Oil Compagny</title>

    </head>
    <body>
    <?php if (!(in_array("authorization", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)))) {
        echo "Vous n'avez aucune autorisation a la OIL Compagny";
        return;
    } ?>

    <?php if (in_array("view_semaine_page", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>

        <h1 style="text-align: center; margin-top: 1rem;">Oil Compagny</h1>
        <h2 style="text-align: center; margin-top: 1rem;">Semaine <?= $getid ?></h2>
        <a href="index.php" style="font-size: 1.5rem; margin-left: 2.5rem;">retour</a>
        <?php
        $getAllPriceVenteWeek = $bdd->query("SELECT SUM(price) AS price_trajet_total_vente FROM vente WHERE semaine=".$getid)->fetch();
        $getAllPriceVenteWeekTaxe = ($getAllPriceVenteWeek[0] / 100) * $gSettings["settings_taxe"];
        ?>
        <h3 style="text-align: center;">CA : <?= $getAllPriceVenteWeek[0] ?></h3>
        <h3 style="text-align: center;">Taxe : <?= $getAllPriceVenteWeekTaxe ?></h3>

        <div class="container">
            <h3>Listes des trajets</h3>


            <table id="table_id" class="display">
                <thead>
                <tr style="text-align: center;">
                    <th>Prenom/Nom (Intérimaire)</th>
                    <th>Prenom/Nom Inscrit (Employé)</th>
                    <th>Prenom/Nom Receptionné (Employé)</th>
                    <th>Quantité</th>
                    <th>Argent donnée</th>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>Annulé</th>
                </tr>
                </thead>
                <tbody>
                <?php

                $getAllTrajet = $bdd->query("SELECT * FROM trajet WHERE semaine = '" . $getid . "'");
                while ($gAT = $getAllTrajet->fetch()) {

                    $getInfoValid1 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gAT["id_member_start"] . "'");
                    $getInfoValid1f = $getInfoValid1->fetch();
                    $getInfoValid2 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gAT["id_member_end"] . "'");
                    $getInfoValid2f = $getInfoValid2->fetch();
                    $getInfoValid3 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gAT["id_interim"] . "'");
                    $getInfoValid3f = $getInfoValid3->fetch();
                    ?>
                    <tr style="text-align: center;">
                        <td><?= $getInfoValid3f["prenom_interim"]; ?> <?= $getInfoValid3f["nom_interim"]; ?></td>
                        <td><?= $getInfoValid1f["prenom_interim"] ?> <?= $getInfoValid1f["nom_interim"] ?></td>
                        <td><?= $getInfoValid2f["prenom_interim"] ?> <?= $getInfoValid2f["nom_interim"] ?></td>
                        <td><?= $gAT["quantity_trajet"]; ?></td>
                        <td><?= $gAT["price_trajet"]; ?></td>
                        <td><?= $gAT["date_trajet_start"]; ?></td>
                        <td><?= $gAT["date_trajet_end"]; ?></td>
                        <td><?= $gAT["quantity_trajet"] == 0 ? "Oui" : "Non"; ?></td>
                    </tr>
                <?php }
                $getAllTrajet->closeCursor(); ?>
                </tbody>
            </table>

            <h3 style="margin-top: 2rem;">Listes des ventes</h3>
            <table id="table_vente" class="display">
                <thead>
                <tr style="text-align: center;">
                    <th>Prenom/Nom</th>
                    <th>Quantité</th>
                    <th>Argent Obtenue</th>
                    <th>Date de vente</th>
                </tr>
                </thead>
                <tbody>
                <?php

                $getAllVente = $bdd->query("SELECT * FROM vente WHERE semaine = '" . $getid . "'");
                while ($gAV = $getAllVente->fetch()) {

                    $getInfoValid1 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gAV["id_interim"] . "'");
                    $getInfoValid1f = $getInfoValid1->fetch();
                    ?>
                    <tr style="text-align: center;">
                        <td><?= $getInfoValid1f["prenom_interim"] ?> <?= $getInfoValid1f["nom_interim"] ?></td>
                        <td><?= $gAV["quantity"]; ?></td>
                        <td><?= $gAV["price"]; ?></td>
                        <td><?= $gAV["date_vente"]; ?></td>
                    </tr>
                <?php }
                $getAllVente->closeCursor(); ?>
                </tbody>
            </table>



            <h2 style="margin-top: 2rem;">Listes des employés</h2>
            <table id="employee" class="display">
                <thead>
                <tr style="text-align: center;">
                    <th>ID Interim</th>
                    <th>Prenom/Nom</th>
                    <th>Nombre de trajet (envoyé)</th>
                    <th>Nombre de trajet (receptionné)</th>
                    <th>Nombre de trajet (perso)</th>
                    <th>Nombre de vente effecuté</th>
                    <th>L'argent total déposer</th>
                    <th>Total quantité vendu</th>
                    <th>Différence farm-vente</th>
                    <th>Prime de vente</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $getEmploye = $bdd->query("SELECT * FROM member");

                while ($gEInfo = $getEmploye->fetch()) {

                    $getEmployeInfo = $bdd->query("SELECT * FROM interim WHERE id_interim = " . $gEInfo["id_member"]);
                    $getEI = $getEmployeInfo->fetch();

                    $getEmployeCountStart = $bdd->query("SELECT COUNT(*) FROM trajet WHERE id_member_start = ".$gEInfo['id_member']." AND semaine = '".$getid."'")->fetch();

                    $getEmployeCountEnd = $bdd->query("SELECT COUNT(*) FROM trajet WHERE id_member_end = " . $gEInfo['id_member']." AND semaine = '".$getid."' AND employe_trajet = 0")->fetch();
                    $getEmployeQuantityEnd = $bdd->query("SELECT SUM(quantity_trajet) AS quantity_trajet_total_end FROM trajet WHERE id_member_end = ".$gEInfo['id_member']." AND semaine = '".$getid."' AND employe_trajet = 0")->fetch();

                    $getEmployeCountPerso = $bdd->query("SELECT COUNT(*) FROM trajet WHERE id_member_end = " . $gEInfo['id_member']." AND semaine = '".$getid."' AND employe_trajet = 1")->fetch();
                    $getEmployeQuantityPerso = $bdd->query("SELECT SUM(quantity_trajet) AS quantity_trajet_total_end FROM trajet WHERE id_member_end = ".$gEInfo['id_member']." AND semaine = '".$getid."' AND employe_trajet = 1")->fetch();

                    $getEmployeQuantityVente = $bdd->query("SELECT SUM(quantity) AS quantity_trajet_total_vente FROM vente WHERE id_interim = ".$gEInfo['id_member']." AND semaine = '".$getid."'")->fetch();
                    $getEmployePriceVente = $bdd->query("SELECT SUM(price) AS price_trajet_total_vente FROM vente WHERE id_interim = ".$gEInfo['id_member']." AND semaine = '".$getid."'")->fetch();
                    $getEmployeCountVente = $bdd->query("SELECT COUNT(*) FROM vente WHERE id_interim = ".$gEInfo['id_member']." AND semaine = '".$getid."'")->fetch();

                    $difffarmvente = ($getEmployeQuantityEnd[0] + $getEmployeQuantityPerso[0]) - $getEmployeQuantityVente[0];

                    $primedevente = $bdd->query("SELECT SUM(salaire) AS price_trajet_total_salaire FROM vente WHERE id_interim = ".$gEInfo['id_member']." AND semaine = '".$getid."'")->fetch();

                    ?>

                    <tr style="text-align: center;">

                        <td><?= $gEInfo["id_member"]; ?></td>
                        <td><?= $getEI["prenom_interim"]; ?> <?= $getEI["nom_interim"]; ?></td>
                        <td><?= $getEmployeCountStart[0] ?></td>
                        <td><?= $getEmployeQuantityEnd[0] ?> (<?= $getEmployeCountEnd[0] ?>)</td>
                        <td><?= $getEmployeQuantityPerso[0] ?> (<?= $getEmployeCountPerso[0] ?>)</td>
                        <td><?= $getEmployeCountVente[0] ?></td>
                        <td><?= $getEmployePriceVente[0] ?></td>
                        <td><?= $getEmployeQuantityVente[0] ?></td>
                        <td><?= $difffarmvente ?></td>
                        <td><?= $primedevente[0] ?></td>
                    </tr>
                <?php }
                $getEmploye->closeCursor(); ?>
                </tbody>
            </table>


        </div>


    <?php } ?>

    <br><br><br>
    <div style="text-align: center;">
        <?php logoutbutton(); ?>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });

        $(document).ready(function () {
            $('#table_vente').DataTable();
        });

        $(document).ready(function () {
            $('#employee').DataTable();
        });

    </script>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8"
            src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

    </body>
    </html>

<?php } ?>