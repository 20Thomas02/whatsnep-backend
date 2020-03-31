<?php

class DatabaseConnector
{
    private static $pdo = null;
    
    function __construct($database, $username, $password) {
		if(!(is_string($database) && is_string($username) && is_string($password))) { 
			throw new Exception("De naam van de database, de username en het wachtwoord moeten strings zijn");
		}
        if($this->isConnected()) {
			$this->close();
        }
		self::$pdo = new PDO("mysql:host=mysql;dbname=$database;charset=UTF8", $username, $password);
    }
	
	public function isConnected() : bool {
		return (self::$pdo !== null);
	}
	
	public function close() {
		self::$pdo = null;
	}
	
    public function selectRows($query) : array {
		if(!is_string($query)){
			throw new Exception("De query moet een string zijn");
		}
		$prepared = self::$pdo->prepare($query);
        if($prepared->execute()) {
            return $prepared->fetchAll(PDO::FETCH_NAMED);
        } else {
            throw new Exception("De query kon niet worden uitgevoerd");
        }
    }
	
	//Alias only
	public function execute($query) : array {
		return $this->selectRows($query);
    }
	
	public function selectSingleRow($query) : array {
		$response = $this->selectRows($query);
		if(count($response) === 1) { return $response[0]; }
		return null;
    }
	
	public function selectValue($query) {
		$response = $this->selectRows($query);
		if(count($response) === 1) { return array_values($response[0])[0]; }
		return null;
	}
	
	//Alias only
	public function getSingleValue($query) : array {
		return $this->selectValue($query);
    }
}