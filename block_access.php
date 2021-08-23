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

class plgSystemBlock_access extends JPlugin
{

	function plgSystemBlock_access(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}


	function onAfterInitialise() // onAfterDispatch()
	{
		$app		= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$session= JFactory::getSession();

		if (!$this->params->get('securitykey') || $session->get('block_access'))
		{
			return;
		}
		// Check if security key has been entered
		$logged = isset($_GET[$this->params->get('securitykey')]);

		// Check the current area the user wants so enter (site / admin)
		if ($app->isClient('site'))
		{
			$area = "site";
		}

		if ($app->isClient('administrator'))
		{
			$area = "admin";
		}

		$securedArea = strtolower($this->params->get('area'));

		if($area == $securedArea || $securedArea == "both")
		{
			if ($logged)
			{
				$session = JFactory::getSession();
				$session->set('block_access', true);
	//			return true;
			}
			else {
				$this->blockArea();
			}

		}

	}

	function blockArea()
	{
		if($this->params->get('typeOfBlock') == "message")
		{
			header('HTTP/1.0 403 Forbidden');
			die($this->params->get('text'));
		}
		elseif($this->params->get('typeOfBlock') == "errorpage")
		{
			$uri = Uri::getInstance();
			$url = $uri->toString();
			(CMSApplication::getInstance('site'))->redirect(JUri::root(), 301);
		}
	}
}
