<?php
class Sedo_ColorsHelper_Listener_Init
{
	public static function init_helpers(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		XenForo_Template_Helper_Core::$helperCallbacks += array(
			'playwithcolors' => array('Sedo_ColorsHelper_Helper_PlayWithColors', 'init')
		);
	}
}