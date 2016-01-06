<?php
/**
 * ACEITLab Application - Console Module
 *
 * provides interface for accessing vm remote consoles
 * requires USER security level or higher
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

/**
 * setup
 */
require_once('fns.php');
session_start();
ace_validate_session(_USER_SECURITY_LEVEL_);
ace_session_redirect_form_refresh(_CONSOLE_URL_ . ((isset($_SESSION['current_lab_id'])) ? '?lab_id=' . $_SESSION['current_lab_id'] : ''));

$nonce = rand();

if (isset($_POST['lab_id']) && ($_POST['lab_id'] != 'null')) {
    $lab_id = $_POST['lab_id'];
    $_SESSION['current_lab_id'] = $lab_id;
} else {
    $lab_id = NULL;
    $_SESSION['current_lab_id'] = NULL;
    ace_out_redirect_page(_LAB_URL_);
}

# BEGIN COMMAND PROCESSING
switch ($_POST['action']) {
    case 'vm_power_on' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_activate($_POST['vm_id']);
            $message = create_message($success, "starting $vm_display_name");
        } else {
            $message = create_message(FALSE, "starting vm, no vm_id specified");
        }
        break;
    case 'vm_power_off' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_deactivate($_POST['vm_id']);
            $message = create_message($success, "stopping $vm_display_name");
        } else {
            $message = create_message(FALSE, "stopping vm, no vm_id specified");
        }
        break;
    case 'vm_shutdown' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_shutdown($_POST['vm_id']);
            $message = create_message($success, "shutting down $vm_display_name");
        } else {
            $message = create_message(FALSE, "shutting down vm, no vm_id specified");
        }
        break;
    case 'vm_soft_reset' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_soft_reset($_POST['vm_id']);
            $message = create_message($success, "soft resetting $vm_display_name");
        } else {
            $message = create_message(FALSE, "soft resetting vm, no vm_id specified");
        }
        break;
    case 'vm_snapshot_revert' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_snapshot_revert($_POST['vm_id'], $_POST['vm_snapshot_instance']);
            $message = create_message($success, "reverting to snapshot of $vm_display_name");
        } else {
            $message = create_message(FALSE, 'reverting to snapshot');
        }
        break;
    case 'vm_snapshot_create' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_create_snapshot($_POST['vm_id']);
            $message = create_message($success, "creating snapshot of $vm_display_name");
        } else {
            $message = create_message(FALSE, "creating snapshot, no vm_id specified");
        }
        break;
    case 'vm_media_change' :
        if (isset($_POST['vm_id'])) {
            $vm_id = $_POST['vm_id'];
            $vm_cdrom_instance = $_POST['cdrom_instance'];
            $volume_id = $_POST['volume_id'];
            $vm_display_name = ace_vm_get_display_name_by_id($vm_id);
            if ($volume_id == 'none') {
                $success = ace_vm_cdrom_eject_media($vm_id, $vm_cdrom_instance);
            } else {
                $success = ace_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $volume_id);
            }
            $message = create_message($success, "changing media in cd$vm_cdrom_instance of $vm_display_name");
        } else {
            $message = create_message(FALSE, "changing media, no vm_id specified");
        }
        break;
}

if (isset($message)) $messages[] = $message;
unset($message);
# END COMMAND PROCESSING

# BEGIN PAGE DATA
if (isset($lab_id) && $lab_id != 'null' && $lab_id != NULL) {
    $valid_lab_selected = TRUE;
    $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
} else {
    $valid_lab_selected = FALSE;
}

$nonce = rand();

$user_id = $_SESSION['user_id'];
$user_info = ace_user_get_info($user_id);
if (($user_info['first'] . $user_info['last']) <> '') {
    $user_display_name = $user_info['first'].' '.$user_info['last'];
} else {
    $user_display_name = $user_info['name'];
}
$page_links = ace_session_get_page_links();

$html_select_vm_console_buttons = '
						<form name="select_console_form" action="' . _CONSOLE_URL_ . '" method="post">
							<input name="lab_id" value="' . $lab_id . '" type="hidden" />
							<input name="action" value="show_vm_console" type="hidden" />';
$vm_table = ace_lab_get_vm_table($lab_id);
foreach ($vm_table as $vm) {
    if ($vm['user_visible'] == 1) {
        $vm_button_class = ($vm['id'] == $_POST['vm_id']) ? 'active' : '';
        $html_select_vm_console_buttons .= '
							<div class="row_element">
								<button name="vm_id" value="' . $vm['id'] . '" class="' . $vm_button_class . '" type="submit">' . $vm['display_name'] . '</button>
							</div>';
    }
}
$html_select_vm_console_buttons .= '
							<input name="nonce" value=' . $nonce . ' type="hidden" />
						</form>';

