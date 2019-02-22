<?php
error_reporting( E_ALL );
if( $_POST['formulario']!='' ){
	require_once('../../../wp-load.php');
    global $wpdb;
	$table_name = $wpdb->prefix . 'mgs_forms_submits';
	$sql = "SELECT * FROM ".$table_name." WHERE post_id='".$_POST['formulario']."' ORDER BY fecha DESC";
	$out = '';$header = '';
	$a_header = array('ID', 'fecha');
	$resus = $wpdb->get_results($sql);
	header('Content-type: text/csv');
	header('Content-Disposition: attachment; filename="registros.csv"');
	foreach( $resus as $resu ){
		$f = unserialize($resu->fields);
		foreach( $f as $k=>$v ){
			$a_header[] = $k;		
		}
	}
	$a_header = array_unique($a_header);
	$header = implode(',',$a_header);
	$out .= $header.PHP_EOL;

	$resus = $wpdb->get_results($sql);
	foreach( $resus as $resu ){
		$t = array($resu->id, $resu->fecha);
		$f = unserialize($resu->fields);
		foreach( $f as $k=>$v ){
			if( is_array($v) ){
				$t[] = implode(' | ', $v);
			}else{
				$t[] = $v;
			}
		}
		$l = '"'.implode('","', $t).'"';
		$out .= $l.PHP_EOL;
	}
	echo $out;
}
	