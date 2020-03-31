<?php

header("Content-Type: application/json; charset=UTF-8");

//Types:
const GET_MESSAGES              = 0;
const ADD_MESSAGE               = 1;
const DELETE_MESSAGE            = 2;
const CHATS_REQUEST             = 3;
const ADD_USER_TO_CHAT          = 4;
const REMOVE_USER_FROM_CHAT     = 5;
const NEW_USER                  = 6;
const NEW_CHAT  				= 7;

require_once("DatabaseConnector.php");
include("whatsnep_object.php");

$databasename           =   "whatsnep_messenger";
$gebruikersnaam_db  =   "whatsnep";
$wachtwoord_db      =   "5Cb=;e";

$database = new DatabaseConnector($databasename, $gebruikersnaam_db, $wachtwoord_db);

$wno = new whatsnep_object(INPUT_GET);
$result = null;
$status = 0;
try {
	if($wno->validate(array($wno::type))) {
		switch ($wno->get($wno::type)) {
			case GET_MESSAGES:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::chat_id)) && $wno->checkChatMembers($wno::user_id)) {
					if($wno->validate(array($wno::datetime))) {
						$result = $database->execute("SELECT * FROM messages WHERE chat_id = '" . $wno->get($wno::chat_id) . "' AND datetime >= FROM_UNIXTIME('" . $wno->get($wno::datetime) . "') ORDER BY datetime");
					} else {
						$result = $database->execute("SELECT * FROM messages WHERE chat_id = '" . $wno->get($wno::chat_id) . "' ORDER BY datetime");
					}
				}
				break;
			case ADD_MESSAGE:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::chat_id, $wno::message_text)) && $wno->checkChatMembers($wno::user_id)) {
					$wno->generateMessageId();
					$result = $database->execute("INSERT INTO messages(id, chat_id, user_id, message_text) VALUES ('" . $wno->get($wno::message_id) . "', '" . $wno->get($wno::chat_id) . "', '" . $wno->get($wno::user_id) . "', '" . $wno->get($wno::message_text) . "')");
				}
				break;
			case DELETE_MESSAGE:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::message_id))) {
					$result = $database->execute("DELETE FROM messages WHERE id = '" . $wno->get($wno::message_id) . "'");
				}
				break;
			case CHATS_REQUEST:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::chat_id)) && $wno->checkChatMembers($wno::user_id)) {
					$result = $database->execute("SELECT chat_id FROM chat_members WHERE user_id = '" . $wno->get($wno::user_id) . "'");
					$temp = array();
					foreach ($result as $value) {
						$temp[] = $value["chat_id"];
					}
					$result = $temp;
				}
				break;
			case ADD_USER_TO_CHAT:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::chat_id, $wno::user_id_2)) && $wno->checkChatMembers($wno::user_id) && !($wno->checkChatMembers($wno::user_id_2))) {
					$result = $database->execute("INSERT INTO chat_members(chat_id, user_id) VALUES ('" . $wno->get($wno::chat_id) . "', '" . $wno->get($wno::user_id_2) . "')");
				}
				break;
			case REMOVE_USER_FROM_CHAT:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::chat_id, $wno::user_id_2)) && $wno->checkChatMembers($wno::user_id) && $wno->checkChatMembers($wno::user_id_2)) {
					$result = $database->execute("DELETE FROM chat_members WHERE chat_id = '" . $wno->get($wno::chat_id) . "' AND `user_id` = '" . $wno->get($wno::user_id_2) . "'");
				}
				break;
			case NEW_USER:
				if((!$wno->validate(array($wno::user_id))) && $wno->array[$wno::password_hash][1]) {
					$result = $database->execute("INSERT INTO users(id, password_hash) VALUES ('" . $wno->get($wno::user_id) . "', '" . $wno->get($wno::password_hash) . "')");
				}
				break;	
			case NEW_CHAT:
				if($wno->validate(array($wno::user_id, $wno::password_hash, $wno::chat_name))) {
					$wno->generateChatId();
					$result = $database->execute("INSERT INTO chats(id, chat_name) VALUES ('" . $wno->get($wno::chat_id) . "', '" . $wno->get($wno::chat_name) . "')");
					$result = $database->execute("INSERT INTO chat_members(chat_id, user_id) VALUES ('" . $wno->get($wno::chat_id) . "', '" . $wno->get($wno::user_id) . "')");
				}
				break;
		}
	}
} catch(Exception $e){
	$status = -1;
}

if ($result !== null) {
	$status = 0;
} else if ($status !== -1) {
	$status = $wno->generateStatus();
}

echo json_encode(array("result" => $result, "status" => $status));