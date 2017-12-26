<?php

# Obtention des arguments
$nomFichier = $argv[1];


# 1. Extraction du PCG sous forme structurée et export en CSV

# Extraction du texte
shell_exec( "pdftotext -f 124 -l 143 '$nomFichier' PCG.csv" );

# Lecture du texte résultat
$text = file_get_contents( 'PCG.csv' );

# Retrait des 9 premières lignes et des 36 dernières lignes
$text = implode( "\n", array_slice( explode( "\n", $text ), 8, -36 ) );

# Retrait des changements de page
$text = preg_replace( '/\n\nAutorité des normes comptables\n\n- page n°[0-9]{3}\/177\n\n\x0c/', "\n", $text );

# Retrait des titres 'Classe [0-9]'
$text = preg_replace( '/^Classe ([0-9]) : (.*)$/m', "$1\t$2", $text );

# Formatage avec une tabulation
$text = preg_replace( '/^([0-9]+)( ?- | – )/m', "$1\t", $text );

# Retrait des retours à la ligne
$lines = explode( "\n", $text );
$linesLength = count( $lines );
for( $i = 1; $i < $linesLength; $i++ ) {
	
	if( ! preg_match( '/\t/', $lines[$i] ) ) {
		$lines[$i-1] = $lines[$i-1] . ' ' . $lines[$i];
		unset( $lines[$i] );
	}
}
$text = implode( "\n", $lines );

# Utilisation d’apostrophes typographiques
$text = preg_replace( '/\'/', '’', $text );

# Typographie
$text = preg_replace( '/Ecart/', 'Écart', $text );
$text = preg_replace( '/Etranger/', 'Étranger', $text );
$text = preg_replace( '/Etat/', 'État', $text );
$text = preg_replace( '/Emission/', 'Émission', $text );
$text = preg_replace( '/Etude/', 'Étude', $text );
$text = preg_replace( '/Echantillon/', 'Échantillon', $text );
$text = preg_replace( '/\(Même ventilation que celle du compte /', '(même ventilation que celle du compte ', $text );

# Ajout d’un retour à la ligne à la fin
#$text .= "\n";

# Écriture du texte résultat
file_put_contents( 'PCG.csv', $text );


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
	
	return <<<EOT
<gnc:account version="2.0.0">
  <act:name>$number $name</act:name>
  <act:id type="guid">$guid</act:id>
  <act:type>ASSET</act:type>
  <act:commodity>
    <cmdty:space>ISO4217</cmdty:space>
    <cmdty:id>EUR</cmdty:id>
  </act:commodity>
  <act:commodity-scu>100</act:commodity-scu>
  <act:description></act:description>
  <act:slots>
    <slot>
      <slot:key>placeholder</slot:key>
      <slot:value type="string">true</slot:value>
    </slot>
  </act:slots>
  <act:parent type="guid">$parent</act:parent>
</gnc:account>

EOT;
}

function GnuCash_create_footer() {
	
	return <<<EOT
</gnc-v2>

EOT;
}


function GnuCash_add_hierarchy( $text, $root ) {
	
	$lines = explode( "\n", $text );
	sort( $lines );
	
	$accounts = array();
	$guids = array();
	$i = 0;
	foreach( $lines as $account ) {
		
		preg_match( '/^([0-9]+)\t(.*)$/', $account, $matches );
		$guids[$matches[1]] = GnuCash_get_guid();
		
		$parent = $root;
		if( $matches[1] >= 10 && isset( $guids[substr($matches[1],0,-1)] ) )
			$parent = $guids[substr($matches[1],0,-1)];
		
		elseif( intval($matches[1]) >= 10 )
			throw new Exception();
		
		$accounts[] = array( $matches[1], $matches[2], $guids[$matches[1]], $parent );
		$i++;
	}
	
	return $accounts;
}


# Remplacement des deux-points car GnuCash l’utilise comme séparateur
$text = preg_replace( '/:/', ';', $text );

# Create GnuCash XML file
$root_guid = GnuCash_get_guid();
$accounts = GnuCash_add_hierarchy( $text, $root_guid );
$output = '';

$output .= GnuCash_create_header( $root_guid, count( $accounts ) + 1 );

foreach( $accounts as $account ) {
	
	$output .= GnuCash_create_account( $account );
}

$output .= GnuCash_create_footer();

file_put_contents( 'PCG-gnucash.xml', $output );

