<?php
require 'config.php';
require 'db.php';

set_time_limit(3600);
$db = new DB();
$image_name = '+no-image';
ini_set('display_errors', 'on');
error_reporting(E_ALL);
set_time_limit(15);
if ($db->connect() )
{
	odbtp_use_row_cache() or die("0");


	// Call ODBC API catalog function SQLTables to get list of tables.
	$qryTables = odbtp_query( "||SQLTables" ) or die("1");

	// Detach result so that another query can be executed on
	// the same connection without losing this one.
	odbtp_detach( $qryTables ) or die("2");
	odbtp_attach_field( $qryTables, 2, $table ) or die("3");
	print_r($qryTables);die();

	echo "<dl>";
	while( odbtp_fetch( $qryTables ) ) {
		// Skip system tables.
		if( !strncmp( $table, 'sys', 3 ) ) continue; // ignore system tables

		echo "<dt>$table<dd>";
		die();
		// Call ODBC API catalog function SQLColumns to get
		// table columns.
		$qryColumns = odbtp_query( "||SQLColumns|||$table" ) or die;
		odbtp_attach_field( $qryColumns, 3, $column );
		odbtp_attach_field( $qryColumns, 5, $type );

		while( odbtp_fetch( $qryColumns ) ) {
			echo "$column:";
			$r = $db->fetch_array("SELECT ".$column." FROM ".$table." WHERE ".$column."=17287");
			print_r($r);
			die();

		}
		echo " <br></dd>";
	}
	echo "</dl>";

	odbtp_close();
}
