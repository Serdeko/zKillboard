<?php
class zkillboard
{
	public static function availableStyles()
	{
		$json = json_decode(Util::getData("http://bootswatch.com/api/3.json"));

		$available = array();
		foreach($json->themes as $theme)
			$available[] = strtolower($theme->name);

		$available[] = "default";
		return $available;
	}
}
