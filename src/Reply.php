<?php
/**
 * Reply Class Doc Comment
 *
 * PHP version 5
 *
 * @category PHP
 * @package  OpenChat
 * @author   Ankit Jain <ankitjain28may77@gmail.com>
 * @license  The MIT License (MIT)
 * @link     https://github.com/ankitjain28may/openchat
 */
namespace ChatApp;
require_once dirname(__DIR__).'/vendor/autoload.php';
use mysqli;
use Dotenv\Dotenv;
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();


/**
 * Store Message in the Database
 *
 * @category PHP
 * @package  OpenChat
 * @author   Ankit Jain <ankitjain28may77@gmail.com>
 * @license  The MIT License (MIT)
 * @link     https://github.com/ankitjain28may/openchat
 */
class Reply
{
    /*
    |--------------------------------------------------------------------------
    | Reply Class
    |--------------------------------------------------------------------------
    |
    | Store Message in the Database
    |
    */

    protected $connect;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connect = new mysqli(
            getenv('DB_HOST'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            getenv('DB_NAME')
        );

        date_default_timezone_set('Asia/Kolkata');
    }

    /**
     * Store Message in Db so as to send message to other members
     *
     * @param object $msg To store user id and massage
     *
     * @return string
     */
    public function replyTo($msg)
    {
        if (!empty($msg)) {
            // checks for the value send
            $userId = $msg->userId;
            // stores id of the person whom message is to be sent
            $receiverID = $msg->name;
            $identifier;

            if ($receiverID > $userId) {
                // geneate specific unique code to store messages
                $user1 = $userId;
                $user2 = $receiverID;
                $identifier = $userId.":".$receiverID;
            } else {
                $user1 = $receiverID;
                $user2 = $userId;
                $identifier = $receiverID.":".$userId;
            }

            // stores the message sent by the user.
            $reply = addslashes(trim($msg->reply));
            // current time
            $time = date("D d M Y H:i:s");
            // echo $time;
            // to sort the array on the basis of time
            $time_id = date("YmdHis");

            // the sender id must not be equal to current session id
            if ($reply != "" && $receiverID != $userId) {
                // check whether the receiver is authorized or registered
                $query = "SELECT * from login where login_id = '$receiverID'";

                $result = $this->connect->query($query);
                if ($result->num_rows > 0) {
                    // check whether he is sending message
                    // for the first time or he has sent messages before
                    $query = "SELECT * from total_message where
                                identifier = '$identifier'";
                    $result = $this->connect->query($query);
                    if ($result->num_rows > 0) {
                        // if he has sent messages before Update Total_Message Table
                        $query = "UPDATE total_message SET
                                total_messages = total_messages + 1,
                                time = '$time', unread = 1,
                                id = '$time_id' WHERE identifier = '$identifier'";

                        return $this->updateMessages(
                            $query, $identifier, $reply, $userId, $time
                        );

                    } else {
                        // if he sends message for the first time
                        // insert Details in Total_Message Table
                        $query = "INSERT into total_message values(
                            '$identifier', 1, '$user1', '$user2', 1,
                            '$time', '$time_id'
                        )";
                        return $this->updateMessages(
                            $query, $identifier, $reply, $userId, $time
                        );
                    }
                }
                // if he is unauthorized echo message is failed
                return "Invalid Authentication";
            }
        }
        return "Failed";
    }

    /**
     * To Store Message in DB Based on Identifier
     *
     * @param string $query      To store the query performed
     * @param string $identifier To store unique id
     * @param string $reply      To store message
     * @param string $userId     To store userid
     * @param string $time       To store time
     *
     * @return string
     */
    public function updateMessages($query, $identifier, $reply, $userId, $time)
    {
        if ($result = $this->connect->query($query)) {
            //insert message in db
            $query = "INSERT into messages values(
                '$identifier', '$reply', '$userId', '$time', null
            )";
            if ($this->connect->query($query)) {
                // if query is executed return true
                return "Messages is sent\n";
            }
            return "Message is failed\n";
        }
    }


}

