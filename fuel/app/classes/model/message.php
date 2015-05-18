<?php

class Model_Message extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'room_id',
		'sender',
		'action',
		'text',
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

	protected static $_table_name = 'messages';

	public static function clear_cached_objects()
    {
        static::$_cached_objects['Model_Message'] = array();
    }

}
