<?php


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

$d = date('W-Y');
echo $d;

/*$addTrajet = $bdd->prepare("INSERT INTO test (semaine) VALUES (?)");
$addTrajet->execute(array(1));*/