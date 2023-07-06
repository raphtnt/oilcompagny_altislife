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

$semaine_trajet = date('W-Y');

function getPermissionDecode($bdd) {
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
<!--        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">-->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
        <title>Oil Compagny</title>

        <style>

            * {
                box-sizing: border-box;
            }

            *:focus {
                outline: none;
            }

            body {
                text-align: center;
                margin: 0;
                padding: 0;
            }

            #calNote {
                color: white;
            }

            .panel {
                padding: 20px 40px;
                width: 600px;
                margin: auto;
            }

            .panel-primary {
                background-color: #edf2ef;
            }

            * {
                box-sizing: border-box;
            }

            *:focus {
                outline: none;
            }

            body {
                text-align: center;
                margin: 0;
                padding: 0;
            }

            .panel {
                padding: 20px 40px;
                width: 600px;
                margin: auto;
            }

            .panel-primary {
                background-color: #edf2ef;
            }

            .panel-calculator {
                width: 350px;
                background-color: #041936;
                border-radius: 20px;
                margin: auto;
                padding: 20px 10px;
            }
            .panel-calculator table td {
                width: 25%;
            }
            .panel-calculator form {
                position: relative;
            }
            .panel-calculator form .note {
                position: absolute;
                right: 0;
                top: 0;
                color: #00c4ff;
                width: 100px;
            }

            .calView {
                width: 100%;
                background-color: #041936;
                padding: 40px;
                color: white;
                border: 0;
                outline: none;
                font-size: 30px;
                border-radius: 20px;
                letter-spacing: 2px;
            }
            .calView:focus {
                outline-width: 0;
            }

            .button {
                text-align: center;
                color: white;
                border: none;
                background-color: #041936;
                width: 100%;
                height: 64px;
                font-size: 25px;
                margin: 2px;
                border-radius: 20px;
                font-weight: 100;
                cursor: pointer;
            }

            .equal {
                background: linear-gradient(90deg, #00c4ff 10%, #6c00ff 90%);
            }

            .nav-tabs {
                justify-content: center;
            }

            .msg {
                margin-top: 2rem;
                font-size: 3rem;
                text-align: center;
            }

            .info-c {
                font-size: 1.1rem;
                font-weight: bold;
                margin: 1rem 0;
            }

        </style>

    </head>
    <body>
    <?php if (!(in_array("authorization", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)))) {
        echo "Vous n'avez aucune autorisation a la OIL Compagny<br>";
        echo "Votre SteamID : ".$_SESSION["steamid"];
        return;
    }?>


    <div class="container">
        <h1 style="text-align: center; margin-top: 2rem;">Oil Compagny</h1>

        <nav class="text-center" style="margin-top: 1.5rem;">
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <?= ((in_array("view_dashboard", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)))) ? '<button class="nav-link" id="nav-dashboard-tab" data-bs-toggle="tab" data-bs-target="#nav-dashboard" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Dashboard</button>' : "" ?>
                <?= ((in_array("view_global", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)))) ? '<button class="nav-link active" id="nav-all-tab" data-bs-toggle="tab" data-bs-target="#nav-all" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Global</button>' : "" ?>
                <?= ((in_array("view_settings", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)))) ? '<button class="nav-link" id="nav-settings-tab" data-bs-toggle="tab" data-bs-target="#nav-settings" type="button" role="tab" aria-controls="nav-settings" aria-selected="false">Settings</button>' : "" ?>
            </div>
        </nav>

        <?php

        $getSettings = $bdd->query("SELECT * FROM settings");
        $gSettings = $getSettings->fetch();

        $getOldPrice = $gSettings["info_ancien"];
        $getOldTaxe = $gSettings["info_taxe"];
        $getOldPayeInterim = $gSettings["info_payeinterim"];
        $getOldDepenseEntreprise = $gSettings["info_depenseentreprise"];
        $getOldSalaire = $gSettings["info_salaire"];
        $getOldPerte = $gSettings["info_pertes"];
        $getOldBenefice = $gSettings["info_benefice"];

        $getAllDepense = $bdd->query("SELECT SUM(montant) AS depenses_depense FROM depenseperte WHERE depense=1")->fetch();
        $getAllPerte = $bdd->query("SELECT SUM(montant) AS depenses_perte FROM depenseperte WHERE perte=1")->fetch();
        $gAD = $getAllDepense[0] - $gSettings["info_depenseentreprise"];
        $gAP = $getAllPerte[0] - $gSettings["info_pertes"];


        $getAllInterimVente = $bdd->query("SELECT SUM(nbr_interim) AS price_vente_interim FROM vente")->fetch();
        $gAIV = $getAllInterimVente[0] * $gSettings["trajet_interimaire_vente"];

        $getAllQuantityVente = $bdd->query("SELECT SUM(quantity) AS price_trajet_total_moinssalaire FROM vente")->fetch();
        $getAQV = $getAllQuantityVente[0] * $gSettings["cout_total_petrol"];
        $getAllPriceVente = $bdd->query("SELECT SUM(price) AS price_trajet_total_vente FROM vente")->fetch();
        $getAPV = $getAllPriceVente[0] - $getOldPrice;

        $getAllQuantityPerte = $bdd->query("SELECT SUM(quantity) AS price_trajet_total_perte FROM depenseperte")->fetch();
        $getAllQuantitysTrajetEmploye = $bdd->query("SELECT SUM(quantity_trajet) AS price_trajet_total_employe FROM trajet WHERE employe_trajet = 1")->fetch();
        $getAllQuantitysTrajetInterim = $bdd->query("SELECT SUM(quantity_trajet) AS price_trajet_total_interim FROM trajet WHERE employe_trajet = 0")->fetch();
        $getAllQuantityTrajetEmploye = $bdd->query("SELECT SUM(price_trajet) AS price_trajet_total_employe FROM trajet WHERE employe_trajet = 1")->fetch();
        $getAllQuantityTrajetInterim = $bdd->query("SELECT SUM(price_trajet) AS price_trajet_total_employe FROM trajet WHERE employe_trajet = 0")->fetch();

        $getQuantity = ($getAllQuantitysTrajetEmploye[0] + $getAllQuantitysTrajetInterim[0]) - $getAllQuantityVente[0] - $getAllQuantityPerte[0];


        $prime = $bdd->query("SELECT SUM(somme) AS price_trajet_total_prime FROM prime")->fetch();

        $salaire1 = $bdd->query("SELECT SUM(salaire) AS price_trajet_total_salaire FROM vente")->fetch();
        $salaire2 = $bdd->query("SELECT SUM(somme) AS price_trajet_total_prime FROM prime")->fetch();
        $salaire = $salaire1[0] + $salaire2[0];
        $salaireNow = $salaire - $getOldSalaire;

        $getTaxe = ($getAPV/100) * $gSettings["settings_taxe"];
        $getTaxes = $getTaxe - $gSettings["info_taxe"];

        $payeInterim = ($getAQV + $getAllQuantityTrajetEmploye[0]);
        $payeInterimNow = $payeInterim - $gSettings["info_payeinterim"];


        $depenseTotal = $getTaxe + $payeInterim + $getAllDepense[0] + $salaire + $getAllPerte[0];
        $benefice = $getAllPriceVente[0] - $getTaxe - $payeInterim - $getAllDepense[0] - $salaire - $getAllPerte[0];
        $beneficeNow = $getAPV - $getTaxes - $payeInterimNow - $gAD - $salaireNow - $gAP;

        $capital = $gSettings["capital"];
        $compte01 = ((($getAllQuantitysTrajetEmploye[0] + $getAllQuantitysTrajetInterim[0]) * $gSettings["cout_total_petrol"]) + $getAllDepense[0]) + $getAllPerte[0] + $prime[0];
        $compte = ($capital - $compte01) + $getAllPriceVente[0];
        $compteAfterTaxe = $compte - $gSettings["info_taxe"];


        ?>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade " id="nav-dashboard" role="tabpanel" aria-labelledby="nav-all-tab">

                <?php
                if(isset($_POST["submit_searchweek"])) {
                    $week = htmlspecialchars($_POST["week"]);
                    header("Location: semaine_info.php?semaine_info=". $week);
                }

                ?>

                <div class="container" style="margin-top: 2rem;">
                    <form action="" method="POST">
                        <h2 style="margin-bottom: 1.5rem;">Recherche d'une semaine</h2>
                        <label>
                            <input type="text" name="week" placeholder="ex: 05-2022" style="margin-right: 1rem;">
                        </label>
                        <button class="btn btn-primary" type="submit" name="submit_searchweek" style="margin-left: 1rem;">Rechercher</button>

                        <h2 style="margin-top: 2rem;">Informations</h2>

                        <div class="row">
                            <div class="col-4">
                                <p class="info-c">Capital : <?= $capital ?></p>
                            </div>
                            <div class="col-4">
                                <p class="info-c">Compte : <?= $compte ?></p>
                                <p class="info-c">Quantité : <?= $getQuantity ?></p>
                            </div>
                            <div class="col-4">
                                <p class="info-c">Compte après taxe : <?= $compteAfterTaxe ?></p>
                            </div>
                        </div>

                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">Titre</th>
                                <th scope="col">Actuellement</th>
                                <th scope="col">Anciennement</th>
                                <th scope="col">Depuis le début</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th>CA</th>
                                <td><?= $getAPV; ?></td>
                                <td><?= $getOldPrice ?></td>
                                <td><?= $getAllPriceVente[0] ?></td>
                            </tr>
                            <tr>
                                <th>Taxe</th>
                                <td><?= $getTaxes ?></td>
                                <td><?= $getOldTaxe ?></td>
                                <td><?= $getTaxe ?></td>
                            </tr>
                            <tr>
                                <th>Paye intérim</th>
                                <td><?= $payeInterimNow ?></td>
                                <td><?= $getOldPayeInterim ?></td>
                                <td><?= $payeInterim ?></td>
                            </tr>
                            <tr>
                                <th>Dépense Entreprise</th>
                                <td><?= $gAD ?></td>
                                <td><?= $getOldDepenseEntreprise ?></td>
                                <td><?= $getAllDepense[0] ?></td>
                            </tr>
                            <tr>
                                <th>Salaire</th>
                                <td><?= $salaireNow ?></td>
                                <td><?= $getOldSalaire ?></td>
                                <td><?= $salaire ?></td>
                            </tr>
                            <tr>
                                <th>Pertes</th>
                                <td><?= $gAP ?></td>
                                <td><?= $getOldPerte ?></td>
                                <td><?= $getAllPerte[0] ?></td>
                            </tr>
                            <tr>
                                <th>Bénéfice</th>
                                <td><?= $beneficeNow ?></td>
                                <td><?= $getOldBenefice ?></td>
                                <td><?= $benefice ?></td>
                            </tr>
                            <tr>
                                <th>Dépense total</th>
                                <td></td>
                                <td></td>
                                <td style="font-weight: bold;"><?= $depenseTotal ?></td>
                            </tr>
                            </tbody>
                        </table>

                        <h2 style="margin-top: 2rem;">Listes des pertes/dépenses</h2>
                        <table id="depenseperte" class="display">
                            <thead>
                            <tr style="text-align: center;">
                                <th>ID Interim</th>
                                <th>Prenom/Nom</th>
                                <th>Montant</th>
                                <th>Raison</th>
                                <th>Type</th>
                                <th>Dates</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $getDepensePerte = $bdd->query("SELECT * FROM depenseperte");

                            while ($gDP = $getDepensePerte->fetch()) {

                                $getEIDP = $bdd->query("SELECT * FROM interim WHERE id_interim = ".$gDP["id_interim"]);
                                $getEI = $getEIDP->fetch();

                                $getDP = $gDP["depense"] ? "Dépense" : "Perte";

                                ?>


                                <tr style="text-align: center;">

                                    <td><?= $gDP["id_interim"] ?></td>
                                    <td><?= $getEI["prenom_interim"]; ?> <?= $getEI["nom_interim"]; ?></td>
                                    <td><?= $gDP["montant"] ?></td>
                                    <td><?= $gDP["raison"] ?></td>
                                    <td><?= $getDP ?></td>
                                    <td><?= $gDP["dates"] ?></td>

                                </tr>
                            <?php  }$getDepensePerte->closeCursor(); ?>
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
                                <th>Nombre de vente effecuté</th>
                                <th>L'argent total déposer</th>
                                <th>Total quantité vendu</th>
                                <th>Différence farm-vente</th>
    <?php if (in_array("delete_member", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>
        <th>Supprimé</th>
            <?php } ?>

        </tr>
                            </thead>
                            <tbody>
                            <?php
                            $getEmploye = $bdd->query("SELECT * FROM member");

                            while ($gEInfo = $getEmploye->fetch()) {

                                $getEmployeInfo = $bdd->query("SELECT * FROM interim WHERE id_interim = ".$gEInfo["id_member"]);
                                $getEI = $getEmployeInfo->fetch();

                                $getEmployeCountStart = $bdd->query("SELECT COUNT(*) FROM trajet WHERE id_member_start = ".$gEInfo["id_member"])->fetch();
                                $getEmployeCountEnd = $bdd->query("SELECT COUNT(*) FROM trajet WHERE id_member_end = ".$gEInfo["id_member"])->fetch();
                                $getEmployeQuantityEnd = $bdd->query("SELECT SUM(quantity_trajet) AS quantity_trajet_total_end FROM trajet WHERE id_member_end = ".$gEInfo["id_member"])->fetch();

//                                $getEmployeQuantityPerso = $bdd->query("SELECT SUM(quantity_trajet) AS quantity_trajet_total_end FROM trajet WHERE id_member_end = ".$gEInfo['id_member']." AND employe_trajet = 1")->fetch();
                                $getEmployeQuantityVente = $bdd->query("SELECT SUM(quantity) AS quantity_trajet_total_vente FROM vente WHERE id_interim = ".$gEInfo['id_member'])->fetch();
                                $getEmployePriceVente = $bdd->query("SELECT SUM(price) AS price_trajet_total_vente FROM vente WHERE id_interim = ".$gEInfo['id_member'])->fetch();
                                $getEmployeCountVente = $bdd->query("SELECT COUNT(*) FROM vente WHERE id_interim = ".$gEInfo['id_member'])->fetch();

//                                $difffarmvente = ($getEmployeQuantityEnd[0] + $getEmployeQuantityPerso[0]) - $getEmployeQuantityVente[0];
                                $difffarmvente = $getEmployeQuantityEnd[0] - $getEmployeQuantityVente[0];


                                ?>


                                <tr style="text-align: center;">

                                    <td><?= $gEInfo["id_member"]; ?></td>
                                    <td><?= $getEI["prenom_interim"]; ?> <?= $getEI["nom_interim"]; ?></td>
                                    <td><?= $getEmployeCountStart[0] ?></td>
                                    <td><?= $getEmployeQuantityEnd[0] ?> (<?= $getEmployeCountEnd[0] ?>)</td>
                                    <td><?= $getEmployeCountVente[0] ?></td>
                                    <td><?= $getEmployePriceVente[0] ?></td>
                                    <td><?= $getEmployeQuantityVente[0] ?></td>
                                    <td><?= $difffarmvente ?></td>
                                    <?php if (in_array("delete_member", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>
                                        <td><button class="btn btn-danger text-white text-center" type="submit" name="del_member" value="<?= $gEInfo["id"];?>">Supprimé</button></td>
                                    <?php } ?>

                                </tr>
                            <?php  }$getEmploye->closeCursor(); ?>
                            </tbody>
                        </table>

                        <h2 style="margin-top: 2rem;">Listes des primes attribué</h2>
                        <table id="prime" class="display">
                            <thead>
                            <tr style="text-align: center;">
                                <th>Prenom/Nom (Attribueur)</th>
                                <th>Prenom/Nom (Receveur)</th>
                                <th>Somme</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $getPrime = $bdd->query("SELECT * FROM prime");

                            while ($gPrime = $getPrime->fetch()) {

                                $getEmployeInfoA = $bdd->query("SELECT * FROM interim WHERE id_interim = ".$gPrime["id_member"]);
                                $gPrimeInfoA = $getEmployeInfoA->fetch();
                                $getEmployeInfoR = $bdd->query("SELECT * FROM interim WHERE id_interim = ".$gPrime["id_interim"]);
                                $gPrimeInfoR = $getEmployeInfoR->fetch();

                                ?>


                                <tr style="text-align: center;">

                                    <td><?= $gPrimeInfoA["prenom_interim"]; ?> <?= $gPrimeInfoA["nom_interim"]; ?></td>
                                    <td><?= $gPrimeInfoR["prenom_interim"]; ?> <?= $gPrimeInfoR["nom_interim"]; ?></td>
                                    <td><?= $gPrime["somme"] ?></td>
                                    <td><?= $gPrime["dates"] ?></td>

                                </tr>
                            <?php  }$getEmploye->closeCursor(); ?>
                            </tbody>
                        </table>


                    </form>
                </div>


            </div>

            <div class="tab-pane fade show active" id="nav-all" role="tabpanel" aria-labelledby="nav-all-tab">


                <?php

                $getInfoValid0 = $bdd->query("SELECT * FROM member WHERE steamid_member = '" . $_SESSION["steamid"] . "'");
                $getInfoValid0f = $getInfoValid0->fetch();
                $getInfoValid1 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $getInfoValid0f["id_member"] . "'");
                $getInfoValid1f = $getInfoValid1->fetch();

                if (in_array("view_interim", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) {

                    if(isset($_POST["create_trajet"])) {
                        $idInterims = htmlspecialchars($_POST["create_trajet"]);
                        $idEmployee = $getInfoValid1f["id_interim"];
                        $addTrajet = $bdd->prepare("INSERT INTO trajet (id_interim, id_member_start, semaine, date_trajet_start) VALUES (?, ?, ?, NOW())");
                        $addTrajet->execute(array($idInterims, $idEmployee, $semaine_trajet));
                    }

                    if(isset($_POST["submit_vente"])) {
                        $idEmployee = $getInfoValid1f["id_interim"];
                        $qv = htmlspecialchars($_POST["quantity_vente"]);
                        $pv = htmlspecialchars($_POST["prix_vente"]);
                        if($gSettings["ispourcentage"]) {
                            $primedevente = ($pv / 100) * $gSettings["pourcentage_primevente"];
                        }else {
                            $primedevente = $qv * $gSettings["pourcentage_primevente"];
                        }


                        $addvente = $bdd->prepare("INSERT INTO vente (id_interim, quantity, price, semaine, salaire, date_vente) VALUES (?, ?, ?, ?, ?, NOW())");
                        $addvente->execute(array($idEmployee, $qv, $pv, $semaine_trajet, $primedevente));
                        echo "<p class='msg'>Vente ajouté !</p>";
                    }

                    if(isset($_POST["submit_create_prime"])) {
                        $idEmployee = $getInfoValid1f["id_interim"];
                        $idInterims = htmlspecialchars($_POST["submit_create_prime"]);
                        $montant_prime = htmlspecialchars($_POST["montant_prime"]);
                        $addvente = $bdd->prepare("INSERT INTO prime (id_interim, id_member, somme, dates) VALUES (?, ?, ?, NOW())");
                        $addvente->execute(array($idInterims, $idEmployee, $montant_prime));
                        echo "<p class='msg'>Prime ajouté !</p>";
                    }

                    if(isset($_POST["submit_trajet_perso"])) {
                        $idEmployee = $getInfoValid1f["id_interim"];
                        $qtp = htmlspecialchars($_POST["quantity_trajet_perso"]);
                        $priceTrajetEmploye = $qtp * $gSettings["sell_price_employe"];
                        $addtrajetperso = $bdd->prepare("INSERT INTO trajet (id_interim, id_member_start, id_member_end, quantity_trajet, price_trajet, employe_trajet, status_trajet, semaine, date_trajet_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                        $addtrajetperso->execute(array($idEmployee, $idEmployee, $idEmployee, $qtp, $priceTrajetEmploye, 1, 1, $semaine_trajet));
                        echo "<p class='msg'>Trajet perso ajouté !</p>";
                    }

                    if(isset($_POST["submit_perte"])) {
                        $idEmployee = $getInfoValid1f["id_interim"];
                        $montant = htmlspecialchars($_POST["montant"]);
                        $quant = htmlspecialchars($_POST["quant"]);
                        $raison = htmlspecialchars($_POST["raison"]);
                        $depense = htmlspecialchars($_POST["depense"]) ? 1 : 0;
                        $perte = htmlspecialchars($_POST["perte"]) ? 1 : 0;
                        $addperte = $bdd->prepare("INSERT INTO depenseperte (id_interim, raison, montant, quant, depense, perte, dates) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $addperte->execute(array($idEmployee, $raison, $montant, $quant, $depense, $perte));
                        echo "<p class='msg'>Ajout de la perte effectué  !</p>";
                    }

                    if(isset($_POST["del_interim"])) {
                        $del_interim = htmlspecialchars($_POST["del_interim"]);
                        $delInterim = $bdd->prepare("DELETE FROM `interim` WHERE id=?");
                        $delInterim->execute(array($del_interim));
                    }

                    if(isset($_POST["del_member"])) {
                        $del_member = htmlspecialchars($_POST["del_member"]);
                        $delMember = $bdd->prepare("DELETE FROM `member` WHERE id=?");
                        $delMember->execute(array($del_member));
                    }

                    if(isset($_POST["submit_inscrit"])) {
                        $idinterims_inscrit = htmlspecialchars($_POST["id_inscrit"]);
                        $prenom_inscrit = htmlspecialchars($_POST["prenom_inscrit"]);
                        $nom_inscrit = htmlspecialchars($_POST["nom_inscrit"]);
                        $tel_inscrit = htmlspecialchars($_POST["tel_inscrit"]);
                        $permisdeconduire_inscrit = htmlspecialchars($_POST["permisdeconduire_inscrit"]) ? 1 : 0;
                        $permispoidslourd_inscrit = htmlspecialchars($_POST["permispoidslourd_inscrit"]) ? 1 : 0;
                        $permisbateau_inscrit = htmlspecialchars($_POST["permisbateau_inscrit"]) ? 1 : 0;
                        $permispilote_inscrit = htmlspecialchars($_POST["permispilote_inscrit"]) ? 1 : 0;

                        $addInterim = $bdd->prepare("INSERT INTO interim (id_interim, prenom_interim, nom_interim, tel_interim, permisconduire_interim, permiscamion_interim, permisbateau_interim, permispilote_interim) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $addInterim->execute(array($idinterims_inscrit, $prenom_inscrit, $nom_inscrit, $tel_inscrit, $permisdeconduire_inscrit, $permispoidslourd_inscrit, $permisbateau_inscrit, $permispilote_inscrit));

                        $rank_inscrit = htmlspecialchars($_POST["rank_inscrit"]);
                        $steamid_inscrit = htmlspecialchars($_POST["steamid_inscrit"]);

                        if($rank_inscrit && $steamid_inscrit) {
                            $addEmployes = $bdd->prepare("INSERT INTO member (id_member, rank_member, steamid_member) VALUES (?, ?, ?)");
                            $addEmployes->execute(array($idinterims_inscrit, $rank_inscrit, $steamid_inscrit));
                        }

                    }

                    ?>

                    <div class="container" style="margin-top: 2rem;">
                        <form action="" method="POST">

                            <div class="row">
                                <div class="col-3">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addtrajetperso">
                                        Trajet Perso
                                    </button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addventeemploye">
                                        Ajout vente
                                    </button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addperte">
                                        Ajout dépense / perte
                                    </button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#absence">
                                        Absence
                                    </button>
                                </div>
                            </div>

                            <h2 style="margin-top: 1.5rem;">Listes des intérimaire</h2>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addinterimaire">
                                Ajouté un intérimaire
                            </button>
                            <table id="table_id" class="display">
                                <thead>
                                <tr style="text-align: center;">
                                    <th>ID Interim</th>
                                    <th>Prenom/Nom</th>
                                    <th>Blacklist</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $getInterim = $bdd->query("SELECT * FROM interim");
                                while ($gInterim = $getInterim->fetch()) { ?>
                                    <tr style="text-align: center; <?= $gInterim["blacklist"] ? "background-color: black; color: red;" : "background-color: white; color: black;"; ?>">

                                        <td><?= $gInterim["id_interim"]; ?></td>
                                        <td><?= $gInterim["prenom_interim"]; ?> <?= $gInterim["nom_interim"]; ?></td>
                                        <td><?= $gInterim["blacklist"] ? "Oui" : "Non"; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                                    <li><a class="dropdown-item" type="button" href="interim.php?id_interim=<?= $gInterim["id_interim"]; ?>">Inspecter</a></li>
                                                    <li><button class="dropdown-item" type="submit" name="create_trajet" value="<?= $gInterim["id_interim"];?>">Créer un trajet</button></li>
                                                    <?php if (in_array("delete_interim", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>
                                                        <hr>
                                                    <li><button class="dropdown-item bg-danger text-white text-center" type="submit" name="del_interim" value="<?= $gInterim["id"];?>">Supprimé</button></li>
                                                      <?php }?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php  }$getInterim->closeCursor(); ?>
                                </tbody>
                            </table>
                        </form>
                    </div>



                    <div class="modal fade" id="addperte" tabindex="-1" aria-labelledby="addperteLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="addperteLabel">Ajout d'une perte</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <form class="row g-3" method="POST" action="">
                                        <div class="col-md-12">
                                            <label for="validationDefault01" class="form-label">Montant</label>
                                            <input type="number" class="form-control" id="validationDefault01" name="montant" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="validationDefault01" class="form-label">Quantité</label>
                                            <input type="number" class="form-control" id="validationDefault01" name="quant" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="validationDefault03" class="form-label">Raison</label>
                                            <input type="text" class="form-control" id="validationDefault03" name="raison" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-check-label" for="invalidCheck2">
                                                Dépense
                                            </label>
                                            <input class="form-check-input" type="checkbox" id="invalidCheck2" name="depense">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-check-label" for="invalidCheck2">
                                                Perte
                                            </label>
                                            <input class="form-check-input" type="checkbox" id="invalidCheck2" name="perte">
                                        </div>
                                            <div class="col-12">
                                                <button class="btn btn-primary" type="submit" name="submit_perte">Ajouté la perte/dépense</button>
                                            </div>
                                    </form>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>

                            </div>
                        </div>
                    </div>





                    <div class="modal fade" id="addinterimaire" tabindex="-1" aria-labelledby="addinterimaireLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addinterimaireLabel">Création d'un intérimaire</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form class="row g-3" method="POST" action="">
                                        <div class="col-md-4">
                                            <label for="validationDefault01" class="form-label">ID</label>
                                            <input type="number" class="form-control" id="validationDefault01" name="id_inscrit" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="validationDefault01" class="form-label">Prénom</label>
                                            <input type="text" class="form-control" id="validationDefault01" name="prenom_inscrit" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="validationDefault02" class="form-label">Nom</label>
                                            <input type="text" class="form-control" id="validationDefault02" name="nom_inscrit" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="validationDefault03" class="form-label">Numéro de téléphone</label>
                                            <input type="number" class="form-control" id="validationDefault03" name="tel_inscrit" required>
                                        </div>
                                        <div class="col-3">
                                                <label class="form-check-label" for="invalidCheck2">
                                                    Permis de conduire
                                                </label>
                                                <input class="form-check-input" type="checkbox" id="invalidCheck2" name="permisdeconduire_inscrit">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-check-label" for="invalidCheck2">
                                                Permis Poids Lourd
                                            </label>
                                            <input class="form-check-input" type="checkbox" id="invalidCheck2" name="permispoidslourd_inscrit">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-check-label" for="invalidCheck2">
                                                Permis Bateau
                                            </label>
                                            <input class="form-check-input" type="checkbox" id="invalidCheck2" name="permisbateau_inscrit">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-check-label" for="invalidCheck2">
                                                Permis pilote
                                            </label>
                                            <input class="form-check-input" type="checkbox" id="invalidCheck2" name="permispilote_inscrit">
                                        </div>
                                        <?php
                                        if(in_array("add_employe", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>

                                            <hr>

                                            <div class="col-md-6">
                                                <label for="validationDefault01SteamID" class="form-label">SteamID</label>
                                                <input type="number" class="form-control" id="validationDefault01" name="steamid_inscrit">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="validationGraDE" class="form-label">Grade</label>
                                                <select class="form-select" aria-label="Default select example" name="rank_inscrit">
                                                    <option selected value="0">Clique pour ouvrir le menu</option>
                                                    <?php

                                                    $getAllRank = $bdd->query("SELECT * FROM rank");
                                                    while ($gRank = $getAllRank->fetch()) { ?>

                                                        <option value="<?= $gRank["rankname"]; ?>"><?= $gRank["rankname"]; ?></option>

                                                    <?php }$getAllRank->closeCursor(); ?>
                                                </select>
                                            </div>

                                        <?php } ?>
                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit" name="submit_inscrit">Ajouté l'interimaire</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php

                    if(isset($_POST["submit_absence"])) {

                        $absence_date_debut = htmlspecialchars($_POST["absence_date_debut"]);
                        $absence_date_fin = htmlspecialchars($_POST["absence_date_fin"]);
                        $absence_text =  htmlspecialchars($_POST["absence_text"]);

//                        $url = "https://discord.com/api/webhooks/941758080012673044/jdE3qW-rshboTXIb3YvcyUHR2GtG01XkJtLNq8hCRJ1Ggo1tZlxxgfWVEEtIFZNvnC0-";
                        $url = "https://discord.com/api/webhooks/941764657838952509/-wEULjWgF9XxnGgSM8KZ4OinGkEWNhXbs3bcxepPQf1-RQe5KLXm5-mwmC257oy_8ZER";

                        $hookObject = json_encode([
                            "content" => "",
                            "username" => $getInfoValid1f["prenom_interim"].' '.$getInfoValid1f["nom_interim"],
                            "embeds" => [
                                [
                                    "title" => "Absence",
                                    "type" => "rich",
                                    "description" => "Absence du ".$absence_date_debut." au ".$absence_date_fin." \n\nraison : ".$absence_text,
                                    "url" => "https://oil.raphtnt.com/",
                                    "color" => hexdec( "FFFFFF" ),
                                ]
                            ]

                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

                        $ch = curl_init();

                        curl_setopt_array( $ch, [
                            CURLOPT_URL => $url,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $hookObject,
                            CURLOPT_HTTPHEADER => [
                                "Content-Type: application/json"
                            ]
                        ]);

                        $response = curl_exec( $ch );
                        curl_close( $ch );
                    }

                    ?>

                    <div class="modal fade" id="absence" tabindex="-1" aria-labelledby="absenceLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="absenceLabel">Absence</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form class="row g-3" method="POST" action="">
                                        <div class="col-md-6">
                                            <label for="absence_date_debut" class="form-label">Date de début</label>
                                            <input type="date" class="form-control" id="absence_date_debut" name="absence_date_debut" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="absence_date_fin" class="form-label">Date de fin</label>
                                            <input type="date" class="form-control" id="absence_date_fin" name="absence_date_fin" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="absence_text" class="form-label">Raison</label>
                                            <input type="text" class="form-control" id="absence_text" name="absence_text" required>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit" name="submit_absence">Ajout de l'absence</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="modal fade" id="addtrajetperso" tabindex="-1" aria-labelledby="addtrajetpersoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addtrajetpersoLabel">Ajout d'un trajet perso</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form class="row g-3" method="POST" action="">
                                        <div class="col-md-12">
                                            <label for="validationquantity_trajet_perso" class="form-label">Quantité</label>
                                            <input type="number" class="form-control" id="validationquantity_trajet_perso" name="quantity_trajet_perso" required>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit" name="submit_trajet_perso">Ajout du trajet personnel</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="modal fade" id="addventeemploye" tabindex="-1" aria-labelledby="addventeemployeLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addventeemployeLabel">Ajout d'une vente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form class="row g-3" method="POST" action="">
                                        <div class="col-md-12">
                                            <label for="validationquantity_vente" class="form-label">Quantité</label>
                                            <input type="number" class="form-control" id="validationquantity_vente" name="quantity_vente" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="validationprix_vente" class="form-label">Argent Obtenue</label>
                                            <input type="number" class="form-control" id="validationprix_vente" name="prix_vente" required>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit" name="submit_vente">Ajout de la vente</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>


                <?php } ?>

                <?php if (in_array("view_trajet", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) {

                    if(isset($_POST["finish"])) {
//            $prenom = htmlspecialchars($_POST["prenom_interim"]);
//            $nom = htmlspecialchars($_POST["nom_interim"]);
//            $quantity = htmlspecialchars($_POST["quantity"]);
                        $quantity = htmlspecialchars($_POST["calView"]);
                        $finish = htmlspecialchars($_POST["finish"]);
                        $split = explode(";", $finish);
                        $id = $split[0];
                        $prenom = $split[1];
                        $nom = $split[2];

                        $calcul = ($quantity) * $gSettings["sell_price_interim"];

                        $finishTrajet = $bdd->prepare("UPDATE trajet SET id_member_end = ?, quantity_trajet = ?, price_trajet = ?, status_trajet = ?, date_trajet_end = NOW() WHERE id = ?");
                        $finishTrajet->execute(array($getInfoValid1f["id_interim"], $quantity, $calcul, 1, $id));
                        echo "<p style='margin-top: 2rem;font-size: 3rem; text-align: center;'>Vous devez payé ".$prenom." ". $nom ." ". $calcul ."$</p>";

                    }

                    if(isset($_POST["restart"])) {
                        $quantity = htmlspecialchars($_POST["calView"]);
                        $restart = htmlspecialchars($_POST["restart"]);
                        $split = explode(";", $restart);
                        $id = $split[0];
                        $prenom = $split[1];
                        $nom = $split[2];
                        $idInterimss = $split[3];

                        $calcul = ($quantity) * $gSettings["sell_price_interim"];

                        $finishTrajets = $bdd->prepare("UPDATE trajet SET id_member_end = ?, quantity_trajet = ?, price_trajet = ?, status_trajet = ?, date_trajet_end = NOW() WHERE id = ?");
                        $finishTrajets->execute(array($getInfoValid1f["id_interim"], $quantity, $calcul, 1, $id));
                        echo "<p style='margin-top: 2rem;font-size: 3rem; text-align: center;'>Vous devez payé ".$prenom." ". $nom ." ". $calcul ."$</p>";

                        $idEmployee = $getInfoValid1f["id_interim"];
                        $addTrajet = $bdd->prepare("INSERT INTO trajet (id_interim, id_member_start, semaine, date_trajet_start) VALUES (?, ?, ?, NOW())");
                        $addTrajet->execute(array($idInterimss, $idEmployee, $semaine_trajet));


                    }

                    if(isset($_POST["cancel"])) {
                        $finish = htmlspecialchars($_POST["cancel"]);
                        $split = explode(";", $finish);
                        $id = $split[0];
                        $prenom = $split[1];
                        $nom = $split[2];

                        $cancel = $bdd->prepare("UPDATE trajet SET id_member_end = ?, quantity_trajet = ?, price_trajet = ?, status_trajet = ?, date_trajet_end = NOW() WHERE id = ?");
                        $cancel->execute(array($getInfoValid1f["id_interim"], 0, 0, 1, $id));

                        echo "<p style='margin-top: 2rem;font-size: 3rem; text-align: center;'>Vous avez annulé le trajet de ".$prenom." ". $nom."</p>";
                    }



                    ?>
                    <form method="POST" action="" name="form">
                        <div class="container" style="margin-top: 5rem;">
                            <div class="row">
                                <h2>Listes des trajets en cours</h2>
                                <div class="col-xl-9 col-lg-12">
                                    <table id="table_id_trajet" class="display">
                                        <thead>
                                        <tr style="text-align: center;">
                                            <th>ID Interim</th>
                                            <th>Prenom/Nom (Intérimaire)</th>
                                            <th>Prenom/Nom (Employé)</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <?php
                                        $getTrajet = $bdd->query("SELECT * FROM trajet WHERE status_trajet = '0'");
                                        while ($gTrajet = $getTrajet->fetch()) {

                                            $getTrajetInterimInfo = $bdd->query("SELECT * FROM trajet INNER JOIN interim ON trajet.id_interim = interim.id_interim WHERE trajet.id_interim = '" . $gTrajet["id_interim"] . "'");
                                            $gtii = $getTrajetInterimInfo->fetch();
                                            $getTrajetMemberInfo = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gTrajet["id_member_start"] . "'");
                                            $gtmi = $getTrajetMemberInfo->fetch();

                                            ?>

                                            <tr style="text-align: center;">
                                                <td><?= $gTrajet["id_interim"]; ?></td>
                                                <!--                        <td>--><?//= $getTrajetInterimInfo["prenom_interim"]; ?><!-- --><?//= $getTrajetInterimInfo["nom_interim"]; ?><!--</td>-->
                                                <td><?= $gtii["prenom_interim"] ?> <?= $gtii["nom_interim"] ?></td>
                                                <td><?= $gtmi["prenom_interim"] ?> <?= $gtmi["nom_interim"] ?></td>

                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Action
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                                            <li><button class="dropdown-item" type="submit" name="cancel" value="<?= $gTrajet['id'] ?>;<?= $gtii['prenom_interim']?>;<?= $gtii['nom_interim'] ?>">Annulé</button></li>
                                                            <li><button class="dropdown-item" type="submit" name="finish" value="<?= $gTrajet['id'] ?>;<?= $gtii['prenom_interim']?>;<?= $gtii['nom_interim'] ?>" onclick="equal()">Términer</button></li>
                                                            <li><button class="dropdown-item" type="submit" name="restart" value="<?= $gTrajet['id'] ?>;<?= $gtii['prenom_interim']?>;<?= $gtii['nom_interim'] ?>;<?= $gtii['id_interim'] ?>" onclick="equal()">Relancer</button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php  }$getTrajet->closeCursor(); ?>
                                        </tbody>
                                    </table>

                                </div>
                                <div class="col-xl-3 col-lg-12 text-center">


                                    <div class="panel-calculator" id='calculator'>
                                        <span class="note" id="calNote"></span>
                                        <input class="calView" name="calView" maxlength="15">
                                        <table width="100%">

                                            <tr>
                                                <td><input type="button" class="button" value="7" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="8" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="9" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="/" onclick="insert(this.value)"></td>
                                            <tr>
                                                <td><input type="button" class="button" value="4"onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="5" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="6" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="*" onclick="insert('*')"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="button" class="button" value="1" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="2" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="3" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="+" onclick="insert(this.value)"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="button" class="button" value="0" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="187" onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="." onclick="insert(this.value)"></td>
                                                <td><input type="button" class="button" value="-" onclick="insert(this.value)"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="button" class="button" value="AC" onclick="clean()" ></td>
                                                <td><input type="button" class="button" value="&#x21E6;" onclick="back()"></td>
                                                <td colspan='2' ><input type="button" class="button equal" value="=" onclick="equal()"  ></td>

                                            </tr>

                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>


                <?php } ?>

            </div>

            <div class="tab-pane fade " id="nav-settings" role="tabpanel" aria-labelledby="nav-settings-tab">

                <?php if (in_array("settings", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) {

                    if(isset($_POST["submit_update"])) {
                        $id = 1;
                        $pvi = htmlspecialchars($_POST["pvi"]);
                        $pve = htmlspecialchars($_POST["pve"]);
                        $caa = htmlspecialchars($_POST["caa"]);
                        $pia = htmlspecialchars($_POST["pia"]);
                        $ta = htmlspecialchars($_POST["ta"]);
                        $dea = htmlspecialchars($_POST["dea"]);
                        $sa = htmlspecialchars($_POST["sa"]);
                        $pa = htmlspecialchars($_POST["pa"]);
                        $ba = htmlspecialchars($_POST["ba"]);
                        $st = htmlspecialchars($_POST["st"]);
                        $tiv = htmlspecialchars($_POST["tiv"]);
                        $ctp = htmlspecialchars($_POST["ctp"]);
                        $pdv = htmlspecialchars($_POST["pdv"]);
                        $pdvc = htmlspecialchars($_POST["pdvc"]) ? 1 : 0;
                        $c = htmlspecialchars($_POST["c"]);
                        $pe = htmlspecialchars($_POST["pe"]);

/*                        echo $pvi."<br>";
                        echo $pve."<br>";
                        echo $caa."<br>";
                        echo $pia."<br>";
                        echo $ta."<br>";
                        echo $dea."<br>";
                        echo $sa."<br>";
                        echo $pa."<br>";
                        echo $ba."<br>";
                        echo $st."<br>";
                        echo $tiv."<br>";
                        echo $ctp."<br>";
                        echo $pdv."<br>";
                        echo $pdvc."<br>";
                        echo $c."<br>";
                        echo $pe."<br>";*/

                        $update = $bdd->prepare("UPDATE settings SET sell_price_interim = ?, sell_price_employe = ?, info_ancien = ?, info_payeinterim = ?, info_taxe = ?, info_depenseentreprise = ?, info_salaire = ?, info_pertes = ?, info_benefice = ?, settings_taxe = ?, trajet_interimaire_vente = ?, cout_total_petrol = ?, pourcentage_primevente = ?, ispourcentage = ?, capital = ?, price_employe = ? WHERE id = ?");
                        $update->execute(array($pvi, $pve, $caa, $pia, $ta, $dea, $sa, $pa, $ba, $st, $tiv, $ctp, $pdv, $pdvc, $c, $pe, $id));
                    }

                    ?>

                    <form class="row g-3" method="POST" action="">
                        <div class="col-md-3">
                            <label for="1" class="form-label">Prix vente interim</label>
                            <input type="number" class="form-control" id="1" name="pvi" value="<?= $gSettings['sell_price_interim'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="2" class="form-label">Prix vente employe</label>
                            <input type="number" class="form-control" id="2" name="pve" value="<?= $gSettings['sell_price_employe'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="3" class="form-label">CA Ancien</label>
                            <input type="number" class="form-control" id="3" name="caa" value="<?= $gSettings['info_ancien'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="4" class="form-label">Taxe Ancien</label>
                            <input type="number" class="form-control" id="4" name="ta" value="<?= $gSettings['info_taxe'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="5" class="form-label">Paye Interim Ancien</label>
                            <input type="number" class="form-control" id="5" name="pia" value="<?= $gSettings['info_payeinterim'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="6" class="form-label">Depense Entreprise Ancien</label>
                            <input type="number" class="form-control" id="6" name="dea" value="<?= $gSettings['info_depenseentreprise'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="7" class="form-label">Salaire Ancien</label>
                            <input type="number" class="form-control" id="7" name="sa" value="<?= $gSettings['info_salaire'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="8" class="form-label">Pertes Ancien</label>
                            <input type="number" class="form-control" id="8" name="pa" value="<?= $gSettings['info_pertes'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="9" class="form-label">Benefice Ancien</label>
                            <input type="number" class="form-control" id="9" name="ba" value="<?= $gSettings['info_benefice'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="10" class="form-label">% Taxe</label>
                            <input type="number" class="form-control" id="10" name="st" value="<?= $gSettings['settings_taxe'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="11" class="form-label">Trajet interimaire vente</label>
                            <input type="number" class="form-control" id="11" name="tiv" value="<?= $gSettings['trajet_interimaire_vente'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="12" class="form-label">Cout total petrol</label>
                            <input type="number" class="form-control" id="12" name="ctp" value="<?= $gSettings['cout_total_petrol'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="13" class="form-label">Prime de vente</label>
                            <input type="number" class="form-control" id="13" name="pdv" value="<?= $gSettings['pourcentage_primevente'] ?>" required>
                            %
                            <input class="form-check-input" type="checkbox" id="invalidCheck2" name="pdvc" <?= $gSettings['ispourcentage'] ? 'checked' : '' ?>>
                        </div>
                        <div class="col-md-3">
                            <label for="14" class="form-label">Capital</label>
                            <input type="number" class="form-control" id="14" name="c" value="<?= $gSettings['capital'] ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="15" class="form-label">Prix employe</label>
                            <input type="number" class="form-control" id="15" name="pe" value="<?= $gSettings['price_employe'] ?>" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit" name="submit_update">Mettre à jour</button>
                        </div>
                    </form>

                <?php } ?>


            </div>

        </div>


        </div>



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
            $('#table_id_trajet').DataTable();
        });

        $(document).ready(function () {
            $('#employee').DataTable();
        });

        $(document).ready(function () {
            $('#depenseperte').DataTable();
        });

        $(document).ready(function () {
            $('#prime').DataTable();
        });

        function clearNote() {
            $('#calNote').text('')
        }

        function insertNode() {

        }

        function insert(num) {
            if (document.form.calView.value.length>=15) return

            let originVal =  document.form.calView.value;
            document.form.calView.value = originVal+ num


            let originNote = $('#calNote').text()
            console.log('originNote: ', originNote);
            clearNote()
            $('#calNote').append(originNote + num)

            if(parseInt(num)) {
                let value = document.form.calView.value
                console.log('value: ', value);
                value = value.replace(/,/g,'')
                // var re = '/[+-\/*,]/g';
                // let numArr = value.split(re)
                document.form.calView.value = numberWithCommas(value)
            }
        }

        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function equal(){
            var exp = document.form.calView.value

            if(exp){
                exp =  exp.replace(/,/g,'')
                document.form.calView.value = eval(exp)
                document.form.calView.value = numberWithCommas(document.form.calView.value)
            }
        }

        function clean(){
            document.form.calView.value = ""
            clearNote()
        }

        function back(){
            var exp = document.form.calView.value;
            document.form.calView.value = exp.substring(0,exp.length-1)
        }

    </script>


<!--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>-->
    <script src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

    </body>
    </html>

<?php } ?>