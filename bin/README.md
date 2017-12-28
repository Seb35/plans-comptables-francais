Binaires de création des plans comptables
=========================================

Fichiers source
---------------

Les différents plans comptables sont extraits des PDF venant de http://www.anc.gouv.fr/cms/sites/anc/accueil/normes-francaises/reglementation-sectorielle.html.

Les condensats MD5 des PDF sont :

- Reglt 2014-03\_Plan comptable general.pdf : 33918071000b7de43ea72b369ed26e7a
- PCG JANVIER 2016.pdf : 20ee634be8da776de7a0167b7748d076
- pcg\_janvier 2017.pdf : c3a8af79f120a991c4342b65f6e59159
- Reg\_9901\_consolide.pdf : cb1d9c0876296afca4e151cb548aaff8

PCG
---

Pour les PCG correspondant au règlement 2014-03, lancer un des `extraction_PCG_2014-03_v*.php` avec pour seul argument l’emplacement du PDF. Par exemple :

```sh
php extraction_PCG_2014-03_v2017-01-01.php pcg_janvier 2017.pdf
```

Noter que le niveau de détails 'étendu' n’est pas obtenu pour v2016-01-01 et v2017-01-01 car l’extraction du PDF avec `pdftohtml` ne rend pas l’italique (probablement une différence dans le formatage interne du PDF), ce niveau de détails a donc été entré à la main après obtention du CSV (il faut ensuite exécuter `export_gnucash.php` sur le fichier CSV pour obtenir les fichiers GNUCash correspondants au CSV).

PCA
---

Pour les PCA correspondant au règlement 99-01 consolidé, lancer au préalable l’extraction du PCG sur lequel se base le PCA, puis lancer `extraction_PCA_99-01_associations_issu_du_PCG_2014-03_v2017-01-01.php` avec pour seul argument l’emplacement du CSV du PCG. Par exemple :

```sh
php extraction_PCA_99-01_associations.php PCG_2014-03_v2017-01-01.csv
```

Noter que, contrairement au PCG, le règlement 99-01 du PCA ne mentionne pas de niveau de détail (abrégé, base, étendu). Les comptes créés le sont ici avec le niveau de détail 'base' et les comptes remplacés conservent leur niveau de détail du PCG.

Autre
-----

Le binaire `export_gnucash.php` permet, à partir d’un fichier CSV, d’obtenir les trois fichiers \*-gnucash.xml correspondants aux trois niveaux de détails.
