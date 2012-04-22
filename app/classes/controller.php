<?php

class Controller {
	
	public $postdata;	// POST-data

	public $errorMsg;	// error-messages for the views

	public $DB;			// Database Object

	public $collection; // Database Object

	public $memcache;	// Memcached Object

	public $session;	// Session Object

	public $config;		// Whole Config file config/config.php
	
	public $allowed = array();			// here saved the Methods allowed to invoke 

	public $authExceptions = array();	// here saved the Methods allowed to invoke without being logged in

	protected $twitter;					// the Services_Twitter Object

	public function __construct() {} 

	public function loadMethods($rest) {

		/**
		 *	the Method 'allowed()' can be set in every controller to only allow the 
		 *	Methods you really want to (private functions also do the job) - otherwise 
		 *	only the Methods of the Controller-Class itself and all interceptors 
		 *	(also __constructor()/__destructor()) are excluded by default.
		 *	
		 *	e.g.:
		 *	public function allowed() {
		 *		$this->allowed = array('index','login','logout','register');
		 *  }
		 */
		if(method_exists($this, 'allowed')) {

			$this->allowed();
		}

		/**
		 *	the Method 'authExceptions()' can be set in every controller to only allow the 
		 *	Methods you want to be accessed without the user being logged in.
		 *	otherwise the user will be redirected - if authExceptions() is not set then 
		 *	there's	no automatic implication of the User/Session-management whatsoever
		 *	
		 *	e.g.:
		 *	public function authExceptions() {	
		 *		$this->authExceptions = array('index','login','logout','register');
		 *	}
		 */
		if(method_exists($this, 'authExceptions')) {

			$this->authExceptions();
		}

		if(isset($rest[1])) {

			$class = strtolower($rest[0]);
			$method = strtolower($rest[1]);

			/** 
			 *	the list of Methods not allowed to be invoked 
			 *	(all of this 'Controller' class)
			 */
			if(method_exists($this, 'allowed')) {

				$t = get_class_methods($this);
				$forbiddenMethods = array_diff($t, $this->allowed);

			} else {

				$forbiddenMethods = get_class_methods('Controller');
			}

			/** 
			 *	filter for underscores as first character, omitting interceptor methods/contructor & also don't 
			 *	permit form the list $forbiddenMethods - and of course omits the methods not mentioned in 'allowed'
			 */
			if((strpos($method, '_') !== 0) && !(in_array($method, array_map('strtolower', $forbiddenMethods )))) {

				/** 
				 *	check if the string given by rest-url 
				 *	matches with one of the class-methods
				 */
				$classMethods = get_class_methods($this);

				/** 
				 *	check if the called method exists 
				 */				
				if(in_array($method, array_map('strtolower', $classMethods))) {

					/**	
					 *	Initialise the Session
					 */
					$this->initSession();

					/**
					 *	Instantiate a Memcache Object	
					 */
					$this->memcache = new Memcache();
					$this->memcache->addServer('localhost', 11211);// or die ('Connection to Memcached not possible!');

					/**
					 *	read the configuration
					 */
					// TODO memcache config
					$this->config = dbconf::getdefault();

					/**	
					 *	Call DB initialisation
					 */
					$this->initDB();

					/**
					 *	Set the collection on class'es name by default
					 */
					$this->collection = $this->DB->selectCollection($this->config['database'], $class);

					/**
					 *	when the user is not logged in and the method 'authExceptions' exists, 
					 *	all containing Path/Methodnames are permitted to access, 
					 *	all other method are going to be redirected to $path
					 */
					if(method_exists($this, 'authExceptions') 
						&& (!in_array($method, array_map('strtolower', $this->authExceptions))) 
						&& (!$this->isLoggedIn())) {

						// path where to redirect to
						$path = 'users/login';
						if(!($path === $GET['path'])) {
							header('Location: http://' . $_SERVER['HTTP_HOST'] . '/' . $path . '/');
							exit();
						}
					}

					/** 
					 *	Set the postdata array;
					 */
					if(isset($_POST['postdata'])) {

						$this->postdata = $_POST['postdata'];
					}

					/** 
					 *	remove the first 2 arrayelements (classname & methodname) in order to get potential variables 
					 *	(everything after classname/methodname/var1/var2/ ...)
					 */
					array_shift($rest);array_shift($rest);

					/** 
					 *	Call this Method within
					 */
					$errorMsg = $this->$method($rest);

					/** 
					 *	Set feasible Error messages
					 */
					$this->setErrorMsg($errorMsg);
					
					/** 
					 *	load the Classes' views
					 */
					{
						include('app/webroot/header.php');
						$this->loadViews($class, $method);
						include('app/webroot/footer.php');
					}

				} else {
					// Method not found
					header('Location: http://' . $_SERVER['HTTP_HOST'] . '/');
				}
			} else {
				// not PERMITTED
				header('Location: http://' . $_SERVER['HTTP_HOST'] . '/');
			}
		
		} else {
			header('Location: http://' . $_SERVER['HTTP_HOST'] . '/');
		}
	}

