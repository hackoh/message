<?php 

class Controller_Image extends Controller
{
	public function action_index($number)
	{
		$room = Model_Room::find_by_number($number);

		if ( ! $room)
		{
			throw new HttpNotFoundException();
		}

		if (Input::method() == 'POST')
		{
			$config = array(
				'path' => DOCROOT.DS.'files'.DS.'images',
				'randomize' => true,
				'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
				'max_size' => 1024 * 1024 * 10
			);
			Upload::process($config);

			if (Upload::is_valid())
			{
				Upload::save();
	 
				foreach (Upload::get_files() as $file)
				{
					$image = Model_Image::forge($file);
					$image->room_id = $number;
					$image->save();

					$i = Image::load($file['saved_to'].$file['saved_as']);
					$i->resize(null, 160);
					$thumb_path = DOCROOT.DS.'files'.DS.'thumbs';
					$i->save($thumb_path.DS.$file['saved_as']);
					chmod($thumb_path.DS.$file['saved_as'], 0777);
					chmod($file['saved_to'].$file['saved_as'], 0777);

					$image->thumb = Uri::create('files/thumbs/'.$file['saved_as']);

					return Response::forge(Format::forge($image)->to_json());
				}
			}

			Response::forge(null, 400);
		}
	}
}