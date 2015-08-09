 <?php
defined('_JEXEC') or die('Restricted access');
 
class mod_zachallengeInstallerScript
{

	function postflight( $route, JAdapterInstance $adapter ) {
	
		if ($route == 'install')
		{
			$this->release = $adapter->get( "manifest" )->version;
			echo '<div class="well"><p style="color: #468847;">' . 
				JText::_('MOD_ZACHALLENGE_POSTFLIGHT_MESSAGE_INSTALL') . 
				' <a href="'.JRoute::_('index.php?option=com_modules').'">'.
				JText::_('MOD_ZACHALLENGE_POSTFLIGHT_LINK_MODULEMANAGER').
				'</a> 
				</p></div>';		
		}
		
		if ($route == 'update')
		{

			$this->release = $adapter->get( "manifest" )->version;
			echo '<div class="well"><p style="color: #468847;">' . 
			JText::_('MOD_ZACHALLENGE_POSTFLIGHT_MESSAGE_UPGRADE') . '<strong>' . $this->release . '</strong>' . '</p></div>';
				
		}
	
	}


}