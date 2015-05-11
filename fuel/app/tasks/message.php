<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Tasks;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

/**
 * Robot example task
 *
 * Ruthlessly stolen from the beareded Canadian sexy symbol:
 *
 *		Derek Allard: http://derekallard.com/
 *
 * @package		Fuel
 * @version		1.0
 * @author		Phil Sturgeon
 */

class Chat implements MessageComponentInterface
{
    protected $clients;
 
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
 
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }
 
    public function onMessage(ConnectionInterface $from, $msg) {
    	\Cli::write($msg);

    	$data = json_decode($msg, true);

    	$room = \Model_Room::find_by_number($data['number']);

    	if ( ! $room)
    	{
    		foreach ($this->clients as $client) {
	        	$client->send('{result:0}');
	        }
    	}

    	$sent = false;

    	foreach ($this->clients as $client) {
    		if ($from != $client)
    		{
				$sent = true;
    		}
        	$client->send($msg);
        }

        \Cli::write('sent = '.($sent ? 'true' : 'false'));

    	if ($data['text']) {
	    	$message = \Model_Message::forge(array(
	    		'text' => \Crypt::encode($data['text']),
	    		'sender' => $data['sender'],
	    		'room_id' => $room->id
	    	));

	    	$message->save();

            $email = $data['sender'] == 1 ? 'email_2' : 'email_1';
            $to_sender = $data['sender'] == 1 ? 2 : 1;

            if (($address = $room->$email) && $sent === false)
            {
                $email = \Email::forge('message');
                $email->from('no-repry@impv.co.jp', 'm');

                $email->to($address);

                $email->subject('メッセージを受信しました');
                $body = \View::forge('email/message', array(
                    'text' => $data['text'],
                    'url' => 'http://m.impv.net/rooms/'.$room->number.'/'.$to_sender,
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

       
    }
 
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
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
}
