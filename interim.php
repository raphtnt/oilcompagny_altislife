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


if(isset($_GET['id_interim']) AND $_GET['id_interim'] > 0){

    $getid = intval($_GET['id_interim']);
    $gplayer = $bdd->prepare("SELECT * FROM interim WHERE id_interim = ?");
    $gplayer->execute(array($getid));
    $gInfoInterim = $gplayer->fetch();

}


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
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
        <title>Oil Compagny</title>

    </head>
    <body>
    <?php if (!(in_array("authorization", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)))) {
        echo "Vous n'avez aucune autorisation a la OIL Compagny";
        return;
    }?>

    <?php if (in_array("view_interim_page", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) {

        if(isset($_POST["submit_update_permis"])){
            $permisdeconduire_inscrit = htmlspecialchars($_POST["permisdeconduire_inscrit"]) ? 1 : 0;
            $permispoidslourd_inscrit = htmlspecialchars($_POST["permispoidslourd_inscrit"]) ? 1 : 0;
            $permisbateau_inscrit = htmlspecialchars($_POST["permisbateau_inscrit"]) ? 1 : 0;
            $permispilote_inscrit = htmlspecialchars($_POST["permispilote_inscrit"]) ? 1 : 0;


            $updateInterimPermis = $bdd->prepare("UPDATE interim SET permisconduire_interim = ?, permiscamion_interim = ?, permisbateau_interim = ?, permispilote_interim = ? WHERE id_interim = ?");
            $updateInterimPermis->execute(array($permisdeconduire_inscrit, $permispoidslourd_inscrit, $permisbateau_inscrit, $permispilote_inscrit, $getid));
            header("Location: ./interim.php?id_interim=".$getid);
        }

        $getInfoValid0 = $bdd->query("SELECT * FROM member WHERE steamid_member = '" . $_SESSION["steamid"] . "'");
        $getInfoValid0f = $getInfoValid0->fetch();
        $getInfoValid1 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $getInfoValid0f["id_member"] . "'");
        $getInfoValid1f = $getInfoValid1->fetch();

        if(isset($_POST["add_blacklist"])) {
            $blacklistraison = htmlspecialchars($_POST["raison_blacklist"]);
            $updateBlackList = $bdd->prepare("UPDATE interim SET blacklist = ? WHERE id_interim = ?");
            if($gInfoInterim["blacklist"]) {
                $updateBlackList->execute(array(0, $getid));
            }else {
                $updateBlackList->execute(array(1, $getid));
            }
            $addblacklist = $bdd->prepare("INSERT INTO note (id_interim, id_member, raison, type, dates) VALUES (?, ?, ?, ?, NOW())");
            $addblacklist->execute(array($getid, $getInfoValid1f["id_interim"], $blacklistraison, 1));
        }

        if(isset($_POST["add_note"])) {
            $noteraison = htmlspecialchars($_POST["raison_note"]);
            $addnote = $bdd->prepare("INSERT INTO note (id_interim, id_member, raison, type, dates) VALUES (?, ?, ?, ?, NOW())");
            $addnote->execute(array($getid, $getInfoValid1f["id_interim"], $noteraison, 0));
        }

        if(isset($_POST["add_prime"])) {
            $montantprime = htmlspecialchars($_POST["montant_prime"]);
            $addPrime = $bdd->prepare("INSERT INTO prime (id_interim, id_member, somme, dates) VALUES (?, ?, ?, NOW())");
            $addPrime->execute(array($getid, $getInfoValid1f["id_interim"], $montantprime));
        }

        if(isset($_POST["delete_trajet"])) {
            $deleteTrajet = $bdd->prepare("DELETE FROM `trajet` WHERE id = ?");
            $deleteTrajet->execute(array($_POST["delete_trajet"]));
        }

        if(isset($_POST["submit_update_tel"])) {
            $tel = htmlspecialchars($_POST["update_tel"]);
            $updateTel = $bdd->prepare("UPDATE interim SET tel_interim = ? WHERE id_interim = ?");
            $updateTel->execute(array($tel, $getid));
            header("Location: ./interim.php?id_interim=".$getid);
        }

        ?>


        <h1 style="text-align: center; margin-top: 1rem;">Oil Compagny</h1>
        <h2 class="text-center">Fiche de <?= $gInfoInterim["prenom_interim"]; ?> <?= $gInfoInterim["nom_interim"]; ?></h2>
        <?php
        if (in_array("update_tel", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) {?>
        <form action="" method="POST" class="text-center">
            <label>
                <h3 class="text-center">Numéro de téléphone : 06<input type="number" name="update_tel" value="<?= $gInfoInterim['tel_interim'];?>"></h3>
            </label><br>
            <button type="submit" class="btn btn-primary" name="submit_update_tel" style="margin-top: 1rem;">
                Update numéro téléphone
            </button>
        </form>
        <?php }else { ?>
            <h3 class="text-center">Numéro de téléphone : 06<?= $gInfoInterim["tel_interim"]; ?></h3>
       <?php } ?>
        <a href="index.php" style="font-size: 1.5rem; margin-left: 2.5rem;">retour</a>
        <div class="container" style="margin-top: 1.5rem;">
            <form action="" method="POST">
            <div class="row">
                <div class="<?= in_array("add_prime", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)) ? 'col-6' : 'col-12' ?> text-center">
                    <label>
                        Raison :<br>
                        <input type="text" name="raison_blacklist">
                    </label><br>
                    <button type="submit" class="btn btn-primary" name="add_blacklist" style="margin-top: 1rem;">
                        <?= $gInfoInterim["blacklist"] ? "Déblacklist" : "Blacklist" ?>
                    </button>
                </div>
                <?php if(in_array("add_prime", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>
                    <div class="col-6 text-center">
                        <label>
                            Montant :<br>
                            <input type="text" name="montant_prime"><br>
                        </label><br>
                        <button type="submit" class="btn btn-primary" name="add_prime" style="margin-top: 1rem;">
                            Prime
                        </button>
                    </div>
                <?php } ?>

                <?php if(in_array("add_rank", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) {

                    $getEmploye = $bdd->query("SELECT * FROM member WHERE id_member = ".$getid);
                    $gEmploye = $getEmploye->fetch();
                    if(isset($_POST["submit_aue"])) {
                        $rank_inscrit = htmlspecialchars($_POST["rank_inscrit"]);
                        $steamid_inscrit = htmlspecialchars($_POST["steamid_inscrit"]);
                        if($rank_inscrit && $steamid_inscrit) {
                            if($gEmploye) {
                                $updateEmploye = $bdd->prepare("UPDATE member SET rank_member = ?, steamid_member = ? WHERE id_member = ?");
                                $updateEmploye->execute(array($rank_inscrit, $steamid_inscrit, $getid));
                            }else {
                                $addEmployes = $bdd->prepare("INSERT INTO member (id_member, rank_member, steamid_member) VALUES (?, ?, ?)");
                                $addEmployes->execute(array($getid, $rank_inscrit, $steamid_inscrit));
                            }
                        }

                    }

                    ?>

                    <div class="col-md-6">
                        <label for="validationDefault01SteamID" class="form-label">SteamID</label>
                        <input type="number" class="form-control" id="validationDefault01" name="steamid_inscrit" value="<?= $gEmploye["steamid_member"] ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="validationGraDE" class="form-label">Grade</label>
                        <select class="form-select" aria-label="Default select example" name="rank_inscrit">
                            <option selected value="0">Clique pour ouvrir le menu - Rank : <?= $gEmploye["rank_member"] ?></option>
                            <?php

                            $getAllRank = $bdd->query("SELECT * FROM rank");
                            while ($gRank = $getAllRank->fetch()) { ?>

                                <option value="<?= $gRank["rankname"]; ?>"><?= $gRank["rankname"]; ?></option>

                            <?php }$getAllRank->closeCursor(); ?>
                        </select>
                    </div>
                    <div class="col-12" style="text-align: center;"><br>
                        <button class="btn btn-primary" type="submit" name="submit_aue">Ajout/Update de l'employé</button>
                    </div>

                <?php } ?>

            </form>
            </div>


            <h3 style="margin-top: 1.5rem;">Listes de ces permis</h3>
            <form action="" method="POST">

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Permis conduire</th>
                    <th scope="col">Permis poids lourd</th>
                    <th scope="col">Permis bateau</th>
                    <th scope="col">Permis pilote</th>
                    <th scope="col">Update</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?= $gInfoInterim["permisconduire_interim"] ? "Oui" : "Non" ?></td>
                    <td><?= $gInfoInterim["permiscamion_interim"] ? "Oui" : "Non" ?></td>
                    <td><?= $gInfoInterim["permisbateau_interim"] ? "Oui" : "Non" ?></td>
                    <td><?= $gInfoInterim["permispilote_interim"] ? "Oui" : "Non" ?></td>
                    <td></td>
                </tr>
                <tr>

                    <td><?= $gInfoInterim["permisconduire_interim"] ? '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permisdeconduire_inscrit" checked>' : '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permisdeconduire_inscrit">' ?></td>
                    <td><?= $gInfoInterim["permiscamion_interim"] ? '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permispoidslourd_inscrit" checked>' : '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permispoidslourd_inscrit">' ?></td>
                    <td><?= $gInfoInterim["permisbateau_interim"] ? '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permisbateau_inscrit" checked>' : '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permisbateau_inscrit">' ?></td>
                    <td><?= $gInfoInterim["permispilote_interim"] ? '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permispilote_inscrit" checked>' : '<input class="form-check-input" type="checkbox" id="invalidCheck2" name="permispilote_inscrit">' ?></td>
                    <td><button class="btn btn-primary" type="submit" name="submit_update_permis">Mettre à jour</button></td>
                </tr>
                </tbody>
            </table>
            </form>
        </div>

        <div class="container" style="margin-top: 2rem;">
            <form action="" method="POST">
            <h2>Listes des trajets</h2>
                <table id="table_id" class="display">
                    <thead>
                    <tr style="text-align: center;">
                        <th>Prenom/Nom Inscrit (Employé)</th>
                        <th>Prenom/Nom Receptionné (Employé)</th>
                        <th>Quantité</th>
                        <th>Argent donnée</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>Annulé</th>
                        <?= in_array("delete", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)) ? "<th>Supprimé</th>" : "" ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $getAllTrajetInterim = $bdd->query("SELECT * FROM trajet WHERE id_interim = ". $_GET["id_interim"]." AND isdelete=0");


                    while ($gATI = $getAllTrajetInterim->fetch()) {

                        $getInfoValid1 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gATI["id_member_start"] . "'");
                        $getInfoValid1f = $getInfoValid1->fetch();
                        $getInfoValid2 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gATI["id_member_end"] . "'");
                        $getInfoValid2f = $getInfoValid2->fetch();
                        ?>
                        <tr style="text-align: center; <?= $gATI["quantity_trajet"] == 0 ? 'background-color:lightgray;' : 'background-color: white;' ?>">
                            <td><?= $getInfoValid1f["prenom_interim"] ?> <?= $getInfoValid1f["nom_interim"] ?></td>
                            <td><?= $getInfoValid2f["prenom_interim"] ?> <?= $getInfoValid2f["nom_interim"] ?></td>
                            <td><?= $gATI["quantity_trajet"]; ?></td>
                            <td><?= $gATI["price_trajet"]; ?></td>
                            <td><?= $gATI["date_trajet_start"]; ?></td>
                            <td><?= $gATI["date_trajet_end"]; ?></td>
                            <td><?= $gATI["quantity_trajet"] == 0 ? "Oui" : "Non"; ?></td>
                            <?= in_array("delete", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd)) ? '<td><button type="submit" class="btn btn-danger" name="delete_trajet" value="'.$gATI["id"].'" style="margin-top: 1rem;">Supprimé</button></td>' : "" ?>
                        </tr>
                    <?php  }$getAllTrajetInterim->closeCursor(); ?>
                    </tbody>
                </table>
            </form>
        </div>


        <div class="container" style="margin-top: 2rem;">
            <form action="" method="POST">
                    <?php
                    if (in_array("add_note", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>
                        <div class="text-center">
                            <h2>Ajout d'une note :</h2>
                            <label>
                                Raison :<br>
                                <input type="text" name="raison_note">
                            </label><br>
                            <button type="submit" class="btn btn-primary" name="add_note" style="margin-top: 1rem;">
                                Ajout de la note
                            </button>
                        </div>
                    <?php } ?>

                <h2 style="margin-top: 1.5rem;">Listes des notes</h2>
                <table id="table_note" class="display">
                    <thead>
                    <tr style="text-align: center;">
                        <th>Prenom/Nom (Employé)</th>
                        <th>Raison</th>
                        <th>type</th>
                        <th>Dates</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $getAllNote = $bdd->query("SELECT * FROM note WHERE id_interim = ". $_GET["id_interim"]);

                    while ($gAN = $getAllNote->fetch()) {

                        switch ($gAN["type"]) {
                            case 0:
                                $type = "Note";
                                break;
                            case 1:
                                $type = "Blacklist";
                                break;
                            default:
                                $type = "Undefined";
                                break;
                        }

                        $getInfoValid1 = $bdd->query("SELECT * FROM interim WHERE id_interim = '" . $gAN["id_member"] . "'");
                        $getInfoValid1f = $getInfoValid1->fetch();
                        ?>
                        <tr style="text-align: center;">
                            <td><?= $getInfoValid1f["prenom_interim"] ?> <?= $getInfoValid1f["nom_interim"] ?></td>
                            <td><?= $gAN["raison"]; ?></td>
                            <td><?= $type ?></td>
                            <td><?= $gAN["dates"]; ?></td>
                        </tr>
                    <?php  }$getAllNote->closeCursor(); ?>
                    </tbody>
                </table>
            </form>
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
            $('#table_note').DataTable();
        });

    </script>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

    </body>
    </html>

<?php } ?>