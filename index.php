<?php
/*
    CoreManager, PHP Front End for ArcEmu, MaNGOS, and TrinityCore
    Copyright (C) 2010-2013  CoreManager Project
    Copyright (C) 2009-2010  ArcManager Project

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


require_once "header.php";
require_once "libs/bb2html_lib.php";
require_once "libs/char_lib.php";
require_once "libs/map_zone_lib.php";
require_once "libs/get_uptime_lib.php";
require_once "libs/forum_lib.php";
require_once "libs/data_lib.php";
require_once "libs/item_lib.php";

valid_login($action_permission["view"]);

//#############################################################################
// COREMANAGER MAIN PAGE
//#############################################################################
function main()
{
  global $output, $realm_id, $world_db, $logon_db, $characters_db, $corem_db, $server,
    $action_permission, $user_lvl, $user_id, $site_encoding, $hide_coupons,
    $locales_search_option, $base_datasite, $item_datasite,
    $showcountryflag, $gm_online_count, $gm_online, $itemperpage, $hide_uptime, $player_online,
    $hide_max_players, $hide_avg_latency, $hide_plr_latency, $hide_server_mem, $sql, $core;

  // do any raffle drawings that are necessary
  do_raffles();

  // check achievement contests
  do_contests();

  $output .= '
          <div class="top">';

  //---------------------Information for Explorer Users--------------------------
  if ( preg_match("/MSIE/", $_SERVER["HTTP_USER_AGENT"]) )
  {
    $msie = '
            <br />
              <span id="index_explorer_warning">'.lang("index", "explorer_warn").'</span>
            <br />';
  }
  else
    $msie = '';
  //-----------------------------------------------------------------------------

  if ( test_port($server[$realm_id]["addr"], $server[$realm_id]["game_port"]) )
  {
    if ( $core == 1 )
    {
      $stats = get_uptime($server[$realm_id]["stats.xml"]);
      
      $staticUptime = ' <em>'.htmlentities(get_realm_name($realm_id), ENT_COMPAT, $site_encoding).'</em> <br />'.$stats["platform"][4].' '.$stats["platform"][5].' '.$stats["platform"][6];

      if ( !$hide_uptime )
        $staticUptime .= '<br />'.lang("index", "online").' for '.$stats["uptime"];

      $output .= '
            <div id="uptime">'.$msie.'
              <h1>
                <span id="index_realm_info">
                  <span>'.$staticUptime.'</span>';

      if ( !$hide_max_players )
      {
        $output .= '
                  <br />
                  <span>'.lang("index", "maxplayers").': </span>
                  <span class="index_realm_info_value">'.$stats["peak"].'</span>';
      }

      if ( !$hide_avg_latency )
      {
        $output .= '
                  <br />
                  <span>'.lang("index", "avglat").': </span>
                  <span class="index_realm_info_value">'.$stats["avglat"].'</span>';
      }

      $output .= '
                  <br />';

      if ( $hide_server_mem <> 0 )
      {
        if ( ( $hide_server_mem == 2 ) || ( $user_lvl == $action_permission["delete"] ) )
        {
          $output .= '
                  <span>'.lang("index", "cpu").': </span>
                  <span class="index_realm_info_value">'.$stats["cpu"].'%, </span>
                  <span>'.lang("index", "ram").': </span>
                  <span class="index_realm_info_value">'.$stats["ram"].' MB, </span>
                  <span>'.lang("index", "threads").': </span>
                  <span class="index_realm_info_value">'.$stats["threads"].'</span>';
        }
      }
      $output .= '
               </span>
              </h1>
            </div>';
    }
    else
    {
      $stats = $sql["logon"]->fetch_assoc($sql["logon"]->query("SELECT starttime, maxplayers FROM uptime WHERE realmid='".$realm_id."' ORDER BY starttime DESC LIMIT 1"), 0);
      $uptimetime = time() - $stats["starttime"];

      // a more reliable method of counting how many characters have been online since server start
      //$maxplayers_query = "SELECT COUNT(*) FROM `".$characters_db[$realm_id]["name"]."`.characters WHERE logout_time>='".$stats["starttime"]."' AND logout_time>=(SELECT UNIX_TIMESTAMP(last_login) FROM `".$logon_db["name"]."`.account WHERE id=`".$characters_db[$realm_id]["name"]."`.characters.account)";
      //$maxplayers_result = $sql["char"]->query($maxplayers_query);
      //$maxplayers_result = $sql["char"]->fetch_assoc($maxplayers_result);
      //$stats["maxplayers"] = $maxplayers_result["COUNT(*)"];

      function format_uptime($seconds)
      {
        $secs  = intval($seconds % 60);
        $mins  = intval($seconds / 60 % 60);
        $hours = intval($seconds / 3600 % 24);
        $days  = intval($seconds / 86400);
        if ( $days > 365 )
        {
          $days  = intval($seconds / 86400 % 365.24);
          $years = intval($seconds / 31556926);
        }

        $uptimeString = '';

        if ( $years )
        {
          // we have a server that has been up for over a year? O_o
          // actually, it's probably because the server didn't write a useful
          // value to the uptime table's starttime field.
          $uptimeString .= $years;
          $uptimeString .= ( ( $years == 1 ) ? ' '.lang("index", "uptime_year") : ' '.lang("index", "uptime_years") );
          if ( $days )
          {
            $uptimeString .= ( ( $years > 0 ) ? ', ' : '' ).$days;
            $uptimeString .= ( ( $days == 1 ) ? ' '.lang("index", "uptime_day") : ' '.lang("index", "uptime_days"));
          }
        }
        else
        {
          if ( $days )
          {
            $uptimeString .= $days;
            $uptimeString .= ( ( $days == 1 ) ? ' '.lang("index", "uptime_day") : ' '.lang("index", "uptime_days") );
          }
        }
        if ( $hours )
        {
          $uptimeString .= ( ( $days > 0 ) ? ', ' : '' ).$hours;
          $uptimeString .= ( ( $hours == 1 ) ? ' '.lang("index", "uptime_hour") : ' '.lang("index", "uptime_hours") );
        }
        if ( $mins )
        {
          $uptimeString .= ( ( $days > 0 || $hours > 0 ) ? ', ' : '' ).$mins;
          $uptimeString .= ( ( $mins == 1) ? ' '.lang("index", "uptime_minute") : ' '.lang("index", "uptime_minutes") );
        }
        if ( $secs )
        {
          $uptimeString .= ( ( $days > 0 || $hours > 0 || $mins > 0 ) ? ', ' : '' ).$secs;
          $uptimeString .= ( ( $secs == 1 ) ? ' '.lang("index", "uptime_second") : ' '.lang("index", "uptime_seconds") );
        }
        return $uptimeString;
      }

      $staticUptime = ' <em>'.htmlentities(get_realm_name($realm_id), ENT_COMPAT, $site_encoding).'</em> ';

      if ( !$hide_uptime )
      {
        if ( $stats["starttime"] <> 0 )
          $staticUptime .= '<br /><span>'.lang("index", "online").format_uptime($uptimetime).'</span>';
        else
          $staticUptime .= '<br /><span style="color:orange">'.lang("index", "time_error1").': <br>'.format_uptime($uptimetime).'</span><br><span style="color:red">'.lang("index", "time_error2").'</span>';
      }

      unset($uptimetime);

      $output .= '
            <div id="uptime">'.$msie.'
              <h1>
                <span id="index_realm_info">'
                  .$staticUptime;

      if ( !$hide_max_players )
      {
        $output .= '
                  <br />
                  <span>'.lang("index", "maxplayers").': </span>
                  <span class="index_realm_info_value">'.$stats["maxplayers"].'</span>';
      }

      // this_is_junk: MaNGOS doesn't store player latency. :/
      if ( $core == 3 )
      {
        if ( !$hide_avg_latency )
        {
          $lat_query = "SELECT SUM(latency), COUNT(*) FROM characters WHERE online=1";
          $lat_result = $sql["char"]->query($lat_query);
          $lat_fields = $sql["char"]->fetch_assoc($lat_result);
          $avglat = sprintf("%.3f", $lat_fields["SUM(latency)"] / $lat_fields["COUNT(*)"]);
          
          $output .= '
                    <br />
                    <span>'.lang("index", "avglat").': </span>
                    <span class="index_realm_info_value">'.$avglat.'</span>';
        }
      }
      $output .= '
                </span>
              </h1>
            </div>';

      unset($stats);
      $online = true;
    }
    
    unset($staticUptime);
    //unset($stats);
    $online = true;
  }
  else
  {
    $output .= $msie.'<h1><span class="error">'.lang("index", "realm").' <em>'.htmlentities(get_realm_name($realm_id), ENT_COMPAT, $site_encoding).'</em> '.lang("index", "offline_or_let_high").'</span></h1>';
    $online = false;
  }

  //close the div
  $output .= '
          </div>';

  // MOTDs
  // get our MotDs...
  $motd = "";
  $motd_result = $sql["mgr"]->query("SELECT *, UNIX_TIMESTAMP(Created) AS Created, UNIX_TIMESTAMP(Last_Edited) AS Last_Edited FROM motd WHERE Enabled<>0 AND (Target='".$user_id."' OR Target=0) ORDER BY Priority ASC");
  // if we don't get any MotDs, it'll stay empty

  if ( $user_lvl >= $action_permission["update"] )
  {
    $output .= '
          <script type="text/javascript">
            // <![CDATA[
              answerbox.btn_ok = "'.lang("global", "yes_low").'";
              answerbox.btn_cancel = "'.lang("global", "no").'";
              var del_motd = "motd.php?action=delete_motd&amp;id=";
            // ]]>
          </script>';
  }

  if ( $sql["mgr"]->num_rows($motd_result) > 0 )
  {
    $output .= '
            <div class="div_lined">
              <div class="div_lined_head">
                <span>'.lang("index", "motd").'</span>
              </div>';
  }

  while ( $temp = $sql["mgr"]->fetch_assoc($motd_result) )
  {
    if ( $user_lvl >= $temp["Min_Sec_Level"] )
    {
      $motd = bb2html($temp["Message"])."<br /><br />";
      if ( $motd )
      {
        if ( $temp["Target"] != 0 )
        {
          $output .= '
                <div class="div_lined_line left_text">'.lang("motd", "private").'</div>';
        }

        $output .= '
                <div class="div_lined_line left_text">';

        $output .= $motd;
        $output .= '
                  <br />';

        // Get User Name for poster
        if ( $core == 1 )
          $posted_name_query = "SELECT login FROM accounts WHERE acct='".$temp["Created_By"]."'";
        else
          $posted_name_query = "SELECT username AS login FROM account WHERE id='".$temp["Created_By"]."'";

        $posted_name_result = $sql["logon"]->query($posted_name_query);
        $posted_name = $sql["logon"]->fetch_assoc($posted_name_result);
        $posted_name = $posted_name["login"];

        // Get Screen Name for poster, if available
        $posted_screenname_query = "SELECT ScreenName FROM config_accounts WHERE Login='".$posted_name."'";
        $posted_screenname_result = $sql["mgr"]->query($posted_screenname_query);
        $posted_screenname = $sql["mgr"]->fetch_assoc($posted_screenname_result);

        if ( $posted_screenname["ScreenName"] != NULL )
          $posted_name = htmlspecialchars($posted_screenname["ScreenName"]);

        // if the Server Message is posted by the system, Created_By will be zero
        if ( $temp["Created_By"] != 0 )
        {
          $output .= '
                  <span>'.lang("motd", "posted_by").':</span>';

          if ( $user_lvl > -1 )
          {
            $output .= '
                  <a href="user.php?action=edit_user&amp;error=11&amp;acct='.$temp["Created_By"].'">';
          }

          $output .= '
                    <span>'.$posted_name.'</span>';

          if ( $user_lvl > -1 )
            $output .= '
                  </a>';
          $output .= '
                  <span>('.date("M d, Y @ H:i:s", $temp["Created"]).')</span>';
        }
        else
        {
          $output .= '
                  <span>'.lang("motd", "posted").': '.date("M d, Y @ H:i:s", $temp["Created"]).'</span>';
        }
        
        // Get User Name for last editor
        if ( $core == 1 )
          $edited_name_query = "SELECT login FROM accounts WHERE acct='".$temp["Last_Edited_By"]."'";
        else
          $edited_name_query = "SELECT username AS login FROM account WHERE id='".$temp["Last_Edited_By"]."'";

        $edited_name_result = $sql["logon"]->query($edited_name_query);
        $edited_name = $sql["logon"]->fetch_assoc($edited_name_result);
        $edited_name = $edited_name["login"];

        // Get Screen Name for last editor, if available
        $edited_screenname_query = "SELECT ScreenName FROM config_accounts WHERE Login='".$edited_name."'";
        $edited_screenname_result = $sql["mgr"]->query($edited_screenname_query);
        $edited_screenname = $sql["mgr"]->fetch_assoc($edited_screenname_result);

        if ( $edited_screenname["ScreenName"] != NULL )
          $edited_name = htmlspecialchars($edited_screenname["ScreenName"]);

        if ( $temp["Last_Edited_By"] != 0 )
        {
          $output .= '
                  <br />
                  <div>
                    <span>'.lang("motd", "edited_by").':</span>';

          if ( $user_lvl > -1 )
          {
            $output .= '
                    <a href="user.php?action=edit_user&amp;error=11&amp;acct='.$temp["Last_Edited_By"].'">';
          }

          $output .= '
                      <span>'.$edited_name.'</span>';

          if ( $user_lvl > -1 )
          {
            $output .= '
                    </a>';
          }

          $output .= '
                    <span>('.date("M d, Y @ H:i:s", $temp["Last_Edited"]).')</span>
                  </div>';
        }

        $output .= '
                </div>';

        if ( $user_lvl >= $action_permission["update"] )
        {
          $output .= '
                <div class="div_lined_line right_text">
                  <img src="img/aff_cross.png" width="16" height="16" onclick="answerBox(\''.lang("global", "delete").': &lt;font color=white&gt;'.$temp["ID"].'&lt;/font&gt;&lt;br /&gt;'.lang("global", "are_you_sure").'\', del_motd + '.$temp["ID"].');" alt="" />';
        }

        if ( $user_lvl >= $action_permission["update"] )
        {
          $output .= '
                  <a href="motd.php?action=edit_motd&amp;error=3&amp;id='.$temp["ID"].'">
                    <img src="img/edit.png" width="16" height="16" alt="" />
                  </a>
                </div>';
        }

        $output .= '
                <br />';
      }
    }
  }

  if ( $sql["mgr"]->num_rows($motd_result) )
    $output = substr($output, 0, strlen($output) - 24);

  if ( $sql["mgr"]->num_rows($motd_result) > 0 )
  {
    $output .= '
            </div>';
  }

  if ( $user_lvl >= $action_permission["insert"] )
  {
    $output .= '
            <div class="div_lined">
              <div class="div_lined_head right_text">
                <a href="motd.php?action=add_motd&amp;error=4">'.lang("index", "add_motd").'</a>
              </div>
            </div>';
  }

  // Coupons
  if ( !$hide_coupons )
  {
    $coupon_query = "SELECT * FROM point_system_coupons WHERE ((target='0' AND contest='0') OR target='".$user_id."') AND enabled='1'";
    $coupon_result = $sql["mgr"]->query($coupon_query);

    if ( $sql["mgr"]->num_rows($coupon_result) > 0 )
    {
      $output .= '
            <br />
            <div class="div_lined">
              <div class="div_lined_head">
                <span>'.lang("index", "avail_coupons").'</span>
              </div>';

      while ( $coupon = $sql["mgr"]->fetch_assoc($coupon_result) )
      {
        $usage_query = "SELECT * FROM point_system_coupon_usage WHERE coupon='".$coupon["entry"]."' AND user='".$user_id."'";
        $usage_result = $sql["mgr"]->query($usage_query);

        if ( ( $sql["mgr"]->num_rows($usage_result) < $coupon["usage_limit"] ) || ( $coupon["usage_limit"] == -1 ) )
        {
          $output .= '
              <div class="div_lined_line left_text">
                <span>'.$coupon["title"].'</span>
              </div>';

          if ( $coupon["text"] != "" )
          {
            $output .= '
              <div class="div_lined_line left_text">
                <span>'.$coupon["text"].'</span>
              </div>';
          }

          if ( ( $coupon["credits"] != 0 ) || ( $coupon["money"] != 0 ) || ( $coupon["item_id"] != 0 ) || ( $coupon["raffle_id"] != 0 ) )
          {
            $output .= '
              <div class="div_lined_line left_text">
                <span>'.lang("index", "coupon_value").':</span>';

            if ( $coupon["credits"] > 0 )
            {
              if ( $coupon["credits"] > 1 )
                $tip = lang("index", "coupon_credits");
              else
                $tip = lang("index", "coupon_credit");

              $output .= '
                <br />
                <br />
                <span>'.$coupon["credits"].'</span>
                <span>'.$tip.'</span>';
            }

            if ( $coupon["money"] > 0 )
            {
              // extract gold/silver/copper from single gold number
              $coupon["money"] = str_pad($coupon["money"], 4, "0", STR_PAD_LEFT);
              $coupon_g = substr($coupon["money"],  0, -4);
              if ( $coupon_g == "" )
                $coupon_g = 0;
              $coupon_s = substr($coupon["money"], -4,  2);
              if ( ( $coupon_s == "" ) || ( $coupon_s == "00" ) )
                $coupon_s = 0;
              $coupon_c = substr($coupon["money"], -2);
              if ( ( $coupon_c == "" ) || ( $coupon_c == "00" ) )
                $coupon_c = 0;

              $output .= '
                <br />
                <br />
                <span>'.$coupon_g.'</span>
                <img src="img/gold.gif" alt="gold" />
                <span>'.$coupon_s.'</span>
                <img src="img/silver.gif" alt="gold" />
                <span>'.$coupon_c.'</span>
                <img src="img/copper.gif" alt="gold" />';
            }

            if ( $coupon["item_id"] != 0 )
            {
              if ( $coupon["item_id"] > 0 )
              {
                // get item data
                if ( $core == 1 )
                {
                  $i_query = "SELECT 
                    *, description AS description1, name1 AS name, quality AS Quality, inventorytype AS InventoryType, 
                    socket_color_1 AS socketColor_1, socket_color_2 AS socketColor_2, socket_color_3 AS socketColor_3,
                    requiredlevel AS RequiredLevel, allowableclass AS AllowableClass,
                    sellprice AS SellPrice, itemlevel AS ItemLevel
                    FROM items "
                      .( ( $locales_search_option != 0 ) ? "LEFT JOIN items_localized ON (items_localized.entry=items.entry AND language_code='".$locales_search_option."') " : " " ).
                    "WHERE items.entry='".$coupon["item_id"]."'";
                }
                else
                {
                  $i_query = "SELECT *, description AS description1 FROM item_template "
                      .( ( $locales_search_option != 0 ) ? "LEFT JOIN locales_item ON locales_item.entry=item_template.entry " : " " ).
                    "WHERE item_template.entry='".$coupon["item_id"]."'";
                }

                $i_result = $sql["world"]->query($i_query);
                $i = $sql["world"]->fetch_assoc($i_result);

                $output .= '
                <br />
                <br />
                <div class="coupon_item">
                  <div>
                    <a href="'.$base_datasite.$item_datasite.$coupon["item_id"].'" rel="external" onmouseover="ShowTooltip(this,\'_b'.$coupon["entry"].'\');" onmouseout="HideTooltip(\'_b'.$coupon["entry"].'\');">
                      <img src="'.get_item_icon($coupon["item_id"]).'" alt="" />
                    </a>';

                if ( $coupon["item_count"] > 1 )
                {
                  $output .= '
                    <div class="ch_inv_quantity_shadow">'.$coupon["item_count"].'</div>
                    <div class="ch_inv_quantity">'.$coupon["item_count"].'</div>';
                }

                $output .= '
                  </div>';

                // build a tooltip object for this item
                $output .= '
                  <div class="item_tooltip_div" id="tooltip_b'.$coupon["entry"].'" style="left: -129px; top: 42px;">
                    <div>'.get_item_tooltip($i, 0, 0, 0, 0, 0).'</div>
                  </div>';

                $output .= '
                </div>';
              }
              else
              {
                $output .= '
                <br />
                <br />
                <div class="coupon_item">
                  <div>
                    <a href="point_system.php?action=view_bag&amp;bag_id='.($coupon["item_id"]*-1).'" onmousemove="oldtoolTip(\''.lang("points", "prize_bag").'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()">
                      <img src="'.get_item_icon(1725).'" alt="" />
                    </a>
                  </div>
                </div>';
              }
            }

            if ( $coupon["raffle_id"] != 0 )
            {
              // find out how many entries per user the raffle allows and whether the raffle is enabled
              if ( $coupon["raffle_id"] != -1 )
              {
                $query = "SELECT tickets_per_user, enabled FROM point_system_raffles WHERE entry='".$coupon["raffle_id"]."'";
                $result = $sql["mgr"]->query($query);
                $result = $sql["mgr"]->fetch_assoc($result);
                $per_user = $result["tickets_per_user"];
                $raffle_enabled = $result["enabled"];

                // if tickets_per_user is -1 then its unlimited, fake it with a reasonably high number
                $per_user = 999999999;
              }
              else
              {
                // if it allows any raffle, then fake it
                $per_user = 999999999;
                $raffle_enabled = 1;
              }

              if ( $raffle_enabled )
              {
                // find out how many time we've entered
                $query = "SELECT COUNT(*) FROM point_system_raffle_tickets WHERE raffle='".$coupon["raffle_id"]."' AND user='".$user_id."'";
                $result = $sql["mgr"]->query($query);
                $result = $sql["mgr"]->fetch_assoc($result);
                $tickets = $result["COUNT(*)"];

                // if we haven't already purchased the maximum number of tickets
                // or the raffle allows purchase of tickets from any raffle
                if ( ( $tickets < $per_user ) || ( $coupon["raffle_id"] == -1 ) )
                {
                  if ( $coupon["redemption_option"] == 0 )
                  {
                    $output .= '
                <br />
                <br />
                <span>'.lang("index", "and_raffle").'</span>';
                  }
                  else
                  {
                    $output .= '
                <br />
                <br />
                <span>'.lang("index", "or_raffle").'</span>';
                  }
                }
              }
            }

            $output .= '
              </div>';
          }

          if ( $coupon["credits"] < 0 )
          {
            $message = lang("points", "coupon_cost_credits");
            $message = str_replace("%1", ($coupon["credits"] * -1), $message);

            $output .= '
              <div class="div_lined_line right_text">
                <span>'.$message.'</span>
              </div>';
          }

          if ( $coupon["money"] < 0 )
          {
            $coupon["money"] = $coupon["money"] * -1;

            $coupon_money = $coupon["money"];
            $coupon_money = str_pad($coupon_money, 4, "0", STR_PAD_LEFT);
            $cg = substr($coupon_money,  0, -4);
            if ( $cg == "" )
              $cg = 0;
            $cs = substr($coupon_money, -4,  2);
            if ( ( $cs == "" ) || ( $cs == "00" ) )
              $cs = 0;
            $cc = substr($coupon_money, -2);
            if ( ( $cc == "" ) || ( $cc == "00" ) )
              $cc = 0;

            $coupon_money_display = '
              <span>'.$cg.'</span>
              <img src="img/gold.gif" alt="" align="middle" />
              <span>'.$cs.'</span>
              <img src="img/silver.gif" alt="" align="middle" />
              <span>'.$cc.'</span>
              <img src="img/copper.gif" alt="" align="middle" />';

            $message = lang("points", "coupon_cost_money");
            $message = str_replace("%1", $coupon_money_display, $message);

            $output .= '
              <div class="div_lined_line right_text">
                <span>'.$message.'</span>
              </div>';
          }

          $output .= '
              <div class="div_lined_line right_text">
                <a href="point_system.php?action=redeem_coupon&amp;coupon_id='.$coupon["entry"].'">
                  <img src="img/star.png" width="16" height="16" alt="" />
                  <span>&nbsp;'.lang("index", "use_coupon").'</span>
                </a>
              </div>';

          $output .= '
              <br />';
        }
      }

      if ( $sql["mgr"]->num_rows($coupon_result) )
        $output = substr($output, 0, strlen($output) - 20);

      $output .= '
            </div>';
    }
  }

  // GM Tickets
  $start_m = ( ( isset($_GET["start_m"]) ) ? $sql["char"]->quote_smart($_GET["start_m"]) : 0 );
  if ( !is_numeric($start_m) )
    $start_m = 0;

  if ( $core == 1 )
    $all_record_m = $sql["char"]->result($sql["char"]->query("SELECT COUNT(*) FROM gm_tickets WHERE deleted=0"), 0);
  elseif ( $core == 2 )
    $all_record_m = $sql["char"]->result($sql["char"]->query("SELECT COUNT(*) FROM character_ticket"), 0);
  else
    $all_record_m = $sql["char"]->result($sql["char"]->query("SELECT COUNT(*) FROM gm_tickets WHERE closedBy=0"), 0);

  // show gm tickets
  $output .= '
            <br />';

  if ( $user_lvl >= $action_permission["insert"] )
  {
    if ( $all_record_m )
    {
      $output .= '
            <div class="div_lined">
              <div class="div_lined_head">
                <span>'.lang("index", "tickets").'</span>
              </div>';

      if ( $core == 1 )
      {
        $result = $sql["char"]->query("SELECT ticketid, level, message, name, deleted,
          timestamp, gm_tickets.playerGuid, acct
          FROM gm_tickets
            LEFT JOIN characters ON characters.guid=gm_tickets.playerGuid
          ORDER BY ticketid DESC LIMIT ".$start_m.", 3");
      }
      elseif ( $core == 2 )
      {
        $result = $sql["char"]->query("SELECT character_ticket.ticket_id AS ticketid, characters.level,
          ticket_text AS message, characters.name, UNIX_TIMESTAMP(ticket_lastchange) AS timestamp,
          character_ticket.guid AS playerGuid, account AS acct
          FROM character_ticket
            LEFT JOIN characters ON characters.guid=character_ticket.guid
          ORDER BY ticketid DESC LIMIT ".$start_m.", 3");
      }
      else
      {
        $result = $sql["char"]->query("SELECT gm_tickets.guid AS ticketid, characters.level, message,
          gm_tickets.name, closedBy AS deleted, lastModifiedTime AS timestamp, gm_tickets.guid AS playerGuid, account AS acct
          FROM gm_tickets
            LEFT JOIN characters ON characters.guid=gm_tickets.guid
          ORDER BY ticketid DESC LIMIT ".$start_m.", 3");
      }

      while ( $post = $sql["char"]->fetch_assoc($result) )
      {
        if ( !$post["deleted"] )
        {
          if ( $core == 1 )
            $login_result = $sql["logon"]->query("SELECT * FROM accounts WHERE acct='".$post["acct"]."'");
          else
            $login_result = $sql["logon"]->query("SELECT *, username AS login FROM account WHERE id='".$post["acct"]."'");

          $login = $sql["logon"]->fetch_assoc($login_result);
          $gm_result = $sql["mgr"]->query("SELECT SecurityLevel FROM config_accounts WHERE Login='".$login["login"]."'");
          $gm = $sql["mgr"]->fetch_assoc($gm_result);
          $gm = $gm["SecurityLevel"];

          if ( ( $user_lvl > 0 ) && ( ( $user_lvl >= gmlevel($gm) ) || ( $user_lvl == $action_permission["delete"] ) ) )
          {
            $output .= '
              <div class="div_lined_line left_text">
                <a href="char.php?id='.$post["playerGuid"].'">
                  <span onmousemove="oldtoolTip(\''.htmlspecialchars($login["username"]).' ('.id_get_gm_level($gm).')'.'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()">'.htmlentities($post["name"], ENT_COMPAT, $site_encoding).'</span>
                </a>
              </div>
              <div class="div_lined_line left_text">
                <span>'.htmlspecialchars($post["message"]).'</span>
              </div>
              <div class="div_lined_line right_text">';
          }

          $output .= '
                <span>'.lang("index", "submitted").": ".date('G:i:s m-d-Y', $post["timestamp"]).'</span>';

          $output .= '
              </div>
              <div class="div_lined_line right_text">';

          if ( $user_lvl >= $action_permission["update"] )
          {
            $output .= '
                <a href="ticket.php?action=edit_ticket&amp;error=4&amp;id='.$post["ticketid"].'">
                  <img src="img/edit.png" width="16" height="16" alt="" />
                </a>';
          }

          $output .= '
              </div>
              <br />';
        }
      }

      $output .= '
            </div>';
    }
  }

  // Character Changes
  // count pending character changes
  $char_change_count = $sql["mgr"]->result($sql["mgr"]->query("SELECT COUNT(*) FROM char_changes"), 0);

  // show pending character changes
  $output .= '
            <br />';

  if ( $user_lvl >= $action_permission["update"] )
  {
    if ( $char_change_count )
    {
      $output .= '
            <div class="div_lined">
              <div class="div_lined_head">
                <span>'.lang("index", "pendingchanges").'</span>
              </div>';

      $result = $sql["mgr"]->query("SELECT * FROM char_changes");

      while ( $change = $sql["mgr"]->fetch_assoc($result) )
      {
        if ( $core == 1 )
          $change_char_query = "SELECT * FROM characters WHERE guid='".$change["guid"]."'";
        else
          $change_char_query = "SELECT *, account AS acct FROM characters WHERE guid='".$change["guid"]."'";

        $change_char = $sql["char"]->fetch_assoc($sql["char"]->query($change_char_query));

        if ( $core == 1 )
          $change_acct_query = "SELECT * FROM accounts WHERE acct='".$change_char["acct"]."'";
        else
          $change_acct_query = "SELECT *, username AS login FROM account WHERE id='".$change_char["acct"]."'";

        $change_acct = $sql["logon"]->fetch_assoc($sql["logon"]->query($change_acct_query));
        if ( isset($change["new_name"]) )
        {
          // Localization
          $namechange = lang("xname", "playerhasreq");
          $namechange = str_replace("%1", htmlspecialchars($change_acct["login"]), $namechange);
          $namechange = str_replace("%2", htmlspecialchars($change_char["name"]), $namechange);
          $namechange = str_replace("%3", htmlspecialchars($change["new_name"]), $namechange);

          $output .= '
              <div class="div_lined_line left_text large">
                <span>'.$namechange.'</span>';
        }

        if ( isset($change["new_race"]) )
        {
          // Localization
          $racechange = lang("xrace", "playerhasreq");
          $racechange = str_replace("%1", htmlspecialchars($change_acct["login"]), $racechange);
          $racechange = str_replace("%2", htmlspecialchars($change_char["name"]), $racechange);
          $racechange = str_replace("%3", char_get_race_name($change["new_race"]), $racechange);

          $output .= '
              <div class="div_lined_line left_text large">
                <span>'.$racechange.'</span>';
        }

        if ( isset($change["new_acct"]) )
        {
          if ( $core == 1 )
            $new_acct_query = "SELECT login FROM accounts WHERE acct='".$change["new_acct"]."'";
          else
            $new_acct_query = "SELECT username AS login FROM account WHERE id='".$change["new_acct"]."'";
          $new_acct_result = $sql["logon"]->query($new_acct_query);
          $new_acct_result = $sql["logon"]->fetch_assoc($new_acct_result);
          $new_acct_name = $new_acct_result["login"];

          // Localization
          $acctchange = lang("xacct", "playerhasreq");
          $acctchange = str_replace("%1", htmlspecialchars($change_acct["login"]), $acctchange);
          $acctchange = str_replace("%2", htmlspecialchars($change_char["name"]), $acctchange);
          $acctchange = str_replace("%3", $new_acct_name, $acctchange);

          $output .= '
              <div class="div_lined_line left_text large">
                <span>'.$acctchange.'</span>';
        }

        if ( $change_char["online"] )
        {
           $output .= '
                <br />
                <br />
                <img src="img/aff_warn.gif" alt="warn" />
                <span class="error">'.lang("xname", "online").'</span>';
        }

        $output .= '
              </div>';

        if ( isset($change["new_name"]) )
          $file = "change_char_name.php";
        elseif ( isset($change["new_race"]) )
          $file = "change_char_race.php";
        else
          $file = "change_char_account.php";

        $output .= '
              <div class="div_lined_line right_text">
                <a href="'.$file.'?action=denied&amp;guid='.$change["guid"].'">
                  <img src="img/cross.png" width="12" height="12" alt="" />
                </a>';

        if ( !$change_char["online"] )
        {
          $output .= '
                <a href="'.$file.'?action=approve&amp;guid='.$change["guid"].'">
                  <img src="img/aff_tick.png" width="14" height="14" alt="" />
                </a>';
        }

        $output .= '
              </div>
              <br />';
      }

      $output .= '
            </div>';
    }
  }

  //print online chars
  if ( $online && ( $user_lvl >= $player_online ) )
  {
    //==========================$_GET and SECURE=================================
    $start = ( ( isset($_GET["start"]) ) ? $sql["char"]->quote_smart($_GET["start"]) : 0 );
    if ( !is_numeric($start) )
      $start = 0;

    $order_by = ( ( isset($_GET["order_by"]) ) ? $sql["char"]->quote_smart($_GET["order_by"]) : "name" );
    if ( !preg_match("/^[_[:lower:]]{1,12}$/", $order_by) )
      $order_by = "name";

    $dir = ( ( isset($_GET["dir"]) ) ? $sql["char"]->quote_smart($_GET["dir"]) : 1 );
    if ( !preg_match("/^[01]{1}$/", $dir) )
      $dir = 1;

    $order_dir = ( ( $dir ) ? "ASC" : "DESC" );
    $dir = ( ( $dir ) ? 0 : 1 );
    //==========================$_GET and SECURE end=============================

    if ( $order_by === "mapid" )
    {
      $order_by = "mapid, zoneid ";
      $order_hold = "mapid";
    }
    elseif ( $order_by === "zoneid" )
    {
      $order_by = "zoneid, mapid ";
      $order_hold = "zoneid";
    }
    else
      $order_hold = $order_by;

    $order_side = "";
    if ( !( $user_lvl || $server[$realm_id]["both_factions"] ) )
    {
      if ( $core == 1 )
        $result = $sql["char"]->query("SELECT race FROM characters WHERE acct=".$user_id."
          AND SUBSTRING_INDEX(SUBSTRING_INDEX(playedtime, ' ', 2), ' ', -1)=(SELECT MAX(SUBSTRING_INDEX(SUBSTRING_INDEX(playedtime, ' ', 2), ' ', -1)) FROM characters WHERE acct=".$user_id.") LIMIT 1");
      else
        $result = $sql["char"]->query("SELECT race FROM characters WHERE account=".$user_id."
          AND totaltime=(SELECT MAX(totaltime) FROM characters WHERE account=".$user_id.") LIMIT 1");

      if ( $sql["char"]->num_rows($result) )
        $order_side = ( ( in_array($sql["char"]->result($result, 0), array(2, 5, 6, 8, 10)) ) ? " AND race IN (2, 5, 6, 8, 10) " : " AND race IN (1, 3, 4, 7, 11) " );
    }

    if ( $core == 1 )
    {
      $result = $sql["char"]->query("SELECT guid, name, race, class, zoneid, mapid, level, characters.acct, gender,
                            CAST( SUBSTRING_INDEX( SUBSTRING_INDEX( data, ';', ".(PLAYER_FIELD_HONOR_CURRENCY+1)." ), ';', -1 ) AS UNSIGNED ) AS highest_rank, lastip
                            FROM characters
                              LEFT JOIN `".$logon_db["name"]."`.accounts ON characters.acct=`".$logon_db["name"]."`.accounts.acct
                            WHERE characters.online=1 ".$order_side." ORDER BY ".$order_by." ".$order_dir." LIMIT ".$start.", ".$itemperpage);
    }
    elseif ( $core == 2 ) // this_is_junk: MaNGOS doesn't store player latency
    {
      $result = $sql["char"]->query("SELECT guid, name, race, class, zone AS zoneid, map AS mapid, level, account AS acct, gender,
                            totalHonorPoints AS highest_rank, last_ip AS lastip
                            FROM characters
                              LEFT JOIN `".$logon_db["name"]."`.account ON characters.account=`".$logon_db["name"]."`.account.id
                            WHERE characters.online=1 ".$order_side." ORDER BY ".$order_by." ".$order_dir." LIMIT ".$start.", ".$itemperpage);
    }
    else
    {
      $result = $sql["char"]->query("SELECT characters.guid, characters.name, race, class, zone AS zoneid, map AS mapid, level, account AS acct, gender,
                            totalHonorPoints AS highest_rank, latency, last_ip AS lastip, guild.name AS gname
                            FROM characters
                              LEFT JOIN `".$logon_db["name"]."`.account ON characters.account=`".$logon_db["name"]."`.account.id
                              LEFT JOIN guild_member ON characters.guid=guild_member.guid
                              LEFT JOIN guild ON guild_member.guildid=guild.guildid
                            WHERE characters.online=1 ".$order_side." ORDER BY ".$order_by." ".$order_dir." LIMIT ".$start.", ".$itemperpage);
    }

    $total_online = $sql["char"]->result($sql["char"]->query("SELECT count(*) FROM characters WHERE online= 1"), 0);

    $output .= '
            <div class="div_lined center_text">
              <span class="bold">'.lang("index", "tot_users_online").': '.$total_online.'</span>
            </div>';

    if ( $total_online )
    {
      $output .= '
            <table class="lined">
              <tr>
                <td colspan="'.(9-$showcountryflag).'" align="right" class="hidden" style="width: 25%;">';
      $output .= generate_pagination("index.php?start_m=".$start_m."&amp;order_by=".$order_hold."&amp;dir=".( ( $dir ) ? 0 : 1 ), $total_online, $itemperpage, $start);
      $output .= '
                </td>
              </tr>
              <tr>
                <th style="width: 15%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=name&amp;dir='.$dir.'"'.( ( $order_by === "name" ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "name").'</a>
                </th>
                <th style="width: 1%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=race&amp;dir='.$dir.'"'.( ( $order_by === "race" ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "race").'</a>
                </th>
                <th style="width: 1%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=class&amp;dir='.$dir.'"'.( ( $order_by === "class" ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "class").'</a>
                </th>
                <th style="width: 5%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=level&amp;dir='.$dir.'"'.( ( $order_by === "level" ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "level").'</a>
                </th>
                <th style="width: 1%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=highest_rank&amp;dir='.$dir.'"'.( ( $order_by === "highest_rank" ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "rank").'</a>
                </th>
                <th style="width: 15%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=gname&amp;dir='.$dir.'"'.( ( $order_by === "gname" ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "guild").'</a>
                </th>
                <th style="width: 20%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=mapid&amp;dir='.$dir.'"'.( ( $order_by === "mapid, zoneid " ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "map").'</a>
                </th>
                <th style="width: 25%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=zoneid&amp;dir='.$dir.'"'.( ( $order_by === "zoneid, mapid " ) ? ' class="'.$order_dir.'"' : '' ).'>'.lang("index", "zone").'</a>
                </th>';

      if ( $core == 1 )
      {
        $output .= '
                <th style="width: 25%;">
                  <span>'.lang("index", "area").'</span>
                </th>';
      }
    
      // this_is_junk: MaNGOS doesn't store player latency
      if ( $core != 2 )
      {
        if ( !$hide_plr_latency )
        {
          // this_is_junk: Trinity is the only core which can sort by Player Latency
          if ( $core == 3 )
          {
            $output .= '
                <th style="width: 1%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=latency&amp;dir='.$dir.'"'.( ( $order_by === "latency" ) ? ' class="'.$order_dir.'"' : '' ).'>
                    <img src="img/time.png" onmousemove="oldtoolTip(\''.lang("index", "latency").'\',\'old_item_tooltip\')" onmouseout="oldtoolTip()" alt=""'.( ( $order_by === "latency" ) ? '' : ' style="position: relative; top: 7px;"' ).' />
                  </a>
                </th>';
          }
          else
          {
            $output .= '
                <th style="width: 1%;">
                  <img src="img/time.png" onmousemove="oldtoolTip(\''.lang("index", "latency").'\',\'old_item_tooltip\')" onmouseout="oldtoolTip()" alt="" style="position: relative; top: 7px;" />
                </th>';
          }
        }
      }

      if ( $showcountryflag )
      {
        require_once "libs/misc_lib.php";

        $output .= '
                <th style="width: 1%;">
                  <a href="index.php?start='.$start.'&amp;start_m='.$start_m.'&amp;order_by=lastip&amp;dir='.$dir.'"'.( ( $order_by === "lastip" ) ? ' class="'.$order_dir.'"' : '' ).'>
                    <img src="img/world.png" onmousemove="oldtoolTip(\''.lang("global", "country").'\',\'old_item_tooltip\')" onmouseout="oldtoolTip()" alt=""'.( ( $order_by === "lastip" ) ? '' : ' style="position: relative; top: 7px;"' ).' />
                  </a>
                </th>';
      }

      $output .= '
              </tr>';
    }

    while ( $char = $sql["char"]->fetch_assoc($result) )
    {
      if ( $core == 1 )
        $ca_query = "SELECT accounts.login AS name FROM `".$logon_db["name"]."`.accounts LEFT JOIN `".$corem_db["name"]."`.config_accounts ON accounts.login=`".$corem_db["name"]."`.config_accounts.Login COLLATE utf8_unicode_ci WHERE acct='".$char["acct"]."'";
      else
        $ca_query = "SELECT *, username AS name FROM `".$logon_db["name"]."`.account LEFT JOIN `".$corem_db["name"]."`.config_accounts ON account.username=`".$corem_db["name"]."`.config_accounts.Login WHERE id='".$char["acct"]."'";
        
      $ca_result = $sql["mgr"]->query($ca_query);
      $char_acct = $sql["mgr"]->fetch_assoc($ca_result);

      $gm = $char_acct["SecurityLevel"];
      if ( !isset($gm) )
        $gm = 0;
	
	    if ( $core == 1 )
        $guild_id = $sql["char"]->result($sql["char"]->query("SELECT guildid FROM guild_data WHERE playerid='".$char["guid"]."'"), 0);
      else
        $guild_id = $sql["char"]->result($sql["char"]->query("SELECT guildid FROM guild_member WHERE guid='".$char["guid"]."'"), 0);
      
      if ( $core == 1 )
        $guild_name_query = "SELECT guildName FROM guilds WHERE guildid='".$guild_id."'";
      else
        $guild_name_query = "SELECT name AS guildName FROM guild WHERE guildid='".$guild_id."'";
        
      $guild_name_result = $sql["char"]->query($guild_name_query);
      $guild_name = $sql["char"]->fetch_assoc($guild_name_result);
      $guild_name = $guild_name["guildName"];

      $output .= '
              <tr>
                <td>';

      if ( ( $user_lvl > 0 ) && ( ( $user_lvl >= gmlevel($gm) ) || ( $user_lvl == $action_permission["delete"] ) ) )
      {
        $output .= '
                  <a href="char.php?id='.$char["guid"].'">
                    <span onmousemove="oldtoolTip(\''.htmlspecialchars($char_acct["name"]).' ('.id_get_gm_level($gm).')'.'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()">'.htmlentities($char["name"], ENT_COMPAT, $site_encoding).'</span>
                  </a>';
      }
      else
      {
        $output .='
                  <span>'.htmlentities($char["name"], ENT_COMPAT, $site_encoding).'</span>';
      }

      $output .= '
                </td>
                <td>
                  <img src="img/c_icons/'.$char["race"].'-'.$char["gender"].'.gif" onmousemove="oldtoolTip(\''.char_get_race_name($char["race"]).'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()" alt="" />
                </td>
                <td>
                  <img src="img/c_icons/'.$char["class"].'.gif" onmousemove="oldtoolTip(\''.char_get_class_name($char["class"]).'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()" alt="" />
                </td>
                <td>'.char_get_level_color($char["level"]).'</td>
                <td>
                  <span onmouseover="oldtoolTip(\''.char_get_pvp_rank_name($char["highest_rank"], char_get_side_id($char["race"])).'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()" id="index_delete_cursor"><img src="img/ranks/rank'.char_get_pvp_rank_id($char["highest_rank"], char_get_side_id($char["race"])).'.gif" alt="" /></span>
                </td>
                <td>
                  <a href="guild.php?action=view_guild&amp;error=3&amp;id='.$guild_id.'">'.htmlentities($guild_name, ENT_COMPAT, $site_encoding).'</a>
                </td>
                <td>
                  <span onmousemove="oldtoolTip(\'MapID:'.$char["mapid"].'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()">'.get_map_name($char["mapid"]).'</span>
                </td>
                <td>
                  <span onmousemove="oldtoolTip(\'ZoneID:'.$char["zoneid"].'\', \'old_item_tooltip\')" onmouseout="oldtoolTip()">'.get_zone_name($char["zoneid"]).'</span>
                </td>';

      // display player area, if available
      if ( $core == 1 )
      {
        for ( $i = 0; $i < count($stats["plrs_area"]); $i++ )
        {
          if ($stats["plrs_area"][$i][0] == $char["name"])
          {
            $output .= '
                <td>
                  <span onmousemove="toolTip(\'AreaID:'.$stats["plrs_area"][$i][1].'\', \'item_tooltip\')" onmouseout="toolTip()">'.get_zone_name($stats["plrs_area"][$i][1]).'</span>
                </td>';
          }

          if ( !isset( $stats["plrs_lat"][$i][1] ) )
          {
            $output .= '
                <td>-</td>';
          }
        }
      }
      
      // display player latency, if enabled, and if available
      if ( !$hide_plr_latency )
      {
        if ( $core == 1 )
        {
          for ( $i = 0; $i < count($stats["plrs_lat"]); $i++ )
          {
            if ( $stats["plrs_lat"][$i][0] == $char["name"] )
            {
              $output .= '
                <td>'.$stats["plrs_lat"][$i][1].'</td>';
            }
            if ( !isset($stats["plrs_lat"][$i][1]) )
              $output .= '
                <td>-</td>';
          }
        }
        else
        {
          // this_is_junk: MaNGOS doesn't store player latency
          if ( $core == 3 )
            $output .= '
                <td>'.$char["latency"].'</td>';
        }
      }

      if ( $showcountryflag )
      {
        $country = misc_get_country_by_ip($char["lastip"]);
        $output .='
                <td>'.( ( $country["code"] ) ? '<img src="img/flags/'.$country["code"].'.png" onmousemove="oldtoolTip(\''.($country["country"]).( ( $user_lvl >= $action_permission["update"] ) ? '<br />'.$country["actualip"] : '' ).'\',\'old_item_tooltip\')" onmouseout="oldtoolTip()" alt="" />' : '-' ).'</td>';
      }
      $output .='
              </tr>';
    }

    if ( $total_online )
    {
      $output .= '
              <tr>';
      $output .= '
                <td colspan="'.(9-$showcountryflag).'" align="right" class="hidden" style="width: 25%;">';
      $output .= generate_pagination("index.php?start_m=".$start_m."&amp;order_by=".$order_by."&amp;dir=".( ( $dir ) ? 0 : 1 ), $total_online, $itemperpage, $start);
      $output .= '
                </td>
              </tr>
            </table>';
    }
    $output .= '
            <br />';

    unset($total_online);
  }
}

function do_raffles()
{
  global $sql, $core;

  // get any raffles that need to be completed
  $r_query = "SELECT * FROM point_system_raffles WHERE enabled=1 AND completed=0 AND drawing <= NOW()";
  $r_result = $sql["mgr"]->query($r_query);

  while ( $raffle = $sql["mgr"]->fetch_assoc($r_result) )
  {
    // first, we make sure this raffle is still ready to process
    $query = "SELECT * FROM point_system_raffles WHERE enabled=1 AND entry='".$raffle["entry"]."'";
    $result = $sql["mgr"]->query($query);

    if ( $sql["mgr"]->num_rows($result) )
    {
      // disable the raffle to make sure no one else tries to process it
      $query = "UPDATE point_system_raffles SET enabled=0 WHERE entry='".$raffle["entry"]."'";
      $result = $sql["mgr"]->query($query);

      // get the entries
      $e_query = "SELECT * FROM point_system_raffle_tickets WHERE raffle='".$raffle["entry"]."'";
      $e_result = $sql["mgr"]->query($e_query);

      // load entries into an array
      $tickets = array();
      while ( $ticket = $sql["mgr"]->fetch_assoc($e_result) )
      {
        $tickets[] = $ticket;
      }

      // randomize
      shuffle($tickets);

      // first entry is winner
      $winner = $tickets[0]["user"];

      // record the winner to the raffle
      $query = "UPDATE point_system_raffles SET winner='".$winner."', completed=1 WHERE entry='".$raffle["entry"]."'";
      $result = $sql["mgr"]->query($query);

      // issue a coupon for the raffle prize(s)
      $query = "INSERT INTO point_system_coupons (target, credits, money, item_id, item_count, title, text, usage_limit, redemption_option, raffle_id, enabled) VALUES ('".$winner."', '".$raffle["credits"]."', '".$raffle["money"]."', '".$raffle["item_id"]."', '".$raffle["item_count"]."', '".lang("points", "prize_coupon_title")."', '".str_replace("%1", $raffle["title"], lang("points", "prize_coupon_text"))."', '1', '0', '0', '1')";
      $result = $sql["mgr"]->query($query);

      // create an announcement server message
      // get the winner's name
      if ( $core == 1 )
        $query = "SELECT login AS username FROM accounts WHERE acct='".$winner."'";
      else
        $query = "SELECT username FROM account WHERE id='".$winner."'";

      $result = $sql["logon"]->query($query);
      $acct = $sql["logon"]->fetch_assoc($result);

      $query = "SELECT ScreenName FROM config_accounts WHERE Login='".$acct["username"]."'";
      $result = $sql["mgr"]->query($query);
      $winner_name = $sql["mgr"]->fetch_assoc($result);
      $winner_name = $winner_name["ScreenName"];

      // build the congrats message
      $gratz = lang("points", "congrats");
      $gratz = str_replace("%1", $raffle["title"], $gratz);
      $gratz = str_replace("%2", $winner_name, $gratz);

      $text = '[center][color="white"][b][size="16px"]'.$gratz.'[/size][/b][/color][/center]';

      // post it
      $query = "INSERT INTO motd (Message, Created, Created_By, Enabled) VALUES ('".$text."', NOW(), '".$raffle["announce_acct"]."', '1')";
      $result = $sql["mgr"]->query($query);
    }
  }
}

function do_contests()
{
  global $sql, $core, $characters_db;

  $contests_query = "SELECT *, UNIX_TIMESTAMP(date_issued) AS issue_stamp FROM point_system_achievement_contests WHERE enabled='1' AND winner_guid='0' AND expiration>NOW()";
  $contests_result = $sql["mgr"]->query($contests_query);

  // luckily, the Achievement tables for ArcEmu and Trinity are based on MaNGOS
  // so we need fewer queries for this
  while ( $contest = $sql["mgr"]->fetch_assoc($contests_result) )
  {
    // get the characters database for this contest
    $sqlc = new SQL;
    $sqlc->connect($characters_db[$contest["realm"]]["addr"], $characters_db[$contest["realm"]]["user"], $characters_db[$contest["realm"]]["pass"], $characters_db[$contest["realm"]]["name"], $characters_db[$contest["realm"]]["encoding"]);

    if ( $contest["after_only"] )
      $achieve_query = "SELECT * FROM character_achievement WHERE achievement='".$contest["achievement"]."' AND `date`>='".$contest["issue_stamp"]."' ORDER BY `date` ASC";
    else
      $achieve_query = "SELECT * FROM character_achievement WHERE achievement='".$contest["achievement"]."' ORDER BY `date` ASC";

    $achieve_result = $sqlc->query($achieve_query);

    // set up a variable for the winner so we only have to do the reward code once
    $winner = 0;

    // now, find the winner
    if ( $sqlc->num_rows($achieve_result) > 1 )
    {
      // this triggers if multiple people unlock the achievement before we check
      // for winner.  We have to find the first

      // start by dumping all the unlocks, but skip any that ARE NOT duplicates
      $unlockers = array();
      while ( $row = $sqlc->fetch_assoc($achieve_result) )
      {
        if ( is_array($unlockers[0]) )
        {
          if ( $row["date"] == $unlockers[0]["date"] )
            $unlockers[] = $row;
        }
        else
          $unlockers[] = $row; // we need the first one
      }

      // see how many unlockers we have left
      if ( count($unlockers) > 1 )
      {
        // we have multiples, 'randomly' pick a winner
        $winners_row = rand(1, count($unlockers)) - 1;
        $winner = $unlockers[$winners_row]["guid"];
      }
      else
      {
        // otherwise, we had only one 'first' unlocker
        $winner = $unlockers[0]["guid"];
      }
    }
    elseif ( $sqlc->num_rows($achieve_result) == 1 )
    {
      // single player unlocked the achievement
      $unlocker = $sql["char"]->fetch_assoc($achieve_result);
      $winner = $unlocker["guid"];
    }
    // otherwise, no winner

    // reward
    if ( $winner != 0 )
    {
      // first, set the contest to reflect who won
      $query = "UPDATE point_system_achievement_contests SET winner_guid='".$winner."' WHERE entry='".$contest["entry"]."'";
      $sql["mgr"]->query($query);

      // winner is character guid.  Prize Bags and Coupons are account level.
      // we need the winner's account
      if ( $core == 1 )
        $winner_acct_query = "SELECT acct AS account, name FROM characters WHERE guid='".$winner."'";
      else
        $winner_acct_query = "SELECT account, name FROM characters WHERE guid='".$winner."'";

      $winner_acct_result = $sqlc->query($winner_acct_query);
      $winner_acct_result = $sqlc->fetch_assoc($winner_acct_result);
      $winner_acct = $winner_acct_result["account"];
      $winner_name = $winner_acct_result["name"];

      if ( $contest["prize_bag"] > 0 )
      {
        // update the prize bag to reflect its new owner
        $reward_query = "UPDATE point_system_prize_bags SET owner='".$winner_acct."'";
      }
      else
      {
        // update the coupon to reflect its new owner
        $reward_query = "UPDATE point_system_coupons SET target='".$winner_acct."'";
      }

      // update the prize bag / coupon record
      $sql["mgr"]->query($reward_query);

      // get the achievement's name
      $ach_query = "SELECT * FROM achievement WHERE id='".$contest["achievement"]."'";
      $ach_result = $sql["dbc"]->query($ach_query);
      $ach_result = $sql["dbc"]->fetch_assoc($ach_result);

      // create Server Message
      $msg = $contest["announce_msg"];
      $msg = str_replace("%1", $winner_name, $msg);
      $msg = str_replace("%2", $ach_result["name"], $msg);
      
      // insert message
      if ( $contest["announce"] == 0 )
        $announce_query = "INSERT INTO motd (Message, Created, Enabled) VALUES ('".$msg."', NOW(), 1)";
      else
        $announce_query = "INSERT INTO motd (Message, Created, Target, Enabled) VALUES ('".$msg."', NOW(), '".$winner_acct."', 1)";

      $sql["mgr"]->query($announce_query);
    }
  }
}

//#############################################################################
// MAIN
//#############################################################################

$output .= '
        <div class="bubble">';

main();

unset($action_permission);

require_once "footer.php";


?>
