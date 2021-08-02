<?php
require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

set_time_limit(3600);

$db = new DB();
if ($db->connect() )
{
	$query = $db->query( 'SELECT data FROM ITEMS_ WHERE ROWID='.((int)$_REQUEST['id']) );
	$db->set_field_len( $query, 'data', 0x100000 );
	$rec = $db->fetch_assoc($query);
	if ($rec['data']) {
		header('Content-Type: image/jpg');
		header('Content-disposition: filename="'.md5($_REQUEST['id']).'.jpg"');
		print imagejpeg(imagecreatefromstring($rec['data']));
	} else {
		print "-";
	}
} else {
	print "-";
}