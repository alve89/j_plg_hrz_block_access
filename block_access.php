<?php
/**
 * @version 	1.1.8
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

	/**
	* Load the language file on instantiation.
	*
	* @var    boolean
	* @since  3.1
	*/
	protected $autoloadLanguage = true;

	private $securedArea = '';
	private $correctKey = false;
	private $currentUri = '';
	private $redirectUri = '';

	function onAfterInitialise() { // onAfterDispatch()
		$app		= JFactory::getApplication();
		//$user 	= JFactory::getUser();
		$session= JFactory::getSession();

		// Get current URL
		 $this->currentUri = Uri::getInstance();

		if (is_null($this->params->get('securitykey')) || $session->get('block_access') ) {
			return;
		}

		// Get area to be blocked (configured in plugins settings)
		$this->securedArea = strtolower($this->params->get('area'));

		// Check the current area the user wants so enter (site / admin)
		if ($app->isClient('site')) {
			$area = "site";

			// Check if a specific frontend-securitykey was configured
			if(!is_null($this->params->get('securitykeyFrontend'))) {
				// Check if FRONTEND security key has been entered
				$this->correctKey = !is_null($app->input->get($this->params->get('securitykeyFrontend')));
			}
			else {
				// Check if GENERAL security key has been entered
				$this->correctKey = !is_null($app->input->get($this->params->get('securitykey')));
			}
		}
		else if ($app->isClient('administrator')) {
			$area = "admin";

			// Check if GENERAL security key has been entered
			$this->correctKey = !is_null($app->input->get($this->params->get('securitykey')));
		}
		else {
			$area = 'all';
			// Check if GENERAL security key has been entered
			$this->correctKey = !is_null($app->input->get($this->params->get('securitykey')));
		}

		if($area == $this->securedArea || $this->securedArea == 'all') {
			// Area is blocked

			if($this->correctKey) {
				// Correct key was provided with URL
				$session->set('block_access', true);
				return;
			}
			else {
				// Correct key was not provided -> block access
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
			// User is already on configured URL => don't do anything, otherwise the browser will raise a ERR_TOO_MANY_REDIRECTS error
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
