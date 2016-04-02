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

// Get the information pages available
$pages = Util::informationPages();

// Figure out the path based on the request
$path = null;
foreach($pages as $key => $val)
{
	if($key == $page)
	{
		if(count($val) >= 2) // It's a folder
		{
			foreach($val as $sub)
				if($sub["name"] == $subPage)
					$path = $sub["path"];
		}
		else
		{
			$path = $val[0]["path"];
		}
	}
}

// If path is null, then none of the above triggered, in which case, the request isn't valid.. REDIRECTION TIME BRO...... Atleast i'm not sending you to goatse or something
if($path == null)
	$app->redirect("/");

// Load the markdown file
$markdown = file_get_contents($path);

// Load the markdown parser
$parsedown = new Parsedown();
$output = $parsedown->text($markdown);

if($page == "payments")
{
	global $adFreeMonthCost;
	$output = str_replace("{cost}", $adFreeMonthCost, $output);
}

if($page == "statistics")
{
	// Replace certain tags with different data
	$info = array();
	$info["kills"] = number_format(Storage::retrieve("totalKills"), 0, ".", ",");
	$info["total"] = number_format(Storage::retrieve("actualKills"), 0, ".", ",");
	$info["percentage"] = number_format($info["total"] / $info["kills"] * 100, 2, ".", ",");
	$info["NextWalletFetch"] = Storage::retrieve("NextWalletFetch");

	foreach($info as $k => $d)
		$output = str_replace("{".$k."}", $d, $output);

	$info["apistats"] = Db::query("select errorCode, count(*) count from zz_api_log where requestTime >= date_sub(now(), interval 1 hour) group by 1");

	$apitable = '
	<table class="table table-striped table-hover table-bordered">
	  <tr><th>Error</th><th>Count</th></tr>';

	  foreach($info["apistats"] as $data)
	  {
	  	$apitable .= '<tr>';
	  	$apitable .= '<td>';

	  	if($data["errorCode"] == NULL)
	  		$apitable .= 'No error';
	  	else
	  		$apitable .= $data["errorCode"];

	  	$apitable .= '</td>';
	  	$apitable .= '<td>';
	  	$apitable .= number_format($data["count"]);
	  	$apitable .= '</td>';
	  	$apitable .= '</tr>';
	  }
	  $apitable .= "</table>";

	$output = str_replace("{apitable}", $apitable, $output);

	$info["pointValues"] = Points::getPointValues();
	$pointtable = "<ul>";
	foreach ($info["pointValues"] as $points)
		$pointtable .= "<li>" . $points[0] . ": " . $points[1] . "</li>";
	$pointtable .= "</ul>";

	$output = str_replace("{pointsystem}", $pointtable, $output);
}

// Load the information page html, which is just the bare minimum to load base.html and whatnot, and then spit out the markdown output!
$app->render("information.html", array("data" => $output));