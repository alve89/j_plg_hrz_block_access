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


class plgSystemBlock_access extends JPlugin
{

	

	
	function plgSystemBlock_access(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	
	function onAfterInitialise() // onAfterDispatch()
	{
		$app	 	= JFactory::getApplication();
		$user 		= JFactory::getUser();
		$session	= JFactory::getSession();
		
		if (!$this->params->get('securitykey') || $session->get('block_access'))
		{	
			return;
		}
		// Check if security key has been entered
		$logged = isset($_GET[$this->params->get('securitykey')]);
		
		// Check the current area the user wants so enter (site / admin)
		if ($app->isSite())
		{
			$area = "site";
		}
		
		if ($app->isAdmin())
		{
			$area = "admin";
		}
		
		$securedArea = strtolower($this->params->get('area'));
		
		if($area == $securedArea || $securedArea == "both")
		{
			$this->blockArea($logged);			
		}
	
	}
	
	function blockArea($logged)
	{
		$session = JFactory::getSession();
		
		if (!$logged)
		{
			if($this->params->get('typeOfBlock') == "message")
			{
				header('HTTP/1.0 403 Forbidden');
				die($this->params->get('text'));
			}
			elseif($this->params->get('typeOfBlock') == "errorpage")
			{
				header("HTTP/1.0 404 Not Found");
				header("Location:".JUri::root()."404");
			}


		}
		
		if ($logged)
		{
			$session->set('block_access', true);
//			return true;
		}
	}
}
