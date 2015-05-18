<?php

class Model_Room extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'number',
		'passcode',
		'email_1',
		'email_2',
		'name' => array('default' => ''),
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

	protected static $_table_name = 'rooms';

	protected static $_has_many = array(
		'messages' => array(
			'model_to' => 'Model_Message',
			'key_to' => 'room_id',
			'key_from' => 'id',
			'conditions' => array(
				'order_by' => array('created_at' => 'asc')
			)
		),
		'images' => array(
			'model_to' => 'Model_Image',
			'key_to' => 'room_id',
			'key_from' => 'id',
			'conditions' => array(
				'order_by' => array('created_at' => 'asc')
			)
		),
	);

	public static function validate()
	{
		$val = Validation::forge('create');
		$val->add_field('number', __('Number', array(), 'Number'), 'required|max_length[255]|unique_number');
		$val->add_field('passcode', __('Passcode', array(), 'Passcode'), 'required|max_length[255]');

		return $val;
	}

	public static function clear_cached_objects()
    {
        static::$_cached_objects['Model_Room'] = array();
    }

}
