Plans comptables français
=========================

Les fichiers disponibles ici sont les plans comptables français (PCG, PCA, etc.) aux formats **CSV** et **GNUCash**.

Les différents plans comptables sont :

* _Plan comptable général (PCG) :_ applicable à la majorité des entreprises, issu du règlement 2014-03 consolidé au 1er janvier 2019,
* _Plan comptable associatif (PCA) :_ applicable à la majorité des associations, issu du règlement 99-01 du 16 février 1999, il reprend le PCG (consolidé au au 1er janvier 2019) et le précise,
* _Plan comptable associatif applicable aux groupements d’épargne retraite populaire :_ issu du règlement 2004-12 du 23 novembre 2004, il reprend le PCA (consolidé au 1er janvier 2019) et le précise.

À venir :

* _Plan comptable associatif applicable aux fondations :_ issu du règlement 2009-01 du 3 décembre 2009, il reprend le PCG (consolidé au 1er janvier 2019) et le précise,
* _Plan comptable applicable aux entreprises d’assurance :_ issu du règlement 2015-11 du 26 novembre 2015 consolidé au 31 décembre 2016.
* _Plan comptable applicable aux organismes de placement collectif à capital variable :_ issu du règlement 2014-01 du 14 janvier 2014 (qui sera probablement consolidé à la suite de l’homologation du règlement 2017-05 du 1er décembre 2017)
* _Plan comptable applicable aux organismes de gestion collective des droits d’auteur et droits voisins :_ issu du règlement 2017-07 du 1er décembre 2017 (en cours d’homologation à date), très légère adaptation du PCG

AVIS DE NON-RESPONSABILITÉ
--------------------------

Bien que ces fichiers soient automatiquement extraits des PDF officiels, ils n’ont été **ni relus ni certifiés** par un expert-comptable ou une instance officielle, ils sont donc à utiliser **sous votre seule responsabilité**.

De plus, ces fichiers ne sont que des plans comptables, la règlementation comptable française est explicitée plus en détails notamment dans les différents PDF officiels.

Re-génération
-------------

Pour re-générer les différents fichiers, consulter les instructions dans le dossier `bin`.

Relations entre plans comptables
--------------------------------

Ce graphe indique les relations de dépendance entre les différents plans comptables français. L’éloignement dans le graphe indique à peu près l’éloignement le nombre de différences.

Il ne s’agit pas vraiment d’un graphe officiel, quoique tiré des règlements lorsqu’il y a une phrase du type « Le présent plan comptable se base sur tel autre plan comptable ».

```
PCG 2014-03                entreprises
 |\
 | - PCG 2017-07           organismes de gestion collective des droits d’auteur et droits voisins (en cours d’homologation à fin 2017)
 |\
 | - PCA 99-01             associations
 | |\
 | | - PCA 2004-12         associations groupements d’épargne retraite populaire
 |  \
 |   - PCA 2009-01         fondations et fonds de dotation
 |\
 | - PCG 2014-01           organismes de placement collectif à capital variable
  \
   - PCAssurances 2015-11  entreprises d’assurance
```