$vm_cdrom_table = ace_vm_get_cdrom_table($_POST['vm_id']);
$html_vm_cdrom_dropdown_list = NULL;
foreach ($vm_cdrom_table as $cdrom) {
    $volume_display_name = ace_volume_get_display_name_by_id($cdrom['volume_id']);
    $html_volume_display_name = ($volume_display_name) ? (' : ' . $volume_display_name) : '';
    $html_vm_cdrom_dropdown_list .= '<option value="' . $cdrom['instance'] . '">cd' . $cdrom['instance'] . $html_volume_display_name . '</option>';
}
$vm_cdrom_exists = FALSE;
if (is_array($vm_cdrom_table)) {
    $vm_cdrom_exists = TRUE;
}

$iso_table = ace_get_iso_table();
$html_vm_media_dropdown_list = '<option value="none">none</option>';
foreach ($iso_table as $volume) {
    if ($volume['user_visible'] == 1) {
        $volume_display_name = $volume['display_name'];
        $volume_id = $volume['id'];
        $html_vm_media_dropdown_list .= '<option value="' . $volume_id . '">' . $volume_display_name . '</option>';
    }
}

if (isset($_POST['vm_id']) && $_POST['vm_id'] != NULL) {
    $vm_id = $_POST['vm_id'];
    $console_disabled = '';
    //if (ace_vm_is_active($vm_id)) {
    if (ace_vm_get_virt_state($vm_id)) {
        $console_url = ace_vm_get_console_url($vm_id);
        $html_console_control_form_power_button = '<button name="action" value="vm_power_off" type="submit">Power Off</button>';
        $html_console_control_form_shutdown_button = '<button name="action" value="vm_shutdown" type="submit">Shutdown</button>';
        $html_console_control_form_soft_reset_button = '<button name="action" value="vm_soft_reset" type="submit">Ctl-Alt-Del</button>';
    } else {
        $console_url = _CONSOLE_ERROR_URL_;
        $html_console_control_form_power_button = '<button name="action" value="vm_power_on" type="submit">Power On</button>';
        $html_console_control_form_shutdown_button = '<button disabled>Shutdown</button>';
        $html_console_control_form_soft_reset_button = '<button disabled>Ctl-Alt-Del</button>';
    }
} else {
    $console_disabled = 'disabled';
    $console_url = _CONSOLE_ERROR_URL_;
    $html_console_control_form_power_button = '<button disabled>On</button>';
    $html_console_control_form_shutdown_button = '<button disabled>Shutdown</button>';
    $html_console_control_form_soft_reset_button = '<button disabled>Ctl-Alt-Del</button>';
}

$num_vm_snapshots = 0;
if (isset($_POST['vm_id']) && $_POST['vm_id'] != NULL) {
    $vm_id = $_POST['vm_id'];
    $vm_snapshot_list = ace_vm_get_snapshot_list($vm_id);
    $num_vm_snapshots = count($vm_snapshot_list);
    $vm_snapshot_revert_button_disabled = ($num_vm_snapshots < 2) ? 'disabled' : '';
    $vm_snapshot_current_button_disabled = ($num_vm_snapshots == 0) ? 'disabled' : '';
    $vm_snapshot_create_button_disabled = ($num_vm_snapshots > 9) ? 'disabled' : '';

    $vm_snapshot_buttons = '';
    $ix = 0;
    foreach ($vm_snapshot_list as $vm_snapshot_name) {
        $vm_snapshot_buttons .= '<button name="vm_snapshot_instance" value="' . $ix . '" type="submit">' . $ix . '</button>';
        $ix++;
    }
} else {

}


# END PAGE DATA

?>
<!-- HTML PAGE - headers and menu -->
<!doctype html>
<html>
<head profile="http://www.w3.org/2005/10/profile">
    <title>ACEITLab - Console</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="css/rudi.css"/>
    <link rel="icon" type="image/png" href="icon/favicon-32x32.png"/>
    <script type="text/javascript">
        function setFocusToIframe() {
            document.getElementById("console_iframe").focus();
        }
    </script>
</head>
<body>
<!-- BEGIN HEADER -->
<div id="section_header" class="section">
    <div class="header_left">
        <div class="element_table">
            <div class="element_column">
                <div id="site_title" class="element">
                    <p>ACEITLab</p>
                </div>
            </div>
        </div>
    </div>
    <div class="header_left">
        <div class="element_table">
            <div class="element_column">
                <?php echo $page_links; ?>
            </div>
        </div>
    </div>
    <div class="header_right">
        <div class="element_table">
            <div class="element_column">
                <div class="row_element_right">
                    <form action="<?php echo _LOGIN_URL_; ?>" method="post">
                        <button name="action" value="logout" type="submit">Logout</button>
                    </form>
                </div>
                <div class="row_element_right">
                    <form action="<?php echo _USER_URL_; ?>">
                        <button name="action" value="edit_profile"
                                type="submit"><?php echo $user_display_name; ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<!-- END HEADER -->
<!--<div class="horizontal_divider"></div>-->
<!-- BEGIN MENU BAR 1 -->
<div id="menu_bar" class="menu_bar">
    <div class="element_table">
        <div class="element_column">
            <div class="element">
                <form id="back_button_form" class="bar_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <button name="action" value="lab_load" type="submit">Back to Lab</button>
                </form>
            </div>
        </div>
        <div class="element_column">
            <?php echo $html_select_vm_console_buttons; ?>
        </div>
    </div>
    <div class="clear"></div>
