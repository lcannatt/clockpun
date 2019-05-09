<?php
require_once 'login_creds.php';

class TPR_Database {
	//the singleton instance
	private static $instance = null;

	//the database connection
	private $connection;

	//this is for diagnosing the cause of the original error in a function chain
	//simple with complex mechanics, successive errors will append to it
	//and the getter  will set it back to blank.
	private $error_message;

	/*
	 * Constructs the Database object, we override it so we can
	 * make the constructor private, as this class is a singleton
	 * pattern.
	 */
	private function __construct() {
		$this->pdocon = new PDO("mysql:host=".SERVER.";dbname=".DATABASE.";charset=utf8", USERNAME, PASSWORD);
		if(!$this->pdocon) {
				echo 'bad pdo';
		}
		//connect using creds from login_creds
		$this->connection = mysqli_connect(SERVER, USERNAME,
							PASSWORD, DATABASE);

		//log errors if we can't get a db connection
		if(!$this->connection) {
			$error_msg = "Failed to connect to MySQL: ("
				. $this->connection->connect_errno . ") "
				. $this->connection->connect_error;
			$this->appendError($error_msg);
		}

		//we have to do this for local instances because otherwise it won't let us delete posts
		$this->connection->query('SET foreign_key_checks = 0');
	}

	//the singleton constructor
	public static function getDB() {
		//generic singleton patter, if we don't have an instance,
		//make one
		if(self::$instance == null) {
			self::$instance = new TPR_Database();
		}
		//return singleton instance
		return self::$instance;
	}

	public function appendError($message) {
		$this->error_message .= $message . "\n";
	}

	public function getError() {
		$temp = $this->error_message;
		$this->error_message = "";
		return $temp;
	}
	

	public function preparedQuerySingleRow($sql, $argtypes="", $arguments=array(), $rettype=MYSQLI_ASSOC) {
		$result = $this->preparedQuery($sql, $argtypes, $arguments, $rettype);

		if(!$result) {
			$this->appendError("Query had an internal error");
			return false;
		} else if(sizeof($result)===0) {
			$this->appendError("At least a single row expected from query, yet none were returned");
			return false;
		}

		return $result[0];
	}

	//prepared query
	// public function preparedQuery($sql, $argtypes="", $arguments=array(), $rettype = MYSQLI_ASSOC) {
	// 	$mysqli = $this->connection;
	// 	if(!($stmt = $mysqli->prepare($sql))) {
	// 		$this->appendError("Prepared query failed to prepare");
	// 		return false;
	// 	}

	// 	if(sizeof($arguments)>0){
	// 		if(!$stmt->bind_param($argtypes, ...$arguments)) {
	// 			$this->appendError("Parameter binding on the query failed");
	// 			return false;
	// 		}
	// 	}

	// 	if(!$stmt->execute()) {
	// 		$this->appendError("Execution of the query failed");
	// 		return false;
	// 	}

    //             if(!($res = $stmt->get_result())) {
	// 		//this error code denotes a lack of applicable results,
	// 		//it means we need to return true rather than results
	// 		if($stmt->errno === 0) {
	// 			return true;
	// 		}
	// 		$this->appendError("Failed to retrieve query results");
	// 		return false;
	// 	}

	// 	return $res->fetch_all($rettype);
	// }
	public function pdoPreparedQuery($sql, $argtypes, $arguments, $rettype) {
		if($rettype == MYSQLI_ASSOC) {
				$rettype=PDO::FETCH_ASSOC;
		} else {
				$rettype=PDO::FETCH_NUM;
		}
		$stmt = $this->pdocon->prepare($sql);
		if(!$stmt) {
				echo 'wew';
		}
		//now we bind the values
		for($i = 0; $i < sizeof($arguments); $i++) {
				$type = $argtypes[$i]=="s"?PDO::PARAM_STR:($argtypes[$i]=="i"?PDO::PARAM_INT:PDO::PARAM_STR);
				if(!$stmt->bindValue($i+1, $arguments[$i], $type)) {
						echo 'cant bind';
				}
		}

		if(!$stmt->execute()) {
				echo 'cant exec';
		}
		if($stmt->columnCount()==0) {
				return true;
		}
		$vals = $stmt->fetchAll($rettype);
		return $vals;
	}

	public function preparedQuery($sql, $argtypes, $arguments, $rettype = MYSQLI_ASSOC) {
			return $this->pdoPreparedQuery($sql, $argtypes, $arguments, $rettype);

	}

}
