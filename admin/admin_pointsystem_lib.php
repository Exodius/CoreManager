<?php
/*
    CoreManager, PHP Front End for ArcEmu, MaNGOS, and TrinityCore
    Copyright (C) 2012-2014  CoreManager Project
    Copyright (C) 2009-2012  ArcManager Project (in /admin.php)

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

function pointsystem()
{
  global $output, $corem_db, $logon_db, $get_timezone_type, $core;

  // we need $core to be set
  if ( $core == 0 )
    $core = detectcore();

  $sqlm = new SQL;
  $sqlm->connect($corem_db["addr"], $corem_db["user"], $corem_db["pass"], $corem_db["name"], $corem_db["encoding"]);

  $subsection = ( ( isset($_GET["subsection"]) ) ? $sqlm->quote_smart($_GET["subsection"]) : 1 );

  $output .= '
        <table id="sidebar">
          <tr>
            <td '.( ( $subsection == "basic" ) ? 'class="current"' : '' ).'>
              <a href="admin.php?section=pointsystem&amp;subsection=basic">'.lang("admin", "basic").'</a>
            </td>
          </tr>
          <tr>
            <td '.( ( $subsection == "coupons" ) ? 'class="current"' : '' ).'>
              <a href="admin.php?section=pointsystem&amp;subsection=coupons">'.lang("admin", "coupons").'</a>
            </td>
          </tr>
          <tr>
            <td '.( ( $subsection == "raffles" ) ? 'class="current"' : '' ).'>
              <a href="admin.php?section=pointsystem&amp;subsection=raffles">'.lang("admin", "raffles").'</a>
            </td>
          </tr>
          <tr>
            <td '.( ( $subsection == "bags" ) ? 'class="current"' : '' ).'>
              <a href="admin.php?section=pointsystem&amp;subsection=bags">'.lang("admin", "bags").'</a>
            </td>
          </tr>
          <tr>
            <td '.( ( $subsection == "achieve" ) ? 'class="current"' : '' ).'>
              <a href="admin.php?section=pointsystem&amp;subsection=achieve">'.lang("admin", "achieve").'</a>
            </td>
          </tr>
        </table>';

  $sub_action = ( ( isset($_GET["subaction"]) ) ? $_GET["subaction"] : '' );

  if ( isset($_GET["error"]) )
    $output .= '
      <div id="misc_error">';
  else
    $output .= '
      <div id="misc">';

  switch ( $subsection )
  {
    case "basic":
    {
      if ( !$sub_action )
      {
        $allow_fractional = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Credits_Fractional'"));
        $credits_per_recruit = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Credits_Per_Recruit'"));
        $recruit_reward_auto = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Recruit_Reward_Auto'"));
        $initial_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='New_Account_Credits'"));
        $qiv_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='QIV_Credits'"));
        $qiv_gold = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='QIV_Gold'"));
        $uv_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='UV_Credits'"));
        $uv_gold = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='UV_Gold'"));

        // extract gold/silver/copper from single gold number
        $qiv_gold["Value"] = str_pad($qiv_gold["Value"], 4, "0", STR_PAD_LEFT);
        $qiv_g = substr($qiv_gold["Value"],  0, -4);
        if ( $qiv_g == '' )
          $qiv_g = 0;
        $qiv_s = substr($qiv_gold["Value"], -4,  2);
        if ( ( $qiv_s == '' ) || ( $qiv_s == '00' ) )
          $qiv_s = 0;
        $qiv_c = substr($qiv_gold["Value"], -2);
        if ( ( $qiv_c == '' ) || ( $qiv_c == '00' ) )
          $qiv_c = 0;

        // extract gold/silver/copper from single gold number
        $uv_gold["Value"] = str_pad($uv_gold["Value"], 4, "0", STR_PAD_LEFT);
        $uv_g = substr($uv_gold["Value"],  0, -4);
        if ( $uv_g == '' )
          $uv_g = 0;
        $uv_s = substr($uv_gold["Value"], -4,  2);
        if ( ( $uv_s == '' ) || ( $uv_s == '00' ) )
          $uv_s = 0;
        $uv_c = substr($uv_gold["Value"], -2);
        if ( ( $uv_c == '' ) || ( $uv_c == '00' ) )
          $uv_c = 0;

        $name_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Name_Change_Credits'"));
        $race_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Race_Change_Credits'"));
        $trans_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Transfer_Credits'"));
        $hearth_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Hearthstone_Credits'"));
        $achievement_point_points = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Achievement_Point_Points'"));
        $achievement_point_credits = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM config_misc WHERE `Key`='Achievement_Point_Credits'"));

        $output .= '
        <form action="admin.php" method="get" id="form">
          <div>
            <input type="hidden" name="section" value="pointsystem" />
            <input type="hidden" name="subaction" value="savepoints" />
            <input type="hidden" name="subsection" value="basic" />
          </div>
          <table class="simple" id="admin_more">
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "fractional_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "fractional").':</a>
              </td>
              <td>
                <input type="checkbox" name="allowfractional" '.( ( $allow_fractional["Value"] == 1 ) ? 'checked="checked"' : '' ).' />
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "recruitment").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "credits_per_recruit_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "credits_per_recruit").':</a>
              </td>
              <td>
                <input type="text" name="creditsperrecruit" value="'.$credits_per_recruit["Value"].'"/>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "recruit_reward_auto_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "recruit_reward_auto").':</a>
              </td>
              <td>
                <input type="checkbox" name="recruitrewardauto" '.( ( $recruit_reward_auto["Value"] == 1 ) ? 'checked="checked"' : '' ).' />
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "newaccounts").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "initial_credits_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "initial_credits").':</a>
              </td>
              <td>
                <input type="text" name="initialcredits" value="'.$initial_credits["Value"].'"/>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "tool_qiv").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "qiv_credits_per_gold_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "qiv_credits_per_gold").':</a>
              </td>
              <td>
                <input type="text" name="qiv_creditspergold_credits" value="'.$qiv_credits["Value"].'" size="6"/>
                <span>'.lang("admin", "credits").'&nbsp;=&nbsp;</span>
                <input type="text" name="qiv_creditspergold_gold" value="'.$qiv_g.'" size="6"/>
                <img src="../img/gold.gif" alt="gold" />
                <input type="text" name="qiv_creditspergold_silver" value="'.$qiv_s.'" maxlength="2" size="6"/>
                <img src="../img/silver.gif" alt="gold" />
                <input type="text" name="qiv_creditspergold_copper" value="'.$qiv_c.'" maxlength="2" size="6"/>
                <img src="../img/copper.gif" alt="gold" />
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "tool_uv").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "uv_credits_per_gold_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "uv_credits_per_gold").':</a>
              </td>
              <td>
                <input type="text" name="uv_creditspergold_credits" value="'.$uv_credits["Value"].'" size="6"/>
                <span>'.lang("admin", "credits").'&nbsp;=&nbsp;</span>
                <input type="text" name="uv_creditspergold_gold" value="'.$uv_g.'" size="6"/>
                <img src="../img/gold.gif" alt="gold" />
                <input type="text" name="uv_creditspergold_silver" value="'.$uv_s.'" maxlength="2" size="6"/>
                <img src="../img/silver.gif" alt="gold" />
                <input type="text" name="uv_creditspergold_copper" value="'.$uv_c.'" maxlength="2" size="6"/>
                <img src="../img/copper.gif" alt="gold" />
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "tool_name").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "name_credits_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "name_credits").':</a>
              </td>
              <td>
                <input type="text" name="namecredits" value="'.$name_credits["Value"].'"/>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "tool_race").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "race_credits_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "race_credits").':</a>
              </td>
              <td>
                <input type="text" name="racecredits" value="'.$race_credits["Value"].'"/>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "tool_trans").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "trans_credits_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "trans_credits").':</a>
              </td>
              <td>
                <input type="text" name="transcredits" value="'.$trans_credits["Value"].'"/>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "tool_hearth").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
               <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "hearth_credits_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "hearth_credits").':</a>
              </td>
              <td>
                <input type="text" name="hearthcredits" value="'.$hearth_credits["Value"].'"/>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <b>'.lang("admin", "achievement_points").'</b>
              </td>
            </tr>
            <tr>
              <td class="help">
                <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "creds_per_achieve_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "creds_per_achieve").':</a>
              </td>
              <td>
                <input type="text" name="achievement_point_points" value="'.$achievement_point_points["Value"].'" size="6"/>
                <span>'.lang("admin", "points").'&nbsp;=&nbsp;</span>
                <input type="text" name="achievement_point_credits" value="'.$achievement_point_credits["Value"].'" size="6"/>
                <span>'.lang("admin", "credits").'</span>
              </td>
            </tr>
          </table>
          <div>
            <input type="submit" name="save" value="'.lang("admin", "save").'" />
          </div>
        </form>';
      }
      else
      {
        $allow_fractional = ( ( isset($_GET["allowfractional"]) ) ? 1 : 0 );
        $credits_per_recruit = $sqlm->quote_smart($_GET["creditsperrecruit"]);
        $recruit_reward_auto = ( ( isset($_GET["recruitrewardauto"]) ) ? 1 : 0 );
        $initial_credits = $sqlm->quote_smart($_GET["initialcredits"]);
        $qiv_credits = $sqlm->quote_smart($_GET["qiv_creditspergold_credits"]);
        $qiv_gold = $sqlm->quote_smart($_GET["qiv_creditspergold_gold"]);
        $qiv_silver = $sqlm->quote_smart($_GET["qiv_creditspergold_silver"]);
        $qiv_copper = $sqlm->quote_smart($_GET["qiv_creditspergold_copper"]);
        $uv_credits = $sqlm->quote_smart($_GET["uv_creditspergold_credits"]);
        $uv_gold = $sqlm->quote_smart($_GET["uv_creditspergold_gold"]);
        $uv_silver = $sqlm->quote_smart($_GET["uv_creditspergold_silver"]);
        $uv_copper = $sqlm->quote_smart($_GET["uv_creditspergold_copper"]);

        // pad
        $qiv_silver = str_pad($qiv_silver, 2, "0", STR_PAD_LEFT);
        $qiv_copper = str_pad($qiv_copper, 2, "0", STR_PAD_LEFT);
        $uv_silver = str_pad($uv_silver, 2, "0", STR_PAD_LEFT);
        $uv_copper = str_pad($uv_copper, 2, "0", STR_PAD_LEFT);

        // combine
        $qiv_money = $qiv_gold.$qiv_silver.$qiv_copper;
        $uv_money = $uv_gold.$uv_silver.$uv_copper;

        $name_credits = $sqlm->quote_smart($_GET["namecredits"]);
        $race_credits = $sqlm->quote_smart($_GET["racecredits"]);
        $trans_credits = $sqlm->quote_smart($_GET["transcredits"]);
        $hearth_credits = $sqlm->quote_smart($_GET["hearthcredits"]);
        $achievement_point_points = $sqlm->quote_smart($_GET["achievement_point_points"]);
        $achievement_point_credits = $sqlm->quote_smart($_GET["achievement_point_credits"]);

        $result = $sqlm->query("UPDATE config_misc SET Value='".$allow_fractional."' WHERE `Key`='Credits_Fractional'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$credits_per_recruit."' WHERE `Key`='Credits_Per_Recruit'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$recruit_reward_auto."' WHERE `Key`='Recruit_Reward_Auto'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$initial_credits."' WHERE `Key`='New_Account_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$qiv_credits."' WHERE `Key`='QIV_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$qiv_money."' WHERE `Key`='QIV_Gold'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$uv_credits."' WHERE `Key`='UV_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$uv_money."' WHERE `Key`='UV_Gold'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$name_credits."' WHERE `Key`='Name_Change_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$race_credits."' WHERE `Key`='Race_Change_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$trans_credits."' WHERE `Key`='Transfer_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$hearth_credits."' WHERE `Key`='Hearthstone_Credits'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$achievement_point_points."' WHERE `Key`='Achievement_Point_Points'");
        $result = $sqlm->query("UPDATE config_misc SET Value='".$achievement_point_credits."' WHERE `Key`='Achievement_Point_Credits'");

        redirect("admin.php?section=pointsystem&subsection=basic");
      }
      break;
    }
    case "coupons":
    {
      $query = "SELECT * FROM point_system_coupons";
      $result = $sqlm->query($query);

      $coupon_action = 0;
      if ( isset($_GET["editcoupon"]) )
        $coupon_action = "edit";
      if ( isset($_GET["delcoupon"]) )
        $coupon_action = "del";
      if ( isset($_GET["addcoupon"]) )
        $coupon_action = "add";

      $sub_action = ( ( isset($_GET["subaction"]) ) ? $_GET["subaction"] : '' );

      $sqll = new SQL;
      $sqll->connect($logon_db["addr"], $logon_db["user"], $logon_db["pass"], $logon_db["name"], $logon_db["encoding"]);

      if ( !$coupon_action )
      {
        $output .= '
        <form action="admin.php" method="get" id="form">
          <div>
            <input type="hidden" name="section" value="pointsystem" />
            <input type="hidden" name="subsection" value="coupons" />
          </div>
          <table class="simple" id="admin_point_coupon_list">
            <tr>
              <th style="width: 1%;"></th>
              <th style="width: 1%;"></th>
              <th style="width: 3%;">'.lang("admin", "coupon_id").'</th>
              <th style="width: 15%;">'.lang("admin", "coupon_title").'</th>
              <th style="width: 15%;">'.lang("admin", "coupon_target").'</th>
              <th style="width: 15%;">'.lang("admin", "coupon_issued").'</th>
              <!-- th width="15%">'.lang("admin", "coupon_expiration").'</th -->
              <th style="width: 10%;">'.lang("admin", "coupon_credits").'</th>
              <th style="width: 5%;">'.lang("admin", "coupon_money").'</th>
              <th style="width: 5%;">'.lang("admin", "coupon_item").'</th>
              <th style="width: 5%;">'.lang("admin", "coupon_count").'</th>
              <th style="width: 5%;">'.lang("admin", "coupon_raffle").'</th>
              <th style="width: 5%;">'.lang("admin", "coupon_usage").'</th>
              <th style="width: 5%;">'.lang("admin", "enabled").'</th>
            </tr>';
        $color = "#EEEEEE";
        while ( $coupon = $sqlm->fetch_assoc($result) )
        {
          // If the coupon belongs to an Achievement Contest, it overrides any specific target
          if ( $coupon["contest"] == 0 )
          {
            // determine target
            if ( $coupon["target"] != 0 )
            {
              if ( $core == 1 )
                $target_query = "SELECT login FROM accounts WHERE acct='".$coupon["target"]."'";
              else
                $target_query = "SELECT username AS login FROM account WHERE id='".$coupon["target"]."'";

              $target_result = $sqll->query($target_query);
              $target_result = $sqll->fetch_assoc($target_result);
              $target = $target_result["login"];
            }
            else
              $target = lang("admin", "coupon_public");
          }
          else
            $target = '<a href="admin.php?section=pointsystem&subsection=achieve&sel_contest='.$coupon["contest"].'&editcontest=editcontest">'.lang("admin", "coupon_contest_id").': '.$coupon["contest"].'</a>';

          // determine usage
          $usage_query = "SELECT COUNT(*) FROM point_system_coupon_usage WHERE coupon='".$coupon["entry"]."'";
          $usage_result = $sqlm->query($usage_query);
          $usage_result = $sqlm->fetch_assoc($usage_result);
          $times_used = $usage_result["COUNT(*)"];

          if ( $coupon["usage_limit"] > -1 )
            $usage = $times_used."/".$coupon["usage_limit"];
          else
            $usage = $times_used;

          $output .= '
            <tr>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=coupons&amp;sel_coupon='.$coupon["entry"].'&amp;editcoupon=editcoupon" onmouseover="oldtoolTip(\''.lang("admin", "edit").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/edit.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=coupons&amp;sel_coupon='.$coupon["entry"].'&amp;delcoupon=deletecoupon" onmouseover="oldtoolTip(\''.lang("admin", "remove").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/aff_cross.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["entry"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["title"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$target.'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["date_issued"].'</td>
              <!-- td style="background-color:'.$color.'">'.$coupon["expiration"].'</td -->
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["credits"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["money"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["item_id"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["item_count"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$coupon["raffle_id"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$usage.'</td>
              <td style="background-color:'.$color.'; text-align: center;">
                <img src="img/'.( ( $coupon["enabled"] ) ? 'up' : 'down' ).'.gif" alt="" />
              </td>
            </tr>';

          $color = ( ( $color == "#EEEEEE" ) ? "#FFFFFF" : "#EEEEEE" );
        }
        $output .= '
              <tr>
                <td style="background-color:'.$color.'">
                  <a href="admin.php?section=pointsystem&amp;subsection=coupons&amp;addcoupon=addcoupon">
                    <img src="img/add.png" alt="" />
                  </a>
                </td>
                <td style="background-color:'.$color.'" colspan="13">
                  <a href="admin.php?section=pointsystem&amp;subsection=coupons&amp;addcoupon=addcoupon">'.lang("admin", "addcoupon").'</a>
                </td>
              </tr>
          </table>
        </form>';
      }
      else
      {
        if ( $coupon_action == "edit" )
        {
          $coupon_id = $sqlm->quote_smart($_GET["sel_coupon"]);
          if ( is_numeric($coupon_id) )
          {
            if ( !$sub_action )
            {
              $coupon = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM point_system_coupons WHERE `entry`='".$coupon_id."'"));

              // if money is negative, we make a note of that and make it positive
              $neg_money = false;
              if ( $coupon["money"] < 0 )
              {
                $neg_money = true;
                $coupon["money"] = $coupon["money"] * -1;
              }

              // extract gold/silver/copper from single gold number
              $coupon["money"] = str_pad($coupon["money"], 4, "0", STR_PAD_LEFT);
              $coupon_g = substr($coupon["money"],  0, -4);
              if ( $coupon_g == '' )
                $coupon_g = 0;
              $coupon_s = substr($coupon["money"], -4,  2);
              if ( ( $coupon_s == '' ) || ( $coupon_s == '00' ) )
                $coupon_s = 0;
              $coupon_c = substr($coupon["money"], -2);
              if ( ( $coupon_c == '' ) || ( $coupon_c == '00' ) )
                $coupon_c = 0;

              if ( $core == 1 )
              {
                $accounts_query = "SELECT *
                          FROM accounts
                            LEFT JOIN `".$corem_db["name"]."`.config_accounts ON accounts.login=`".$corem_db["name"]."`.config_accounts.Login COLLATE utf8_general_ci";
              }
              else
              {
                $accounts_query = "SELECT *, id AS acct, username AS login
                          FROM account
                            LEFT JOIN `".$corem_db["name"]."`.config_accounts ON account.username=`".$corem_db["name"]."`.config_accounts.Login";
              }

              $accounts_result = $sqll->query($accounts_query);

              $output .= '
              <div>
                <form action="admin.php" method="get" id="form">
                  <fieldset id="admin_edit_coupon">
                    <div>
                      <input type="hidden" name="section" value="pointsystem" />
                      <input type="hidden" name="subsection" value="coupons" />
                      <input type="hidden" name="editcoupon" value="editcoupon" />
                      <input type="hidden" name="subaction" value="savecoupon" />
                      <input type="hidden" name="sel_coupon" value="'.$coupon["entry"].'" />
                      <input type="hidden" name="oldcreation" value="'.$coupon["date_issued"].'" />
                    </div>
                    <table>
                      <tr>
                        <td>'.lang("admin", "coupon_id").':</td>
                        <td>'.$coupon["entry"].'</td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_issued_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_issued").':</a>
                        </td>
                        <td>'.$coupon["date_issued"].'</td>
                      </tr>
                      <tr>
                        <td class="help"><a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_usage_limit_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_usage_limit").'</a>:</td>
                        <td>
                          <input type="text" name="coupon_usage_limit" value="'.$coupon["usage_limit"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "coupon_target").':</td>
                        <td>
                          <select name="coupon_target">
                            <option value="0">'.lang("admin", "coupon_public").'</option>
                            <option value="-1" disabled="disabled">-</option>';

              while ( $row = $sqll->fetch_assoc($accounts_result) )
              {
                $output .= '
                            <option value="'.$row["acct"].'" '.( ( $row["acct"] == $coupon["target"] ) ? 'selected="selected"' : '' ).'>'.$row["ScreenName"].' ('.$row["login"].')</option>';
              }

              $output .= '
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_contest_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_contest").':</a>
                        </td>
                        <td>
                          <input type="text" name="coupon_contest" value="'.$coupon["contest"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <!-- tr>
                        <td width="45%" class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_expiration_tip").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_expiration").'</a>:
                        </td>
                        <td>
                          <input type="text" name="coupon_expiration" value="'.$coupon["expiration"].'" />
                        </td>
                      </tr -->
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <b>'.lang("admin", "coupon_prize").'</b>
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_credits_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_credits").':</a>
                        </td>
                        <td>
                          <input type="text" name="coupon_credits" value="'.$coupon["credits"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "coupon_money").':</td>
                        <td>
                          <input type="text" name="coupon_money_gold" value="'.$coupon_g.'" maxlength="6" size="6" />
                          <img src="../img/gold.gif" alt="gold" />
                          <input type="text" name="coupon_money_silver" value="'.$coupon_s.'" maxlength="2" size="3" />
                          <img src="../img/silver.gif" alt="gold" />
                          <input type="text" name="coupon_money_copper" value="'.$coupon_c.'" maxlength="2" size="3" />
                          <img src="../img/copper.gif" alt="gold" />
                        </td>
                      </tr>
                      <tr>
                        <td class="help"><a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_cost_money_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_cost_money").'</a>:</td>
                        <td>
                          <input type="checkbox" name="coupon_neg_money" value="-1" size="12"'.( ( $neg_money ) ? ' checked="checked"' : '' ).' />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_item_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_item").':</a>
                        </td>
                        <td>
                          <input type="text" name="coupon_item" value="'.$coupon["item_id"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "coupon_count").':</td>
                        <td>
                          <input type="text" name="coupon_count" value="'.$coupon["item_count"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_raffle_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "coupon_raffle").':</a>
                        </td>
                        <td>
                          <input type="text" name="coupon_raffle_id" value="'.$coupon["raffle_id"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "coupon_redemption_option").':</td>
                        <td>
                          <input type="radio" name="coupon_method" value="0" '.( ( $coupon["redemption_option"] == 0 ) ? 'checked="checked"' : '' ).' />'.lang("admin", "coupon_redemption_option_both").'<br />
                          <input type="radio" name="coupon_method" value="1" '.( ( $coupon["redemption_option"] == 1 ) ? 'checked="checked"' : '' ).'/>'.lang("admin", "coupon_redemption_option_single").'
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "coupon_title").':</td>
                        <td>
                          <input type="text" name="coupon_title" value="'.$coupon["title"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "coupon_text").':</td>
                        <td>
                          <textarea name="coupon_text" rows="2" cols="32">'.$coupon["text"].'</textarea>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "enabled").':</td>
                        <td>
                          <input type="checkbox" name="coupon_enabled" value="1" '.( ( $coupon["enabled"] == 1 ) ? 'checked="checked"' : '' ).' />
                        </td>
                      </tr>
                    </table>
                  </fieldset>
                  <div>
                    <input type="submit" name="savecoupon" value="'.lang("admin", "save").'" />
                  </div>
                </form>
              </div>';
            }
            else
            {
              // save coupon
              $coupon = $sqlm->quote_smart($_GET["sel_coupon"]);
              $coupon_target = $sqlm->quote_smart($_GET["coupon_target"]);
              $coupon_contest = $sqlm->quote_smart($_GET["coupon_contest"]);
              $coupon_usage_limit = $sqlm->quote_smart($_GET["coupon_usage_limit"]);
              $coupon_old_creation = $sqlm->quote_smart($_GET["oldcreation"]);
              //$coupon_expiration = $sqlm->quote_smart($_GET["coupon_expiration"]);
              $coupon_credits = $sqlm->quote_smart($_GET["coupon_credits"]);
              $coupon_money_gold = $sqlm->quote_smart($_GET["coupon_money_gold"]);
              $coupon_money_silver = $sqlm->quote_smart($_GET["coupon_money_silver"]);
              $coupon_money_copper = $sqlm->quote_smart($_GET["coupon_money_copper"]);
              $coupon_neg_money = ( ( isset($_GET["coupon_neg_money"]) ) ? $sqlm->quote_smart($_GET["coupon_neg_money"]) : 1 );
              $coupon_item = $sqlm->quote_smart($_GET["coupon_item"]);
              $coupon_count = $sqlm->quote_smart($_GET["coupon_count"]);
              $coupon_raffle_id = $sqlm->quote_smart($_GET["coupon_raffle_id"]);
              $coupon_method = $sqlm->quote_smart($_GET["coupon_method"]);
              $coupon_title = $sqlm->quote_smart($_GET["coupon_title"]);
              $coupon_text = $sqlm->quote_smart($_GET["coupon_text"]);
              $coupon_enabled = ( ( isset($_GET["coupon_enabled"]) ) ? 1 : 0 );

              // pad
              $coupon_money_silver = str_pad($coupon_money_silver, 2, "0", STR_PAD_LEFT);
              $coupon_money_copper = str_pad($coupon_money_copper, 2, "0", STR_PAD_LEFT);

              // combine
              $coupon_money = $coupon_money_gold.$coupon_money_silver.$coupon_money_copper;

              // if the Coupon costs money then it'll be saved negative
              $coupon_money = $coupon_money * $coupon_neg_money;

              if ( $coupon_old_creation == "0000-00-00 00:00:00" )
                $coupon_not_enabled = 1;

              $query = "UPDATE point_system_coupons
                          SET target='".$coupon_target."', ".( ( $coupon_enabled && $coupon_not_enabled ) ? "date_issued=NOW()," : "" )." usage_limit='".$coupon_usage_limit."',
                          contest='".$coupon_contest."', expiration=NOW(), credits='".$coupon_credits."', money='".$coupon_money."',
                          item_id='".$coupon_item."', item_count='".$coupon_count."', raffle_id='".$coupon_raffle_id."',
                          redemption_option='".$coupon_method."', title='".$coupon_title."', text='".$coupon_text."',
                          enabled='".$coupon_enabled."'
                        WHERE entry='".$coupon."'";

              $sqlm->query($query);
              redirect("admin.php?section=pointsystem&subsection=coupons&editcoupon=editcoupon&sel_coupon=".$coupon);
            }
          }
          else
            redirect("admin.php?section=pointsystem&subsection=coupons&error=1");
        }
        elseif ( $coupon_action == "del" )
        {
          $coupon_id = $sqlm->quote_smart($_GET["sel_coupon"]);
          if ( is_numeric($coupon_id) )
          {
            $result = $sqlm->query("DELETE FROM point_system_coupons WHERE `entry`='".$coupon_id."'");
            redirect("admin.php?section=pointsystem&subsection=coupons");
          }
          else
            redirect("admin.php?section=pointsystem&subsection=coupons&error=1");
        }
        else
        {
          $result = $sqlm->query("INSERT INTO point_system_coupons (target, credits, money, item_id, item_count, title, text, usage_limit, redemption_option, raffle_id, enabled) VALUES ('0', '0', '0', '0', '0', '', '', '1', '0', '0', '0')");

          redirect("admin.php?section=pointsystem&subsection=coupons");
        }
      }
      break;
    }
    case "raffles":
    {
      $query = "SELECT * FROM point_system_raffles";
      $result = $sqlm->query($query);

      $raffle_action = 0;
      if ( isset($_GET["editraffle"]) )
        $raffle_action = "edit";
      if ( isset($_GET["delraffle"]) )
        $raffle_action = "del";
      if ( isset($_GET["addraffle"]) )
        $raffle_action = "add";

      $sub_action = ( ( isset($_GET["subaction"]) ) ? $_GET["subaction"] : '' );

      $sqll = new SQL;
      $sqll->connect($logon_db["addr"], $logon_db["user"], $logon_db["pass"], $logon_db["name"], $logon_db["encoding"]);

      if ( !$raffle_action )
      {
        $output .= '
        <form action="admin.php" method="get" id="form">
          <div>
            <input type="hidden" name="section" value="pointsystem" />
            <input type="hidden" name="subsection" value="raffle" />
          </div>
          <table class="simple" id="admin_point_coupon_list">
            <tr>
              <th style="width: 1%;"></th>
              <th style="width: 1%;"></th>
              <th style="width: 3%;">'.lang("admin", "raffle_id").'</th>
              <th style="width: 15%;">'.lang("admin", "raffle_title").'</th>
              <th style="width: 15%;">'.lang("admin", "raffle_drawing").'</th>
              <th style="width: 10%;">'.lang("admin", "raffle_credits").'</th>
              <th style="width: 5%;">'.lang("admin", "raffle_money").'</th>
              <th style="width: 5%;">'.lang("admin", "raffle_item").'</th>
              <th style="width: 5%;">'.lang("admin", "raffle_count").'</th>
              <th style="width: 5%;">'.lang("admin", "raffle_usage").'</th>
              <th style="width: 5%;">'.lang("admin", "enabled").'</th>
              <th style="width: 5%;">'.lang("admin", "raffle_completed").'</th>
            </tr>';
        $color = "#EEEEEE";
        while ( $raffle = $sqlm->fetch_assoc($result) )
        {
          // determine usage
          $tickets_query = "SELECT COUNT(*) FROM point_system_raffle_tickets WHERE raffle='".$raffle["entry"]."'";
          $tickets_result = $sqlm->query($tickets_query);
          $tickets_result = $sqlm->fetch_assoc($tickets_result);
          $tickets_sold = $tickets_result["COUNT(*)"];

          $output .= '
            <tr>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=raffles&amp;sel_raffle='.$raffle["entry"].'&amp;editraffle=editraffle" onmouseover="oldtoolTip(\''.lang("admin", "edit").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/edit.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=raffles&amp;sel_raffle='.$raffle["entry"].'&amp;delraffle=deleteraffle" onmouseover="oldtoolTip(\''.lang("admin", "remove").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/aff_cross.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["entry"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["title"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["drawing"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["credits"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["money"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["item_id"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$raffle["item_count"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$tickets_sold.'</td>
              <td style="background-color:'.$color.'; text-align: center;">
                <img src="img/'.( ( $raffle["enabled"] ) ? 'up' : 'down' ).'.gif" alt="" />
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <img src="img/'.( ( $raffle["completed"] ) ? 'aff_tick.png' : '' ).'" alt="" />
              </td>
            </tr>';

          $color = ( ( $color == "#EEEEEE" ) ? "#FFFFFF" : "#EEEEEE" );
        }
        $output .= '
              <tr>
                <td style="background-color:'.$color.'">
                  <a href="admin.php?section=pointsystem&amp;subsection=raffles&amp;addraffle=addraffle">
                    <img src="img/add.png" alt="" />
                  </a>
                </td>
                <td style="background-color:'.$color.'" colspan="13">
                  <a href="admin.php?section=pointsystem&amp;subsection=raffles&amp;addraffle=addraffle">'.lang("admin", "addraffle").'</a>
                </td>
              </tr>
          </table>
        </form>';
      }
      else
      {
        if ( $raffle_action == "edit" )
        {
          $raffle_id = $sqlm->quote_smart($_GET["sel_raffle"]);
          if ( is_numeric($raffle_id) )
          {
            if ( !$sub_action )
            {
              $raffle = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM point_system_raffles WHERE `entry`='".$raffle_id."'"));

              // prize: extract gold/silver/copper from single gold number
              $raffle["money"] = str_pad($raffle["money"], 4, "0", STR_PAD_LEFT);
              $raffle_g = substr($raffle["money"],  0, -4);
              if ( $raffle_g == '' )
                $raffle_g = 0;
              $raffle_s = substr($raffle["money"], -4,  2);
              if ( ( $raffle_s == '' ) || ( $raffle_s == '00' ) )
                $raffle_s = 0;
              $raffle_c = substr($raffle["money"], -2);
              if ( ( $raffle_c == '' ) || ( $raffle_c == '00' ) )
                $raffle_c = 0;

              // cost: extract gold/silver/copper from single gold number
              $raffle["cost_money"] = str_pad($raffle["cost_money"], 4, "0", STR_PAD_LEFT);
              $raffle_cost_g = substr($raffle["cost_money"],  0, -4);
              if ( $raffle_cost_g == '' )
                $raffle_cost_g = 0;
              $raffle_cost_s = substr($raffle["cost_money"], -4,  2);
              if ( ( $raffle_cost_s == '' ) || ( $raffle_cost_s == '00' ) )
                $raffle_cost_s = 0;
              $raffle_cost_c = substr($raffle["cost_money"], -2);
              if ( ( $raffle_cost_c == '' ) || ( $raffle_cost_c == '00' ) )
                $raffle_cost_c = 0;

              $drawing = strtotime($raffle["drawing"]);
              $drawing_year = date("Y", $drawing);
              $drawing_month = date("m", $drawing);
              $drawing_day = date("d", $drawing);
              $drawing_hour = date("H", $drawing);
              $drawing_minute = date("i", $drawing);

              if ( $drawing_year == 1969 )
                $drawing_year = date("Y");

              $output .= '
              <div>
                <form action="admin.php" method="get" id="form">
                  <fieldset id="admin_edit_coupon">
                    <div>
                      <input type="hidden" name="section" value="pointsystem" />
                      <input type="hidden" name="subsection" value="raffles" />
                      <input type="hidden" name="editraffle" value="editraffle" />
                      <input type="hidden" name="subaction" value="saveraffle" />
                      <input type="hidden" name="sel_raffle" value="'.$raffle["entry"].'" />
                    </div>
                    <table>
                      <tr>
                        <td>'.lang("admin", "raffle_id").':</td>
                        <td>'.$raffle["entry"].'</td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_drawing").':</td>
                        <td>'.$raffle["drawing"].'</td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_drawing_year").'-'.lang("admin", "raffle_drawing_month").'-'.lang("admin", "raffle_drawing_day").':</td>
                        <td>
                          <input type="text" name="drawing_year" value="'.$drawing_year.'" maxlength="4" size="4" />&nbsp;-&nbsp;
                          <input type="text" name="drawing_month" value="'.$drawing_month.'" maxlength="2" size="2" />&nbsp;-&nbsp;
                          <input type="text" name="drawing_day" value="'.$drawing_day.'" maxlength="2" size="2" />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "raffle_time_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "raffle_drawing_hour").'&nbsp;:&nbsp;'.lang("admin", "raffle_drawing_minute").':</a>
                        </td>
                        <td>
                          <input type="text" name="drawing_hour" value="'.$drawing_hour.'" maxlength="4" size="4" />&nbsp;:&nbsp;
                          <input type="text" name="drawing_minute" value="'.$drawing_minute.'" maxlength="2" size="2" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <b>'.lang("admin", "raffle_prize").'</b>
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_credits").':</td>
                        <td>
                          <input type="text" name="raffle_credits" value="'.$raffle["credits"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_money").':</td>
                        <td>
                          <input type="text" name="raffle_money_gold" value="'.$raffle_g.'" maxlength="6" size="6"/>
                          <img src="../img/gold.gif" alt="gold" />
                          <input type="text" name="raffle_money_silver" value="'.$raffle_s.'" maxlength="2" size="6"/>
                          <img src="../img/silver.gif" alt="gold" />
                          <input type="text" name="raffle_money_copper" value="'.$raffle_c.'" maxlength="2" size="6"/>
                          <img src="../img/copper.gif" alt="gold" />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "coupon_item_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "raffle_item").':</a>
                        </td>
                        <td>
                          <input type="text" name="raffle_item" value="'.$raffle["item_id"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_count").':</td>
                        <td>
                          <input type="text" name="raffle_count" value="'.$raffle["item_count"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2" class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "raffle_cost_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">
                            <b>'.lang("admin", "raffle_cost").'</b>
                          </a>
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_credits").':</td>
                        <td>
                          <input type="text" name="raffle_cost_credits" value="'.$raffle["cost_credits"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_money").':</td>
                        <td>
                          <input type="text" name="raffle_cost_gold" value="'.$raffle_cost_g.'" maxlength="6" size="6"/>
                          <img src="../img/gold.gif" alt="gold" />
                          <input type="text" name="raffle_cost_silver" value="'.$raffle_cost_s.'" maxlength="2" size="6"/>
                          <img src="../img/silver.gif" alt="gold" />
                          <input type="text" name="raffle_cost_copper" value="'.$raffle_cost_c.'" maxlength="2" size="6"/>
                          <img src="../img/copper.gif" alt="gold" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_title").':</td>
                        <td>
                          <input type="text" name="raffle_title" value="'.$raffle["title"].'" class="admin_edit_coupon_fields" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_text").':</td>
                        <td>
                          <textarea name="raffle_text" rows="2" cols="32">'.$raffle["text"].'</textarea>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_ticket_limit").':</td>
                        <td>
                          <input type="text" name="raffle_ticket_limit" value="'.$raffle["ticket_limit"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_per_user").':</td>
                        <td>
                          <input type="text" name="raffle_per_user" value="'.$raffle["tickets_per_user"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_announce_acct").':</td>
                        <td>
                          <input type="text" name="raffle_announce_acct" value="'.$raffle["announce_acct"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "enabled").':</td>
                        <td>
                          <input type="checkbox" name="raffle_enabled" value="1" '.( ( $raffle["enabled"] == 1 ) ? 'checked="checked"' : '' ).' />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "raffle_completed").':</td>
                        <td>
                          <img src="img/aff_'.( ( $raffle["completed"] ) ? 'tick' : 'cross' ).'.png" alt="" />
                        </td>
                      </tr>
                    </table>
                  </fieldset>
                  <div>
                    <input type="submit" name="saveraffle" value="'.lang("admin", "save").'" />
                  </div>
                </form>
              </div>';
            }
            else
            {
              // save raffle
              $raffle = $sqlm->quote_smart($_GET["sel_raffle"]);
              $raffle_credits = $sqlm->quote_smart($_GET["raffle_credits"]);
              $raffle_money_gold = $sqlm->quote_smart($_GET["raffle_money_gold"]);
              $raffle_money_silver = $sqlm->quote_smart($_GET["raffle_money_silver"]);
              $raffle_money_copper = $sqlm->quote_smart($_GET["raffle_money_copper"]);
              $raffle_item = $sqlm->quote_smart($_GET["raffle_item"]);
              $raffle_count = $sqlm->quote_smart($_GET["raffle_count"]);
              $raffle_cost_credits = $sqlm->quote_smart($_GET["raffle_cost_credits"]);
              $raffle_cost_gold = $sqlm->quote_smart($_GET["raffle_cost_gold"]);
              $raffle_cost_silver = $sqlm->quote_smart($_GET["raffle_cost_silver"]);
              $raffle_cost_copper = $sqlm->quote_smart($_GET["raffle_cost_copper"]);
              $raffle_title = $sqlm->quote_smart($_GET["raffle_title"]);
              $raffle_text = $sqlm->quote_smart($_GET["raffle_text"]);
              $raffle_ticket_limit = $sqlm->quote_smart($_GET["raffle_ticket_limit"]);
              $raffle_per_user = $sqlm->quote_smart($_GET["raffle_per_user"]);
              $raffle_announce_acct = $sqlm->quote_smart($_GET["raffle_announce_acct"]);
              $raffle_enabled = ( ( isset($_GET["raffle_enabled"]) ) ? 1 : 0 );

              // drawing
              $year = $sqlm->quote_smart($_GET["drawing_year"]);
              $month = $sqlm->quote_smart($_GET["drawing_month"]);
              $day = $sqlm->quote_smart($_GET["drawing_day"]);
              $hour = $sqlm->quote_smart($_GET["drawing_hour"]);
              $minute = $sqlm->quote_smart($_GET["drawing_minute"]);

              $drawing = $year."-".$month."-".$day." ".$hour.":".$minute.":00";

              // prize
              // pad
              $raffle_money_silver = str_pad($raffle_money_silver, 2, "0", STR_PAD_LEFT);
              $raffle_money_copper = str_pad($raffle_money_copper, 2, "0", STR_PAD_LEFT);

              // combine
              $raffle_money = $raffle_money_gold.$raffle_money_silver.$raffle_money_copper;

              // cost
              // pad
              $raffle_cost_silver = str_pad($raffle_cost_silver, 2, "0", STR_PAD_LEFT);
              $raffle_cost_copper = str_pad($raffle_cost_copper, 2, "0", STR_PAD_LEFT);

              // combine
              $raffle_cost = $raffle_cost_gold.$raffle_cost_silver.$raffle_cost_copper;

              $query = "UPDATE point_system_raffles
                          SET drawing='".$drawing."', credits='".$raffle_credits."', money='".$raffle_money."',
                          item_id='".$raffle_item."', item_count='".$raffle_count."',
                          title='".$raffle_title."', text='".$raffle_text."',
                          cost_credits='".$raffle_cost_credits."', cost_money='".$raffle_cost."',
                          ticket_limit='".$raffle_ticket_limit."', tickets_per_user='".$raffle_per_user."',
                          announce_acct='".$raffle_announce_acct."', enabled='".$raffle_enabled."'
                        WHERE entry='".$raffle."'";

              $sqlm->query($query);
              redirect("admin.php?section=pointsystem&subsection=raffles&editraffle=editraffle&sel_raffle=".$raffle);
            }
          }
          else
            redirect("admin.php?section=pointsystem&subsection=raffles&error=1");
        }
        elseif ( $raffle_action == "del" )
        {
          $raffle_id = $sqlm->quote_smart($_GET["sel_raffle"]);
          if ( is_numeric($raffle_id) )
          {
            $result = $sqlm->query("DELETE FROM point_system_raffles WHERE `entry`='".$raffle_id."'");
            redirect("admin.php?section=pointsystem&subsection=raffles");
          }
          else
            redirect("admin.php?section=pointsystem&subsection=raffles&error=1");
        }
        else
        {
          $result = $sqlm->query("INSERT INTO point_system_raffles (credits, money, item_id, item_count, title, text, cost_credits, cost_money, tickets_per_user, ticket_limit, announce_acct, winner, enabled, completed) VALUES ('0', '0', '0', '0', '', '', '0', '0', '1', '1', '1', '0', '0', '0')");

          redirect("admin.php?section=pointsystem&subsection=raffles");
        }
      }
      break;
    }
    case "bags":
    {
      $query = "SELECT * FROM point_system_prize_bags";
      $result = $sqlm->query($query);

      $bag_action = 0;
      if ( isset($_GET["editbag"]) )
        $bag_action = "edit";
      if ( isset($_GET["delbag"]) )
        $bag_action = "del";
      if ( isset($_GET["addbag"]) )
        $bag_action = "add";

      $sub_action = ( ( isset($_GET["subaction"]) ) ? $_GET["subaction"] : '' );

      $sqll = new SQL;
      $sqll->connect($logon_db["addr"], $logon_db["user"], $logon_db["pass"], $logon_db["name"], $logon_db["encoding"]);

      if ( !$bag_action )
      {
        $output .= '
        <form action="admin.php" method="get" id="form">
          <div>
            <input type="hidden" name="section" value="pointsystem" />
            <input type="hidden" name="subsection" value="bags" />
          </div>
          <table class="simple" id="admin_point_bag_list">
            <tr>
              <th style="width: 1%;"></th>
              <th style="width: 1%;"></th>
              <th style="width: 4%;">'.lang("admin", "bag_id").'</th>
              <th style="width: 37%;">'.lang("admin", "bag_slots").'</th>
              <th style="width: 37%;">'.lang("admin", "bag_owner").'</th>
              <th style="width: 20%;">'.lang("admin", "bag_template").'</th>
            </tr>';
        $color = "#EEEEEE";
        while ( $bag = $sqlm->fetch_assoc($result) )
        {
          if ( $core == 1 )
            $owner_query = "SELECT login FROM accounts WHERE acct='".$bag["owner"]."'";
          else
            $owner_query = "SELECT username AS login FROM account WHERE id='".$bag["owner"]."'";

          $owner_result = $sqll->query($owner_query);

          if ( $sqll->num_rows($owner_result) > 0 )
          {
            $owner_result = $sqll->fetch_assoc($owner_result);
            $owner = $owner_result["login"];
          }
          else
            $owner = '<b>'.lang("admin", "bag_no_owner").'</b>';

          $output .= '
            <tr>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=bags&amp;sel_bag='.$bag["entry"].'&amp;editbag=editbag" onmouseover="oldtoolTip(\''.lang("admin", "edit").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/edit.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=bags&amp;sel_bag='.$bag["entry"].'&amp;delbag=deletebag" onmouseover="oldtoolTip(\''.lang("admin", "remove").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/aff_cross.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">'.$bag["entry"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$bag["slots"].'</td>
              <td style="background-color:'.$color.'; text-align: center;">'.$owner.'</td>
              <td style="background-color:'.$color.'; text-align: center;">';

        if ( $bag["is_template"] )
          $output .= '
                <img src="img/star.png" alt="" />';
        else
          $output .= '
                <span>&nbsp;</span>';

        $output .= '
              </td>
            </tr>';

          $color = ( ( $color == "#EEEEEE" ) ? "#FFFFFF" : "#EEEEEE" );
        }
        $output .= '
              <tr>
                <td style="background-color:'.$color.'">
                  <a href="admin.php?section=pointsystem&amp;subsection=bags&amp;addbag=addbag">
                    <img src="img/add.png" alt="" />
                  </a>
                </td>
                <td style="background-color:'.$color.'" colspan="13">
                  <a href="admin.php?section=pointsystem&amp;subsection=bags&amp;addbag=addbag">'.lang("admin", "addbag").'</a>
                </td>
              </tr>
          </table>
        </form>';
      }
      else
      {
        if ( $bag_action == "edit" )
        {
          $bag_id = $sqlm->quote_smart($_GET["sel_bag"]);
          if ( is_numeric($bag_id) )
          {
            if ( !$sub_action )
            {
              $bag = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM point_system_prize_bags WHERE `entry`='".$bag_id."'"));

              if ( $core == 1 )
                $owner_query = "SELECT login FROM accounts WHERE acct='".$bag["owner"]."'";
              else
                $owner_query = "SELECT username AS login FROM account WHERE id='".$bag["owner"]."'";

              $owner_result = $sqll->query($owner_query);

              if ( $sqll->num_rows($owner_result) > 0 )
              {
                $owner_result = $sqll->fetch_assoc($owner_result);
                $owner = $owner_result["login"];
              }
              else
                $owner = '<b>'.lang("admin", "bag_no_owner").'</b>';

              $output .= '
              <div>
                <form action="admin.php" method="get" id="form">
                  <fieldset>
                    <div>
                      <input type="hidden" name="section" value="pointsystem" />
                      <input type="hidden" name="subsection" value="bags" />
                      <input type="hidden" name="editbag" value="editbag" />
                      <input type="hidden" name="subaction" value="savebag" />
                      <input type="hidden" name="sel_bag" value="'.$bag["entry"].'" />
                    </div>
                    <table id="admin_edit_coupon">
                      <tr>
                        <td>'.lang("admin", "bag_id").':</td>
                        <td>'.$bag["entry"].'</td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "bag_owner").':</td>
                        <td>'.$owner.'</td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "bag_template").':</td>
                        <td>
                          <input type="checkbox" name="is_template" value="1"'.( ( $bag["is_template"] ) ? ' checked="checked"' : '' ).' />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "bag_slots").':</td>
                        <td>
                          <select name="slots">
                            <option value="4" '.( ( $bag["slots"] == 4 ) ? 'selected="selected"' : '' ).'>4</option>
                            <option value="6" '.( ( $bag["slots"] == 6 ) ? 'selected="selected"' : '' ).'>6</option>
                            <option value="8" '.( ( $bag["slots"] == 8 ) ? 'selected="selected"' : '' ).'>8</option>
                            <option value="10" '.( ( $bag["slots"] == 10 ) ? 'selected="selected"' : '' ).'>10</option>
                            <option value="12" '.( ( $bag["slots"] == 12 ) ? 'selected="selected"' : '' ).'>12</option>
                            <option value="14" '.( ( $bag["slots"] == 14 ) ? 'selected="selected"' : '' ).'>14</option>
                            <option value="16" '.( ( $bag["slots"] == 16 ) ? 'selected="selected"' : '' ).'>16</option>
                            <option value="18" '.( ( $bag["slots"] == 18 ) ? 'selected="selected"' : '' ).'>18</option>
                            <option value="20" '.( ( $bag["slots"] == 20 ) ? 'selected="selected"' : '' ).'>20</option>
                            <option value="22" '.( ( $bag["slots"] == 22 ) ? 'selected="selected"' : '' ).'>22</option>
                            <option value="24" '.( ( $bag["slots"] == 24 ) ? 'selected="selected"' : '' ).'>24</option>
                            <option value="26" '.( ( $bag["slots"] == 26 ) ? 'selected="selected"' : '' ).'>26</option>
                            <option value="28" '.( ( $bag["slots"] == 28 ) ? 'selected="selected"' : '' ).'>28</option>
                            <option value="30" '.( ( $bag["slots"] == 30 ) ? 'selected="selected"' : '' ).'>30</option>
                            <option value="32" '.( ( $bag["slots"] == 32 ) ? 'selected="selected"' : '' ).'>32</option>
                            <option value="34" '.( ( $bag["slots"] == 34 ) ? 'selected="selected"' : '' ).'>34</option>
                            <option value="36" '.( ( $bag["slots"] == 36 ) ? 'selected="selected"' : '' ).'>36</option>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td valign="top" align="center">
                          <span>'.lang("admin", "bag_simulation").'</span>
                          <div class="bag" style="width:'.(4*43).'px; height:'.(ceil($bag["slots"]/4)*41).'px;">';

              $dsp = $bag["slots"]%4;

              if ( $dsp )
                $output .= '
                            <div class="no_slot"></div>';

              // get bag items
              $items_query = "SELECT item_id, slot, item_count FROM point_system_prize_bag_items WHERE `bag`='".$bag_id."'";
              $items_result = $sqlm->query($items_query);

              // create a empty bag array and fill it with nothing
              $items = array();
              for ( $i = 0; $i < $bag["slots"]; $i++ )
                $items[] = array("item_id" => 0, "slot" => 0, "item_count" => 0);

              while ( $item = $sqlm->fetch_assoc($items_result) )
              {
                $item["item_count"] = ( ( $item["item_count"] == 1 ) ? "" : $item["item_count"] );
                // this_is_junk: style left hardcoded because it's calculated.
                $output .= '
                            <div class="bag_slot" style="left:'.(($item["slot"]+$dsp)%4*44).'px;top:'.((floor(($item["slot"]+$dsp)/4)*41)+3).'px;">
                              <img src="'.get_item_icon($item["item_id"]).'" alt="" class="item_img" />';
                $output .= '
                              <div class="points_bag_quantity_shadow">'.$item["item_count"].'</div>
                              <div class="points_bag_quantity">'.$item["item_count"].'</div>';
                $output .= '
                            </div>';

                $item["item_count"] = ( ( $item["item_count"] == "" ) ? 1 : $item["item_count"] );

                $items[$item["slot"]] = $item;
              }

              $output .= '
                          </div>
                        </td>
                        <td>
                          <table>
                            <tr>
                              <td>'.lang("admin", "bag_slot").'</td>
                              <td align="center">'.lang("admin", "bag_item").'</td>
                              <td align="center">'.lang("admin", "bag_item_count").'</td>
                            </tr>';

              for ( $i = 0; $i < $bag["slots"]; $i++ )
              {
                $output .= '
                            <tr>
                              <td>'.($i + 1).':</td>
                              <td>
                                <input type="text" name="slot_'.$i.'" value="'.$items[$i]["item_id"].'" size="7" />
                              </td>
                              <td>
                                <input type="text" name="slot_count_'.$i.'" value="'.$items[$i]["item_count"].'" size="7" />
                              </td>
                            </tr>';
              }

              $output .= '
                          </table>
                        </td>
                      </tr>
                    </table>
                  </fieldset>
                  <div>
                    <input type="submit" name="savebag" value="'.lang("admin", "save").'" />
                  </div>
                </form>
              </div>';
            }
            else
            {
              // save prize bag & items
              $bag_id = $_GET["sel_bag"];
              $slots = $_GET["slots"];
              $is_template = ( ( isset($_GET["is_template"]) ) ? 1 : 0 );

              $items = array();
              $item_counts = array();
              for ( $i = 0; $i < $slots; $i++ )
              {
                if ( $_GET["slot_".$i] != 0 )
                {
                  $items[] = $_GET["slot_".$i];
                  $item_counts[] = $_GET["slot_count_".$i];
                }
              }

              // update bag
              $query = "UPDATE point_system_prize_bags SET slots='".$slots."', is_template='".$is_template."' WHERE entry='".$bag_id."'";
              $sqlm->query($query);

              // delete existing items
              $query = "DELETE FROM point_system_prize_bag_items WHERE bag='".$bag_id."'";
              $sqlm->query($query);

              for ( $i = 0; $i < count($items); $i++ )
              {
                $query = "INSERT INTO point_system_prize_bag_items (bag, slot, item_id, item_count) VALUES ('".$bag_id."', '".$i."', '".$items[$i]."', '".$item_counts[$i]."')";
                $sqlm->query($query);
              }

              redirect("admin.php?section=pointsystem&subsection=bags&editbag=editbag&sel_bag=".$bag_id);
            }
          }
          else
            redirect("admin.php?section=pointsystem&subsection=bags&error=1");
        }
        elseif ( $bag_action == "del" )
        {
          $bag_id = $sqlm->quote_smart($_GET["sel_bag"]);
          if ( is_numeric($bag_id) )
          {
            $result = $sqlm->query("DELETE FROM point_system_prize_bags WHERE `entry`='".$bag_id."'");
            $result = $sqlm->query("DELETE FROM point_system_prize_bag_items WHERE `bag`='".$bag_id."'");
            redirect("admin.php?section=pointsystem&subsection=bags");
          }
          else
            redirect("admin.php?section=pointsystem&subsection=bags&error=1");
        }
        else
        {
          $result = $sqlm->query("INSERT INTO point_system_prize_bags (slots, owner) VALUES ('4', '0')");

          redirect("admin.php?section=pointsystem&subsection=bags");
        }
      }
      break;
    }
    case "achieve":
    {
      $query = "SELECT * FROM point_system_achievement_contests";
      $result = $sqlm->query($query);

      $contest_action = 0;
      if ( isset($_GET["editcontest"]) )
        $contest_action = "edit";
      if ( isset($_GET["delcontest"]) )
        $contest_action = "del";
      if ( isset($_GET["addcontest"]) )
        $contest_action = "add";

      $sub_action = ( ( isset($_GET["subaction"]) ) ? $_GET["subaction"] : '' );

      $sqll = new SQL;
      $sqll->connect($logon_db["addr"], $logon_db["user"], $logon_db["pass"], $logon_db["name"], $logon_db["encoding"]);

      if ( !$contest_action )
      {
        $output .= '
        <form action="admin.php" method="get" id="form">
          <div>
            <input type="hidden" name="section" value="pointsystem" />
            <input type="hidden" name="subsection" value="contests" />
          </div>
          <table class="simple" id="admin_achievement_contest_list">
            <tr>
              <th style="width: 3%;"></th>
              <th style="width: 3%;"></th>
              <th style="width: 7%;">'.lang("admin", "contest_id").'</th>
              <th style="width: 7%;">'.lang("admin", "contest_realm").'</th>
              <th style="width: 19%;">'.lang("admin", "contest_achieve_id").'</th>
              <th style="width: 20%;">'.lang("admin", "contest_issued").'</th>
              <th style="width: 15%;">'.lang("admin", "contest_prize_bag").'</th>
              <th style="width: 16%;">'.lang("admin", "contest_winner").'</th>
              <th style="width: 10%;">'.lang("admin", "contest_enabled").'</th>
            </tr>';
        $color = "#EEEEEE";
        while ( $contest = $sqlm->fetch_assoc($result) )
        {
          $output .= '
            <tr>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=achieve&amp;sel_contest='.$contest["entry"].'&amp;editcontest=editcontest" onmouseover="oldtoolTip(\''.lang("admin", "edit").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/edit.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <a href="admin.php?section=pointsystem&amp;subsection=achieve&amp;sel_contest='.$contest["entry"].'&amp;delcontest=deletecontest" onmouseover="oldtoolTip(\''.lang("admin", "remove").'\', \'info_tooltip\')" onmouseout="oldtoolTip()">
                  <img src="img/aff_cross.png" alt="" />
                </a>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span>'.$contest["entry"].'</span>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span>'.$contest["realm"].'</span>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span>'.$contest["achievement"].'</span>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span>'.$contest["date_issued"].'</span>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span>'.$contest["prize_bag"].'</span>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span>'.$contest["winner_guid"].'</span>
              </td>
              <td style="background-color:'.$color.'; text-align: center;">
                <span><img src="img/'.( ( $contest["enabled"] ) ? 'up' : 'down' ).'.gif" alt="" /></span>
              </td>
            </tr>';

          $color = ( ( $color == "#EEEEEE" ) ? "#FFFFFF" : "#EEEEEE" );
        }
        $output .= '
              <tr>
                <td style="background-color:'.$color.'">
                  <a href="admin.php?section=pointsystem&amp;subsection=achieve&amp;addcontest=addcontest">
                    <img src="img/add.png" alt="" />
                  </a>
                </td>
                <td style="background-color:'.$color.'" colspan="13">
                  <a href="admin.php?section=pointsystem&amp;subsection=achieve&amp;addcontest=addcontest">'.lang("admin", "contest_add").'</a>
                </td>
              </tr>
          </table>
        </form>';
      }
      else
      {
        if ( $contest_action == "edit" )
        {
          $contest_id = $sqlm->quote_smart($_GET["sel_contest"]);
          if ( is_numeric($contest_id) )
          {
            if ( !$sub_action )
            {
              $contest = $sqlm->fetch_assoc($sqlm->query("SELECT * FROM point_system_achievement_contests WHERE `entry`='".$contest_id."'"));

              $expires = strtotime($contest["expiration"]);
              $expires_year = date("Y", $expires);
              $expires_month = date("m", $expires);
              $expires_day = date("d", $expires);
              $expires_hour = date("H", $expires);
              $expires_minute = date("i", $expires);

              if ( $expires_year == 1969 )
                $expires_year = date("Y");

              $output .= '
              <script type="text/javascript">
                // <![CDATA[
                  function check_realm()
                  {
                    realm_field = document.getElementById("contest_realm");
                    warn = document.getElementById("warn");
                    if ( realm_field.value == 0 )
                      warn.style="color: #CF0000;";
                    else
                      warn.style="display: none; color: #CF0000;";
                  }
                // ]]>
              </script>
              <div>
                <form action="admin.php" method="get" id="form">
                  <fieldset>
                    <div>
                      <input type="hidden" name="section" value="pointsystem" />
                      <input type="hidden" name="subsection" value="achieve" />
                      <input type="hidden" name="editcontest" value="editcontest" />
                      <input type="hidden" name="subaction" value="savecontest" />
                      <input type="hidden" name="sel_contest" value="'.$contest["entry"].'" />
                    </div>
                    <table id="admin_edit_contest">
                      <tr>
                        <td>'.lang("admin", "contest_id").':</td>
                        <td>'.$contest["entry"].'</td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "contest_realm_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "contest_realm").':</a>
                        </td>
                        <td>
                          <input type="text" id="contest_realm" name="contest_realm" value="'.$contest["realm"].'" maxlength="6" style="width: 75px;" onblur="check_realm()" />
                          <span id="warn" style="display: none; color: #CF0000">'.lang("admin", "contest_realm_warn").'</span>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "contest_issued").':</td>
                        <td>'.$contest["date_issued"].'</td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "contest_after_only_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "contest_after_only").':</a>
                        </td>
                        <td>
                          <input type="checkbox" name="contest_after_only" '.( ( $contest["after_only"] == 1 ) ? 'checked="checked"' : '' ).' />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "contest_ach_id").':</td>
                        <td>
                          <input type="text" name="contest_ach_id" value="'.$contest["achievement"].'" maxlength="6" style="width: 75px;" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <span class="bold">'.lang("admin", "contest_expiration").'</span>
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "contest_expires_year").'-'.lang("admin", "contest_expires_month").'-'.lang("admin", "contest_expires_day").':</td>
                        <td>
                          <input type="text" name="expires_year" value="'.$expires_year.'" maxlength="4" size="4" /><span>&nbsp;-&nbsp;</span>
                          <input type="text" name="expires_month" value="'.$expires_month.'" maxlength="2" size="2" /><span>&nbsp;-&nbsp;</span>
                          <input type="text" name="expires_day" value="'.$expires_day.'" maxlength="2" size="2" />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "contest_expires_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "contest_expires_hour").':&nbsp;'.lang("admin", "contest_expires_minute").':</a>
                        </td>
                        <td>
                          <input type="text" name="expires_hour" value="'.$expires_hour.'" maxlength="4" size="4" /><span>&nbsp;:&nbsp;</span>
                          <input type="text" name="expires_minute" value="'.$expires_minute.'" maxlength="2" size="2" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "contest_prize_bag_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "contest_prize_bag").':</a>
                        </td>
                        <td>
                          <input type="text" name="contest_prize_bag" value="'.$contest["prize_bag"].'" size="6" />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "contest_winner").':</td>
                        <td>'.$contest["winner_guid"].'</td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td class="help" rowspan="2">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "contest_announce_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "contest_announce").':</a>
                        </td>
                        <td>
                          <input type="radio" name="contest_announce" value="0" '.( ( $contest["announce"] == 0 ) ? ' checked="checked"' : '' ).' size="6" />
                          <span>'.lang("admin", "contest_announce_global").'</span>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <input type="radio" name="contest_announce" value="1" '.( ( $contest["announce"] == 1 ) ? ' checked="checked"' : '' ).' size="6" />
                          <span>'.lang("admin", "contest_announce_private").'</span>
                        </td>
                      </tr>
                      <tr>
                        <td class="help">
                          <a href="#" onmouseover="oldtoolTip(\''.lang("admin", "contest_announce_msg_tip").'\',\'info_tooltip\')" onmouseout="oldtoolTip()">'.lang("admin", "contest_announce_msg").':</a>
                        </td>
                        <td>
                          <textarea name="contest_announce_msg" rows="3" cols="20">'.$contest["announce_msg"].'</textarea>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <hr />
                        </td>
                      </tr>
                      <tr>
                        <td>'.lang("admin", "contest_enabled").':</td>
                        <td>
                          <input type="checkbox" name="contest_enabled" '.( ( $contest["enabled"] == 1 ) ? 'checked="checked"' : '' ).' />
                        </td>
                      </tr>
                    </table>
                  </fieldset>
                  <div>
                    <input type="submit" name="savecontest" value="'.lang("admin", "save").'" />
                  </div>
                </form>
              </div>';
            }
            else
            {
              // save achievement contest
              $contest_id = $_GET["sel_contest"];

              $contest_ach_id = $sqlm->quote_smart($_GET["contest_ach_id"]);
              $contest_realm = $sqlm->quote_smart($_GET["contest_realm"]);
              $contest_after_only = ( ( isset($_GET["contest_after_only"]) ) ? 1 : 0 );
              $expires_year = $sqlm->quote_smart($_GET["expires_year"]);
              $expires_month = $sqlm->quote_smart($_GET["expires_month"]);
              $expires_day = $sqlm->quote_smart($_GET["expires_day"]);
              $expires_hour = $sqlm->quote_smart($_GET["expires_hour"]);
              $expires_minute = $sqlm->quote_smart($_GET["expires_minute"]);
              $contest_prize_bag = $sqlm->quote_smart($_GET["contest_prize_bag"]);
              $contest_announce = $sqlm->quote_smart($_GET["contest_announce"]);
              $contest_announce_msg = $sqlm->quote_smart($_GET["contest_announce_msg"]);
              $contest_enabled = ( ( isset($_GET["contest_enabled"]) ) ? 1 : 0 );

              $expires = $expires_year."-".$expires_month."-".$expires_day." ".$expires_hour.":".$expires_minute.":00";

              $query = "UPDATE point_system_achievement_contests SET realm='".$contest_realm."', achievement='".$contest_ach_id."', after_only='".$contest_after_only."', expiration='".$expires."', prize_bag='".$contest_prize_bag."', announce='".$contest_announce."', announce_msg='".$contest_announce_msg."', enabled='".$contest_enabled."' WHERE entry='".$contest_id."'";
              $sqlm->query($query);

              redirect("admin.php?section=pointsystem&subsection=achieve&editcontest=editcontest&sel_contest=".$contest_id);
            }
          }
          else
            redirect("admin.php?section=pointsystem&subsection=achieve&error=1");
        }
        elseif ( $contest_action == "del" )
        {
          $contest_id = $sqlm->quote_smart($_GET["sel_contest"]);
          if ( is_numeric($contest_id) )
          {
            $result = $sqlm->query("DELETE FROM point_system_achievement_contests WHERE `entry`='".$contest_id."'");
            redirect("admin.php?section=pointsystem&subsection=achieve");
          }
          else
            redirect("admin.php?section=pointsystem&subsection=achieve&error=1");
        }
        else
        {
          $result = $sqlm->query("INSERT INTO point_system_achievement_contests (date_issued) VALUES (NOW())");

          redirect("admin.php?section=pointsystem&subsection=achieve");
        }
      }
    }
  }

  $output .= '
      </div>';
}

?>
