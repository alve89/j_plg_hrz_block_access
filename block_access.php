<?php
/**
 * @version 	1.0.0
 * @package 	Block Access
 * @copyright 	(c) 2017 Stefan Herzog
 * @license		GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.error.exception');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Router\Route;

class plgSystemBlock_access extends JPlugin {

	private $securedArea = '';
	private $currentUri = '';
	private $redirectUri = '';

	function onAfterInitialise() { // onAfterDispatch()
		$app		= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$session= JFactory::getSession();

		// Get current URL
		 $this->currentUri = Uri::getInstance();

		if (is_null($this->params->get('securitykey')) || $session->get('block_access')) {
			return;
		}
		// Check if security key has been entered
		$correctKey = isset($_GET[$this->params->get('securitykey')]);

		// Check the current area the user wants so enter (site / admin)
		if ($app->isClient('site')) {
			$area = "site";
		}
		if ($app->isClient('administrator')) {
			$area = "admin";
		}

		if(!is_null($this->params->get('redirectUrl'))) {
			//$redirectUri->set
		}

		$this->securedArea = strtolower($this->params->get('area'));

		if($area == $this->securedArea || $this->securedArea == "all") {
			if($correctKey) {
				// Correct key was provided with URL
				$session = JFactory::getSession();
				$session->set('block_access', true);
				return;
			}
			else {
				$this->setUris();
				$this->blockArea();
			}
		}
	}

	function blockArea() {
		if($this->params->get('typeOfBlock') == "message") {
			header('HTTP/1.0 401 Unauthorized');
			die($this->params->get('message'));
			return; // Actually pointless
		}
		elseif($this->params->get('typeOfBlock') == "redirect") {
			// User is already on configured URL => don't do anything
			if($this->currentUri->toString() == $this->redirectUri->toString()) return;
		}
		else {
			// Nothing to do
		}

		// Execute the redirect
		CMSApplication::getInstance('site')->redirect($this->redirectUri->toString(), 401);

	}

	private function setUris() {
		// Set redirect URI: Use specified one (from plugin configuration) or default (Joomla Root)
		// redirectUri is either http(s)://mydomain.tld or, if Joomla is installed in subdirectory, http(s)://mydomain.tld/path/to/joomla
		// It's not depending on where it is called from (site/admin)
	 if(substr($this->params->get('redirectUrl'),0,4) == 'http') {
		 // If a valid URI was set, use this
		 $this->redirectUri = Uri::getInstance($this->params->get('redirectUrl'));
	 }
	 // If an relative path was set, use this)
	 else if(substr($this->params->get('redirectUrl'),0,1) == '/') {
		 // Set redirect URI (remove leading slash first)
		 $this->redirectUri = Uri::getInstance(Uri::root().substr($this->params->get('redirectUrl'),1));
	 }
	 else {
		 // Otherwise use the default URI (Joomla Root)
		 $this->redirectUri = Uri::getInstance(Uri::root());
	 }
	}
}
