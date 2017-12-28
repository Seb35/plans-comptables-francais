<?php

# Les données et traitements ci-après sont issus du PDF du règlement 99-01 du 16 février 1999 consolidé avec des règlements 2004-12 (23 novembre 2004), 2008-12 (7 mai 2008), 2009-01 (3 décembre 2009), disponible sur http://www.anc.gouv.fr/cms/sites/anc/accueil/normes-francaises/reglementation-sectorielle.html (condensat MD5 = cb1d9c0876296afca4e151cb548aaff8).

# Donnée d’entrée : fichier CSV du PCG

# Chapitre IV – Liste et contenu des comptes de fonds associatifs
$fonds_associatifs = <<<EOF
10	Fonds associatifs et réserves
102	Fonds associatifs sans droit de reprise
1021	Valeur du patrimoine intégré
1022	Fonds statutaires (à éclater en fonction des statuts)
1024	Apports sans droit de reprise
1025	Legs et donations avec contrepartie d’actifs immobilisés
1026	Subventions d’investissement affectées à des biens renouvelables
103	Fonds associatifs avec droit de reprise
1034	Apports avec droit de reprise
1035	Legs et donations avec contrepartie d’actifs immobilisés assortis d’une obligation ou d’une condition
1036	Subventions d’investissement affectées à des biens renouvelables
105	Ecarts de réévaluation
1051	Ecarts de réévaluation sur des biens sans droit de reprise
1052	Ecarts de réévaluation sur des biens avec droit de reprise
106	Réserves
1062	Réserves indisponibles
1063	Réserves statutaires ou contractuelles
1064	Réserves réglementées
1068	Autres réserves (dont réserves pour projet associatif)
11	Éléments en instance d’affectation
110	Report à nouveau
115	Résultats sous contrôle de tiers financeurs
13	Subventions d’investissements affectées à des biens non renouvelables
EOF;

# Chapitre VI – Nomenclature des comptes spécifiques
$autres_comptes = <<<EOF
1516	Provisions pour risques d’emploi
181	Apports permanents entre siège social et établissements
185	Biens et prestations de services échangés entre établissements et le siège social
186	Biens et prestations de services échangés entre établissements (charges)
187	Biens et prestations de services échangés entre établissements (produits)
19	Fonds dédiés
194	Fonds dédiés sur subventions de fonctionnement
195	Fonds dédiés sur dons manuels affectés
197	Fonds dédiés sur legs et donations affectés
228	Immobilisations grevées de droits
229	Droits des propriétaires
45	Confédération, fédération, union, associations affiliées
475	Legs et donations en cours de réalisation
68	Dotations aux amortissements, provisions et engagements
689	Engagements à réaliser sur ressources affectées
6894	Engagements à réaliser sur subventions attribuées
6895	Engagements à réaliser sur dons manuels affectés
6897	Engagements à réaliser sur legs et donations affectés
695	Impôts sur les sociétés
657	Subventions versées par l’organisme
756	Cotisations
789	Report des ressources non utilisées des exercices antérieurs
86	Emplois des contributions volontaires en nature
87	Contributions volontaires en nature
EOF;

# Chapitre III – Traitement des contributions volontaires en nature
$classe8 = <<<EOF
860	Secours en nature (alimentaire, vestimentaire, …)
861	Mise à disposition gratuite de bien (locaux, matériels, …)
862	Prestations
864	Personnel bénévole
870	Bénévolat
871	Prestations en nature
875	Dons en nature
EOF;

# Obtention des arguments
$nomFichierCSV = $argv[1];


# 1. Création du PCA à partir du PCG au format CSV

# Ouverture du fichier
$text = trim( file_get_contents( $nomFichierCSV ) );

# Retrait des comptes 10, 11, 13
$text = preg_replace( '/^(10|11|13)[0-9]*\t.*/m', '', $text );

# Ajout des fonds associatifs
$text .= "\n" . preg_replace( '/^([0-9]+)\t(.*)$/m', "$1\tbase\t$2", $fonds_associatifs );
$lines = explode( "\n", $text );
sort( $lines );
$text = trim( implode( "\n", $lines ) );

# Liste autres comptes
foreach( explode( "\n", $autres_comptes . "\n" . $classe8 ) as $line ) {
	$linearray = explode( "\t", $line );
	$nbcompte = $linearray[0];
	$nomcompte = $linearray[1];
	if( preg_match( "/^$nbcompte\t([a-zé]+)\t(.*)$/m", $text ) ) {
		$text = preg_replace( "/^$nbcompte\t([a-zé]+)\t(.*)$/m", "$nbcompte\t$1\t$nomcompte", $text );
	} else {
		$text .= "\n" . "$nbcompte\tbase\t$nomcompte";
	}
}
$lines = explode( "\n", $text );
sort( $lines );
$text = trim( implode( "\n", $lines ) );

