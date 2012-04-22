<?php

include('app/config/config.php');
include('app/app.php');
include('app/classes/session.php');

class Dispatcher {

	public function __construct() {
	}

	/**
	 *	GET variable gets chopped, sliced and dispatched, ready to 
	 *	instantiate Controller Classes	
	 */
	public function dispatch() {

		if(!empty($_GET['path'])) {

			/**
			 *	check if any 'real' get variables are set (is so then extract them)
			 *	(by real it is meant: URI?a=varA&b=varB
			 */
			$get = explode("?", $_SERVER['REQUEST_URI']);
			if(isset($get[1])) {
				$this->setGetVariables($get[1]);
			}

			$url = explode('/', $_GET['path']);

			if(!empty($url)) {
				$this->invoker($url);
				return TRUE;
			} else {
				header('Location: http://' . $_SERVER['HTTP_HOST'] . '/');
				return TRUE;
			}
		} else {
			include('app/webroot/header.php');
			include('app/webroot/home.php');
			include('app/webroot/footer.php');
			return;
		}
	}

	/**
	 *	$_GET variables are set again
	 *
	 *	@params		String		$get
	 *	@return		Void
	 */
	private function setGetVariables($get = NULL) {

		$arr = explode('&', $get);
		foreach($arr as $ar) {
			$a = explode('=', $ar);
			if(isset($a[1])) {
				$_GET[$a[0]] = $a[1];
			}
		}
	}

	/**
	 *	check if REST-url is a Controller name	
	 */
	private function invoker($url) {

		$controller = strtolower($url[0]);

		$files = scandir('app/controllers');
		array_walk($files, function(&$value, $key){
			$p = explode('.', $value);
			if($p[0] !== '') {
				$value = $p[0];
			}
		});

		// check if the passed url (the first part) is in one of the Controller-Names
		if(in_array($controller, array_map('strtolower', $files))) {
			// found
			$ret = $this->loadController($controller, $url);
			return TRUE;
		} else {
			// Class not found -> no such path
			header('Location: http://' . $_SERVER['HTTP_HOST']);
			return FALSE;
		}
	}

	/**
	 *		
	 */
	private function loadController($controller = NULL, $rest) {
		
		if($controller !== NULL) {
			try {
				include('app/controllers/' . $controller . '.php');
			} catch (Exception $e) {
				echo 'Exception: ',  $e->getMessage(), '<br />';
				return;
			}

			// remove void REST elements (e.g.: /controller/method//)
			for ($i=0; $i<count($rest); $i++) {
				if ($rest[$i] === '') {
					unset($rest[$i]);
				}
			}

			$ControllerClass = new $controller();

			$ControllerClass->loadMethods($rest);
		}
	}

}