	/**
	 *	Simply load the view files for the given Methods
	 */
	private function loadViews($class = NULL, $view = NULL) {

		$path = 'app/views/' . $class . '/' . $view . '.php';
		if(file_exists($path)) {

			require($path);
		} else {
		}
	}

/**
 *	Database
 *	########
 */
	/**
	 *	Initialize mongoDB
	 */
	private function initDB() {

		try {

			$config = $this->config;

			/** MongoAuth Class */
			require('app/classes/Mongo/Auth.php');
			$this->DB = new MongoAuth($config['host'] . ':' . $config['port'], 1, $config['persistent']);
			$this->DB->login($config['database'], $config['username'], $config['password']); 

		} catch (Exception $e) {

			dumper($e->getMessage());
			return FALSE;
		}
		return TRUE;
	}

	public function getConfig() {

		return $this->config;
	}

/**
 *	Error-messages
 *	##############
 */
	/**
	 *	Error message getter/setter
	 */
	public function setErrorMsg($err = NULL) {

		if($err) {
			$this->errorMsg = $err;
		}
	}

	public function getErrorMsg() {

		return $this->errorMsg;
	}

/**
 *	Model-like validation methods
 *	#############################
 */
	
	/**
	 *	Alphanumeric validation method
	 *	@param		String
	 *	@return		Boolean
	 */
	protected function alphaNum($str) {
		
		echo '<br />' . $str . '<br />';

		if (ctype_alnum($str)) {
			echo "The string $str consists of all letters or digits.\n";
		} else {
			echo "The string $str does not consist of all letters or digits.\n";
		}
	}


	/**
	 *	Alphanumeric validation method
	 *
	 *	@param		String
	 *	@return		Boolean
	 */
	protected function validEmail($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) {
		   $isValid = false;
		} else {
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
			   // local part length exceeded
			   $isValid = false;
			} else if ($domainLen < 1 || $domainLen > 255) {
			   // domain part length exceeded
			   $isValid = false;
			} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
			   // local part starts or ends with '.'
			   $isValid = false;
			} else if (preg_match('/\\.\\./', $local)) {
			   // local part has two consecutive dots
			   $isValid = false;
			} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
			   // character not valid in domain part
			   $isValid = false;
			} else if (preg_match('/\\.\\./', $domain)) {
			   // domain part has two consecutive dots
			   $isValid = false;
			} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
				 str_replace("\\\\","",$local))) {
			// character not valid in local part unless local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
			   $isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
	}


	/**
	 *	Check if passwords have minlength of 7 characters & and are identical
	 *
	 *	@params		String		$p1		password1
	 *	@params		String		$p2		password2
	 *	@return		Boolean
	 */
	protected function validPasswords($p1, $p2) {

		if(strlen($p1) >= 6 && ($p1 === $p2)) {
			return true;
		}
		return false;
	}

	/**
	 *	Simple password hashing 
	 *
	 *	@params		String		$pass		password
	 *	@return		Boolean
	 */
	protected function hashPassword($pass) {

		$pass = sha1($pass . $GLOBALS['salt']);
		return $pass;
	}

	/**
	 *	Generates a unique token for pesistent cookie login
	 *
	 *	@params		String		$user		useremail
	 */		
	protected function generateToken($user) {

		$t= sha1(uniqid(mt_rand(), TRUE));
		$t2 = sha1($GLOBALS['salt'] . $user . $t . $GLOBALS['salt2']);
		$token = hash('sha512', $t2);

		return $token;
	}


/**
 *	SESSION METHODS
 *	###############
 */
	/**
	 *	Initialise the Session
	 */
	protected function initSession() {

		$this->session = Session::getInstance(); //Starten der Session
		//$this->session->test = "TESTSESSION";
	}

	/**
	 *	Just return if the User is Logged in (TRUE)
	 *	also autologin if the cookie 'logintoken' is set and valid
	 *	@return		BOOLEAN
	 */
	protected function isLoggedIn() {

		if($this->session->loggedin) {

			return TRUE;
		} else if(!empty($_COOKIE['logintoken'])) {

			$token = $_COOKIE['logintoken'];

			// search for the given user in 'usertokens'
			$query = array('logintokens' => array('$elemMatch' => array('token' => $token)));
			try {

				// select usertokens collection and save there the token
				$this->collection = $this->DB->selectCollection($this->config['database'], 'usertokens');

				$found = $this->collection->findOne(
					$query,
					array('logintokens', 'userId')
				);

			} catch (Exception $e) {
				dumper($e->getMessage());
				return FALSE;
			}

			// when no such token found in DB
			if(empty($found)) {

				return FALSE;
			}

			$ip = $_SERVER['REMOTE_ADDR'];
			$useragent = $_SERVER['HTTP_USER_AGENT'];

			// filter the returned set for matching ip, useragent and token
			foreach($found['logintokens'] as $f=>$x) {

				if(($x['token'] === $token) && ($x['ip'] === $ip) && ($x['useragent'] === $useragent)) {

					// renew the session
					$this->session->renew();

					// Set Session-variables at once
					$this->session->userID = $found['userId'];
					$this->session->logintoken = $token;
					$this->session->loggedin = TRUE;

					// retrieve the rest of the userdata
					$this->collection = $this->DB->selectCollection($this->config['database'], 'users');
					$query = array('_id' => new MongoId($this->session->userID));
					$userdata = $this->collection->findOne($query, array('email'));

					$this->session->email = $userdata['email'];

					return TRUE;
				}
			}
			return FALSE;
		}
		return FALSE;
	}

	/**
	 *	Simply return the logintoken (if exists)
	 */
	protected function isLoginToken() {

		if (isset($_COOKIE['logintoken'])) {
			return $_COOKIE['logintoken'];
		} else {
			return FALSE;
		}
	}

}
