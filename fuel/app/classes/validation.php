<?php

class Validation extends \Fuel\Core\Validation
{
	public function _validation_unique_number($val)
	{
		if ($this->_empty($val))
		{
			return true;
		}

		$exists = DB::select()->from('rooms')->where('number', '=', $val)->execute()->as_array();

		if ($exists)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}