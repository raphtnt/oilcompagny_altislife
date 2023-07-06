create table depenseperte
(
    id         int auto_increment
        primary key,
    id_interim int                  null,
    raison     longtext             null,
    montant    int                  null,
    dates      datetime             null,
    depense    tinyint(1) default 0 null,
    perte      tinyint(1) default 0 null
);

create table interim
(
    id                     int auto_increment
        primary key,
    id_interim             int          null,
    prenom_interim         varchar(255) null,
    nom_interim            varchar(255) null,
    tel_interim            int          null,
    permisconduire_interim tinyint(1)   null,
    permiscamion_interim   tinyint(1)   null,
    permisbateau_interim   tinyint(1)   null,
    permispilote_interim   tinyint(1)   null,
    blacklist              tinyint(1)   null,
    note_interim           longtext     null
);

create table member
(
    id             int auto_increment
        primary key,
    id_member      int         null,
    rank_member    varchar(32) null,
    steamid_member varchar(64) null
);

create table prime
(
    id         int auto_increment
        primary key,
    id_interim int      null,
    id_member  int      null,
    somme      int      null,
    dates      datetime null
);

create table `rank`
(
    id          int auto_increment
        primary key,
    rankname    varchar(32) null,
    permissions longtext    null
);

create table settings
(
    id                       int auto_increment
        primary key,
    sell_price_interim       int null,
    sell_price_employe       int null,
    info_ancien              int null,
    info_payeinterim         int null,
    info_taxe                int null,
    info_depenseentreprise   int null,
    info_salaire             int null,
    info_pertes              int null,
    info_benefice            int null,
    settings_taxe            int null,
    trajet_interimaire_vente int null,
    cout_total_petrol        int null
);

create table trajet
(
    id                int auto_increment
        primary key,
    id_interim        int                  null,
    id_member_start   int                  null,
    id_member_end     int                  null,
    quantity_trajet   int                  null,
    price_trajet      int                  null,
    status_trajet     int        default 0 null,
    date_trajet_start datetime             null,
    date_trajet_end   datetime             null,
    employe_trajet    tinyint(1) default 0 null,
    semaine           varchar(32)          null
);

create table vente
(
    id          int auto_increment
        primary key,
    id_interim  int           null,
    quantity    int           null,
    price       int           null,
    nbr_interim int default 0 null,
    date_vente  datetime      null,
    semaine     varchar(32)   null
);

