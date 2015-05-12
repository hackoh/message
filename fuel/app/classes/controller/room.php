<?php 

class Controller_Room extends Controller
{
	public function action_messages($number, $sender)
	{

		$room = Model_Room::find_by_number($number);

		if ( ! $room)
		{
			throw new HttpNotFoundException();
		}

		if ($room->number != Session::get('room'))
		{
			Response::redirect('/');
		}
		

		return Response::forge(View::forge('room/messages', array(
			'number' => $number,
			'sender' => $sender,
			'room' => $room,
		)));
	}

	public function action_email($number, $sender)
	{

		$room = Model_Room::find_by_number($number);

		if ( ! $room)
		{
			throw new HttpNotFoundException();
		}

		if ($room->number != Session::get('room'))
		{
			return Response::forge(null, 400);
		}

		$room->{'email_'.$sender} = Input::post('email');
		$room->save();
		return Response::forge(Format::forge(array('result' => 'OK'))->to_json(), 200);
	}
}