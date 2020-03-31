<?php
class whatsnep_object {
	public $array = array();
	private $database;

	public const type = 0;
	public const user_id = 1;
	public const user_id_2 = 2;
	public const chat_id = 3;
	public const message_id = 4;
	public const password_hash = 5;
	public const message_text = 6;
	public const datetime = 7;
	public const chat_name = 8;


  	function __construct($input_get) {
	    $this->array[self::type] = array((int)filter_input(INPUT_GET, 'type', FILTER_SANITIZE_NUMBER_INT), false, false);
	    $this->array[self::user_id] = array((int)filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT), false, false);
	    $this->array[self::user_id_2] = array((int)filter_input(INPUT_GET, 'user_id_2', FILTER_SANITIZE_NUMBER_INT), false, false);
	    $this->array[self::chat_id] = array((int)filter_input(INPUT_GET, 'chat_id', FILTER_SANITIZE_NUMBER_INT), false, false);
	    $this->array[self::message_id] = array((int)filter_input(INPUT_GET, 'message_id', FILTER_SANITIZE_NUMBER_INT), false, false);
	    $this->array[self::password_hash] = array(filter_input(INPUT_GET, 'password_hash', FILTER_SANITIZE_STRING), false, false);
	    $this->array[self::message_text] = array(filter_input(INPUT_GET, 'message_text', FILTER_SANITIZE_STRING), false, false);
	    $this->array[self::datetime] = array((int)filter_input(INPUT_GET, 'datetime', FILTER_SANITIZE_NUMBER_INT), false, false);
	    $this->array[self::chat_name] = array(filter_input(INPUT_GET, 'chat_name', FILTER_SANITIZE_STRING), false, false);
	    $this->connectDataB();
	    $this->updateMissing();
  	}

  	function connectDataB() {
  		//verbind met de databases
		require_once("DatabaseConnector.php");

		$databasename       =   "whatsnep_messenger";
		$gebruikersnaam_db  =   "whatsnep";
		$wachtwoord_db      =   "5Cb=;e";

		$this->database = new DatabaseConnector($databasename, $gebruikersnaam_db, $wachtwoord_db);
  	}

  	function updateMissing() {
  		foreach ($this->array as $key => $value) {
  			if($value[0] !== null) {
    			$this->array[$key][1] = true;
  			}
  		}
  	}

  	function checkExistance($variable) {
  		if($this->array[$variable][1]) {
	  		if($variable == self::type) {
	  			if($this->array[$variable][0] >= self::type && $this->array[$variable][0] <= self::chat_name) {
	            	$this->array[$variable][2] = true;
	        	}
	  		} else if($variable >= self::user_id && $variable <= self::message_id) {
	  			if($variable === self::user_id) {
	  				$result = $this->database->execute("SELECT * FROM users WHERE id = '" . $this->array[self::user_id][0] . "'");
	  			} else if($variable === self::user_id_2) {
	  				$result = $this->database->execute("SELECT * FROM users WHERE id = '" . $this->array[self::user_id_2][0] . "'");
	  			} else if($variable === self::chat_id) {
	  				$result = $this->database->execute("SELECT * FROM chats WHERE id = " . $this->array[self::chat_id][0]);
	  			} else if($variable === self::message_id) {
	  				$result = $this->database->execute("SELECT * FROM messages WHERE id = " . $this->array[self::message_id][0]);
	  			}
	  			if ($result != null) {
	            	$this->array[$variable][2] = true;
	        	}
	        } else if($variable === self::password_hash) {
	  			$result = $this->database->execute("SELECT * FROM users WHERE id = '" . $this->array[self::user_id][0] . "' AND password_hash = '" . $this->array[self::password_hash][0] . "'");
	  			if ($result != null) {
	        		$this->array[$variable][2] = true;
	    		}
	  		} else if($variable >= self::message_text && $variable <= self::chat_name) {
	        	$this->array[$variable][2] = true;
	        } 
	  	}
  	}

  	function checkChatMembers($variable) {
	  	$result = $this->database->execute("SELECT * FROM chat_members WHERE chat_id = '" . $this->array[self::chat_id][0] . "' AND user_id = '" . $this->array[$variable][0] . "'");
	  	if ($result != null) {
	        return true;
	    }
	    return false;
  	}

  	function generateMessageId() {
        $this->array[self::message_id][0] = rand(0, 2147483647);
        $this->array[self::message_id][1] = true;
        $this->checkExistance(self::message_id);
        while ($this->array[self::message_id][2]) {
            $this->array[self::message_id][0] = rand(0, 2147483647);
            $this->array[self::message_id][2] = false;
        	$this->checkExistance(self::message_id);
        }
  	}

  	function generateChatId() {
  		$this->array[self::chat_id][0] = rand(0, 2147483647);
        $this->array[self::chat_id][1] = true;
        $this->checkExistance(self::chat_id);
        while ($this->array[self::chat_id][2]) {
            $this->array[self::chat_id][0] = rand(0, 2147483647);
            $this->array[self::chat_id][2] = false;
        	$this->checkExistance(self::chat_id);
        }
  	}

  	function validate($variables) {
  		$correct = true;
  		foreach ($variables as $value) {
  			$this->checkExistance($value);
  			if(!$this->array[$value][2]) {
  				$correct = false;
  			}
  		}
  		return $correct;
  	}

  	function get($variable) {
  		return strval($this->array[$variable][0]);
  	}

  	function generateStatus() {
  		$status = 0;
  		foreach ($this->array as $key => $value) {
			$starting_bit = 2*$key;
			if(!$value[1]) {
			    $status += 2**$starting_bit;
			}
			if(!$value[2]) {
			    $status += 2**($starting_bit+1);
		    }
  		}
  		return $status;
  	}
}