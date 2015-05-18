<?php

namespace Fuel\Migrations;

class Add_action_to_messages
{
	public function up()
	{
		\DBUtil::add_fields('messages', array(
			'action' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),

		));
	}

	public function down()
	{
		\DBUtil::drop_fields('messages', array(
			'action'

		));
	}
}