</div>
<!-- END MENU BAR 1 -->
<!--<div class="horizontal_divider"></div>-->
<!-- BEGIN MENU BAR 2 -->
<div id="menu_bar2" class="menu_bar active">
    <div class="element_table">
        <div class="element_column">
            <form name="console_control_form" class="bar_form" action="<?php echo _CONSOLE_URL_; ?>" method="post">
                <input id="lab_id" name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                <input id='vm_id' name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                <div class="row_element">
                    <?php echo $html_console_control_form_power_button; ?>
                </div>
                <div class="row_element">
                    <?php echo $html_console_control_form_shutdown_button; ?>
                </div>
                <div class="row_element">
                    <?php echo $html_console_control_form_soft_reset_button; ?>
                </div>
                <input name="nonce" value='<?php echo $nonce; ?>' type="hidden"/>
            </form>
            <form id="vm_power_off_form" name="vm_power_off_form" action="<?php echo _CONSOLE_URL_; ?>" method="post">
                <input id="lab_id" name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                <input id='vm_id' name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                <input id="action" name="action" value="vm_power_off" type="hidden"/>
            </form>
        </div>
<?php
if ($vm_cdrom_exists) {
?>
        <div class="element_column">
            <form name="console_control_form" class="bar_form" action="<?php echo _CONSOLE_URL_; ?>" method="post">
                <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>

                <div class="row_element">
                    Dev:
                    <select name="cdrom_instance" title="cdrom instance"><?php echo $html_vm_cdrom_dropdown_list; ?></select>
                    Media:
                    <select name="volume_id" title="volume id"><?php echo $html_vm_media_dropdown_list; ?></select>
                    <button name="action" value="vm_media_change" type="submit">Change</button>
                </div>
                <input name="nonce" value='<?php echo $nonce; ?>' type="hidden"/>
            </form>
        </div>
<?php
}
?>
        <!--<div class="element_column">
            <div class="row_element">
                <form name="console_control_form" class="bar_form" action="<?php //echo _CONSOLE_URL_; ?>" method="post">
                    <input name="lab_id" value="<?php //echo $lab_id; ?>" type="hidden" />
                    <input name="vm_id" value="<?php //echo $vm_id; ?>" type="hidden" />
                    <input name="action" value="vm_snapshot_revert" type="hidden" />
                    Snapshots: (<?php //echo $num_vm_snapshots; ?>/10)
                    <?php //echo $vm_snapshot_buttons; ?>
                    <input name="nonce" value='<?php //echo $nonce; ?>' type="hidden" />
                </form>
            </div>
            <div class="row_element">
                <form name="console_control_form" class="bar_form" action="<?php //echo _CONSOLE_URL_; ?>" method="post">
                    <input name="lab_id" value="<?php //echo $lab_id; ?>" type="hidden" />
                    <input name="vm_id" value="<?php //echo $vm_id; ?>" type="hidden" />
                    <button name="action" value="vm_snapshot_create" type="submit" <?php //echo $vm_snapshot_create_button_disabled; ?>>+</button>
                    <?php //echo d($_POST); ?>
                    <input name="nonce" value='<?php //echo $nonce; ?>' type="hidden" />
                </form>
            </div>
        </div> -->
    </div>
    <div class="clear"></div>
</div>
<!-- END MENU BAR 2 -->
<!-- BEGIN MAIN BLOCK -->
<div id="console_container" class="vnc_console" onmouseover="setFocusToIframe()">
    <iframe id="console_iframe" src="<?php echo $console_url; ?>"></iframe>
    <div class="clear"></div>
</div>
<!-- END MAIN BLOCK -->
<!-- BEGIN STATUS SECTION -->
<div id="status_section" class="section">
    <div class="message_bar"><?php echo (isset($messages)) ? ace_out_messages($messages) : ''; ?></div>
    <div class="clear"></div>
</div>
<!-- END STATUS SECTION -->
<script src="javascript/jquery-1.11.3.min.js"></script>
<script src="javascript/ajax.js"></script>
<script>
    //var vm_id = $('#vm_id').val();
    //var lab_id = $('#lab_id').val();
    //var current_vm_virt_state;
    //
    //function check_virt_state() {
    //    var query = 'vm_id=' + vm_id;
    //    return ajGetTF(query);
    //}
    //
    //check_virt_state()
    //    .done(function (textTF) {
    //        if (textTF == true) {
    //            current_vm_virt_state = true;
    //        } else {
    //            current_vm_virt_state = false;
    //        }
    //    });

    function resize_console_container() {
        var console_container_height = $(window).height() - 148;
        $('#console_container').height(console_container_height);
    }

    $(document).ready(function () {
        resize_console_container();
        //setInterval(function () {
        //    check_virt_state()
        //        .done(function (textTF) {
        //            if (textTF == false && current_vm_virt_state == true) {
        //                document.getElementById("vm_power_off_form").submit();
        //            }
        //        })
        //},10000);
    });
    $(window).resize(function () {
        resize_console_container();
    });
</script>
</body>
</html>

