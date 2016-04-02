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

$message = array();
$info = User::getUserInfo();
$ticket = Db::queryRow("SELECT * FROM zz_tickets WHERE id = :id", array(":id" => $id), 0);
if($ticket == NULL or sizeof($ticket) == 0)
	$message = array("status" => "error", "message" => "Ticket does not exist.");
elseif($ticket["status"] == 0)
	$message = array("status" => "error", "message" => "Ticket has been closed, you cannot post, only view it");
elseif($ticket["userid"] != $info["id"] && $info["moderator"] == 0 && $info["admin"] == 0)
	$app->notFound();

if($_POST)
{
	$reply = Util::getPost("reply");

	if($reply && $ticket["status"] != 0)
	{
		$name = $info["username"];
		$moderator = $info["moderator"];
		$check = Db::query("SELECT * FROM zz_tickets_replies WHERE reply = :reply AND userid = :userid AND belongsTo = :id", array(":reply" => $reply, ":userid" => $info["id"], ":id" => $id), 0);
		if(!$check)
		{
			Db::execute("INSERT INTO zz_tickets_replies (userid, belongsTo, name, reply, moderator) VALUES (:userid, :belongsTo, :name, :reply, :moderator)", array(":userid" => $info["id"], ":belongsTo" => $id, ":name" => $name, ":reply" => $reply, ":moderator" => $moderator));
			global $baseAddr;
			if (!$moderator) Log::ircAdmin("|g|Ticket response from $name:|n| https://$baseAddr/moderator/tickets/$id/");
			$app->redirect("/tickets/view/$id/");
		}
	}
	else
	{
		$message = array("status" => "error", "message" => "No...");
	}
}

$replies = Db::query("SELECT * FROM zz_tickets_replies WHERE belongsTo = :id", array(":id" => $id), 0);

$app->render("tickets_view.html", array("page" => $id, "message" => $message, "ticket" => $ticket, "replies" => $replies));
