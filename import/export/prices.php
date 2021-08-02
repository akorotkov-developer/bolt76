<?php
$price_count = 0;

function load_prices( $db )
{
	global $NOLINK;
	global $CONF_FILE;
	global $price_count;
	global $PriceV1;
	global $PriceV2;
	global $PriceV3;

	$price_count = 1;
	$prices = array();

	$qq_price = $db->query( "SELECT count(*) as qq FROM TYPECOSTS t JOIN DOC d ON d.ROWID=t.Dokument_ where d.nomer=".$PriceV1." or d.nomer=".$PriceV2." or d.nomer=".$PriceV3 );
	if( ( $row = $db->fetch_assoc( $qq_price ) ) )
	{
		$price_count=$row[ 'qq' ];
		++$price_count;
	}

	$q = "SELECT e.Primechanie, t.ROWID, d.Nomer, t.Razdel, t.Primechanie FROM TYPECOSTS t JOIN DOC d ON d.ROWID=t.Dokument_ join TYPECOSTS e on t.razdel=e.rowid where (d.nomer=".$PriceV1." or d.nomer=".$PriceV2." or d.nomer=".$PriceV3.") order by e.Primechanie";
	$q_price_ver = $db->fetch_array($q);
	$i = 1;
	foreach ($q_price_ver as $row) {
		$prices[ $i ][ 'ID' ] = $row[ 'ROWID' ];
		$prices[ $i ][ 'Ver' ] = $row[ 'Nomer' ];
		$price_name = $db->query( "select Nazvanie FROM TYPECOSTS WHERE Tip=200 AND ROWID=".$row[ 'Razdel' ]." order by Primechanie");
		if ( ( $row_name = $db->fetch_assoc( $price_name ) ) )
			$prices[ $i ][ 'Name' ] = $row_name[ 'Nazvanie' ];
		++$i;
	}
	return $prices;
}
?>