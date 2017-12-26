Binaires de création des plans comptables
=========================================

Les différents plans comptables sont extraits des PDF venant de http://www.anc.gouv.fr/cms/sites/anc/accueil/normes-francaises/reglementation-sectorielle.html.

Pour les PCG correspondant au règlement 2014-03, lancer un des `extraction_PCG_2014-03_v*.php` avec pour seul argument l’emplacement du PDF. Par exemple :

```lang=sh
php extraction_PCG_2014-03_v2017-01-01.php pcg_janvier 2017.pdf
```

Les condensats MD5 des trois PDF sont :

* Reglt 2014-03\_Plan comptable general.pdf : 33918071000b7de43ea72b369ed26e7a
* PCG JANVIER 2016.pdf : 20ee634be8da776de7a0167b7748d076
* pcg\_janvier 2017.pdf : c3a8af79f120a991c4342b65f6e59159

Noter que le niveau de détails 'étendu' n’est pas obtenu pour v2016-01-01 et v2017-01-01, il a été entré à la main. Il y a un petit bug restant dans v2017-01-01 car les numéros de page apparaissent dans certains noms de comptes (dans une page sur deux), ils ont été retirés à la main.

Le binaire `export_gnucash.php` permet, à partir d’un fichier CSV, d’obtenir les trois fichiers \*-gnucash.xml correspondants aux trois niveaux de détails.
