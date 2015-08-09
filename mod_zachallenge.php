 <?php

defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';

$data	= modZachallengeHelper::getData($params);

if (!empty($data))
{
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

	require JModuleHelper::getLayoutPath('mod_zachallenge', 'default');
}