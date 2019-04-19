<?php
require_once 'tpr_database.php';
require_once 'globals.php';
require_once 'logger.php';

class Database {
	//the singleton instance
	private static $instance = null;

	//the database instance
	private $db;

	//user details because we store these in DB for some odd reason
	private $access_flags=6;
	private $flag_names=['active','entry','review','hr','admin','supreme'];
	private $user_access;
	private $user_accessNum;
	private $user_firstName = "";
	private $user_lastName ="";
	private $user_email="";
	private $user_id;
	private $user_username;

	/*
	 * Constructs the Database object, we override it so we can
	 * make the constructor private, as this class is a singleton
	 * pattern.
	 */
	private function __construct() {
		$this->db = TPR_Database::getDB();

		//set up defaults for the database object
		$this->user_firstName = "";
		$this->user_lastName ="";
		$this->user_username = "";
		$this->user_id = -1;
		$this->user_access = [
			'active'=>0,
			'entry'=>0,
			'review'=>0,
			'hr'=>0,
			'admin'=>0,
			'supreme'=>0
		];
		// TRANSLATION: [Is Active, Logs, Time, Reviews Time, is HR, is Admin, Godmode]
	}

	//the singleton constructor
	public static function getDB() {
		//generic singleton patter, if we don't have an instance,
		//make one
		if(self::$instance == null) {
			self::$instance = new Database();
		}
		//return singleton instance
		return self::$instance;
	}
	//getters
	public function getUserAccess() {
		return $this->user_access;
	}

	public function getUserUsername() {
		return $this->user_username;
	}

	public function getUserFirstName() {
		return $this->user_firstName;
	}
	public function getUserLastName() {
		return $this->user_lastName;
	}

	public function getUserID() {
		return $this->user_id;
	}
	private function intToBitArray($accessInt){
		$access=[];
		for($i=0;$i<$this->access_flags;$i++){
			$access[$this->flag_names[$i]]=$accessInt%2;
			$accessInt=intDiv($accessInt,2);
		}
		return $access;
	}
	private function accessToInt($accessArray){
		$access=0;
		for($i=$this->access_flags-1;$i>=0;$i--){
			$access*=2;
			$access+=$accessArray[$this->flag_names[$i]];
		}
		return $access;
	}

	public function authenticateUser($username, $token) {
		$sql = "SELECT user_id, username, last_name, first_name, email, flags from user where user.user_id = (select user_id from user_devices where token=?) AND user.username=?";
		$results = $this->db->preparedQuerySingleRow($sql, "ss", array($token, $username));

		if($results===false) {
			$this->appendError("The user could not be authenticated.");
			ErrorLog::genericError('User cannot be authenticated.');
			return false;
		}

		$this->user_id = $results['user_id'];
		$this->user_username = $results['username'];
		$this->user_firstName = $results['first_name'];
		$this->user_lastName = $results['last_name'];
		$this->user_accessNum = $results['flags'];
		$this->user_access=$this->intToBitArray($results['flags']);
		return true;
	}
	public function getUserLogin($usr) {
		$sql = "SELECT user_id, username, password, flags FROM user WHERE username=?";
		$results = $this->db->preparedQuery($sql, "s", array($usr));
		return $results===false?false:(sizeof($results)!==1?false:$results[0]);
	}
	public function putUserDevice($user_id, $token) {
		$sql = "INSERT INTO user_devices (token, user_id, last_login) VALUES (?, ?, NOW());";
		return $this->db->preparedQuery($sql, "si", array($token, $user_id));
	}
	public function getUsersForBrowse($start,$count,$sort){
		// finds users for management in user management browse mode, limited to those with lower access than you (hr cant edit admin accounts)
		$sql = "SELECT user_id,last_name, first_name, username, email FROM user WHERE flags<? ORDER BY ? LIMIT ?,?;";
		return $this->db->preparedQuery($sql, "isii", array($this->user_accessNum, $sort, $start, $count));
	}
	public function getUserGrants(){
		//returns access this user is cabable of granting to other users. Hr can grant review+entry, admin can grant Hr, supreme can grant admin.
		if($this->user_access['supreme']===1){
			return ['review','hr','admin'];
		} else if($this->user_access['admin']===1){
			return ['review','hr'];
		} else if($this->user_access['hr']===1){
			return ['review'];
		} else {
			ErrorLog::LogInfo('300',$this->$user_username." tried to access user grants from $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			return false; //This function should not have been called in the first place
		}
	}
	public function getManagers(){
		//returns list of users with permission to review time, excluding account 1.
		if($this->user_accessNum<8){ //only hr+ need this info.
			ErrorLog::LogInfo('300',$this->$user_username." tried to access manager list from $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			return false;
		}
		$sql="SELECT user_id,CONCAT(first_name,' ',last_name) as name FROM user WHERE FLOOR(user.flags/4)%2=1 AND user_id != 1;";
		return $this->db->preparedQuery($sql,'',array());
	}
}