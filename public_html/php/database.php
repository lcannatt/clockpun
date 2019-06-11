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

	/*
	 * Returns the id of the last inserted row.
	 *
	 * @return false on failure, lastid on success
	 */
	public function getLastInsertID() {
		$result = $this->db->preparedQuery("SELECT LAST_INSERT_ID() as id");
		return ($result===false)?false:$result[0]['id'];
	}
	public function getError(){
		return $this->db->getError();
	}

	//  SECURITY/AUTHENTICATION  //
	public function authenticateUser($username, $token) {
		$sql = "SELECT user_id, username, last_name, first_name, email, flags from user where user.user_id = (select user_id from user_devices where token=?) AND user.username=?";
		$results = $this->db->preparedQuerySingleRow($sql, "ss", array($token, $username));

		if($results===false) {
			$this->db->appendError("The user could not be authenticated.");
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
	public function getSecPull(){
		// returns security clearance for viewing user information other than their own.
		return $this->user_access['active'] && ($this->user_access['admin'] || $this->user_access['supreme'] || $this->user_access['hr'] || $this->user_access['review']);
	}
	public function getSecEditUser(){
		// returns security clearance for editing users
		return $this->user_access['active'] && ($this->user_access['admin'] || $this->user_access['supreme']);
	}
	public function getSecEntry(){
		//returns security clearance for time entry
		return $this->user_access['active'] && $this->user_access['entry'];
	}
	public function getSecReview(){
		//returns security clearance for time reviewing:
		return $this->user_access['active'] && ($this->user_access['review'] || $this->user_access['hr'] || $this->user_access['supreme']);
	}
	public function getSecHr(){
		return $this->user_access['active'] && $this->user_access['hr'];
	}

	//  GENERAL DATA GETTING  //
	public function getUsersForBrowse($start,$count,$sort){
		// finds users for management in user management browse mode, limited to those with lower access than you.
		// ATTENTION: $sort MUST NEVER COME FROM USER INPUT.
		$sql = "SELECT user_id,last_name, first_name, username, email, MOD(flags,2) as active FROM user WHERE flags<? ORDER BY $sort DESC LIMIT ?,?;";
		return $this->db->preparedQuery($sql, "iii", array($this->user_accessNum, $start, $count));
	}
	public function getUserGrants(){
		//returns access this user is cabable of granting to other users. Hr can grant review+entry, admin can grant Hr, supreme can grant admin.
		if($this->user_access['supreme']===1){
			return ['review','hr','admin','active'];
		} else if($this->user_access['admin']===1){
			return ['review','hr','active'];
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
	
	public function getIsValidManager($mgr){
		// checks if the given user ID is capable of being assigned manager duties. (active, review time access)
		$sql="SELECT user_id FROM user WHERE user_id=? AND user.flags%2=1 AND FLOOR(user.flags/4)%2=1";
		return $this->db->preparedQuery($sql,'i',array($mgr));
	}
	public function getUserDataFromToken($token,$new=false){
		//finds user record from a recovery token. $new being true will require that the user not have a username yet.
		$sql="SELECT user_id,first_name,last_name,email,username FROM user WHERE recovery_code=? AND username".($new?'':'!')."='';";
		return $this->db->preparedQuerySingleRow($sql,'s',array($token));
	}
	public function getUserNameTaken($username){
		//checks if a username is in use
		$sql="SELECT count(user_id) as cnt FROM user where username=?";
		$result=$this->db->preparedQuerySingleRow($sql,'s',array($username));
		return ($result?($result['cnt']>0):false);
	}
	public function getUser($userID){//returns user data
		$sql="SELECT user_id,username,first_name,last_name,flags,email,boss_id FROM user WHERE user_id=?";
		$result=$this->db->preparedQuerySingleRow($sql,'i',array($userID));
		if(!$result){
			return false;
		}
		$access=$this->intToBitArray($result['flags']);
		$result['flags']=$access;
		return $result;
	}
	public function getUserTimeForDay($date,$userID){//expects date in form YYYY-MM-DD as string
		$sql='SELECT time_id,TIME_FORMAT(TIME(time_start),\'%H:%i\') as start,TIME_FORMAT(TIME(time_end),\'%H:%i\') as end,cat_name,timestampdiff(MINUTE,time_start,time_end) as elapsed,comment FROM time_entered LEFT JOIN category_defs ON category=cat_id WHERE user_id=? AND DATE(?)=DATE(time_start) ORDER BY time_start ASC;';
		return $this->db->preparedQuery($sql,'is',array($userID,$date));
	}
	public function getTimeCategories(){
		$sql='SELECT cat_id,cat_name FROM category_defs';
		return $this->db->preparedQuery($sql,'',array());
	}
	public function getEmptyTime(){
		$sql='SELECT time_id from time_entered where user_id=? and time_start is NULL LIMIT 1';
		return $this->db->preparedQuerySingleRow($sql,'i',array($this->user_id));
	}
	public function getTimeData($id){
		$sql='SELECT time_id,time_start,time_end,category,comment FROM time_entered WHERE time_id=? AND user_id=?';
		return $this->db->preparedQuerySingleRow($sql,'ii',array($id,$this->user_id));
	}
	public function getOverviewData($date,$role='review'){//gets a weeks worth of overview data given an input date for user's suboordinates
		$sql='SELECT user.user_id
			,first_name
			,last_name
			,DAYOFWEEK(time_start) as weekday
			,cat_name
			,IF(time_start is null, 0, IF(time_end is null,timestampdiff(MINUTE,time_start,NOW()),timestampdiff(MINUTE,time_start,time_end))) as minutes
		FROM user
			left outer JOIN
				(SELECT 
					* 
				from time_entered 
					LEFT outer JOIN category_defs
					on time_entered.category=category_defs.cat_id 
				WHERE 
					((WEEK(time_start)=WEEK(?)
					OR WEEK(?)=1 AND WEEK(time_start)=53)
					OR time_start is null)) as a1
				on user.user_id=a1.user_id
		WHERE '.($role=='review'?'boss_id=?
			AND ':'').($role=='entry'?'user.user_id=?
			AND ':'').'(username!=\'\' AND flags%2=1)
		GROUP BY
			user_id,weekday,category
		ORDER BY 
			last_name ASC, first_name ASC, weekday ,category ASC';
		$result=$role=='hr'?$this->db->preparedQuery($sql,'ss',array($date,$date)):$this->db->preparedQuery($sql,'ssi',array($date,$date,$this->user_id));
		if(!$result){
			return false;
		}
		//format the return data to be usable
		$formatted=[];
		forEach($result as $row){
			$name=$row['first_name'].' '.$row['last_name'];
			$formatted[$name]['user_id']=$row['user_id'];
			$formatted[$name][$row['weekday']][$row['cat_name']]=$row['minutes'];
		}
		return $formatted;
		
	}
	public function getUserMinutesForWeek($timestamp){
		$sql='SELECT SUM(timestampdiff(MINUTE,time_start,time_end)) as total
			FROM time_entered
			WHERE (WEEK(time_start)=WEEK(?)	OR WEEK(?)=1 AND WEEK(time_start)=53) 
				AND DATE(?)!=DATE(time_start)
				AND user_id=?;';
		return $this->db->preparedQuerySingleRow($sql,'sssi',array($timestamp,$timestamp,$timestamp,$this->user_id));
	}

	///////////////////////////////
	//////////// PUTS /////////////
	///////////////////////////////

	public function putUserDevice($userID, $token) {
		$sql = "INSERT INTO user_devices (token, user_id, last_login) VALUES (?, ?, NOW());";
		return $this->db->preparedQuery($sql, "si", array($token, $userID));
	}

	public function putNewUser($lname,$fname,$email,$mgr,$newAccess,$token){
		//creates a new user. Username and password must be created by the user themself.
		$flags=$this->accessToInt($newAccess);
		$sql = "INSERT INTO user (username,password,last_name,first_name,email,boss_id,flags,recovery_code) VALUES ('','',?,?,?,?,?,?);";
		return $this->db->preparedQuery($sql,'sssiis',array($lname,$fname,$email,$mgr,$flags,$token));
		//username,password,last_name,first_name,email,boss_id,access
	}
	public function putRegisterUser($username,$pwd,$fname,$lname,$email,$token){
		//completes account creation process for new account:
		//get userID, update data, and delete the token out of the user row.
		
		//get the userID/confirm the token is valid
		$sql1="SELECT user_id from user where recovery_code=?;";
		$result=$this->db->preparedQuerySingleRow($sql1,'s',array($token));
		if(!$result){
			ErrorLog::internalError('Could not retrieve a user with the given token: '.$token);
			return false;
		}
		//Update the user info, delete token from user account.
		$userID=$result['user_id'];
		$sql2="UPDATE user SET username=?,password=?,first_name=?,last_name=?,email=?,recovery_code=NULL WHERE user_id=?;";
		$result=$this->db->preparedQuery($sql2,'sssssi',array($username,$pwd,$fname,$lname,$email,$userID));
		return $result?$userID:false;
	}
	public function putUpdateUser($userID,$lname,$fname,$email,$mgr,$newAccess){
		//update user account from admin edit
		$flags=$this->accessToInt($newAccess);
		$sql="UPDATE user SET first_name=?,last_name=?,email=?,boss_id=?,flags=? WHERE user_id=?";
		return $this->db->preparedQuery($sql,'sssiis',array($fname,$lname,$email,$mgr,$flags,$userID));

	}
	public function putResetPassword($userID,$token){
		$sql="UPDATE user SET recovery_code=?,password='' WHERE user_id=?";
		$result=$this->db->preparedQuery($sql,'si',array($token,$userID));
		return $result;
	}
	public function putNewPassword($token,$newPw){
		$sql="UPDATE user set recovery_code=null,password=? WHERE recovery_code=?";
		return $this->db->preparedQuery($sql,'ss',array($newPw,$token));
	}
	public function putEmptyTime(){
		//Checks for an existing empty time for user
		//If one is present, return ID, otherwise create a new one, return ID.
		$empty=$this->getEmptyTime();
		if($empty){
			return $empty;
		}else{
			$sql='INSERT INTO time_entered (user_id) VALUES (?)';
			$result=$this->db->preparedQuery($sql,'i',array($this->user_id));
			if($result){
				ErrorLog::logInfo(504,'recursing');
				return $this->putEmptyTime();
			}
		}
	}
	public function putClearTime($timeID){
		//clears out a time entry for recycling (user deleted)
		$sql='UPDATE time_entered SET time_start=null,time_end=null,category=null,comment=null WHERE time_id=? AND user_id=?';
		return $this->db->preparedQuery($sql,'ii',array($timeID,$this->user_id));
	}
	public function putUpdateTime($timeID,$start,$end,$category,$comment){
		$sql='UPDATE time_entered SET time_start=?,time_end=?,category=?,comment=? WHERE time_id=? AND user_id=?';
		return $this->db->preparedQuery($sql,'ssisii',array($start,$end,$category,$comment,$timeID,$this->user_id));
	}

}