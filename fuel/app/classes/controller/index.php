<?php 

class Controller_Index extends Controller
{
	public function action_index()
	{
		return Response::forge(View::forge('index/index'));
	}
	public function action_login()
	{
		$room = Model_Room::find_by_number(Input::post('number'));
		if ($room)
		{
			if ($room->passcode == Input::post('passcode'))
			{
				Session::set('room', $room->number);
				return Response::forge(Format::forge(array('result' => 1))->to_json());
			}
			else
			{
				return Response::forge(null, 400);
			}
		}
		else
		{
			$val = Model_Room::validate();
			if ($val->run())
			{
				$room = Model_Room::forge(Input::post());
				$room->save();
				Session::set('room', $room->number);
				return Response::forge(Format::forge(array('result' => 1))->to_json());
			}
			else
			{
				return Response::forge(null, 400);
			}
		}
	}
}