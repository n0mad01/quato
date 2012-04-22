<?php

final class Session {

	private static $instance = NULL;
	
	private static  $sessArrayKey = "__sessiondata";
	
	/**
	 *	Init Session
	 */
	private function __construct() {

		session_start();
		if(!isset($_SESSION[Session::$sessArrayKey])) {

			$_SESSION[Session::$sessArrayKey] = array();
		}
	}
	
	/**
	 * @access private
	 *
	 * Kopierkonstruktor verbieten
	 */
	private function __clone() {}
	
	/**
	 * @access private
	 * @return Session
	 *
	 * Erzeugt die Instanz von Session
	 */
	public static function getInstance() {

		if(self::$instance === NULL) {

			self::$instance = new Session();
		}
		return self::$instance;
	} 
	
	/**
	 *	Save SessionVars
	 */
	public function __set($key, $value) {

		$_SESSION[Session::$sessArrayKey][$key] = $value;
	}
	
	/**
	 *	unset SessionVars
	 */
	public function __unset($key) {

		if($this->exists($key)) {

			unset($_SESSION[Session::$sessArrayKey][$key]);
			return TRUE;
		} else {
		  return FALSE;
		}
	}
	
	/**
	 * @access public
	 * @param string $key
	 * @return mixed|NULL
	 *
	 * Ermöglicht Lesen von Sessionvariablen
	 */
	public function __get($key) {

		if($this->exists($key)) {

			return $_SESSION[Session::$sessArrayKey][$key];
		} else {
		  return NULL;
		}
	}
	
	/**
	 * @access public
	 * @param string $key
	 * @return bool
	 *
	 * gibt true zurück, wenn $key existiert
	 * sonst false
	 */
	public function exists($key) {

		return (isset($_SESSION[Session::$sessArrayKey][$key]) ? TRUE : FALSE);
	}
	
	/**
	 * @access public
	 * @param string $key
	 * @return bool
	 *
	 * ALIAS FÜR Session::exists($key)
	 */
	public function __isset($key) {

		return $this->exists($key);
	}
	
	/**
	 * @access public
	 *
	 * Schließt die Session
	 */
	public function close() {

		session_write_close();
	}
	
	/**
	 * Generiert eine neue Session-ID
	 */
	public function renew($del = FALSE) {

		session_regenerate_id($del);
	}
	
	/**
	 * @access public
	 * @return string
	 *
	 * Liefert dei aktuelle Session-ID
	 */
	public function getID() {

		return session_id();
	}

	public function getSessionCookie() {
		if (ini_get("session.use_cookies")) {

			$params = session_get_cookie_params();
			return $params;
		}
		return false;
	}

	/**
	 * @access public
	 *
	 * Zerstört die Session
	 */
	public function destroy() {

		setcookie(session_name(), '', time()-42000, '/');
		session_destroy();
	}
	
	/**
	 * @access public
	 *
	 * Destruktor
	 */
	public function __destruct() {

		$this->close();
	}
}
