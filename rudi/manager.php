<?php
/**
 * ACEITLab Application - Management Module
 *
 * provides interface for performing management tasks
 * requires MANAGER security level or higher
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

require_once('fns.php');
session_start();
ace_validate_session(_MANAGER_SECURITY_LEVEL_);
ace_session_redirect_form_refresh(_MANAGER_URL_);

$nonce = rand();
$new_user_initial_password_text_block = '';

$element = '';
$element_column = array();
$element_table = array();

# BEGIN COMMAND PROCESSING
switch ($_POST['operation']) {
	case 'cancel' :
		break;
	case 'group_create' :
		$group_id = ace_group_create($_POST['group_name'], $_SESSION['user_id']);
		$success = ($group_id !== false) ? true : false;
		$message = create_message($success, 'creating group "' . $_POST['group_name'] . '"');
		break;
    case 'class_create':
        if ((isset($_POST['courseID'])) && ($_POST['courseID'] != "")) {
            $course_name = ace_group_get_name_by_id($_POST['courseID']);
            if ((isset($_POST['sectionID'])) && ($_POST['sectionID'] != "")) {
                $new_group_name = $_POST['courseID'] . '-' . $_POST['sectionID'];
                $group_id = ace_group_create($_POST['courseID'] . '-' . $_POST['sectionID'], $_SESSION['user_id']);
                $section_id = ace_group_create_section_info($group_id,$_POST['courseID'],$_POST['sectionID'],$_POST['schedule'],$_POST['comment']);
                if ($section_id) {
                    $message = create_message(TRUE, 'new class created successfully');
                } else {
                    $message = create_message(FALSE, 'new class not created');
                }
            } else {
                $message = create_message(FALSE, 'creating class. Missing Section ID');
            }
        } else {
            $message = create_message(FALSE, 'creating class. Missing Course ID');
        }
        break;
	case 'group_delete' :
		$group_name = ace_group_get_name_by_id($_POST['group_id']);
		$success = ace_group_delete($_POST['group_id']);
		$message = create_message($success, 'deleting group "' . $group_name .'"');
		break;
    case 'class_delete':
        if (isset($_POST['group_id'])) {
            $success = ace_group_delete($_POST['group_id']);
        }
        break;
    case 'group_update' :
		$group_id = $_POST['group_id'];
		$group = ace_group_get_info($group_id);
		//$success = ace_group_update($group_id, $_POST['group_name'], $group['owner']);
		//$message = create_message($success, 'updating group name from ' . $group['name'] . ' to ' . $_POST['group_name']);
		$old_group_state = ($group['state'] == 1) ? true : false;
		$group_state = ($_POST['group_state'] == 'Active') ? true : false;
		if ($old_group_state != $group_state) {
            $success = ace_group_set_state($group_id,$group_state);
			$messages[] = create_message($success, (($group_state) ? 'activating' : 'deactivating') . ' group ' . $_POST['group_name']);
		}
        $success = ace_group_update_section_info($group_id,$_POST['courseID'],$_POST['sectionID'],$_POST['schedule'],$_POST['comment']);
        $messages[] = create_message($success, 'updating section(' . $_POST['sectionID'] . ') info');
		break;
	case 'group_change_state' :
		$group_id = $_POST['group_id'];
		$group_name = ace_group_get_name_by_id($group_id);
		$state = ace_group_get_state($group_id);
		$group_state = ($state) ? false : true;
		$success = ace_group_set_state($group_id, $group_state);
		$message = create_message($success, (($group_state) ? 'activating ' : 'deactivating ') . $group_name . ' group');
		break;
	case 'group_user_add' :
		$group_id = $_POST['group_id'];
		$group_name = ace_group_get_name_by_id($group_id);
		# if form has data
		if ($_POST['user_email'] != '') {
            $user_name = $_POST['user_email'];
            $user_first = $_POST['user_first'];
            $user_last = $_POST['user_last'];
            $email_count = array();
            $new_user_array = array();
            $new_user_initial_password_text_block = '';
            $sanitized_email = filter_var($user_name, FILTER_VALIDATE_EMAIL);
            if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
                $email_count['good']++;
                if (!ace_user_exists($sanitized_email)) {
                    $user_array = ace_user_create($sanitized_email, '', '');
                    $new_user_array[] = $user_array;
                    if ($user_array['user_id'] !== FALSE) {
                        $email_count['created']++;
                    }
                    ace_group_add_user($group_id, $user_array['user_id']);
                    $email_count['added']++;
                } else {
                    $user_id = ace_user_get_id_by_name($sanitized_email);
                    if (!ace_group_user_is_member($group_id, $user_id)) {
                        ace_group_add_user($group_id, $user_id);
                        $email_count['added']++;
                    }
                }
            } else {
                $email_count['bad']++;
            }
            if ($email_count['good'] > 0) {
                $messages[] = create_message(TRUE, 'importing user(s): ' . $email_count['good'] . ' good email(s)');
                if ($email_count['created'] > 0) {
                    $messages[] = create_message(TRUE, 'creating user(s): ' . $email_count['created'] . 'user(s) created');
                    foreach ($new_user_array as $new_user) {
                        $new_user_initial_password_text_block .= ace_user_get_name_by_id($new_user['user_id']) . ' : ' . $new_user['password'] . '&#13;&#10;';
                    }
                    $new_user_initial_password_text_block = substr($new_user_initial_password_text_block, 0, -10);
                }
                $messages[] = create_message(TRUE, 'adding user(s) to group: ' . $email_count['added'] . ' user(s) added to this group');
            } else {
                $messages[] = create_message(FALSE, 'importing user(s): no good emails found');
            }
            if ($email_count['bad'] > 0) {
                $messages[] = create_message(FALSE, 'importing user(s): ' . $email_count['bad'] . ' bad email(s)');
            }
        }
        break;
    case "group_userList_add":
        $group_id = $_POST['group_id'];
        $group_name = ace_group_get_name_by_id($group_id);
        # if form has data
        if ($_POST['user_email_list'] != '') {
            $student_email_list = preg_split("/[\r\n]+/", $_POST['user_email_list'], -1, PREG_SPLIT_NO_EMPTY);
            $email_count = array();
            $new_user_array = array();
            $new_user_initial_password_text_block ='';
            foreach ($student_email_list as $student_email) {
				$sanitized_email = filter_var($student_email, FILTER_VALIDATE_EMAIL);
                if (filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
                    $email_count['good']++;
                    if (!ace_user_exists($sanitized_email)) {
                        $user_array = ace_user_create($sanitized_email,'','');
                        $new_user_array[] = $user_array;
                        if ($user_array['user_id'] !== FALSE){
                            $email_count['created']++;
                        }
                        ace_group_add_user($group_id,$user_array['user_id']);
                        $email_count['added']++;
                    } else {
                        $user_id = ace_user_get_id_by_name($sanitized_email);
                        if (!ace_group_user_is_member($group_id, $user_id)) {
                            ace_group_add_user($group_id,$user_id);
                            $email_count['added']++;
                        }
                    }
                } else {
                    $email_count['bad']++;
                }
            }
            if ($email_count['good'] > 0) {
                $messages[] = create_message(TRUE, 'importing user(s): ' . $email_count['good'] . ' good email(s) ');
                if ($email_count['created'] > 0) {
                    $messages[] = create_message(TRUE,'creating user(s): ' . $email_count['created'] . ' user(s) created');
                    foreach ($new_user_array as $new_user) {
                        $new_user_initial_password_text_block .= ace_user_get_name_by_id($new_user['user_id']) . ' : ' . $new_user['password'] . '&#13;&#10;';
                    }
                    $new_user_initial_password_text_block = substr($new_user_initial_password_text_block,0,-10);
                }
                $messages[] = create_message(TRUE, 'adding user(s) to group: ' . $email_count['added'] . ' user(s) added to this group');
            } else {
                $messages[] = create_message(FALSE, 'importing user(s): no good emails found');
            }
            if ($email_count['bad'] > 0) {
                $messages[] = create_message(FALSE,'importing user(s): ' . $email_count['bad'] . ' bad email(s)');
            }
        }
        break;
	case 'group_user_remove' :
		if ($_POST['user_id']) {
			$group_id = $_POST['group_id'];
			$group_name = ace_group_get_name_by_id($group_id);
			$user_id = $_POST['user_id'];
			$user_name = ace_user_get_name_by_id($user_id);
			$success = ace_group_remove_user($group_id, $user_id);
			$messages[] = create_message($success, 'removing ' . $user_name . ' from ' . $group_name . ' group');
		} else {
			$messages[] = create_message(FALSE, "removing member from group, no user_id specified");
		}
        break;
	case 'group_user_update' :
		if ($_POST['user_id']) {
			$user_id = $_POST['user_id'];
			$user_name = $_POST['user_email'];
			$user_first = $_POST['user_first'];
			$user_last = $_POST['user_last'];
			$success = ace_user_update($user_id, $user_name, $user_first, $user_last);
            if ($success) {
                $message = create_message($success, 'User updated');
            } else {
                $message = create_message($success, 'User NOT updated');
            }
		} else {
			$message = create_message(FALSE, "updating user, no user_id specified");
		}
		break;
    case 'group_user_reset_password':
        $group_id = $_POST['group_id'];
        $group_name = ace_group_get_name_by_id($group_id);
        $user_id = $_POST['user_id'];
        $user_name = ace_user_get_name_by_id($user_id);
        $new_password = ace_user_reset_password($user_id);
        $success = ($new_password !== FALSE) ? TRUE : FALSE;
        $messages[] = create_message($success, 'resetting user (' . $user_name . ') password to "' . $new_password . '"');
        break;
	case 'group_lab_add' :
		$group_id = $_POST['group_id'];
		$group_name = ace_group_get_name_by_id($group_id);
		# if form has data
		if ($_POST['selected_lab_id'] != '') {
			$lab_id = $_POST['selected_lab_id'];
			$lab_display_name = ace_lab_get_display_name_by_id($lab_id);
			$success = ace_group_add_lab($group_id,$lab_id);
			$message = create_message($success, "adding '$lab_display_name' to '$group_name' group");
		} else {
			$message = create_message(FALSE, "adding a lab, no lab_id specified");
		}
		break;
	case 'group_lab_remove' :
		$group_id = $_POST['group_id'];
		$group_name = ace_group_get_name_by_id($group_id);
		if ($_POST['selected_lab_id'] != '') {
			$lab_id = $_POST['selected_lab_id'];
			$lab_display_name = ace_lab_get_display_name_by_id($lab_id);
			$success = ace_group_remove_lab($group_id, $lab_id);
			$message = create_message($success, "revoking '$lab_display_name' from '$group_name' group");
		} else {
			$message = create_message(FALSE, "revoking a lab, no lab_id specified");
		}
		break;
}
if (isset($message)) $messages[] = $message;
unset($message);

switch ($_POST['display']) {
    case 'class_create_form':
        $element = '
                    <p align="center">Create Class</p>
                    <form name="class_create_form" action="' . _MANAGER_URL_ . '" method="post">
                        <input name="display" value="groups" type="hidden" />
                        <table>
                            <tr>
                                <td align="right">Section ID:</td>
                                <td>
                                    <input name="sectionID" value="" type="text" />
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Course Ref:</td>
                                <td>
                                    <select name="courseID">';

        $known_courses = ace_get_courses();
        if (is_array($known_courses)) {
            foreach ($known_courses as $course) {
                $element .= '
                                            <option value="' . $course['courseID'] . '">' . $course['courseID'] . ' - ' . $course['courseDisplayName'] . '</option>';
            }
        }
        $element .= '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Schedule:</td>
                                <td>
                                    <input name="schedule" value="" type="text" />
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Comment:</td>
                                <td>
                                    <input name="comment" value="" type="text" />
                                </td>
                            </tr>
                        </table>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                        <input name="operation" value="class_create" type="hidden" />
                        <input name="save" value="Create" type="submit" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
	case 'group_update_form' :
		if ((isset($_POST['group_id'])) && ($_POST['group_id'] != NULL)) {
			$group_id = $_POST['group_id'];
			$group = ace_group_get_info($group_id);
            $section = ace_group_get_section_info($group_id);
			$element = '
			        <p align="center"><strong>Class</strong></p>';
            $element_column[] = $element;
            $element = '
					<form name="group_update_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="groups" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<input name="group_name" value="' . $group['name'] . '" type="hidden" />
						<table>
							<tr>
							    <td align="right">Course Ref:</td>
							    <td colspan="2">
							        ' . $section['courseID'] . '
							        <input name="courseID" value="' . $section['courseID'] . '" type="hidden" />
							    </td>
							</tr>
							<tr>
							    <td align="right">Section ID:</td>
							    <td colspan="2">
							        ' . $section['sectionID'] . '
							        <input name="sectionID" value="' . $section['sectionID'] . '" type="hidden" />
							    </td>
							</tr>
							<tr>
							    <td align="right">Schedule:</td>
							    <td colspan="2">
							        <input name="schedule" value="' . $section['schedule'] . '" type="text" />
							    </td>
							</tr>
							<tr>
							    <td align="right">Comment:</td>
							    <td colspan="2">
							        <input name="comment" value="' . $section['comment'] . '" type="text" />
							    </td>
							</tr>
                            <tr>
								<td align="right">Active?</td>
								<td>
									<input name="group_state" type="checkbox" value="Active" ' . (($group['state']) ? 'checked' : '') . '/>
								</td>
								<td align="right">
									<button name="operation" value="group_update" type="submit">Save</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
			$element_column[] = $element;
            $lab_table = ace_group_get_lab_table($group_id);
            $element = '
					<p align="center"><strong>Labs</strong></p>
					<form name="group_list_labs_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="group_update_form" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<table>
							<tr>
								<th></th>
								<th></th>
								<th></th>
							</tr>';
            foreach ($lab_table as $row) {
                $lab_active	= ($row['state'] == 1) ? true : false;
                $element .= '
							<tr class="' . (($lab_active) ? 'active' : 'inactive') . '">
								<td><input name="selected_lab_id" value="' . $row['id'] . '" type="radio" /></td>
								<td>' . $row['display_name'] . '</td>
								<td>' . $row['description'] . '</td>
							</tr>';
            }
            $element .= '
						</table>
						<button name="operation" value="group_lab_remove" type="submit" ' . ((is_array($lab_table)) ? '' : 'disabled') . '>Revoke</button>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
			$element = '
					<p align="center"><strong>Students</strong></p>
					<form name="group_user_update_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="group_update_form" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<table>
							<tr>
								<th></th>
								<th></th>
							</tr>
							';
			$group_members_table = ace_group_get_members_table($group_id);
			foreach ($group_members_table as $member) {
				$user_active = ace_user_get_state($member['user_state']);
				$element .= '
							<tr class="' . (($user_active) ? 'active' : 'inactive') . '">
								<td>
								    <input name="user_id" value="' . $member['user_id'] . '" type="radio" />
								</td>
								<td>' . $member['user_name'] . '</td>
							</tr>';
			}
			$element .= '
						</table>
						<table>
						    <tr>
						        <td colspan="2">
						            <button name="display" value="group_user_update_form" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Edit</button>
						            <button name="operation" value="group_user_remove" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Remove</button>
						        </td>
						        <td align="right">
						            <button name="operation" value="group_user_reset_password" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Reset Pwd</button>
						        </td>
                            </tr>
                        </table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
			$element_column[] = $element;
            $element_table[] = $element_column;
            $element_column = array();
            $element = '
                    <p align="center"><strong>Add Student(s)</strong></p>';
            $element_column[] = $element;
			$element = '
			        <form name="group_user_add_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="group_update_form" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<table>
							<tr>
								<td align="right">email:</td>
								<td>
								    <input name="user_email" value="" type="email" />
								</td>
		                        <td>
		                            <button name="operation" value="group_user_add" type="submit">Add</button>
                                </td>
							</tr>
                        </table>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
            $element_column[] = $element;
            $element = '
                    <form name="group_users_add_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="group_update_form" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<table>
							<tr>
							    <td colspan="3">
                                    <label>
                                        <textarea name="user_email_list" rows="10" cols="35" style="resize:none;"></textarea>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" colspan="3">
                                    <button name="operation" value="group_userList_add" type="submit">Import</button>
                                </td>
                            </tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
			$element_column[] = $element;
            if ($new_user_initial_password_text_block <> '') {
                $element = '
                        <p align="center"><strong>New Student Initial Password(s)</strong></p>
                        <textarea rows=' . count($new_user_array) . ' cols="35" style="resize:none;" readonly>' . $new_user_initial_password_text_block . '</textarea>';
                $element_column[] = $element;
            }
            $element_table[] = $element_column;
			$element_column = array();
			$element = '
					<p align="center"><strong>My Labs</strong></p>';
            $element_column[] = $element;
            $element = '
					<form name="group_publish_lab_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="group_update_form" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />';
            $lab_table = ace_user_get_lab_table($_SESSION['user_id']);
            if (is_array($lab_table)) {
                $element .= '
                        <table>';
                foreach ($lab_table as $lab) {
                    $element .= '
                            <tr>
                                <td>
                                    <input name="selected_lab_id" value="' . $lab['id'] . '" type="radio"/>
                                </td>
                                <td>
                                    ' . $lab['display_name'] . '
                                </td>
                            </tr>';
                }
                $element .= '
                            <tr>
                                <td align="right" colspan="2">
                                    <button name="operation" value="group_lab_add" type="submit">Publish</button>
                                </td>
                            </tr>
                        </table>';
            }
            $element .= '
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
			$element_column[] = $element;
			$element_table[] = $element_column;
			$element_column = array();
		} else {
			$message = create_message(FALSE, 'selecting group, no group selected');
		}
		break;
	case 'group_user_update_form' :
		$group_id = $_POST['group_id'];
		$user_id = $_POST['user_id'];
		$user = ace_user_get_info($user_id);
		$element = '
					<form name="group_user_update_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="group_update_form" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<input name="user_id" value="' . $user_id . '" type="hidden" />
						<table>
							<tr>
								<td align="right">*email:</td>
								<td><input name="user_email" value="' . $user['name'] . '" type="email" /></td>
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
								<td align="right" colspan="2">
									<button name="operation" value="group_user_update" type="submit">Save</button>
									<button name="operation" value="form_cancel" type="submit">Cancel</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
		$element_column[] = $element;
		$element_table[] = $element_column;
		break;
	default:
		$group_table = ace_user_get_owned_groups($_SESSION['user_id']);
		$element = '
					<p align="center"><strong>Classes</strong></p>';
        $element_column[] = $element;
        $element = '
                    <form name="manage_classes_form" action="' . _MANAGER_URL_ . '" method="post">
						<input name="display" value="groups" type="hidden" />
						<table>
							<tr>
								<th></th>
								<th>Course Ref</th>
								<th>Course Name</th>
								<th>Section ID</th>
								<th>Schedule</th>
								<th>Roll</th>
								<th>Comment</th>
							<tr>';
		foreach ($group_table as $group) {
			$group_active = ($group['state'] == 1) ? true : false;
			$element .= '
							<tr class="' . (($group_active) ? 'active' : 'inactive') . '">
								<td style="width: 30px;">
									<input name="group_id" value="' . $group['id'] . '" type="radio" />
								</td>';
			$section = ace_group_get_section_info($group['id']);
            $course_display_name = ace_course_get_display_name_by_ref($section['courseID']);
            $section_members_table = ace_group_get_members_table($group['id']);
            $section_enrollment = is_array($section_members_table) ? count($section_members_table) : 0;
            $element .= '
		                        <td>' . $section['courseID'] . '</td>
                                <td>' . $course_display_name . '</td>
		                        <td>' . $section['sectionID'] . '</td>
		                        <td>' . $section['schedule'] . '</td>
		                        <td>' . $section_enrollment . '</td>
		                        <td>' . $section['comment'] . '</td>';
            $element .= '
							</tr>';
		}
		$element .= '
						</table>
						<button name="operation" value="group_change_state" type="submit">(De)Activate</button>
						<button name="display" value="group_update_form" type="submit">Edit</button>
						<button name="operation" value="group_delete" type="submit">Delete</button>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
		$element_column[] = $element;
        $element = '
                    <form name="class_create_form" action="' . _MANAGER_URL_ . '" method="post">
                        <input name="display" value="class_create_form" type="hidden" />
                        <button name="operation" value="" type="submit">Create</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
		$element_column[] = $element;
		$element_table[] = $element_column;
		break;
}
if (isset($message)) $messages[] = $message;
unset($message);
# END COMMAND PROCESSING

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
<!-- HTML PAGE - headers and menu -->
<!doctype html>
<html>
	<head>
		<title>ACEITLab</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/rudi.css" />
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
								<button name="action" value="edit_profile" type="submit"><?php echo $user_display_name; ?></button>
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
					<form id="manage_menu_form" class="bar_form" action="<?php echo _MANAGER_URL_; ?>" method="post">
						<input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden" />
						<div class="row_element">
							<button name="display" value="groups" type="submit" <?php echo 'class="' . (($_POST['display'] == 'groups' || $_POST['display'] == '') ? 'selected' : '') . '"'; ?>>Classes</button>
							<!--<button name="display" value="labs" type="submit">Labs</button>-->
							<button name="display" value="resources" type="submit" <?php echo 'class="' . (($_POST['display'] == 'resources') ? 'selected' : '') . '"'; ?> disabled>Resources</button>
						</div>
						<input name="nonce" value='<?php echo $nonce; ?>' type="hidden" />
					</form>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<!-- END MENU BAR 1 -->
		<!--<div class="horizontal_divider"></div>-->
		<!-- BEGIN MAIN BLOCK -->
		<div id="main" class="main">
			<div class="element_table">
<?php 
foreach ($element_table as $element_column) {
	echo '
                <div class="element_column">';
	foreach ($element_column as $element) {
		echo '
                    <div class="element">' . $element . '</div>';
	}
	echo '
                </div>';
}
?>
			</div>
			<div class="clear"></div>
		</div>
		<!-- END MAIN BLOCK -->
		<!-- BEGIN STATUS SECTION -->
		<div id="status_section" class="section">
			<div class="message_bar"><?php echo (isset($messages)) ? ace_out_messages($messages) : ''; ?></div>
		</div>
		<!-- END STATUS SECTION -->
	</body>
</html>
