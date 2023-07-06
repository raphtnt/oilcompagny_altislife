<?php
require('steamauth/steamauth.php');
try {
    $devmod = true;
    if($devmod) {
        $bdd = new PDO('mysql:host=127.0.0.1;dbname=homelife;charset=utf8', 'root', 'toor');
    }else {
        $bdd = new PDO('mysql:host=bw8wl.myd.infomaniak.com;dbname=bw8wl_oil;charset=utf8', 'bw8wl_oil', 'x7Btd-lwfL4');
    }
} catch (PDOException $e) {
    die('[BDD] Erreurs, Please contact administrator | Raphael Tax | raphtnt#2339');
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
        <title>HLGroup</title>
    </head>
    <body>
    <?php if (in_array("view_pos", getPermissionDecode($bdd)) || in_array("*", getPermissionDecode($bdd))) { ?>

            <h1 style="text-align: center; margin-top: 1rem;">HLGroup - Mercenaire</h1>
            <div class="container" style="margin-top: 2rem;">
                <h2>Listes des positions</h2>
                <button class="btn-info" style="margin: 1rem 0;">Ajout</button>
                <table id="table_id" class="display">
                    <thead>
                    <tr style="text-align: center;">
                        <th>Type</th>
                        <th>Coordonée</th>
                        <th>Vers ou ?</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $getPosition = $bdd->query("SELECT * FROM positions");
                    while ($gPos = $getPosition->fetch()) { ?>
                        <tr style="text-align: center;">
                            <td><?= $gPos["type"]; ?></td>
                            <td><?= $gPos["coordonee"]; ?></td>
                            <td><?= $gPos["ou"]; ?></td>
                        </tr>
                    <?php  }$getPosition->closeCursor(); ?>
                    </tbody>
                </table>
            </div>

    <?php }else { ?>
        <h1 style="text-align: center;">Votre steamid : <?= $_SESSION["steamid"]; ?> </h1>
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
    </script>

    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

    </body>
    </html>

<?php } ?>