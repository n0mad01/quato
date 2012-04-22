<?php

include('app/classes/controller.php');

class Users extends Controller {

/**
 *	Method 'allowed' stores all the Methods of 
 *	this Class which are allowed to be accessed through REST
 */
	//public function allowed() {

		//$this->allowed = array('test','index','login','logout','register');
	//}

/**
 *	Method 'authExceptions' stores the Methods where the user doesn't need to 
 *	be logged in to access through REST
 *	(Automaticly redirection to login if not in list)
 */
	public function authExceptions() {

		$this->authExceptions = array('index','login','logout','register');
	}

	/**
	 *	default method
	 */
	public function index() {

		//dumper($this->isLoginToken());
        //dumper($_SESSION);
        //dumper($this->memcache->get($this->session->email));

		//dumper($_SERVER);
		if($this->isLoggedIn()) {

			//echo $this->isLoggedIn();
			//dumper($_SESSION);
			echo 'LOGGED IN!';
		} else {

			//echo $this->isLoggedIn();
			echo 'NOT LOGGED IN!';
		}
	}
	
/**
 *	User login 
 */
	public function login() {

		if(isset($this->postdata)) {

			$email = $this->postdata['email'];
			$fromDB = $this->getUserByEmail($email);

			if(empty($fromDB)) {

				$ret['invalid']['notfound'] = _('The user/password combination is wrong!');
				return $ret;
			} else {

				$pass = $this->hashPassword($this->postdata['password']);
				// login correct
				if($pass === $fromDB['password']) {

					$this->session->email = $email;
					$this->session->loggedin = TRUE;
					$this->session->userID = $fromDB['_id']->{'$id'};

					if($this->postdata['stayLoggedIn']) {

						// first, generate new token
						$token = $this->generateToken($email);

						// Save token in SESSION
						$this->session->token = $token;
						//dumper($_SESSION);

						$ip = $_SERVER['REMOTE_ADDR'];
						$useragent = $_SERVER['HTTP_USER_AGENT'];
						$userid = $fromDB['_id']->{'$id'};
						$created = new MongoDate();
						//$refreshed = new MongoDate();

						try {
							// select usertokens collection and save there the token
							$this->collection = $this->DB->selectCollection($this->config['database'], 'usertokens');

							// Search if user has already token-credentials
							$found = $this->collection->findOne(
								array('userId' => $userid,
									'logintokens' => array(
										'$elemMatch' => array(
											'ip' => $ip,
											'useragent' => $useragent
										)
									)
								), array('_ip')
							);

							// when user already has credentials update token and 'refreshed'-date
							if($found) {
								$this->collection->update(
									array('userId' => $userid, 
										'logintokens' => array(
											'$elemMatch' => array(
												'ip' => $ip,
												'useragent' => $useragent
											)
										)
									),
									array('$set' => array(
										'logintokens.$.token' => $token,
										'logintokens.$.refreshed' => $created
										)
									)
							);
							// otherwise save whole dataset new (other ip adress and/or useragent)
							} else {
								$this->collection->update(
									array('userId' => $userid), 
									array('$addToSet' => array(
										'logintokens' => array(
											'ip' => $ip,
											'useragent' => $useragent,
											'token' => $token,
											'created' => $created,
											'refreshed'=> $created
										)
									)
								), true);
							}

						} catch (Exception $e) {

							dumper($e->getMessage());
							return FALSE;
						}
						//$this->collection->update(array('email' => $email), array('$addToSet' => array('logintokens' => array('ip'=>$ip, 'useragent'=>$useragent, 'token'=>$token))));

						$this->session->logintoken = $token;
						setcookie("logintoken", $token, time()+3600*24*30, '/', $_SERVER['HTTP_HOST']);
					}

                    // redirect
					header('Location: http://' . $_SERVER['HTTP_HOST'] . '/users/index/');

				} else {

					echo "PASS NOT OK!";
				}

			}
		}
	}

/**
 *	User logout 
 */
	public function logout() {

		// destroy $_SESSION 
		$this->session->destroy();

		// unset cookies
		setcookie("logintoken", '', time()+3600*24*365, '/', $_SERVER['HTTP_HOST']);
		unset($_COOKIE['logintoken']);

		header('Location: http://' . $_SERVER['HTTP_HOST'] . '/users/index/');
	}

	/**
	 *	New User registration
	 *	@return		Boolean
	 */
	public function register() {

		$valid = FALSE;
		$ret = array(); // returning errormessage
		if(isset($this->postdata)) {

			if($this->validEmail($this->postdata['email'])) {

				$valid = TRUE;
				//echo 'EMAIL VALID!';
			} else {
				$ret['invalid']['email'] = _('Email address you\'ve entered is invalid, please try again!');
				$valid = FALSE;
				//echo 'EMAIL NOT VALID!';
			}

			if($this->validPasswords($this->postdata['password'], $this->postdata['password2'])) {

				//echo 'PASSWORDS OK!';
			} else {
				$ret['invalid']['passwords'] = _('The Passwords must be at least 6 characters long and both the same!');
				$valid = FALSE;
				//echo 'PASSWORDS NOT OK!';
			}

			if($valid) {
				$ret = $this->saveUser($this->postdata);
			}
		}
		return $ret;
	}

/**
 *	save the freshly registered user
 *
 *	@param	Array()
 */
	private function saveUser($user = NULL) {

		$fromDB = $this->getUserByEmail($user['email']);
		if(empty($fromDB)) {

			// set vars to save
			unset($user['password2']);
			$user['password'] = $this->hashPassword($user['password']);
			$user['lastlogin'] = new MongoDate();
			$user['created'] = new MongoDate();


			// save
			try {

				// saving in 'users'
				if($this->collection->insert($user)) {

					$userTokens['email'] = $user['email'];
					$userTokens['userId'] = $user['_id']->{'$id'};
					$userTokens['logintokens'] = array();

					// saving in 'usertokens'
					$this->collection = $this->DB->selectCollection($this->config['database'], 'usertokens');
					$this->collection->insert($userTokens);
				}
				
			} catch (Exception $e) {

				dumper($e->getMessage());
				return FALSE;
			}
			// redirect
			header('Location: http://' . $_SERVER['HTTP_HOST'] . '/users/login/');
			return TRUE;
		} else {

			$ret['invalid']['email'] = _('This Email address is already used!');
			return $ret;
		}
	}

/**
 *	lookup if email address already exists in db
 */
	private function getUserByEmail($email) {

		$query = array('email' => $email);
		try {
			$found = $this->collection->findOne($query, array('_id', 'password', 'logintokens'));
		} catch (Exception $e) {
			dumper($e->getMessage());
			return FALSE;
		}
		return $found;
	}

}
