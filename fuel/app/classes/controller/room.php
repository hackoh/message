<?php 

class Controller_Room extends Controller
{
	public function action_messages($number, $sender = null)
	{

		if (Input::method() == 'DELETE')
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

			foreach ($room->messages as $message)
			{
				$message->delete();
			}

			return Response::forge(Format::forge(array('result' => 'OK'))->to_json(), 200);
		}
		else
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
				'is_ajax' => Input::is_ajax(),
				'messages' => $room->get_messages(Input::get('page', 1)),
			)));
		}
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