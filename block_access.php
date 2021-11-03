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

class plgSystemBlock_access extends JPlugin {

	private $securedArea = '';

	function onAfterInitialise() { // onAfterDispatch()
		$app		= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$session= JFactory::getSession();

		if (!$this->params->get('securitykey') || $session->get('block_access')) {
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

		$this->securedArea = strtolower($this->params->get('area'));

		if($area == $this->securedArea || $this->securedArea == "all") {
			if($correctKey) {
				$session = JFactory::getSession();
				$session->set('block_access', true);
	//			return true;
			}
			else {
				$this->blockArea();
			}
		}
	}


	function blockArea() {
		if($this->params->get('typeOfBlock') == "message") {
			header('HTTP/1.0 401 Unauthorized');
			die($this->params->get('message'));
		}
		elseif($this->params->get('typeOfBlock') == "redirect") {
			$current = Uri::getInstance();
			$currentScheme = $current->getScheme();
			$currentHost = $current->getHost();
			$currentPath = $current->getPath();
			$currentUrl = $current->toString();

			// Make sure that there's a leading slash!
			$redirectPath = (strpos($this->params->get('redirectUrl'),0,1) == "/") ? $this->params->get('redirectUrl') : '/'.$this->params->get('redirectUrl');

			$redirect = Uri::getInstance(JUri::root());
			$redirectScheme = $redirect->getScheme();
			$redirectHost = $redirect->getHost();
			$redirectPath = JUri::root(true).$redirectPath;
			$redirectUrl = $redirect->setScheme($redirectScheme)
															->setHost($redirectHost)
															->setPath($redirectPath);
			$redirectUrl = $redirect->toString();

			// If the current URL is the main page, do nothing
			if($currentUrl == $redirectUrl) return;

			// Else: Redirect to given URL
			(CMSApplication::getInstance('site'))->redirect($redirectUrl, 301);//JUri::root()
		}
		else{
			(CMSApplication::getInstance('site'))->redirect($redirectUrl, 301);
		}
	}
}
