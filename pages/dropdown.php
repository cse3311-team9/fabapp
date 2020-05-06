<?php
/*
 *   CC BY-NC-AS UTA FabLab 2016-2017
 *   FabApp V 0.9
 */
 //This will import all of the CSS and HTML code necessary to build the basic page
//include_once ($_SERVER['DOCUMENT_ROOT'].'/pages/header.php');
/*
include_once ($_SERVER['DOCUMENT_ROOT'].'/connections/db_connect8.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/connections/ldap.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/class/all_classes.php');

//Function for displaying data in notification dropdown according to the user that is logged in
function display_dropdown($role_id)
{
    if($role_id == 2 || $role_id == 4)
    {
        echo("<ul class="dropdown-menu dropdown-user" style="padding-bottom: 0;">
            <li>
                <a href="/pages/info.php" onclick="loadingModal()">
                    <i class="fas fa-list-ol"></i> <b>Queue Info:</b>
                    <p style="margin: 0px; padding-left: 30px">3rd in line: ETA 10 mins</p>
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="/pages/info.php" onclick="loadingModal()">
                    <i class="fas fa-money-check-alt"></i> <b>Balance:</b>
                    <p style="margin: 0px; padding-left: 30px">Ticket 1234: $1.37</p>
                    <p style="margin: 0px; padding-left: 30px">Ticket 5678: $0.75</p>
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="/pages/info.php" onclick="loadingModal()">
                    <i class="fas fa-ticket-alt"></i> <b>Ticket Status:</b>
                    <p style="margin: 0px; padding-left: 30px">Ticket 1234: In Storage</p>
                    <p style="margin: 0px; padding-left: 30px">Ticket 5678: Printing...</p>
                </a>
            </li>
            <li class="divider" style="margin-bottom: 0;"></li>
            <li style="text-align: right;">
                <a href="/pages/lookup.php" onclick="loadingModal()" style="background-color: lightgrey;">
                    <i class="fas fa-cog"></i> <b>Notification Settings</b>
                </a>
            </li>
        </ul>");
    }
}
*/

//Needed variables:
/*
Work sequence:
Tickets->Balance->Wait queue

Ticket:
`Transactions`
"status_id"
`User(child->Staff)`
For getting tickets from staff history use:
<?php foreach ($staff->history() as $ticket){ ?>
    <tr>
        <td align="Center"><a href="/pages/lookup.php?trans_id=<?php echo $ticket[0];?>"><?php echo $ticket[0];?></a></td>
        <td><?php echo $ticket[1];?></td>
        <td><?php echo $ticket[2];?></td>
        <td><?php echo $ticket[3];?></td>
        <td><?php echo $ticket[4];?></td>
    </tr>
<?php }?>


*/