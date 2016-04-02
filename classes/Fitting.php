<?php
/* zKillboard
 * Copyright (C) 2012-2015 EVE-KILL Team and EVSCO.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class Fitting
{
	public static function EFT($array)
	{
		$eft = "";
		$item = "";
		if (isset($array["low"])) foreach ($array["low"] as $flags)
		{
			foreach ($flags as $items)
			{
				$item = $items["typeName"] . "\n";
			}
			$eft .= $item;
		}
		$eft .= "\n";
		$item = "";
		if (isset($array["mid"])) foreach ($array["mid"] as $flags)
		{
			$cnt = 0;
			foreach ($flags as $items)
			{
				if ($cnt == 0)
					$item = $items["typeName"];
				else
					$item .= "," . $items["typeName"];
				$cnt++;
			}
			$item .= "\n";
			$eft .= $item;
		}
		$eft .= "\n";
		$item = "";
		if (isset($array["high"])) foreach ($array["high"] as $flags)
		{
			$cnt = 0;
			foreach ($flags as $items)
			{
				if ($cnt == 0)
					$item = $items["typeName"];
				else
					$item .= "," . $items["typeName"];
				$cnt++;
			}
			$item .= "\n";
			$eft .= $item;
		}
		$eft .= "\n";
		$item = "";
		if (isset($array["rig"])) foreach ($array["rig"] as $flags)
		{
			foreach ($flags as $items)
			{
				$item = $items["typeName"] . "\n";
			}
			$eft .= $item;
		}
		$eft .= "\n";
		$item = "";
		if (isset($array["sub"])) foreach ($array["sub"] as $flags)
		{
			foreach ($flags as $items)
			{
				$item = $items["typeName"] . "\n";
			}
			$eft .= $item;
		}
		$eft .= "\n";
		$item = "";
		if (isset($array["drone"])) foreach ($array["drone"] as $flags)
		{
			foreach ($flags as $items)
			{
				$item .= $items["typeName"] . " x" . $items["qty"] . "\n";
			}
			$eft .= $item;
		}
		return trim($eft);
	}

	public static function DNA($array = array(), $ship)
	{
		$goodspots = array("High Slots", "SubSystems", "Rigs", "Low Slots", "Mid Slots", "Drone Bay", "Fuel Bay");
		$fitArray = array();
		$fitString = $ship.":";

		foreach($array as $item)
		{
			if (isset($item["flagName"]) && in_array($item["flagName"], $goodspots))
			{
				if(isset($fitArray[$item["typeID"]]))
					$fitArray[$item["typeID"]]["count"] = $fitArray[$item["typeID"]]["count"] + ($item["qtyDropped"] + $item["qtyDestroyed"]);
				else
					$fitArray[$item["typeID"]] = array("count" => ($item["qtyDropped"] + $item["qtyDestroyed"]));
			}
		}

		foreach($fitArray as $key => $item)
		{
			$fitString .= "$key;".$item["count"].":";
		}
		$fitString .= ":";
		return $fitString;
	}
}
