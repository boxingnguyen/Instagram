<?php
include "../../../vendor/Instagram/src/Instagram.php";
use MetzWeb\Instagram\Instagram;

$instagram = new Instagram(array(
		'apiKey'      => 'f31c3725215449c6bde2871932e7bc15',
		'apiSecret'   => '0a64babe62df4bba919dcd685e85eead',
		'apiCallback' => 'http://192.168.33.20/Instagram/detail.php'
));

echo "<a href='{$instagram->getLoginUrl()}' target = '_blank'>Login with Instagram</a>";