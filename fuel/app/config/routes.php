<?php
return array(
	'_root_'  => 'index/index',
	'login'  => 'index/login',
	'rooms/(:num)/(:num)'  => 'room/messages/$1/$2',
	'rooms/(:num)/(:num)/email'  => 'room/email/$1/$2',
);