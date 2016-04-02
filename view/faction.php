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

// Find the factionID
if(!is_numeric($faction))
	$factionID = (int) Db::queryField("SELECT factionID FROM ccp_zfactions WHERE name = :name", "factionID", array(":name" => $faction), 3600);
else // Verify it exists
	$factionID = (int) Db::queryField("SELECT factionID FROM ccp_zfactions WHERE factionID = :factionID", "factionID", array(":factionID" => (int) $faction), 3600);

// If the factionID we get from above is zero, don't even bother anymore.....
if($factionID == 0)
	$app->redirect("/");
elseif(!is_numeric($faction)) // if faction isn't numeric, we redirect TO the factionID!
	$app->redirect("/faction/{$factionID}/");

// Now we figure out all the parameters
$parameters = Util::convertUriToParameters();

// Unset the faction => id, and make it factionID => id
unset($parameters["faction"]);
$parameters["factionID"] = $factionID;
$parameters["index"] = "factionID_dttm";

// Make sure that the pageType is correct..
$subPageTypes = array("page", "groupID", "month", "year", "shipTypeID");
if(in_array($pageType, $subPageTypes))
	$pageType = "overview";

// Some defaults
@$page = max(1, $parameters["page"]);
$limit = 50;
$parameters["limit"] = $limit;
$parameters["page"] = $page;

// and now we fetch the info!
$detail = Info::getFactionDetails($factionID, $parameters);

// Define the page information and scope etc.
$pageName = isset($detail["factionName"]) ? $detail["factionName"] : "???";
$columnName = "factionID";
$mixedKills = $pageType == "overview" && UserConfig::get("mixKillsWithLosses", true);

// Load kills for the various pages.
$mixed = $pageType == "overview" ? Kills::getKills($parameters) : array();
$kills = $pageType ==  "kills" ? Kills::getKills($parameters) : array();
$losses = $pageType == "losses" ? Kills::getKills($parameters) : array();

// Solo parameters
//$soloParams = $parameters;
//if (!isset($parameters["kills"]) || !isset($parameters["losses"])) {
//	$soloParams["mixed"] = true;
//}

// Solo kills
//$soloKills = Kills::getKills($soloParams);
//$solo = Kills::mergeKillArrays($soloKills, array(), $limit, $columnName, $factionID);


$topLists = array();
$topKills = array();
if ($pageType == "top)") {
	$topParameters = $parameters; // array("limit" => 10, "kills" => true, "$columnName" => $factionID);
	$topParameters["limit"] = 10;

	if ($pageType != "topalltime") {
		if (!isset($topParameters["year"])) {
			$topParameters["year"] = date("Y");
		}

		if (!isset($topParameters["month"])) {
			$topParameters["month"] = date("m");
		}

	}
	if (!array_key_exists("kills", $topParameters) && !array_key_exists("losses", $topParameters)) {
		$topParameters["kills"] = true;
	}

	$topLists[] = array("type" => "character", "data" => Stats::getTopPilots($topParameters, true));
	$topLists[] = array("type" => "corporation", "data" => Stats::getTopCorps($topParameters, true));
	$topLists[] = array("type" => "alliance", "data" => Stats::getTopAllis($topParameters, true));
	$topLists[] = array("type" => "ship", "data" => Stats::getTopShips($topParameters, true));
	$topLists[] = array("type" => "system", "data" => Stats::getTopSystems($topParameters, true));
	$topLists[] = array("type" => "weapon", "data" => Stats::getTopWeapons($topParameters, true));
	$topLists[] = array("name" => "Top Faction Characters", "type" => "character", "data" => Stats::getTopPilots($topParameters, true));
	$topLists[] = array("name" => "Top Faction Corporations", "type" => "corporation", "data" => Stats::getTopCorps($topParameters, true));
	$topLists[] = array("name" => "Top Faction Alliances", "type" => "alliance", "data" => Stats::getTopAllis($topParameters, true));
}
else
{
	$p = $parameters;
	$numDays = 7;
	$p["limit"] = 10;
	$p["pastSeconds"] = $numDays * 86400;
	$p["kills"] = $pageType != "losses";

	$topLists[] = Info::doMakeCommon("Top Characters", "characterID", Stats::getTopPilots($p));
	$topLists[] = Info::doMakeCommon("Top Corporations", "corporationID", Stats::getTopCorps($p));
	$topLists[] = Info::doMakeCommon("Top Alliances", "allianceID", Stats::getTopAllis($p));
	$topLists[] = Info::doMakeCommon("Top Ships", "shipTypeID", Stats::getTopShips($p));
	$topLists[] = Info::doMakeCommon("Top Systems", "solarSystemID", Stats::getTopSystems($p));

	$p["limit"] = 5;
	$topKills = Stats::getTopIsk($p);
}

// Stats
$cnt = 0;
$cnid = 0;
$stats = array();
$totalcount = ceil(count($detail["stats"]) / 4);
foreach ($detail["stats"] as $q) {
	if ($cnt == $totalcount) {
		$cnid++;
		$cnt = 0;
	}
	$stats[$cnid][] = $q;
	$cnt++;
}
// Fix the history data!
$detail["history"] = $pageType == "stats" ? Summary::getMonthlyHistory($columnName, $factionID) : array();

// Mixed kills yo!
if ($mixedKills)
	$kills = Kills::mergeKillArrays($mixed, array(), $limit, $columnName, $factionID);

// Find the next and previous factionID
$prevID = Db::queryField("select factionID from zz_factions where factionID < :id order by factionID desc limit 1", "factionID", array(":id" => $factionID), 300);
$nextID = Db::queryField("select factionID from zz_factions where factionID > :id order by factionID asc limit 1", "factionID", array(":id" => $factionID), 300);

/*$extra["supers"] = array();
if ($pageType == "supers")
{
	$minKillID = Db::queryField("select min(killID) killID from zz_participants where dttm >= date_sub(now(), interval 90 day) and dttm < date_sub(now(), interval 89 day)", "killID", array(), 900);
	$months = 3;
	$data = array();
	$data["titans"]["data"] = Db::query("SELECT distinct characterID, count(distinct killID) kills, shipTypeID FROM zz_participants WHERE killID >= $minKillID AND isVictim = 0 AND groupID = 30 AND factionID = :id GROUP BY characterID ORDER BY 2 DESC", array(":id" => $factionID), 900);
	$data["titans"]["title"] = "Titans";

	$data["moms"]["data"] = Db::query("SELECT distinct characterID, count(distinct killID) kills, shipTypeID FROM zz_participants WHERE killID >= $minKillID AND isVictim = 0 AND groupID = 659 AND factionID = :id GROUP BY characterID ORDER BY 2 DESC", array(":id" => $factionID), 900);
	$data["moms"]["title"] = "Supercarriers";

	Info::addInfo($data);
	$extra["supers"] = $data;
}*/

$renderParams = array(
	"pageName" => $pageName,
	"kills" => $kills,
	"losses" => $losses,
	"detail" => $detail,
	"page" => $page,
	"topKills" => $topKills,
	"mixed" => $mixedKills,
	"key" => "faction",
	"id" => $factionID,
	"pageType" => $pageType,
	//"solo" => $solo,
	"topLists" => $topLists,
	"summaryTable" => $stats,
	"pager" => (sizeof($kills) + sizeof($losses) >= $limit),
	"datepicker" => true,
	"prevID" => $prevID,
	"nextID" => $nextID,
	//"extra" => $extra
);

$app->etag(md5(serialize($renderParams)));
$app->expires("+5 minutes");
$app->render("overview.html", $renderParams);
