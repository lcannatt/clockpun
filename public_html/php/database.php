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
	private static $access_flags=6;
	private static $flag_names=['active','entry','review','hr','admin','supreme'];
	private $user_access;
	private $user_accessNum;
	private $user_firstName = "";
	private $user_lastName ="";
	private $user_email="";
	private $user_id;
	private $user_username;
	private $logged_in=false;

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
	public function getLoggedIn(){
		return $this->logged_in;
	}
	//statics:
	private static function intToBitArray($accessInt){
		$access=[];
		for($i=0;$i<Database::$access_flags;$i++){
			$access[Database::$flag_names[$i]]=$accessInt%2;
			$accessInt=intDiv($accessInt,2);
		}
		return $access;
	}
	private static function accessToInt($accessArray){
		$access=0;
		for($i=Database::$access_flags-1;$i>=0;$i--){
			$access*=2;
			$access+=$accessArray[Database::$flag_names[$i]];
		}
		return $access;
	}
	public static function getEmptyPermissions(){
		return ['active'=>0,
			'entry'=>0,
			'review'=>0,
			'hr'=>0,
			'admin'=>0,
			'supreme'=>0];
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
		$this->logged_in=true;
		return true;
	}
	public function getUserLogin($usr) {
		$sql = "SELECT user_id, username, password, flags FROM user WHERE username=?";
		$results = $this->db->preparedQuery($sql, "s", array($usr));
		return $results===false?false:(sizeof($results)!==1?false:$results[0]);
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
		//returns list of active users with permission to review time, excluding account 1.
		if($this->user_accessNum<8){ //only hr+ need this info.
			ErrorLog::LogInfo('300',$this->$user_username." tried to access manager list from $_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			return false;
		}
		$sql="SELECT user_id,CONCAT(first_name,' ',last_name) as name FROM user WHERE user.flags%2=1 AND FLOOR(user.flags/4)%2=1 AND user_id != 1;";
		return $this->db->preparedQuery($sql,'',array());
	}

	public function getCanCreateUser(){
		// returns whether logged in user is authorized to edit/create user accounts.
		return $this->user_access['active'] && ($this->user_access['hr'] || $this->user_access['admin'] || $this->user_access['supreme']);
	}
	public function getIsValidManager($mgr){
		// checks if the given user ID is capable of being assigned manager duties. (active, review time access)
		$sql="SELECT user_id FROM user WHERE user_id=? AND user.flags%2=1 AND FLOOR(user.flags/4)%2=1";
		return $this->db->preparedQuery($sql,'i',array($mgr));
	}
	public function getUserDataFromToken($token,$new=false){
		//finds user record from a recovery token. $new being true will require that the user not have a username yet.
		$sql="SELECT user_id,first_name,last_name,email FROM user WHERE recovery_code=? AND username".($new?'':'!')."='';";
		return $this->db->preparedQuerySingleRow($sql,'s',array($token));
	}

	///////////////////////////////
	//////////// PUTS /////////////
	///////////////////////////////

	public function putUserDevice($user_id, $token) {
		$sql = "INSERT INTO user_devices (token, user_id, last_login) VALUES (?, ?, NOW());";
		return $this->db->preparedQuery($sql, "si", array($token, $user_id));
	}

	public function putNewUser($lname,$fname,$email,$mgr,$newAccess,$token){
		//creates a new user. Username and password must be created by the user themself.
		$flags=$this->accessToInt($newAccess);
		$sql = "INSERT INTO user (username,password,last_name,first_name,email,boss_id,flags,recovery_code) VALUES ('','',?,?,?,?,?,?);";
		return $this->db->preparedQuery($sql,'sssiis',array($lname,$fname,$email,$mgr,$flags,$token));
		//username,password,last_name,first_name,email,boss_id,access
	}

}