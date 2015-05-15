<?php
return array(
	'_root_'  => 'index/index',
	'login'  => 'index/login',
	'rooms/(:num)/messages'  => 'room/messages/$1',
	'rooms/(:num)/images'  => 'image/index/$1',
	'rooms/(:num)/(:num)'  => 'room/messages/$1/$2',
	'rooms/(:num)/(:num)/email'  => 'room/email/$1/$2',
);