<?php
	require_once '../vendor/autoload.php';
	
	$hs = new Haystack("yourwebsite.com");
	
	switch ($hs) {
		case $hs->contains('.test'):
			echo "this is a local site";
		break;
		case $hs->contains('.com'):
			echo "this is a live site";
		break;
		default:
			echo "we don't know what this site is";
	}
?>