# Remplacement de « client » par « usager » dans les comptes 41
$text = preg_replace( '/^(41[0-9]*)\t([a-zé]+)\t(.*)client(.*)$/m', "$1\t$2\t$3usager$4", $text );
$text = preg_replace( '/^(41[0-9]*)\t([a-zé]+)\t(.*)Client(.*)$/m', "$1\t$2\t$3Usager$4", $text );

# Enregistrement
file_put_contents( 'PCA_99-01_v2017-01-01.csv', $text . "\n" );


# 2. Export en format GnuCash

function GnuCash_get_guid() {
	
	$guid = '';
	for( $i=0; $i<40; $i++ ) {
		$guid .= dechex( rand( 0, 15 ) );
	}
	return $guid;
}

function GnuCash_create_header( $guid, $num ) {
	
	return <<<EOT
<?xml version="1.0" encoding="utf-8" ?>
<gnc-v2
     xmlns:gnc="http://www.gnucash.org/XML/gnc"
     xmlns:gnc-act="http://www.gnucash.org/XML/gnc-act"
     xmlns:act="http://www.gnucash.org/XML/act"
     xmlns:book="http://www.gnucash.org/XML/book"
     xmlns:cd="http://www.gnucash.org/XML/cd"
     xmlns:cmdty="http://www.gnucash.org/XML/cmdty"
     xmlns:price="http://www.gnucash.org/XML/price"
     xmlns:slot="http://www.gnucash.org/XML/slot"
     xmlns:split="http://www.gnucash.org/XML/split"
     xmlns:sx="http://www.gnucash.org/XML/sx"
     xmlns:trn="http://www.gnucash.org/XML/trn"
     xmlns:ts="http://www.gnucash.org/XML/ts"
     xmlns:fs="http://www.gnucash.org/XML/fs"
     xmlns:bgt="http://www.gnucash.org/XML/bgt"
     xmlns:recurrence="http://www.gnucash.org/XML/recurrence"
     xmlns:lot="http://www.gnucash.org/XML/lot"
     xmlns:cust="http://www.gnucash.org/XML/cust"
     xmlns:job="http://www.gnucash.org/XML/job"
     xmlns:addr="http://www.gnucash.org/XML/addr"
     xmlns:owner="http://www.gnucash.org/XML/owner"
     xmlns:taxtable="http://www.gnucash.org/XML/taxtable"
     xmlns:tte="http://www.gnucash.org/XML/tte"
     xmlns:employee="http://www.gnucash.org/XML/employee"
     xmlns:order="http://www.gnucash.org/XML/order"
     xmlns:billterm="http://www.gnucash.org/XML/billterm"
     xmlns:bt-days="http://www.gnucash.org/XML/bt-days"
     xmlns:bt-prox="http://www.gnucash.org/XML/bt-prox"
     xmlns:invoice="http://www.gnucash.org/XML/invoice"
     xmlns:entry="http://www.gnucash.org/XML/entry"
     xmlns:vendor="http://www.gnucash.org/XML/vendor">
<gnc:count-data cd:type="account">$num</gnc:count-data>
<gnc:account version="2.0.0">
  <act:name>Root Account</act:name>
  <act:id type="guid">$guid</act:id>
  <act:type>ROOT</act:type>
  <act:commodity-scu>0</act:commodity-scu>
</gnc:account>

EOT;
}

function GnuCash_create_account( $account ) {
	
	$number = $account[0];
	$name   = $account[1];
	$guid   = $account[2];
	$parent = $account[3];
	$type   = $account[4];
	$hidden = $account[5];
	
	$hiddenXml = '';
	if( $hidden ) {
		$hiddenXml = <<<EOT
  <act:slots>
    <slot>
      <slot:key>hidden</slot:key>
      <slot:value type="string">true</slot:value>
    </slot>
  </act:slots>

EOT;
	}
	
	return <<<EOT
<gnc:account version="2.0.0">
  <act:name>$number $name</act:name>
  <act:id type="guid">$guid</act:id>
  <act:type>$type</act:type>
  <act:commodity>
    <cmdty:space>ISO4217</cmdty:space>
    <cmdty:id>EUR</cmdty:id>
  </act:commodity>
  <act:commodity-scu>100</act:commodity-scu>
  <act:code>$number</act:code>
  <act:description></act:description>
$hiddenXml  <act:parent type="guid">$parent</act:parent>
</gnc:account>

EOT;
#    <slot>
#      <slot:key>placeholder</slot:key>
#      <slot:value type="string">true</slot:value>
#    </slot>
}

