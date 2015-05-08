<?php

namespace Fuel\Migrations;

class Add_email_1_to_rooms
{
	public function up()
	{
		\DBUtil::add_fields('rooms', array(
			'email_1' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),

		));
	}

	public function down()
	{
		\DBUtil::drop_fields('rooms', array(
			'email_1'

		));
	}
}