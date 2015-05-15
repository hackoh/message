<?php

class Model_Image extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'url' => array('default' => ''),
		'room_id',
		'name',
		'type',
		'size',
		'extension',
		'saved_as',
		'created_at',
		'updated_at',
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_update'),
			'mysql_timestamp' => false,
		),
	);

	protected static $_table_name = 'images';

}
