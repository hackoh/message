<?php

namespace Fuel\Tasks;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface
{
    protected $clients;
    protected $rooms = array();
 
    public function __construct() {
        // $this->clients = new \SplObjectStorage;
    }
 
    public function onOpen(ConnectionInterface $conn) {
        // $this->clients->attach($conn);
    }
 
    public function onMessage(ConnectionInterface $from, $msg) {

        // ob_start();
        // var_dump($this->rooms);
        // \Cli::write(ob_get_clean());

    	\Cli::write($msg);

    	$data = json_decode($msg, true);

        if ($data['action'] == 'join')
        {
            if ( ! isset($this->rooms[$data['number']]))
            {
                $this->rooms[$data['number']] = new \SplObjectStorage;
            }

            $this->rooms[$data['number']]->attach($from);

            foreach ($this->rooms[$data['number']] as $client) {
                $client->send($msg);
            }

            return;
        }

        \Model_Room::clear_cached_objects();

    	$room = \Model_Room::find_by_number($data['number']);

    	if ( ! $room)
    	{
    		foreach ($this->clients as $client) {
	        	$client->send('{result:0}');
	        }
    	}

    	$sent = false;

        $clients = isset($this->rooms[$data['number']]) ? $this->rooms[$data['number']] : null;

        if ( ! $clients)
        {
            return;
        }

    	foreach ($clients as $client) {
    		if ($from != $client)
    		{
				$sent = true;
    		}
        	$client->send($msg);
        }

        \Cli::write('sent = '.($sent ? 'true' : 'false'));

    	if ($data['text'] && ($data['action'] == 'send' || $data['action'] == 'image'))
        {
	    	$message = \Model_Message::forge(array(
	    		'text' => \Crypt::encode($data['text']),
	    		'sender' => $data['sender'],
	    		'room_id' => $room->id,
                'action' => $data['action'],
	    	));

	    	$message->save();

            $email = $data['sender'] == 1 ? 'email_2' : 'email_1';
            $to_sender = $data['sender'] == 1 ? 2 : 1;

            if (($address = $room->$email) && $sent === false && $data['action'] == 'send')
            {
                $email = \Email::forge('message');
                $email->from('no-repry@oheya.io', 'oheya.io');

                $email->to($address);

                $body = \View::forge('email/message', array(
                    'text' => $data['text'],
                    'url' => 'http://oheya.io/rooms/'.$room->number.'/'.$to_sender,
                ));
                $email->body($body);

                try
                {
                   \Background::forge(array($email, 'send'))->run();
                    \Cli::write('sent to '.$address);
                }
                catch (\Email\EmailValidationFailedException $e)
                {
                    \Log::warning('EmailValidationFailedException occured email:'.$address);
                }
                catch (\Email\EmailSendingFailedException $e)
                {
                    \Log::warning('EmailSendingFailedException occured email:'.$address);
                }
            }
	    }

       
    }
 
    public function onClose(ConnectionInterface $conn) {
        foreach ($this->rooms as $clients)
        {
            $clients->detach($conn);
        }
    }
 
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}

class Message
{
	public static function run()
	{
	    $server = IoServer::factory(new WsServer(new Chat), 9000);
	    $server->run();
	}

    public static function crypt()
    {
        foreach (\Model_Message::find('all') as $message)
        {
            $message->text = \Crypt::encode($message->text);
            $message->save();
        }
    }

    public static function test()
    {
        $email = \Email::forge('message');
        $email->from('no-repry@oheya.io', 'oheya.io');

        $email->to('hackoh@softbank.ne.jp');

        $body = \View::forge('email/message', array(
            'text' => 'test',
            'url' => '',
        ));
        $email->body($body);

        try
        {
           \Background::forge(array($email, 'send'))->run();
        }
        catch (\Email\EmailValidationFailedException $e)
        {
            \Log::warning('EmailValidationFailedException occured email:'.$address);
        }
        catch (\Email\EmailSendingFailedException $e)
        {
            \Log::warning('EmailSendingFailedException occured email:'.$address);
        }
    }
}
