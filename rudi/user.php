<?php
/**
 * ACEITLab Application - User Module
 *
 * provides interface for performing user related tasks
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

require_once('fns.php');
session_start();
ace_validate_session(_USER_SECURITY_LEVEL_);
ace_session_redirect_form_refresh(_USER_URL_);

$nonce = rand();

# BEGIN COMMAND PROCESSING
switch ($_POST['operation']) {
	case 'user_save_profile':
        if (isset($_POST['user_id']) && $_POST['user_id'] != '') {
            $user_id = $_POST['user_id'];
            $user_first = $_POST['user_first'];
            $user_last = $_POST['user_last'];
            $user_name = $_POST['user_email'];
            $success = ace_user_update($user_id, $user_name, $user_first, $user_last);
            if ($success) {
                $message = create_message($success, "updating user profile for '$user_name'");
            } else {
                $message = create_message(FALSE, "updating user profile");
            }
        } else {
            $message = create_message(FALSE, "updating user profile, no user specified");
        }
		break;
	case 'user_update_password':
		$user_id = $_POST['user_id'];
		$user_name = ace_user_get_display_name_by_id($user_id);
		$user_password = $_POST['password'];
		if ($user_password != '') {
			$success = ace_user_update_password($user_id, $user_password);
			$message = create_message($success, "updating user password for $user_name");
		} else {
			$message = create_message(FALSE, "updating user password");
		}
		break;
}
if (isset($message)) $messages[] = $message;
unset($message);

switch ($_POST['display']) {
	default:
		$user = ace_user_get_info($_SESSION['user_id']);
        $element = '
                    <p align="center"><strong>Profile</strong></p>';
        $element_column[] = $element;
		$element = '
					<form name="user_profile_form" action="' . _USER_URL_ . '" method="post">
						<input name="display" value="user_profile" type="hidden" />
						<input name="user_id" value="' . $user['id'] . '" type="hidden" />
						<table>
							<tr>
								<td align="right">*email:</td>
								<td><input name="user_email" value="' . $user['name'] . '" type="text" readonly/></td>
							</tr>
							<tr>
								<td align="right">first:</td>
								<td><input name="user_first" value="' . $user['first'] . '" type="text" /></td>
							</tr>
							<tr>
								<td align="right">last:</td>
								<td><input name="user_last" value="' . $user['last'] . '" type="text" /></td>
							</tr>
							<tr>
								<td align="center" colspan="2">
									<button name="operation" value="user_save_profile" type="submit">Save</button>
<!--									<button name="operation" value="form_cancel" type="submit">Cancel</button> -->
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
		$element_column[] = $element;
		$element = '
					<form name="user_profile_password_form" action="' . _USER_URL_ . '" method="post">
						<input name="display" value="user_profile" type="hidden" />
						<input name="user_id" value="' . $user['id'] . '" type="hidden" />
						<table>
							<tr align="center">
								<td>
								    <label for="password">Password:</label>
								    <input id="password" name="password" value="" type="password" />
								</td>
							</tr>
							<tr align="center">
								<td><button name="operation" value="user_update_password" type="submit">Change</button></td>
							</tr>
						</table>
					</form>
		';
		$element_column[] = $element;
		$element_table[] = $element_column;
		break;
}

if (isset($message)) $messages[] = $message;
unset($message);

# BEGIN PAGE DATA
$user_id = $_SESSION['user_id'];
$user_info = ace_user_get_info($user_id);
if (($user_info['first'] . $user_info['last']) <> '') {
    $user_display_name = $user_info['first'].' '.$user_info['last'];
} else {
    $user_display_name = $user_info['name'];
}
$page_links = ace_session_get_page_links();
# END PAGE DATA

?>
<!doctype html>
<html>
	<head>
		<title>ACEITLab</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/rudi.css" />
	</head>
	<body>
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
								<button name="action" value="edit_profile" class="active" type="submit"><?php echo $user_display_name; ?></button>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="horizontal_divider"></div>
		<div id="section_main" class="section">
			<div class="element_table">
<?php 
foreach ($element_table as $element_column) {
	echo '
                <div class="element_column">';
	foreach ($element_column as $element) {
		echo '
                    <div class="element">
                        ' . $element . '
                    </div>';
	}
	echo '
                </div>';
}
?>
			</div>
            <div class="clear"></div>
		</div>
		<div id="status_section" class="section">
		    <div class="message_bar"><?php echo (isset($messages)) ? ace_out_messages($messages) : ''; ?></div>
        </div>
	</body>
</html>















