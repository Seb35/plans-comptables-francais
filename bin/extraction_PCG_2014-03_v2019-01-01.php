<?php

# Obtention des arguments
$nomFichier = escapeshellarg( $argv[1] );


# 1. Extraction du PCG sous forme structurée et export en CSV

# Extraction du texte principal
shell_exec( "pdftohtml -f 171 -l 191 $nomFichier PCG.html" );

# Lecture du texte résultat
$text = file_get_contents( 'PCGs.html' );

# Extraction du texte pour la classe 8
shell_exec( "pdftohtml -f 228 -l 229 $nomFichier PCG.html" );

# Lecture du texte résultat
$text = $text . "\n" . file_get_contents( 'PCGs.html' );

# Nettoyage de l’environnement
unlink( 'PCG.html' );
unlink( 'PCGs.html' );
unlink( 'PCG_ind.html' );

# Remplacement des espaces et des guillemets droits
$text = preg_replace( '/&#160;/', ' ', $text );
$text = preg_replace( '/&#34;/', '"', $text );

# Remplacement des retours à la ligne
$text = preg_replace( "/\n/", '', $text );
$text = preg_replace( '/<br ?\/>/', "\n", $text );

# Extraction des lignes hors texte
$textarray = explode( "\n", $text );
$text = implode( "\n", array_merge( array_slice( $textarray, 3, 1129 ), array( '<b>Classe 8 : Comptes spéciaux</b>' ), array_slice( $textarray, 1163, 1 ), array_slice( $textarray, 1172, 7 ), array_slice( $textarray, 1180, 7 ), array_slice( $textarray, 1201, 8 ), array_slice( $textarray, 1212, 1 ), array_slice( $textarray, 1217, 1 ), array( '<i>890. Bilan d’ouverture', '891. Bilan de clôture</i>' ) ) );

# Retrait des changements de page
$text = preg_replace( "/\n *Version du 1er Janvier 2019 *\n *[0-9]+ *\n *\n<hr\/?><a name=[0-9]+><\/a>PLAN COMPTABLE GENERAL VERSION CONSOLIDEE *\n *\n/i", "\n", $text );

# Retrait des titres 'Classe [0-9]'
$text = preg_replace( '/Classe ([0-9]) : (.*)$/m', "$1 - $2", $text );

# Syntaxe autre pour la classe 8
$text = preg_replace( '/^(<\/?(?:b|i)>)?8([0-9]+)(?:\.| :) (.*)$/m', "\${1}8$2 - $3", $text );

# Ajout de balises <b> et <i> sur chaque ligne lorsqu’applicable
$text = preg_replace_callback( '/<i> *(.*?) *<\/i>/s', function( $matches ) {
	return '<i>' . preg_replace( "/ *\n */", "</i>\n<i>", $matches[1] ) . '</i>';
}, $text );
$text = preg_replace_callback( '/<b> *(.*?) *<\/b>/s', function( $matches ) {
	return '<b>' . preg_replace( "/ *\n */", "</b>\n<b>", $matches[1] ) . '</b>';
}, $text );

$text = preg_replace( '/<b><\/b>/', '', $text );
$text = preg_replace( '/<i><\/i>/', '', $text );

# Exception
$text = preg_replace( '/2081 *Mali de fusion sur actifs incorporels/', '2081 - Mali de fusion sur actifs incorporels', $text );

# Formatage avec une tabulation
$text = preg_replace( '/^(<[bi]>)?([0-9]+)( {0,4}- {0,2}| – )/m', "$1$2\t", $text );

# Retrait des lignes vides
$text = preg_replace( '/ +\n/', "\n", $text );
$text = preg_replace( '/ +$/', '', $text );
$text = preg_replace( '/\n+/', "\n", $text );

# Retrait des retours à la ligne
for( $j=0; $j<2; $j++ ) {
	$lines = explode( "\n", $text );
	$linesLength = count( $lines );
	for( $i = 1; $i < $linesLength; $i++ ) {
		
		if( ! preg_match( '/\t/', $lines[$i] ) ) {
			$lines[$i-1] = $lines[$i-1] . ' ' . $lines[$i];
			unset( $lines[$i] );
			$i++;
		}
	}
	$text = implode( "\n", $lines );
}

$text = preg_replace( '/<\/b> <b>/', ' ', $text );
$text = preg_replace( '/<\/i> <i>/', ' ', $text );

# Création d’une troisième colonne en fonction du système (abrégé, de base, étendu)
$text = preg_replace( '/^([0-9]+)\t(.*)$/m', "$1\tbase\t$2", $text );
$text = preg_replace( '/^<b>([0-9]+)\t(.*)<\/b>$/m', "$1\tabrégé\t$2", $text );
$text = preg_replace( '/^<i>([0-9]+)\t(.*)<\/i>$/m', "$1\tétendu\t$2", $text );

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
$text = preg_replace( '/elle - même/', 'elle-même', $text );
$text = preg_replace( '/Sous - sols/', 'Sous-sols', $text );
$text = preg_replace( '/non - gérants/', 'non-gérants', $text );
$text = preg_replace( '/ - /', ' – ', $text );
$text = preg_replace( '/ –/', ' – ', $text );
$text = preg_replace( '/ +/', ' ', $text );

# Écriture du texte résultat
file_put_contents( 'PCG_2014-03_v2019-01-01.csv', $text . "\n" );


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
		if( intval( substr( $matches[1], 0, 1 ) ) == 4 ) $type = 'LIABILITY';
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
#foreach( array( 'base', 'étendu' ) as $system ) {
	
	# Create GnuCash XML file
	$root_guid = GnuCash_get_guid();
	$accounts = GnuCash_add_hierarchy( $text, $system, $root_guid );
	$output = '';
	
	$output .= GnuCash_create_header( $root_guid, count( $accounts ) + 1 );
	
	foreach( $accounts as $account ) {
		
		$output .= GnuCash_create_account( $account );
	}
	
	$output .= GnuCash_create_footer();
	
	file_put_contents( "PCG_2014-03_v2019-01-01_$system-gnucash.xml", $output );
}