function GnuCash_create_footer() {
	
	return <<<EOT
</gnc-v2>

EOT;
}


function GnuCash_add_hierarchy( $text, $system, $root ) {
	
	$lines = explode( "\n", $text );
	sort( $lines );
	
	$accounts = array();
	$guids = array();
	$accountsIndex = array();
	$index = 0;
	foreach( $lines as $line ) {
		
		if( ! preg_match( '/^([0-9]+)\t([a-zé]+)\t(.*)$/i', $line, $matches ) ) {
			var_dump( $index );
			var_dump( $line );
			var_dump( $matches );
			continue;
		}
		# La condition suivante permettait de n’inscrire que les comptes du système considéré
		# mais il est préféré seulement cacher les comptes des systèmes plus détaillés
		#if( $system != 'étendu' && $matches[2] != 'abrégé' && $matches[2] != $system ) continue;
		
		$guids[$matches[1]] = GnuCash_get_guid();
		
		$parent = $root;
		if( $matches[1] >= 10 && isset( $guids[substr($matches[1],0,-1)] ) )
			$parent = $guids[substr($matches[1],0,-1)];
		
		elseif( $matches[1] >= 10 && isset( $guids[substr($matches[1],0,-2)] ) )
			$parent = $guids[substr($matches[1],0,-2)];
		
		elseif( intval($matches[1]) >= 10 ) {
			var_dump( $line );
			throw new Exception();
		}
		
		# Détermination du type au sens GnuCash
		$type = 'LIABILITY';
		if( intval( substr( $matches[1], 0, 1 ) ) == 1 ) $type = 'LIABILITY';
		if( intval( substr( $matches[1], 0, 1 ) ) == 2 ) $type = 'ASSET';
		if( intval( substr( $matches[1], 0, 1 ) ) == 3 ) $type = 'ASSET';
		if( intval( substr( $matches[1], 0, 1 ) ) == 5 ) $type = 'ASSET';
		if( intval( substr( $matches[1], 0, 1 ) ) == 6 ) $type = 'EXPENSE';
		if( intval( substr( $matches[1], 0, 1 ) ) == 7 ) $type = 'INCOME';
		if( intval( substr( $matches[1], 0, 2 ) ) == 10 ) $type = 'EQUITY';
		if( intval( substr( $matches[1], 0, 2 ) ) == 11 ) $type = 'EQUITY';
		if( intval( substr( $matches[1], 0, 2 ) ) == 12 ) $type = 'EQUITY';
		if( intval( substr( $matches[1], 0, 2 ) ) == 51 ) $type = 'BANK';
		if( intval( substr( $matches[1], 0, 2 ) ) == 53 ) $type = 'CASH';
		if( intval( substr( $matches[1], 0, 3 ) ) == 486 ) $type = 'ASSET';
		
		# Cacher ce compte ?
		$hidden = ( $system != 'étendu' && $matches[2] != 'abrégé' && $matches[2] != $system );
		if( !$hidden && $system != 'étendu' ) {
			for( $i=1; $i<strlen($matches[1]); $i++ ) {
				if( array_key_exists( substr($matches[1],0,$i), $accountsIndex ) ) {
					$accounts[$accountsIndex[substr($matches[1],0,$i)]][5] = false;
				}
			}
		}
		
		$accounts[] = array( $matches[1], $matches[3], $guids[$matches[1]], $parent, $type, $hidden );
		$accountsIndex[$matches[1]] = $index;
		$index++;
	}
	
	return $accounts;
}


# Remplacement des deux-points car GnuCash l’utilise comme séparateur
$text = preg_replace( '/:/', ';', $text );

foreach( array( 'abrégé', 'base', 'étendu' ) as $system ) {
	
	# Create GnuCash XML file
	$root_guid = GnuCash_get_guid();
	$accounts = GnuCash_add_hierarchy( $text, $system, $root_guid );
	$output = '';
	
	$output .= GnuCash_create_header( $root_guid, count( $accounts ) + 1 );
	
	foreach( $accounts as $account ) {
		
		$output .= GnuCash_create_account( $account );
	}
	
	$output .= GnuCash_create_footer();
	
	file_put_contents( "PCA_99-01_v2017-01-01_$system-gnucash.xml", $output );
}
