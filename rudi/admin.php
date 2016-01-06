<?php
/**
 * ACEITLab Application - Admin Module
 *
 * provides interface for performing administrative tasks
 * requires ADMIN security level or higher
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
ace_validate_session(_ADMIN_SECURITY_LEVEL_);
ace_session_redirect_form_refresh(_ADMIN_URL_);

$nonce = rand();
$new_user_initial_password_text_block = '';
$element_table = array();
$element_column = array();
$element = '';

# BEGIN COMMAND PROCESSING
//switch ($_POST['pre_action']) {
//    case 'save_host' :
//        if (isset($_POST['host_id']) && $_POST['host_id'] != '') {
//            $host_id = $_POST['host_id'];
//            $host_name = $_POST['host_name'];
//            $host_domain = $_POST['host_domain'];
//            $host_description = $_POST['host_description'];
//            $host_hypervisor = $_POST['host_hypervisor'];
//            $host_ip_internal = $_POST['host_ip_internal'];
//            $host_ip_external = $_POST['host_ip_external'];
//            $host_username = $_POST['host_username'];
//            $host_password = $_POST['host_password'];
//            $host_threads = $_POST['host_threads'];
//            $host_memory = $_POST['host_memory'];
//            $host_storage = $_POST['host_storage'];
//            $success = ace_host_update($host_id, $host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage);
//            $host_state = ($_POST['host_state'] == 'Active') ? TRUE : FALSE;;
//            if ($host_state) {
//                $db_success = ace_host_activate($host_id);
//            } else {
//                $db_success = ace_host_deactivate($host_id);
//            }
//            $message = create_message($success, "updating host '$host_name'");
//        } elseif (isset($_POST['host_name'])) {
//            $host_name = $_POST['host_name'];
//            $host_domain = $_POST['host_domain'];
//            $host_description = $_POST['host_description'];
//            $host_hypervisor = $_POST['host_hypervisor'];
//            $host_ip_internal = $_POST['host_ip_internal'];
//            $host_ip_external = $_POST['host_ip_external'];
//            $host_username = $_POST['host_username'];
//            $host_password = $_POST['host_password'];
//            $host_threads = $_POST['host_threads'];
//            $host_memory = $_POST['host_memory'];
//            $host_storage = $_POST['host_storage'];
//            $host_id = ace_host_add($host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage);
//            $success = (is_numeric($host_id)) ? TRUE : FALSE;
//            if ($success) {
//                $db_success = ace_host_add_role($host_id, $_POST['host_role_id']);
//            }
//            $message = create_message($success, "adding host '$host_name'");
//        } else {
//            $message = create_message(FALSE, "adding host, not enough data specified");
//        }
//        break;
//    case 'host_add_role' :
//        $host_id = $_POST['host_id'];
//        $host_name = ace_host_get_name_by_id($host_id);
//        $success = ace_host_add_role($host_id, $_POST['select_host_role_id']);
//        $message = create_message($success, "adding a host role to '$host_name'");
//        break;
//    case 'host_remove_role' :
//        $host_id = $_POST['host_id'];
//        $host_name = ace_host_get_name_by_id($host_id);
//        $success = ace_host_remove_role($host_id, $_POST['radio_host_role_id']);
//        $message = create_message($success, "removing a host role from '$host_name'");
//        break;
//    case 'remove_host' :
//        $host_id = $_POST['host_id'];
//        $host_name = ace_host_get_name_by_id($host_id);
//        $success = ace_host_remove($host_id);
//        $message = create_message($success, "removing host '$host_name'");
//        break;
//    case 'save_user' :
//        if (isset($_POST['user_id']) && $_POST['user_id'] != '') {
//            $user_id = $_POST['user_id'];
//            $user_first = $_POST['user_first'];
//            $user_last = $_POST['user_last'];
//            $user_name = $_POST['user_email'];
//            $success = ace_user_update($user_id, $user_name, $user_first, $user_last);
//            //$user_state = ($_POST['user_state'] == 'Active') ? TRUE : FALSE;
//            $success = $_POST['user_state'] == 'Active' ? ace_user_activate($user_id) : ace_user_deactivate($user_id);
//            //$success = ace_user_set_state($user_id, $user_state);
//            $message = create_message($success, "updating user '$user_name'");
//        } elseif (isset($_POST['user_first']) && isset($_POST['user_last']) && isset($_POST['user_email'])) {
//            $user_first = $_POST['user_first'];
//            $user_last = $_POST['user_last'];
//            $user_name = $_POST['user_email'];
//            $cred = ace_user_create($user_name, $user_first, $user_last);
//            //$success = (is_numeric($cred['user_id'])) ? TRUE : FALSE;
//            if (is_numeric($cred['user_id'])) {
//                $message = create_message(TRUE, "creating account for '$user_name' with initial password '" . $cred['password'] . "'");
//            } else {
//                $message = create_message(FALSE, "could not create user account");
//            }
//            //$message = create_message($success, "creating account for '$user_name' with initial password '" . $cred['password'] . "'");
//        } else {
//            $message = create_message(FALSE, 'No user account details to process');
//        }
//        break;
//    case 'change_user_password' :
//        if (isset($_POST['user_id'])) {
//            if ($_POST['user_password'] == $_POST['user_confirm']) {
//                $user_id = $_POST['user_id'];
//                $user_name = ace_user_get_name_by_id($user_id);
//                $user_password = $_POST['user_password'];
//                $success = ace_user_update_password($user_id, $user_password);
//                $message = create_message($success, 'updating user password for ' . $user_name);
//            } else {
//                $message = create_message(FALSE, 'updating user password, entries do not match');
//            }
//        }
//        break;
//    case 'delete_user' :
//        $user_id = $_POST['user_id'];
//        $user_name = ace_user_get_name_by_id($user_id);
//        $success = ace_user_delete($user_id);
//        $message = create_message($success, "deleting user '$user_name'");
//        break;
//    case 'save_group' :
//        if (isset($_POST['group_id']) && $_POST['group_id'] != '') {
//            $group_id = $_POST['group_id'];
//            $group_name = $_POST['group_name'];
//            $group_owner_id = $_POST['group_owner_id'];
//            $success = ace_group_update($group_id, $group_name, $group_owner_id);
//            $group_state = ($_POST['group_state'] == 'Active') ? TRUE : FALSE;
//            $success = ace_group_set_state($group_id, $group_state);
//            $message = create_message($success, "Updating group '$group_name'");
//        } elseif (isset($_POST['group_name']) && isset($_POST['group_owner'])) {
//            $group_name = $_POST['group_name'];
//            $group_owner_id = $_POST['group_owner_id'];
//            $group_id = ace_group_create($group_name, $group_owner_id);
//            $success = (is_numeric($group_id)) ? TRUE : FALSE;
//            $message = create_message($success, "Creating group");
//        } else {
//            $message = create_message(FALSE, 'Group account failure');
//        }
//        break;
//    case 'delete_group' :
//        $group_id = $_POST['group_id'];
//        $group_name = ace_group_get_name_by_id($group_id);
//        $success = ace_group_delete($group_id);
//        $message = create_message($success, "Deleting group '$group_name'");
//        break;
//    case 'remove_user_from_group' :
//        $user_id = $_POST['user_id'];
//        $user_name = ace_user_get_display_name_by_id($user_id);
//        $group_id = $_POST['group_id'];
//        $group_name = ace_group_get_name_by_id($group_id);
//        $success = ace_group_remove_user($group_id, $user_id);
//        $message = create_message($success, "removing user $user_name from group $group_name");
//        break;
//}
//if (isset($message)) $messages[] = $message;
//unset($message);
//
//switch ($_POST['action']) {
//    case 'show_hosts' :
//        $host_table = ace_get_hosts();
//        $html = '
//			<div class="element_table">';
//        foreach ($host_table as $host_record) {
//            $html_hypervisor_info = ace_out_array_as_html_record(ace_host_get_hypervisor_info($host_record['id']));
//            $html_physical_info = ace_out_array_as_html_record(ace_host_get_physical_info($host_record['id']));
//            $host_network_list = ace_host_get_virt_network_list($host_record['id']);
//            $host_network_count = count($host_network_list);
//            $host_volume_list = ace_host_get_virt_volume_list($host_record['id']);
//            $host_volume_count = count($host_volume_list);
//            $host_vm_list = ace_host_get_virt_vm_list($host_record['id']);
//            $host_vm_count = count($host_vm_list);
//
//            $host_state = ($host_record['state'] == 1) ? '<strong>Yes</strong>' : '<strong>No</strong>';
//            $host_class = ($host_record['state'] == 1) ? 'active' : 'inactive';
//            $html .= '
//				<div class="element_column vm">
//					<div class="element">
//						<div class="host_active_indicator ' . $host_class . '">
//							<table>
//								<tr>
//									<th>' . $host_record['name'] . '</th>
//								</tr>
//							</table>
//						</div>
//					</div>
//					<div class="element">
//                        <div style="float:right;">
//                            <form name="host_form" action="' . _ADMIN_URL_ . '" method="post">
//                                <input name="nonce" value=' . $nonce . ' type="hidden" />
//                                <input name="host_id" value="' . $host_record['id'] . '" type="hidden" />
//                                <input name="action" value="show_hosts" type="hidden" />
//                            </form>
//						</div>
//						<div>
//                            <form name="host_form" action="' . _ADMIN_URL_ . '" method="post">
//                                <input name="nonce" value=' . $nonce . ' type="hidden" />
//                                <input name="host_id" value="' . $host_record['id'] . '" type="hidden" />
//                                <button name="action" value="form_edit_host" type="submit">Edit</button>
//                            </form>
//                        </div>
//
//					</div>
//					<div class="element">
//						<table>
//							<tr>
//								<td>FQDN:</td>
//								<td> ' . $host_record['name'] . '.' . $host_record['domain'] . '</td>
//							</tr>
//							<tr>
//								<td>IP:</td><td>' . $host_record['ip_internal'] . '</td>
//							</tr>
//							<tr>
//								<td>username:</td><td>' . $host_record['username'] . '</td>
//							</tr>
//							<tr>
//								<td>Active:</td><td>' . $host_state . '</td>
//							</tr>
//						</table>
//					</div>';
//            if ($host_record['state'] == 1) {
//                $html .= '
//					<div class="element">';
//                $html .= $html_hypervisor_info;
//                $html .= '
//					</div>
//					<div class="element">';
//                $html .= $html_physical_info;
//                $html .= '
//					</div>
//					<div class="element">
//						<table width="100%">';
//                $html .= '
//							<tr>
//								<td>VMs:</td>
//								<td>' . $host_vm_count . '</td>
//							</tr>
//							<tr>
//								<td>VNets:</td>
//								<td>' . $host_network_count . '</td>
//							</tr>
//							<tr>
//								<td>Vols:</td>
//								<td>' . $host_volume_count . '</td>
//							</tr>
//						</table>
//					</div>';
//            }
//            $html .= '
//				</div>';
//        }
//        $html .= '
//			</div>';
//        break;
//    case 'form_add_host' :
//        $host_roles = ace_get_host_roles();
//        $html_select_host_roles = '';
//        foreach ($host_roles as $role) {
//            $html_select_host_roles .= '<option value="' . $role['id'] . '">' . $role['name'] . '</value>';
//        }
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_new_host" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<table>
//								<tr>
//									<td align="right">Hostname:</td>
//									<td><input name="host_name" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Domain:</td>
//									<td><input name="host_domain" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Description:</td>
//									<td><input name="host_description" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Role:</td>
//									<td>
//										<select name="host_role_id">
//											' . $html_select_host_roles . '
//										</select>
//									</td>
//								</tr>
//								<tr>
//									<td align="right">Hypervisor:</td>
//									<td>
//										<select name="host_hypervisor">
//											<option value="hv">Hyper-V</option>
//											<option value="kvm" selected>KVM</option>
//											<option value="lxc">LXC</option>
//											<option value="xen">XEN</option>
//										</select>
//									</td>
//								</tr>
//								<tr>
//									<td align="right">IP internal:</td>
//									<td><input name="host_ip_internal" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">IP external:</td>
//									<td><input name="host_ip_external" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">username:</td>
//									<td><input name="host_username" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">password:</td>
//									<td><input name="host_password" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Threads:</td>
//									<td><input name="host_threads" value="1" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Memory (GiB):</td>
//									<td><input name="host_memory" value="1" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Storage (GiB):</td>
//									<td><input name="host_storage" value="1" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<input name="display" value="host_list" type="hidden" />
//										<button name="pre_action" value="save_host" type="submit">Add</button>
//										<button type="reset">Clear</button>
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'form_edit_host' :
//        $host_id = $_POST['host_id'];
//        $host_record = ace_host_get_info($host_id);
//        $active = ($host_record['state'] == 1) ? TRUE : FALSE;
//        $has_labs = ace_host_has_active_labs($host_id);
//
//        $host_roles = ace_host_get_roles($host_id);
//        $html_host_role_radios = '';
//        foreach ($host_roles as $role) {
//            $html_host_role_radios .= '<tr><td><input name="radio_host_role_id" value="' . $role['host_role_id'] . '" type="radio">' . $role['host_role_name'] . '</td></tr>';
//        }
//
//        $all_host_roles = ace_get_host_roles();
//        $html_select_host_roles = '';
//        foreach ($all_host_roles as $role) {
//            $html_select_host_roles .= '<option value="' . $role['id'] . '">' . $role['name'] . '</value>';
//        }
//
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_edit_host" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="host_id" value="' . $host_id . '" type="hidden" />
//							<table>
//								<tr>
//									<td align="right">Hostname:</td>
//									<td><input name="host_name" value="' . $host_record['name'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Domain:</td>
//									<td><input name="host_domain" value="' . $host_record['domain'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Description:</td>
//									<td><input name="host_description" value="' . $host_record['description'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Hypervisor:</td>
//									<td>
//										<select name="host_hypervisor">
//											<option value="hv" ' . (($host_record['hypervisor'] == 'hv') ? 'selected' : '') . '>Hyper-V</option>
//											<option value="kvm" ' . (($host_record['hypervisor'] == 'kvm') ? 'selected' : '') . '>KVM</option>
//											<option value="lxc" ' . (($host_record['hypervisor'] == 'lxc') ? 'selected' : '') . '>LXC</option>
//											<option value="xen" ' . (($host_record['hypervisor'] == 'xen') ? 'selected' : '') . '>XEN</option>
//										</select>
//									</td>
//								</tr>
//								<tr>
//									<td align="right">IP internal:</td>
//									<td><input name="host_ip_internal" value="' . $host_record['ip_internal'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">IP external:</td>
//									<td><input name="host_ip_external" value="' . $host_record['ip_external'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">username:</td>
//									<td><input name="host_username" value="' . $host_record['username'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">password:</td>
//									<td><input name="host_password" value="' . $host_record['password'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Threads:</td>
//									<td><input name="host_threads" value="' . $host_record['threads'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Memory (GiB):</td>
//									<td><input name="host_memory" value="' . $host_record['memory'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Storage (GiB):</td>
//									<td><input name="host_storage" value="' . $host_record['storage'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Active?</td>
//									<td><input name="host_state" type="checkbox" value="Active" ' . (($active) ? 'checked' : '') . ' ' . (($has_labs) ? 'disabled' : '') . '/></td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<button name="pre_action" value="save_host" type="submit">Save Changes</button>
//										<abbr title="USE WITH CAUTION!">
//										    <button name="pre_action" value="remove_host" type="submit" ' . (($active) ? 'disabled' : '') . '>Remove Host</button>
//                                        </abbr>
//<!--										<button type="cancel">Cancel</button> -->
//									</td>
//								</tr>
//							</table>
//							<input name="display" value="host_list" type="hidden" />
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//						</form>
//					</div>
//					<div class="element">
//						<form name="form_host_roles" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<input name="action" value="show_hosts" type="hidden" />
//							<input name="host_id" value="' . $host_id . '" type="hidden" />
//							<table>
//								<tr>
//									<th>Roles:</th>
//								</tr>
//							' . $html_host_role_radios . '
//								<tr>
//									<td>
//										<button name="pre_action" value="host_remove_role" type="submit">Remove</button>
//									</td>
//								</tr>
//								<tr>
//									<td>
//										<select name="select_host_role_id">
//											' . $html_select_host_roles . '
//										</select>
//										<button name="pre_action" value="host_add_role" type="submit">Add</button>
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'form_new_user' :
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_new_user" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<table>
//								<tr>
//									<td align="right">First:</td>
//									<td><input name="user_first" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Last:</td>
//									<td><input name="user_last" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">eMail:</td>
//									<td><input name="user_email" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<input name="action" value="list_users_by_name" type="hidden" />
//										<button name="pre_action" value="save_user" type="submit">Save</button>
//<!--										<button type="cancel">Cancel</button> -->
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'form_edit_user' :
//        $user_id = $_POST['user_id'];
//        $user_record = ace_user_get_info($user_id);
//        $active = ($user_record['state']) ? TRUE : FALSE;
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="edit_user_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<input name="action" value="list_users_by_name" type="hidden" />
//							<table>
//								<tr>
//									<td align="right">db_id:</td>
//									<td><input name="user_id" value="' . $user_record['id'] . '" type="hidden" />' . $user_record['id'] . '</td>
//								</tr>
//								<tr>
//									<td align="right">First:</td>
//									<td><input name="user_first" value="' . $user_record['first'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Last:</td>
//									<td><input name="user_last" value="' . $user_record['last'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">eMail:</td>
//									<td><input name="user_email" value="' . $user_record['name'] . '" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Password:</td>
//									<td>
//										<button name="action" value="form_change_user_password" type="submit"">Change</button>
//									</td>
//								</tr>
//								<tr>
//									<td align="right">Active?</td>
//									<td><input name="user_state" type="checkbox" value="Active" ' . (($active) ? 'checked' : '') . '/></td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<button name="pre_action" value="save_user" type="submit">Save Changes</button>
//										<button name="pre_action" value="delete_user" type="submit">Delete User</button>
//<!--										<button type="cancel">Cancel</button> -->
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'form_change_user_password' :
//        $user_id = $_POST['user_id'];
//        $user_record = ace_user_get_info($user_id);
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="change_user_password_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<input name="user_id" value="' . $user_record['id'] . '" type="hidden" />
//							<table>
//								<tr>
//									<td align="right">eMail:</td>
//									<td>' . $user_record['name'] . '</td>
//								</tr>
//								<tr>
//									<td align="right">Password:</td>
//									<td><input name="user_password" value="" type="password" /></td>
//								</tr>
//								<tr>
//									<td align="right">Confirm:</td>
//									<td><input name="user_confirm" value="" type="password" /></td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<input name="action" value="list_users_by_name" type="hidden" />
//										<button name="pre_action" value="change_user_password" type="submit">Change</button>
//<!--										<button type="cancel">Cancel</button> -->
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'list_users_by_name' :
//        $user_table = ace_get_users();
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="user_list_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<table>
//								<tr>
//									<th></th>
//									<th>name</th>
//									<th>email</th>
//								</tr>';
//        foreach ($user_table as $user_record) {
//            $active = ($user_record['state'] == 1) ? TRUE : FALSE;
//            $html .= '
//								<tr class="' . (($active) ? 'active' : 'inactive') . '">
//									<td>
//										<input name="user_id" value="' . $user_record['id'] . '" type="radio" />
//									</td>
//									<td>' . $user_record['first'] . ' ' . $user_record['last'] . '</td>
//									<td>' . $user_record['name'] . '</td>
//								</tr>';
//        }
//        $html .= '
//							</table>
//							<button name="action" value="form_edit_user" type="submit">Edit</button>
//							<button name="pre_action" value="delete_user" type="submit">Delete</button>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'list_users_by_group' :
//        $html_group_dropdown_list = NULL;
//        $html_group_dropdown_list = "<option value=null>select a group...</option>";
//        $group_table = ace_get_groups();
//        foreach ($group_table as $group) {
//            $html_group_dropdown_list .= '<option value="' . $group['id'] . '"' . (($_POST['group_id'] == $group['id']) ? 'selected' : '') . '>' . $group['name'] . '</option>';
//        }
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="group_selection_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<select name="group_id">
//								' . $html_group_dropdown_list . '
//							</select>
//							<button name="action" value="list_users_by_group" type="submit">Get Users</button>
//						</form>';
//        if (isset($_POST['group_id'])) {
//            $group_members_table = ace_group_get_members_table($_POST['group_id']);
//            $html .= '
//						<form name="user_by_group_list_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<input name="action" value="list_users_by_group" type="hidden" />
//							<input name="group_id" value="' . $_POST['group_id'] . '" type="hidden" />
//							<table>
//								<tr>
//									<th></th>
//									<th>name</th>
//									<th>email</th>
//								</tr>';
//            foreach ($group_members_table as $row) {
//                $str_active = ($row['user_state'] == 1) ? 'active' : 'inactive';
//                $html .= '
//								<tr class="' . $str_active . '">
//									<td>
//										<input name="user_id" value="' . $row['user_id'] . '" type="radio" />
//									</td>
//									<td>' . $row['user_first'] . ' ' . $row['user_last'] . '</td>
//									<td>' . $row['user_name'] . '</td>
//								</tr>';
//            }
//            $html .= '
//							</table>
//							<button name="action" value="form_edit_user" type="submit">Edit User</button>
//							<button name="pre_action" value="remove_user_from_group" type="submit">Remove from Group</button>
//						</form>
//					</div>
//				</div>
//			</div>';
//        }
//        break;
//    case 'form_new_group' :
//        $html_owner_dropdown_list = NULL;
//        $html_owner_dropdown_list .= "<option value=null>select an owner...</option>";
//        $user_admins_and_managers_table = ace_get_user_admins_and_managers();
//        foreach ($user_admins_and_managers_table as $user) {
//            $html_owner_dropdown_list .= '<option value="' . $user['id'] . '">' . $user['first'] . ' ' . $user['last'] . ' - ' . $user['name'] . '</option>';
//        }
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_new_group" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<table>
//								<tr>
//									<td align="right">Name:</td>
//									<td><input name="group_name" value="" type="text" /></td>
//								</tr>
//								<tr>
//									<td align="right">Owner:</td>
//									<td>
//										<select name="group_owner_id">
//											' . $html_owner_dropdown_list . '
//										</select>
//									</td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<input name="action" value="list_groups_by_name" type="hidden" />
//										<button name="pre_action" value="save_group" type="submit">Save</button>
//<!--										<button name="cancel" type="cancel">Cancel</button> -->
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'form_edit_group' :
//        $group_id = $_POST['group_id'];
//        $group_record = ace_group_get_info($group_id);
//        // echo d($group_record);
//        $active = ($group_record['state']) ? TRUE : FALSE;
//        $html_owner_dropdown_list = NULL;
//        $html_owner_dropdown_list .= "<option value=null>select an owner...</option>";
//        $user_admins_and_managers_table = ace_get_user_admins_and_managers();
//        foreach ($user_admins_and_managers_table as $user) {
//            $html_owner_dropdown_list .= '<option value="' . $user['id'] . '"' . (($user['id'] == $group_record['owner']) ? ' selected' : '') . '>' . $user['first'] . ' ' . $user['last'] . ' - ' . $user['name'] . '</option>';
//        }
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_edit_group" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<table>
//								<tr>
//									<td align="right">Name:</td>
//									<td>
//										<input name="group_id" value="' . $group_record['id'] . '" type="hidden" />
//										<input name="group_name" value="' . $group_record['name'] . '" type="text" />
//									</td>
//								</tr>
//								<tr>
//									<td align="right">Owner:</td>
//									<td>
//										<select name="group_owner_id">
//											' . $html_owner_dropdown_list . '
//										</select>
//									</td>
//								</tr>
//								<tr>
//									<td align="right">Active?</td>
//									<td><input name="group_state" type="checkbox" value="Active" ' . (($active) ? 'checked' : '') . '/></td>
//								</tr>
//								<tr>
//									<td align="right" colspan="2">
//										<input name="action" value="list_groups_by_name" type="hidden" />
//										<button name="pre_action" value="save_group" type="submit">Save Changes</button>
//										<button name="pre_action" value="delete_group" type="submit">Delete Group</button>
//										<a href="' . _ADMIN_URL_ . '?action=list_groups_by_name">Cancel</a>
//									</td>
//								</tr>
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'list_groups_by_name' :
//        $group_table = ace_get_groups_and_owners();
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_group_list" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<input name="action" value="edit_group" type="hidden" />
//							<table>
//								<tr>
//									<th>name</th>
//									<th>owner</th>
//								<tr>';
//        foreach ($group_table as $group_record) {
//            $active = ($group_record['state'] == 1) ? TRUE : FALSE;
//            $html .= '
//								<tr class="' . (($active) ? 'active' : 'inactive') . '">
//									<td><a href="' . _ADMIN_URL_ . '?action=form_edit_group&group_id=' . $group_record['id'] . '">' . $group_record['name'] . '</td>
//									<td>' . $group_record['user_name'] . '</td>
//								</tr>';
//        }
//        $html .= '
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'list_groups_by_owner' :
//        $html_user_dropdown_list = NULL;
//        $html_user_dropdown_list = "<option value=null>select a user...</option>";
//        $user_table = ace_get_users();
//        foreach ($user_table as $user) {
//            $html_user_dropdown_list .= '<option value="' . $user['id'] . '"' . (($_POST['user_id'] == $user['id']) ? 'selected' : '') . '>' . $user['name'] . '</option>';
//        }
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="user_selection_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<select name="user_id">
//								' . $html_user_dropdown_list . '
//							</select>
//							<button name="action" value="list_groups_by_owner" type="submit">Get Groups</button>
//						</form>
//				';
//        if (isset($_POST['user_id'])) {
//            $owned_groups_table = ace_user_get_owned_groups($_POST['user_id']);
//            $html .= '
//						<form name="groups_by_owner_list_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<input name="action" value="edit_group" type="hidden" />
//							<table>
//								<tr>
//									<th>name</th>
//								</tr>';
//            foreach ($owned_groups_table as $row) {
//                $str_active = ($row['state'] == 1) ? 'active' : '';
//                $html .= '
//								<tr class="' . $str_active . '">
//									<td><a href="' . _ADMIN_URL_ . '?action=form_edit_group&group_id=' . $row['id'] . '">' . $row['name'] . '</td>
//								</tr>';
//            }
//            $html .= '
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        }
//        break;
//    case 'list_labs_by_name' :
//        $lab_table = ace_get_labs_and_owners();
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="form_lab_list" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<!-- <input name="action" value="edit_lab" type="hidden" /> -->
//							<table>
//								<tr>
//									<th>name</th>
//									<th>owner</th>
//								<tr>';
//        foreach ($lab_table as $lab_record) {
//            $active = ($lab_record['lab_state'] == 1) ? TRUE : FALSE;
//            $html .= '
//								<tr class="' . (($active) ? 'active' : 'inactive') . '">
//									<td>' . $lab_record['lab_name'] . '</td>
//									<td>' . $lab_record['user_name'] . '</td>
//								</tr>';
//        }
//        $html .= '
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        break;
//    case 'list_labs_by_owner' :
//        $html_user_dropdown_list = NULL;
//        $html_user_dropdown_list = "<option value=null>select a user...</option>";
//        $user_table = ace_get_users();
//        foreach ($user_table as $user) {
//            $html_user_dropdown_list .= '<option value="' . $user['id'] . '"' . (($_POST['user_id'] == $user['id']) ? 'selected' : '') . '>' . $user['name'] . '</option>';
//        }
//        $html = '
//			<div class="element_table">
//				<div class="element_column">
//					<div class="element">
//						<form name="user_selection_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<select name="user_id">
//								' . $html_user_dropdown_list . '
//							</select>
//							<button name="action" value="list_labs_by_owner" type="submit">Get Labs</button>
//						</form>
//				';
//        if (isset($_POST['user_id'])) {
//            $owned_labs_table = ace_user_get_owned_labs($_POST['user_id']);
//            $html .= '
//						<form name="labs_by_owner_list_form" action="' . _ADMIN_URL_ . '" method="post">
//							<input name="nonce" value=' . $nonce . ' type="hidden" />
//							<!-- <input name="action" value="edit_group" type="hidden" /> -->
//							<table>
//								<tr>
//									<th>name</th>
//								</tr>';
//            foreach ($owned_labs_table as $row) {
//                $str_active = ($row['lab_state'] == 1) ? 'active' : '';
//                $html .= '
//								<tr class="' . $str_active . '">
//									<td>' . $row['lab_name'] . '</td>
//								</tr>';
//            }
//            $html .= '
//							</table>
//						</form>
//					</div>
//				</div>
//			</div>';
//        }
//        break;
//}

switch ($_POST['operation']) {
    case 'host_test_connection':
        if (isset($_POST['host_id'])) {
            $host_id = $_POST['host_id'];
            $host_name = ace_host_get_name_by_id($host_id);
            $test_result = ace_host_test_connection($host_id);
            if ($test_result) {
                $messages[] = create_message(TRUE, 'connection to ' . $host_name . ' successful');
            } else {
                $messages[] = create_message(FALSE, 'connection to ' . $host_name . ' failed');
            }
        } else {
            $messages[] = create_message(FALSE, 'no host specified');
        }
        break;
    case 'host_change_state':
        if (isset($_POST['host_id'])) {
            $host_id = $_POST['host_id'];
            $host_name = ace_host_get_name_by_id($host_id);
            $state = ace_host_is_active($host_id);
            $new_state = ($state) ? FALSE : TRUE;
            if ($new_state) {
                $success = ace_host_activate($host_id);
            } else {
                $success = ace_host_deactivate($host_id);
            }
            $messages[] = create_message($success, (($new_state) ? 'activating ' : 'deactivating ') . $host_name . ' host');
        } else {
            $messages[] = create_message(FALSE, 'no host specified');
        }
        break;
    case 'host_save':
        if (isset($_POST['host_id']) && $_POST['host_id'] != '') {
            $host_id = $_POST['host_id'];
            $host_name = $_POST['host_name'];
            $host_domain = $_POST['host_domain'];
            $host_description = $_POST['host_description'];
            $host_hypervisor = $_POST['host_hypervisor'];
            $host_ip_internal = $_POST['host_ip_internal'];
            $host_ip_external = $_POST['host_ip_external'];
            $host_username = $_POST['host_username'];
            $host_password = $_POST['host_password'];
            $host_threads = $_POST['host_threads'];
            $host_memory = $_POST['host_memory'];
            $host_storage = $_POST['host_storage'];
            $success = ace_host_update($host_id, $host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage);
            //$host_state = ($_POST['host_state'] == 'Active') ? TRUE : FALSE;;
            //if ($host_state) {
            //    $db_success = ace_host_activate($host_id);
            //} else {
            //    $db_success = ace_host_deactivate($host_id);
            //}
            $message = create_message($success, "updating host '$host_name'");
        } elseif (isset($_POST['host_name'])) {
            $host_name = $_POST['host_name'];
            $host_domain = $_POST['host_domain'];
            $host_description = $_POST['host_description'];
            $host_hypervisor = $_POST['host_hypervisor'];
            $host_ip_internal = $_POST['host_ip_internal'];
            $host_ip_external = $_POST['host_ip_external'];
            $host_username = $_POST['host_username'];
            $host_password = $_POST['host_password'];
            $host_threads = $_POST['host_threads'];
            $host_memory = $_POST['host_memory'];
            $host_storage = $_POST['host_storage'];
            $host_id = ace_host_add($host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage);
            $success = (is_numeric($host_id)) ? TRUE : FALSE;
            if ($success) {
                $db_success = ace_host_add_role($host_id, $_POST['host_role_id']);
            }
            $message = create_message($success, "adding host '$host_name'");
        } else {
            $message = create_message(FALSE, "adding host, not enough data specified");
        }
        break;
    case 'host_remove':
        $host_id = $_POST['host_id'];
        $host_name = ace_host_get_name_by_id($host_id);
        $success = ace_host_remove($host_id);
        $message = create_message($success, "removing host '$host_name'");
        break;
    case 'host_role_add':
        $host_id = $_POST['host_id'];
        $host_name = ace_host_get_name_by_id($host_id);
        $success = ace_host_add_role($host_id, $_POST['select_host_role_id']);
        $message = create_message($success, "adding a host role to '$host_name'");
        break;
    case 'host_role_remove':
        $host_id = $_POST['host_id'];
        $host_name = ace_host_get_name_by_id($host_id);
        $success = ace_host_remove_role($host_id, $_POST['radio_host_role_id']);
        $message = create_message($success, "removing a host role from '$host_name'");
        break;
    case 'user_change_password':
        break;
    case 'group_change_state':
        $group_id = $_POST['group_id'];
        $group_name = ace_group_get_name_by_id($group_id);
        $state = ace_group_get_state($group_id);
        $group_state = ($state) ? FALSE : TRUE;
        $success = ace_group_set_state($group_id, $group_state);
        $messages[] = create_message($success, (($group_state) ? 'activating ' : 'deactivating ') . $group_name . ' group');
        break;
    case 'academic_group_create':
        $success = FALSE;
        if ($_POST['sectionID'] != "") {
            $group_name = $_POST['courseID'] . '-' . $_POST['sectionID'];
            $group_id = ace_create_academic_group($group_name, $_POST['owner_id']);
            $success = ($group_id !== false) ? TRUE : FALSE;
            $section_id = ace_group_create_section_info($group_id,$_POST['courseID'],$_POST['sectionID'],$_POST['schedule'],$_POST['comment']);
        }
        $messages[] = create_message($success, 'creating academic group');
        break;
    case 'security_group_create':
        $group_id = ace_create_security_group($_POST['group_name']);
        $success = ($group_id !== false) ? TRUE : FALSE;
        $messages[] = create_message($success, 'creating security group "' . $_POST['group_name'] . '"');
        break;
    case 'academic_group_update' :
        $group_id = $_POST['group_id'];
        $group = ace_group_get_info($group_id);
        $success = ace_group_update($_POST['group_id'],$group['name'],$_POST['owner_id']);
        $messages[] = create_message($success, 'updating group (' . $_POST['group_name'] . ')');
        $old_group_state = ($group['state'] == 1) ? true : false;
        $group_state = ($_POST['group_state'] == 'Active') ? true : false;
        if ($old_group_state != $group_state) {
            $success = ace_group_set_state($group_id,$group_state);
            $messages[] = create_message($success, (($group_state) ? 'activating' : 'deactivating') . ' group ' . $_POST['group_name']);
        }
        $success = ace_group_update_section_info($group_id,$_POST['courseID'],$_POST['sectionID'],$_POST['schedule'],$_POST['comment']);
        $messages[] = create_message($success, 'updating section (' . $_POST['sectionID'] . ') info');
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
                    $messages[] = create_message(TRUE, 'creating user(s): ' . $email_count['created'] . ' user(s) created');
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
    case 'group_user_update' :
        if ($_POST['user_id']) {
            $user_id = $_POST['user_id'];
            $user_name = $_POST['user_email'];
            $user_first = $_POST['user_first'];
            $user_last = $_POST['user_last'];
            $success = ace_user_update($user_id, $user_name, $user_first, $user_last);
            if ($success) {
                $messages[] = create_message($success, 'User updated');
            } else {
                $messages[] = create_message($success, 'User NOT updated');
            }
        } else {
            $messages[] = create_message(FALSE, "updating user, no user_id specified");
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
    case 'academic_group_lab_remove' :
        $group_id = $_POST['group_id'];
        $group_name = ace_group_get_name_by_id($group_id);
        if ($_POST['selected_lab_id'] != '') {
            $lab_id = $_POST['selected_lab_id'];
            $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
            $success = ace_group_remove_lab($group_id, $lab_id);
            $messages[] = create_message($success, "revoking '$lab_display_name' from '$group_name' group");
        } else {
            $messages[] = create_message(FALSE, "revoking a lab, no lab_id specified");
        }
        break;
    case 'group_delete':
        $group_name = ace_group_get_name_by_id($_POST['group_id']);
        $success = ace_group_delete($_POST['group_id']);
        $message = create_message($success, 'deleting group "' . $group_name .'"');
        break;
    case 'course_create':
        $course_id = ace_course_create($_POST['course_ref'], $_POST['course_name']);
        $success = ($course_id !== FALSE) ? TRUE : FALSE;
        $messages[] = create_message($success, 'creating course (' . $_POST['course_ref'] . ')');
        break;
    case 'course_update':
        if ($_POST['course_id']) {
            $course_id = $_POST['course_id'];
            $course_ref = $_POST['course_ref'];
            $course_name = $_POST['course_name'];
            $success = ace_course_update($course_id, $course_ref, $course_name);
            $messages[] = create_message($success, 'updating course (' . $course_ref . ')');
        }
        break;
    case 'course_delete':
        $course_ref = ace_course_get_ref_by_id($_POST['course_id']);
        $section_table = ace_course_get_section_table($_POST['course_id']);
        $section_count = (is_array($section_table)) ? count($section_table) : 0;
        if ($section_count > 0) {
            $messages[] = create_message(FALSE,'deleting course (' . $course_ref . '), sections exist for this course');
        } else {
            $success = ace_course_delete($_POST['course_id']);
            $messages[] = create_message($success, 'deleting course (' . $course_ref . ')');
        }
        break;
    case 'user_change_state':
        $user_id = $_POST['user_id'];
        $user_name = ace_user_get_name_by_id($user_id);
        $current_user_state = ace_user_get_state($user_id);
        $new_user_state = ($current_user_state) ? FALSE : TRUE;
        $success = ace_user_set_state($user_id, $new_user_state);
        $messages[] = create_message($success, (($new_user_state) ? 'activating ' : 'deactivating ') . 'user (' . $user_name . ')');
        break;
    case 'user_create':
        $user_id = ace_user_create($_POST['user_email'], $_POST['user_first'], $_POST['user_last']);
        $success = ($user_id !== FALSE) ? TRUE : FALSE;
        $messages[] = create_message($success, 'creating user (' . $_POST['user_email'] . ')');
        break;
    case 'user_update':
        if ($_POST['user_id']) {
            $user_id = $_POST['user_id'];
            $user_email = $_POST['user_email'];
            $user_first = $_POST['user_first'];
            $user_last = $_POST['user_last'];
            $success = ace_user_update($user_id, $user_email, $user_first, $user_last);
            $messages[] = create_message($success, 'updating user (' . $user_email . ')');
        }
        break;
    case 'user_delete':
        $user_id = $_POST['user_id'];
        $user_email = ace_user_get_name_by_id($user_id);
        $success = ace_user_delete($user_id);
        $messages[] = create_message($success, 'deleting user (' . $user_email . ')');
        break;
    case 'quota_create':
        $quota_id = ace_quota_create($_POST['object_type'], $_POST['object_id'], $_POST['labs'], $_POST['vms'],
                                    $_POST['vcpu'], $_POST['memory'], $_POST['networks'], $_POST['volumes'],
                                    $_POST['storage']);
        $success = ($quota_id !== FALSE) ? TRUE : FALSE;
        $messages[] = create_message($success, 'creating quota');
        break;
    case 'quota_update':
        if ($_POST['quota_id']) {
            $quota_id = $_POST['quota_id'];
            $object_type = $_POST['object_type'];
            $object_id = $_POST['object_id'];
            $labs = $_POST['labs'];
            $vms = $_POST['vms'];
            $vcpu = $_POST['vcpu'];
            $memory = $_POST['memory'];
            $networks = $_POST['networks'];
            $volumes = $_POST['volumes'];
            $storage = $_POST['storage'];
            $success = ace_quota_update($quota_id, $object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage);
            $messages[] = create_message($success, 'updating quota');
        }
        break;
    case 'quota_delete':
        $quota_id = $_POST['quota_id'];
        $success = ace_quota_delete($quota_id);
        $messages[] = create_message($success, 'deleting quota');
        break;
    case 'lab_change_state':
        $lab_id = $_POST['lab_id'];
        $lab = ace_lab_get_info($lab_id);
        $lab_state = ($lab['state'] == 1) ? TRUE : FALSE;
        if ($lab_state) {
            $success = ace_lab_deactivate($lab_id);
            $messages[] = create_message($success, "deactivating lab (" . $lab['name'] . ")");
        } else {
            $success = ace_lab_activate($lab_id);
            $messages[] = create_message($success, "activating lab (" . $lab['name'] . ")");
        }
        break;
    case 'lab_update':
        if ($_POST['lab_id']) {
            $lab_id = $_POST['lab_id'];
            $lab_user_id = $_POST['lab_user_id'];
            $lab_user_name = ace_user_get_name_by_id($lab_user_id);
            $lab_host_id = $_POST['lab_host_id'];
            $lab_name = $_POST['lab_name'];
            $lab_display_name = $_POST['lab_display_name'];
            $lab_description = $_POST['lab_description'];
            $success = ace_lab_update($lab_id, $lab_user_id,$lab_host_id,$lab_name,$lab_display_name,$lab_description);
            $messages[] = create_message($success, 'updating lab (' . $lab_user_name . ' - ' . $lab_display_name . ')');
        }
        break;
    case 'lab_delete':
        $lab_id = $_POST['lab_id'];
        $lab_user_id = ace_lab_get_user_id($lab_id);
        $lab_owner_name = ace_user_get_name_by_id($lab_user_id);
        $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
        $success = ace_lab_delete($lab_id);
        $messages[] = create_message($success, 'deleting lab (' . $lab_owner_name . ' - ' . $lab_display_name . ')');
        break;
    case 'labs_purge_aged':
        $aged_active_lab_table = ace_get_aged_active_labs();
        if (is_array($aged_active_lab_table)) {
            $aged_active_lab_count = count($aged_active_lab_table);
            foreach ($aged_active_lab_table as $lab) {
                $success = ace_lab_deactivate($lab['id']);
                $messages[] = create_message($success, 'deactivating aged lab');
            }
            $messages[] = create_message(TRUE, $aged_active_lab_count . ' lab(s) deactivated');
        } else {
            $messages[] = create_message(FALSE, 'No aged labs were found');
        }
        break;
}

switch ($_POST['display']) {
    case 'hosts':
        $host_table = ace_get_hosts();
        $html = '
			<div class="element_table">
                <div class="element_column">
                    <div class="element">
                        <p align="center"><strong>Hosts</strong></p>
                    </div>
                    <div class="element">
                        <form name="host_select_form" action="' . _ADMIN_URL_ . '" method="post">
                            <table>
                                <tr>
                                    <th></th>
                                    <th colspan="3">Identity</th>
                                    <th colspan="3">Physical Capacity</th>
                                    <th colspan="4">Tenant Load</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Hypervisor</th>
                                    <th>Description</th>
                                    <th>vCPU</th>
                                    <th>Mem(GiB)</th>
                                    <th>Storage(GiB)</th>
                                    <th>Labs</th>
                                    <th>VMs</th>
                                    <th>Vols</th>
                                    <th>vNets</th>
                                </tr>';
        foreach ($host_table as $host) {
            if ($host['state'] == 1) {
                $host_class = 'active';

                $host_hypervisor = ace_host_get_hypervisor_info($host['id']);
                $host_physical = ace_host_get_physical_info($host['id']);
                $host_physical['memory'] = round($host_physical['memory'] / 1024 / 1024, 0);
                $host_storage = ace_host_get_virt_storage_info($host['id']);
                $host_storage['allocation'] = round($host_storage['allocation'] / 1024 / 1024 / 1024, 0);
                $host_storage['capacity'] = round($host_storage['capacity'] / 1024 / 1024 / 1024, 0);

                $host_volume_list = ace_host_get_virt_volume_list($host['id']);
                //$host_volume_count = count($host_volume_list);
                $host_volume_count = 0;
                $tenant_volume_count = 0;
                foreach ($host_volume_list as $host_volume) {
                    if (substr($host_volume, 0, 2) == '0-') {
                        $host_volume_count++;
                    } else {
                        if (substr($host_volume, -3, 3) != '-00') {  //adjust for tenant router
                            $tenant_volume_count++;
                        }
                    }
                }

                $host_network_list = ace_host_get_virt_network_list($host['id']);
                //$host_network_count = count($host_network_list);
                $host_network_count = 0;
                $tenant_network_count = 0;
                foreach ($host_network_list as $host_network) {
                    if (substr($host_network, 0, 2) == '0-') {
                        $host_network_count++;
                    } else {
                        $tenant_network_count++;
                    }
                }

                $host_vm_list = ace_host_get_virt_vm_list($host['id']);
                $host_lab_count = 0;
                $host_vm_count = 0;
                $tenant_vm_count = 0;
                foreach ($host_vm_list as $host_vm) {
                    if ((substr($host_vm, 0, 2) == '0-') || (substr($host_vm, -3, 3) == '-00')) {
                        $host_vm_count++;
                        if (substr($host_vm, -3, 3) == '-00') {
                            $host_lab_count++;
                        }
                    } else {
                        $tenant_vm_count++;
                    }
                }
            } else {
                $host_class = 'inactive';
                $host_hypervisor['hypervisor'] = $host['hypervisor'];
                $host_physical['cpus'] = $host['threads'];
                $host_physical['memory'] = $host['memory'];
                $host_storage['allocation'] = 0;
                $host_storage['capacity'] = $host['storage'];
                $host_lab_count = 0;
                $tenant_vm_count = 0;
                $tenant_volume_count = 0;
                $tenant_network_count = 0;
            }

            $html .= '
                                <tr class="' . $host_class . '">
                                    <td><input name="host_id" value="' . $host['id'] . '" type="radio" /></td>
                                    <td align="left">' . $host['name'] . '</td>
                                    <td align="center">' . $host_hypervisor['hypervisor'] . '</td>
                                    <td align="left">' . $host['description'] . '</td>
                                    <td align="center">' . $host_physical['cpus'] . '</td>
                                    <td align="center">' . $host_physical['memory'] . '</td>
                                    <td align="center">' . (($host_storage['allocation'] == 0) ? '' : $host_storage['allocation'] . '/') . $host_storage['capacity'] . '</td>
                                    <td align="center">' . $host_lab_count . '</td>
                                    <td align="center">' . $tenant_vm_count . '</td>
                                    <td align="center">' . $tenant_volume_count . '</td>
                                    <td align="center">' . $tenant_network_count . '</td>
                                </tr>';
        }
        $html .= '
                            </table>
                            <input name="display" value="hosts" type="hidden" />
                            <button name="operation" value="host_change_state" type="submit">(De)Activate</button>
                            <button name="display" value="host_edit_form" type="submit">Edit</button>
                            <button name="operation" value="host_test_connection" type="submit">Test Connection</button>
                            <!-- <button name="operation" value="host_remove" type="submit">Remove</button> -->
                            <input name="nonce" value="' . $nonce . '" type="hidden" />
                        </form>
                    </div>';
        $html .= '
                    <div class="element">
                        <form name="host_add_form" action="' . _ADMIN_URL_ . '" method="post">
                            <input name="display" value="host_add_form" type="hidden" />
                            <button name="operation" value="" type="submit">Add New Host</button>
                            <input name="nonce" value="' . $nonce . '" type="hidden" />
                        </form>
                    </div>';
        $html .= '
                </div>
            </div>';
        break;
    case 'host_add_form' :
        $host_roles = ace_get_host_roles();
        $html_select_host_roles = '';
        foreach ($host_roles as $role) {
            $html_select_host_roles .= '<option value="' . $role['id'] . '">' . $role['name'] . '</value>';
        }
        $html = '
			<div class="element_table">
				<div class="element_column">
					<div class="element">
						<form name="form_new_host" action="' . _ADMIN_URL_ . '" method="post">
							<input name="nonce" value=' . $nonce . ' type="hidden" />
							<table>
								<tr>
									<td align="right">Hostname:</td>
									<td><input name="host_name" value="" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Domain:</td>
									<td><input name="host_domain" value="" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Description:</td>
									<td><input name="host_description" value="" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Role:</td>
									<td>
										<select name="host_role_id">
											' . $html_select_host_roles . '
										</select>
									</td>
								</tr>
								<tr>
									<td align="right">Hypervisor:</td>
									<td>
										<select name="host_hypervisor">
											<option value="hv">Hyper-V</option>
											<option value="kvm" selected>KVM</option>
											<option value="lxc">LXC</option>
											<option value="xen">XEN</option>
										</select>
									</td>
								</tr>
								<tr>
									<td align="right">IP internal:</td>
									<td><input name="host_ip_internal" value="" type="text" /></td>
								</tr>
								<tr>
									<td align="right">IP external:</td>
									<td><input name="host_ip_external" value="" type="text" /></td>
								</tr>
								<tr>
									<td align="right">username:</td>
									<td><input name="host_username" value="" type="text" /></td>
								</tr>
								<tr>
									<td align="right">password:</td>
									<td><input name="host_password" value="" type="password" /></td>
								</tr>
								<tr>
									<td align="right">Threads:</td>
									<td><input name="host_threads" value="1" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Memory (GiB):</td>
									<td><input name="host_memory" value="1" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Storage (GiB):</td>
									<td><input name="host_storage" value="1" type="text" /></td>
								</tr>
								<tr>
									<td align="right" colspan="2">
										<input name="display" value="hosts" type="hidden" />
										<button name="operation" value="host_save" type="submit">Add</button>
										<button type="reset">Clear</button>
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
			</div>';
        break;
    case 'host_edit_form' :
        $host_id = $_POST['host_id'];
        $host_record = ace_host_get_info($host_id);
        $active = ($host_record['state'] == 1) ? TRUE : FALSE;
        $has_labs = ace_host_has_active_labs($host_id);

        $host_roles = ace_host_get_roles($host_id);
        $html_host_role_radios = '';
        foreach ($host_roles as $role) {
            $html_host_role_radios .= '<tr><td><input name="radio_host_role_id" value="' . $role['host_role_id'] . '" type="radio">' . $role['host_role_name'] . '</td></tr>';
        }

        $all_host_roles = ace_get_host_roles();
        $html_select_host_roles = '';
        foreach ($all_host_roles as $role) {
            $html_select_host_roles .= '<option value="' . $role['id'] . '">' . $role['name'] . '</value>';
        }

        $html = '
			<div class="element_table">
				<div class="element_column">
					<div class="element">
						<form name="form_edit_host" action="' . _ADMIN_URL_ . '" method="post">
							<input name="host_id" value="' . $host_id . '" type="hidden" />
							<table>
								<tr>
									<td align="right">Hostname:</td>
									<td><input name="host_name" value="' . $host_record['name'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Domain:</td>
									<td><input name="host_domain" value="' . $host_record['domain'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Description:</td>
									<td><input name="host_description" value="' . $host_record['description'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Hypervisor:</td>
									<td>
										<select name="host_hypervisor">
											<!--<option value="hv" ' . (($host_record['hypervisor'] == 'hv') ? 'selected' : '') . '>Hyper-V</option>-->
											<option value="kvm" ' . (($host_record['hypervisor'] == 'kvm') ? 'selected' : '') . '>QEMU</option>
											<!--<option value="lxc" ' . (($host_record['hypervisor'] == 'lxc') ? 'selected' : '') . '>LXC</option>-->
											<!--<option value="xen" ' . (($host_record['hypervisor'] == 'xen') ? 'selected' : '') . '>XEN</option>-->
										</select>
									</td>
								</tr>
								<tr>
									<td align="right">IP internal:</td>
									<td><input name="host_ip_internal" value="' . $host_record['ip_internal'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">IP external:</td>
									<td><input name="host_ip_external" value="' . $host_record['ip_external'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">username:</td>
									<td><input name="host_username" value="' . $host_record['username'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">password:</td>
									<td><input name="host_password" value="' . $host_record['password'] . '" type="password" /></td>
								</tr>
								<tr>
									<td align="right">Threads:</td>
									<td><input name="host_threads" value="' . $host_record['threads'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Memory (GiB):</td>
									<td><input name="host_memory" value="' . $host_record['memory'] . '" type="text" /></td>
								</tr>
								<tr>
									<td align="right">Storage (GiB):</td>
									<td><input name="host_storage" value="' . $host_record['storage'] . '" type="text" /></td>
								</tr>';
        //$html .= '
			//					<tr>
			//						<td align="right">Active?</td>
			//						<td>
			//						    <input name="host_state" type="checkbox" value="Active" ' . (($active) ? 'checked' : '') . ' ' . (($has_labs) ? 'disabled' : '') . '/>
			//						</td>
			//					</tr>';
        $html .= '
								<tr>
									<td align="right" colspan="2">
										<button name="operation" value="host_save" type="submit">Save Changes</button>
										<abbr title="USE WITH CAUTION!">
										    <button name="operation" value="host_remove" type="submit" ' . (($active) ? 'disabled' : '') . '>Remove Host</button>
                                        </abbr>
<!--										<button type="cancel">Cancel</button> -->
									</td>
								</tr>
							</table>
							<input name="nonce" value=' . $nonce . ' type="hidden" />
							<input name="display" value="hosts" type="hidden" />
						</form>
					</div>
					<div class="element">
						<form name="form_host_roles" action="' . _ADMIN_URL_ . '" method="post">
							<input name="nonce" value=' . $nonce . ' type="hidden" />
							<input name="display" value="host_edit_form" type="hidden" />
							<input name="host_id" value="' . $host_id . '" type="hidden" />
							<table>
								<tr>
									<th>Roles:</th>
								</tr>
							' . $html_host_role_radios . '
								<tr>
									<td>
										<button name="operation" value="host_role_remove" type="submit">Remove</button>
									</td>
								</tr>
								<tr>
									<td>
										<select name="select_host_role_id">
											' . $html_select_host_roles . '
										</select>
										<button name="operation" value="host_role_add" type="submit">Add</button>
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
			</div>';
        break;
    case 'resources':
        $active_host_array = ace_get_active_hosts();
        $media_array = ace_get_iso_table();
        $base_volume_array = ace_get_shared_volume_table();
        $html = '
            <div class="element_table">
                <div class="element_column">
                    <div class="element">
                        <p align="center"><strong>Media</strong></p>
                    </div>
                    <div class="element">
                        <table>';
        if (is_array($media_array)) {
            foreach ($media_array as $media){
                $html .= '
                            <tr>
                                <td>' . $media['display_name'] . '</td>
                            </tr>';
            }
        } else {
            $html .= '
                            <tr>
                                <td>none</td>
                            </tr>';
        }
        $html .= '
                        </table>
                    </div>
                </div>
                <div class="element_column">
                    <div class="element">
                        <p align="center"><strong>Base Images</strong></p>
                    </div>
                    <div class="element">
                        <table>
                            <tr>
                                <th><strong>Image Name</strong></th>';
        foreach ($active_host_array as $host) {
            $html .= '
                                <th><strong>' . $host['name'] . '</strong></th>';
        }
        $html .= '
                            </tr>';
        if (is_array($base_volume_array)){
            foreach ($base_volume_array as $base_volume) {
                $html .= '
                            <tr>
                                <td>' . $base_volume['display_name'] . '</td>';
                foreach ($active_host_array as $host){
                    $base_volume_virt_list = ace_host_get_virt_volume_list($host['id']);
                    if (in_array($base_volume['virt_id'],$base_volume_virt_list)){
                        $base_volume_class = 'active';
                    } else {
                        $base_volume_class = 'inactive';
                    }
                    $html .= '
                                <td align="center" class="' . $base_volume_class . '">';
                    $html .= ($base_volume_class == 'active') ? '+' : '-';
                    $html .= '
                                </td>';
                }
                $html .= '
                            </tr>';
            }
        } else {
            $html .= '
                            <tr>
                                <td>none</td>
                            </tr>';
        }
        $html .= '      </table>
                    </div>
                </div>
            </div>';
        break;
    case 'groups':
        $security_group_table = ace_get_security_groups();
        $element = '
					<p align="center"><strong>Security Groups</strong></p>';
        $element_column[] = $element;
        $element = '
                    <form name="admin_security_groups_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="groups" type="hidden" />
						<table>
							<tr>
								<th></th>
								<th>Name</th>
							<tr>';
        foreach ($security_group_table as $group){
            $group_state = ($group['state'] == 1) ? 'active' : 'inactive';
            $element .= '
                            <tr class="' . $group_state . '">
                                <td style="width:30px;">
                                    <input name="group_id" value="' . $group['id'] . '" type="radio" />
                                </td>
                                <td>' . $group['name'] . '</td>
                            </tr>';
        }
        $element .= '
                        </table>
                        <table style="width: auto;">
                            <tr>
                                <td>
                                    <button name="operation" value="group_change_state" type="submit">(De)Activate</button>
                                </td>
                                <td>
                                    <button name="display" value="security_group_update_form" type="submit">Edit</button>
                                </td>
                            </tr>
                        </table>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'security_group_update_form':
        if ((isset($_POST['group_id'])) && ($_POST['group_id'] != NULL)) {
            $group_id = $_POST['group_id'];
            $group = ace_group_get_info($group_id);
            $element = '
			        <p align="center"><strong>' . $group['name'] . ' Group</strong></p>';
            $element_column[] = $element;
            $element = '
					<p align="center"><strong>Members</strong></p>
					<form name="group_user_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="security_group_update_form" type="hidden" />
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
						<table style="width: auto;">
						    <tr>
						        <td>
						            <button name="display" value="security_group_user_update_form" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Edit</button>
                                </td>
                                <td>
                                    <button name="operation" value="group_user_remove" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Remove</button>
                                </td>
                            </tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $element_table[] = $element_column;
            $element_column = array();
            $element = '
                    <p align="center"><strong>Add Member(s)</strong></p>';
            $element_column[] = $element;
            $element = '
			        <form name="security_group_user_add_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="security_group_update_form" type="hidden" />
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
                    <form name="group_users_add_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="security_group_update_form" type="hidden" />
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
                        <p align="center"><strong>New Member Initial Password(s)</strong></p>
                        <textarea rows=' . count($new_user_array) . ' cols="35" style="resize:none;" readonly>' . $new_user_initial_password_text_block . '</textarea>';
                $element_column[] = $element;
            }
            $element_table[] = $element_column;
            $element_column = array();
        } else {
            $message = create_message(FALSE, 'selecting group, no group selected');
        }
        break;
    case 'academic_group_update_form':
        if ((isset($_POST['group_id'])) && ($_POST['group_id'] != NULL)) {
            $group_id = $_POST['group_id'];
            $group = ace_group_get_info($group_id);
            $section = ace_group_get_section_info($group_id);
            $group_owner = ace_user_get_display_name_by_id($group['owner']);
            $element = '
			        <p align="center"><strong>Class</strong></p>';
            $element_column[] = $element;
            $element = '<div></div>
					<form name="academic_group_update_form" action="' . _ADMIN_URL_ . '" method="post">
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
							    <td align="right">Owner:</td>
							    <td colspan="2">
							        <select name="owner_id">';
            $managers = ace_get_user_admins_and_managers();
            if (is_array($managers)) {
                foreach ($managers as $user) {
                    $element .= '
                                        <option value="' . $user['id'] . '"';
                    if ($user['id'] == $group['owner']) {
                        $element .= ' selected';
                    }
                    $element .= '>' . $user['name'] . '</option>';
                }
            }
			$element .= '
                                    </select>
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
									<button name="operation" value="academic_group_update" type="submit">Save</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $lab_table = ace_group_get_lab_table($group_id);
            $element = '
					<p align="center"><strong>Class Labs</strong></p>
					<form name="group_list_labs_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="academic_group_update_form" type="hidden" />
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
						<button name="operation" value="academic_group_lab_remove" type="submit" ' . ((is_array($lab_table)) ? '' : 'disabled') . '>Revoke</button>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $element = '
					<p align="center"><strong>Class Students</strong></p>
					<form name="academic_group_user_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="academic_group_update_form" type="hidden" />
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
						<button name="display" value="academic_group_user_update_form" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Edit</button>
						<button name="operation" value="group_user_remove" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Remove</button>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $element_table[] = $element_column;
            $element_column = array();
            $element = '
                    <p align="center"><strong>Add Student(s)</strong></p>';
            $element_column[] = $element;
            $element = '
			        <form name="group_user_add_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="academic_group_update_form" type="hidden" />
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
                    <form name="group_users_add_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="academic_group_update_form" type="hidden" />
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
        } else {
            $messages[] = create_message(FALSE, 'selecting group, no group selected');
        }
        break;
    case 'security_group_user_update_form':
        $group_id = $_POST['group_id'];
        $user_id = $_POST['user_id'];
        $user = ace_user_get_info($user_id);
        $element = '
					<form name="security_group_user_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="security_group_update_form" type="hidden" />
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
    case 'academic_group_user_update_form':
        $group_id = $_POST['group_id'];
        $user_id = $_POST['user_id'];
        $user = ace_user_get_info($user_id);
        $element = '
					<form name="academic_group_user_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="academic_group_update_form" type="hidden" />
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
    case 'courses':
        $courses = ace_get_courses();
        $element_column = array();
        $element = '
                    <p align="center"><strong>Courses</strong></p>';
        $element_column[] = $element;
        $element = '
                    <form name="admin_courses_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="courses" type="hidden" />
                        <table>
                            <tr>
                                <th></th>
                                <th>Course Ref</th>
                                <th>Course Name</th>
                                <th>Sections</th>
                            </tr>';
        if (is_array($courses)) {
            foreach ($courses as $course) {
                $section_table = ace_course_get_section_table($course['id']);
                $section_count = (is_array($section_table)) ? count($section_table) : 0;
                $element .= '
                            <tr>
                                <td>
                                    <input name="course_id" value="' . $course['id'] . '" type="radio" />
                                </td>
                                <td>' . $course['courseID'] . '</td>
                                <td>' . $course['courseDisplayName'] . '</td>
                                <td align="center">' . $section_count . '</td>
                            </tr>';
            }
        }
        $element .= '
                        </table>
                        <button name="display" value="course_update_form" type="submit">Edit</button>
                        <button name="operation" value="course_delete" type="submit">Delete</button>
                        <input name="nonce" value="' . $nonce . '" type="hidden" />
                    </form>';

        $element_column[] = $element;
        $element = '
                    <form name="admin_course_create_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="course_create_form" type="hidden" />
                        <button name="operation" value="" type="submit">Create</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;

        break;
    case 'course_create_form':
        $element = '
                        <p align="center"><strong>New Course</strong></p>';
        $element_column[] = $element;
        $element = '
                        <form name="admin_course_create_form" action="' . _ADMIN_URL_ . '" method="post">
                            <input name="display" value="courses" type="hidden" />
                            <table>
                                <tr>
                                    <td align="right">Ref:</td>
                                    <td><input name="course_ref" value="" type="text" /></td>
                                </tr>
                                <tr>
                                    <td align="right">Name:</td>
                                    <td><input name="course_name" value="" type="text" /></td>
                                </tr>
                                <tr>
                                    <td colspan= "2" align="right">
                                        <button name="operation" value="course_create" type="submit">Create</button>
                                        <button name="display" value="courses" type="submit">Cancel</button>
                                    </td>
                                </tr>
                            </table>
                            <input name="nonce" value=' . $nonce . ' type="hidden" />
                        </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'course_update_form':
        if ((isset($_POST['course_id'])) && ($_POST['course_id'] != NULL)) {
            $course_id = $_POST['course_id'];
            $course = ace_course_get_info($course_id);
            $element = '
                        <p align="center"><strong>Course</strong></p>';
            $element_column[] = $element;
            $element = '
                        <form name="admin_course_update_form" action="' . _ADMIN_URL_ . '" method="post">
                            <input name="display" value="courses" type="hidden" />
                            <input name="course_id" value="' . $course_id . '" type="hidden" />
                            <table>
                                <tr>
                                    <td align="right">Ref:</td>
                                    <td><input name="course_ref" value="' . $course['courseID'] . '" type="text" /></td>
                                </tr>
                                <tr>
                                    <td align="right">Name:</td>
                                    <td><input name="course_name" value="' . $course['courseDisplayName'] . '" type="text" /></td>
                                </tr>
                                <tr>
                                    <td colspan= "2" align="right">
                                        <button name="operation" value="course_update" type="submit">Save</button>
                                        <button name="display" value="courses" type="submit">Cancel</button>
                                    </td>
                                </tr>
                            </table>
                        </form>';
            $element_column[] = $element;
            $element_table[] = $element_column;
        } else {
            $messages[] = create_message(FALSE, 'selecting course, no course selected');
        }
        break;
    case 'sections':
        $element_column = array();
        $academic_group_table = ace_get_academic_groups();
        $element = '
					<p align="center"><strong>Classes</strong></p>';
        $element_column[] = $element;
        $element = '
                    <form name="admin_academic_groups_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="sections" type="hidden" />
						<table>
							<tr>
								<th></th>
								<th>ID</th>
								<th>Course Ref</th>
								<th>Course Name</th>
								<th>Owner</th>
							<tr>';
        foreach ($academic_group_table as $group){
            $group_state = ($group['state'] == 1) ? 'active' : 'inactive';
            $course_display_name =
            $user_state = (ace_user_get_state($group['owner'])) ? 'active' : 'inactive';
            $user_name = ace_user_get_display_name_by_id($group['owner']);
            $section = ace_group_get_section_info($group['id']);
            $course_display_name = ace_course_get_display_name_by_ref($section['courseID']);
            if ($user_name == "") {
                $user_name = ace_user_get_name_by_id($group['owner']);
            }
            $element .= '
                            <tr class="' . $group_state . '">
                                <td style="width:30px;">
                                    <input name="group_id" value="' . $group['id'] . '" type="radio" />
                                </td>
                                <td>' . $section['sectionID'] . '
                                <td>' . $section['courseID'] . '</td>
                                <td>' . $course_display_name . '</td>
                                <td class="' . $user_state . '">' . $user_name . '</td>
                            </tr>';
        }
        $element .= '
                        </table>
                        <button name="operation" value="group_change_state" type="submit">(De)Activate</button>
                        <button name="display" value="section_update_form" type="submit">Edit</button>
                        <button name="operation" value="group_delete" type="submit">Delete</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element = '
                    <form name="admin_section_create_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="section_create_form" type="hidden" />
                        <button name="operation" value="" type="submit">Create</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'section_create_form':
        $element = '
                    <p align="center">Create Academic Group</p>
                    <form name="class_create_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="sections" type="hidden" />
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
                                <td align="right">Owner:</td>
                                <td>
                                    <select name="owner_id">';
        $known_owners = ace_get_user_admins_and_managers();
        if (is_array($known_owners)) {
            foreach ($known_owners as $owner) {
                $owner_name = $owner['first'] . ' ' . $owner['last'];
                if ($owner_name == "") {
                    $owner_name = $owner['name'];
                }
                $element .= '
                                            <option value="' . $owner['id'] . '">' . $owner_name . '</option>';
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
                        <input name="operation" value="academic_group_create" type="hidden" />
                        <input name="save" value="Create" type="submit" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'section_update_form':
        if ((isset($_POST['group_id'])) && ($_POST['group_id'] != NULL)) {
            $group_id = $_POST['group_id'];
            $group = ace_group_get_info($group_id);
            $section = ace_group_get_section_info($group_id);
            $course_display_name = ace_course_get_display_name_by_ref($section['courseID']);
            $group_owner = ace_user_get_display_name_by_id($group['owner']);
            $element = '
			        <p align="center"><strong>Class</strong></p>';
            $element_column[] = $element;
            $element = '<div></div>
					<form name="section_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="sections" type="hidden" />
						<input name="group_id" value="' . $group_id . '" type="hidden" />
						<input name="group_name" value="' . $group['name'] . '" type="hidden" />
						<table>
							<tr>
							    <td align="right">Section ID:</td>
							    <td colspan="2">
							        ' . $section['sectionID'] . '
							        <input name="sectionID" value="' . $section['sectionID'] . '" type="hidden" />
							    </td>
							</tr>
							<tr>
							    <td align="right">Course Ref:</td>
							    <td colspan="2">
							        ' . $section['courseID'] . '
							        <input name="courseID" value="' . $section['courseID'] . '" type="hidden" />
							    </td>
							</tr>
							<tr>
							    <td align="right">Course Name:</td>
							    <td colspan="2">
							        ' . $course_display_name . '
							    </td>
							</tr>
							<tr>
							    <td align="right">Owner:</td>
							    <td colspan="2">
							        <select name="owner_id">';
            $managers = ace_get_user_admins_and_managers();
            if (is_array($managers)) {
                foreach ($managers as $user) {
                    $element .= '
                                        <option value="' . $user['id'] . '"';
                    if ($user['id'] == $group['owner']) {
                        $element .= ' selected';
                    }
                    $element .= '>' . $user['name'] . '</option>';
                }
            }
            $element .= '
                                    </select>
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
									<button name="operation" value="academic_group_update" type="submit">Save</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $lab_table = ace_group_get_lab_table($group_id);
            $element = '
					<p align="center"><strong>Labs</strong></p>
					<form name="group_list_labs_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="section_update_form" type="hidden" />
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
						<button name="operation" value="academic_group_lab_remove" type="submit" ' . ((is_array($lab_table)) ? '' : 'disabled') . '>Revoke</button>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $element = '
					<p align="center"><strong>Students</strong></p>
					<form name="academic_group_user_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="section_update_form" type="hidden" />
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
						<button name="display" value="academic_group_user_update_form" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Edit</button>
						<button name="operation" value="group_user_remove" type="submit" ' . ((is_array($group_members_table)) ? '' : 'disabled') . '>Remove</button>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
            $element_column[] = $element;
            $element_table[] = $element_column;
            $element_column = array();
            $element = '
                    <p align="center"><strong>Add Student(s)</strong></p>';
            $element_column[] = $element;
            $element = '
			        <form name="group_user_add_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="section_update_form" type="hidden" />
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
                    <form name="group_users_add_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="section_update_form" type="hidden" />
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
        } else {
            $messages[] = create_message(FALSE, 'selecting group, no group selected');
        }
        break;
    case 'users':
        $users = ace_get_users();
        $element = '
                    <p align="center"><strong>Users</strong></p>';
        $element_column[] = $element;
        $element = '
                    <form name="admin_users_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="users" type="hidden" />
                        <table>
                            <tr>
                                <th></th>
                                <th>eMail</th>
                                <th>First</th>
                                <th>Last</th>
                            </tr>';
        foreach ($users as $user) {
            $user_active = ($user['state'] == 1) ? TRUE : FALSE;
            $element .= '
                            <tr class="' . (($user_active) ? 'active' : 'inactive') . '">
                                <td style="width: 30px;">
                                    <input name="user_id" value="' . $user['id'] . '" type="radio" />
                                </td>
                                <td>' . $user['name'] . '</td>
                                <td>' . $user['first'] . '</td>
                                <td>' . $user['last'] . '</td>
                            </tr>';
        }
        $element .= '
                        </table>
                        <button name="operation" value="user_change_state" type="submit">(De)Activate</button>
                        <button name="display" value="user_update_form" type="submit">Edit</button>
                        <button name="operation" value="user_delete" type="submit">Delete</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element = '
                    <form name="user_create_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="user_create_form" type="hidden" />
                        <button name="operation" value="" type="submit">Create</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'user_create_form':
        $element = '
                    <p align="center"><strong>New User</strong></p>';
        $element_column[] = $element;
        $element = '
					<form name="user_create_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="users" type="hidden" />
						<table>
							<tr>
								<td align="right">*email:</td>
								<td><input name="user_email" value="" type="email" /></td>
							</tr>
							<tr>
								<td align="right">first:</td>
								<td><input name="user_first" value="" type="text" /></td>
							</tr>
							<tr>
								<td align="right">last:</td>
								<td><input name="user_last" value="" type="text" /></td>
							</tr>
							<tr>
								<td align="right" colspan="2">
									<button name="operation" value="user_create" type="submit">Save</button>
									<button name="operation" value="form_cancel" type="submit">Cancel</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'user_update_form':
        $user_id = $_POST['user_id'];
        $user = ace_user_get_info($user_id);
        $element = '
					<form name="user_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="users" type="hidden" />
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
									<button name="operation" value="user_update" type="submit">Save</button>
									<button name="operation" value="form_cancel" type="submit">Cancel</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'quotas':
        $quotas = ace_get_quotas();
        $element = '
                    <p align="center"><strong>Quotas</strong></p>';
        $element_column[] = $element;
        $element = '
                    <form name="admin_quotas_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="quotas" type="hidden" />
                        <table>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Labs</th>
                                <th>VMs</th>
                                <th>vCPUs</th>
                                <th>vMem</th>
                                <th>vNets</th>
                                <th>Vols</th>
                                <th>Storage</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Object</th>
                                <th>(#)</th>
                                <th>(#)</th>
                                <th>(#)</th>
                                <th>(GiB)</th>
                                <th>(#)</th>
                                <th>(#)</th>
                                <th>(GiB)</th>
                            </tr>';
        foreach ($quotas as $quota) {
            $object_display_name = '';
            switch ($quota['object_type']) {
                case 'user':
                    $object_display_name = 'User (' . ace_user_get_name_by_id($quota['object_id']) . ')';
                    break;
                case 'group':
                    $object_display_name = 'Group (' . ace_group_get_name_by_id($quota['object_id']) . ')';
                    break;
                case 'lab':
                    $object_display_name = 'Lab (' . ace_user_get_name_by_id(ace_lab_get_user_id($quota['object_id'])) . ':' . ace_lab_get_display_name_by_id($quota['object_id']) . ')';
                    break;
                case 'host':
                    $object_display_name = 'Host (' . ace_host_get_name_by_id($quota['object_id']) . ')';
                    break;
            }
            $element .= '
                            <tr>
                                <td style="width: 30px;">
                                    <input name="quota_id" value="' . $quota['id'] . '" type="radio" /></td>
                                <td>' . $object_display_name . '</td>
                                <td align="center">' . $quota['labs'] . '</td>
                                <td align="center">' . $quota['vms'] . '</td>
                                <td align="center">' . $quota['vcpu'] . '</td>
                                <td align="center">' . $quota['memory'] . '</td>
                                <td align="center">' . $quota['networks'] . '</td>
                                <td align="center">' . $quota['volumes'] . '</td>
                                <td align="center">' . $quota['storage'] . '</td>
                            </tr>';
        }
        $element .= '
                        </table>
                        <button name="display" value="quota_update_form" type="submit">Edit</button>
                        <button name="operation" value="quota_delete" type="submit">Delete</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element = '
                    <form name="quota_create_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="quota_create_form" type="hidden" />
                        <select name="object_type">
                            <option value="host">Host Quota</option>
                            <option value="group" selected>Group Quota</option>
                            <option value="lab">Lab Quota</option>
                            <option value="user">User Quota</option>
                        </select>
                        <button name="operation" value="" type="submit">Create</button>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'quota_create_form':
        $object_type = $_POST['object_type'];
        $element = '
                    <p align="center"><strong>New Quota</strong></p>';
        $element_column[] = $element;
        $element = '<div></div>
					<form name="admin_quota_create_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="quotas" type="hidden" />
                        <input name="object_type" value="' . $object_type . '" type="hidden" />
						<table>
						    <tr>';
        switch ($object_type) {
            case 'host':
                $element .= '
                                <td align="right">Host:</td>
                                <td>
                                    <select name="object_id">';
                $hosts = ace_get_hosts();
                foreach ($hosts as $host) {
                    $element .= '
                                        <option value="' . $host['id'] . '">' . $host['name'] . '</option>';
                }
                $element .= '
                                    </select>
                                </td>';
                break;
            case 'group':
                $element .= '
                                <td align="right">Group:</td>
						        <td>
						            <select name="object_id">';
                $groups = ace_get_groups();
                foreach($groups as $group) {
                    $element .= '
                                        <option value="' . $group['id'] . '">' . $group['name'] . '</option>';
                }
                $element .= '
                                    </select>
						        </td>';
                break;
            case 'lab':
                $element .= '
                                <td align="right">Lab:</td>
						        <td>
						            <select name="object_id">';
                $labs = ace_get_labs();
                foreach($labs as $lab) {
                    $lab_user_id = ace_lab_get_user_id($lab['id']);
                    $lab_user_name = ace_user_get_name_by_id($lab_user_id);
                    $element .= '
                                        <option value="' . $lab['id'] . '">' . $lab_user_name . ' (' . $lab['display_name'] . ')</option>';
                }
                $element .= '
                                    </select>
						        </td>';
                break;
            case 'user':
                $element .= '
                                <td align="right">User:</td>
						        <td>
						            <select name="object_id">';
                $users = ace_get_users();
                foreach($users as $user) {
                    $element .= '
                                        <option value="' . $user['id'] . '">' . $user['name'] . '</option>';
                }
                $element .= '
                                    </select>
						        </td>';
                break;
        }
		$element .= '
                            </tr>
							<tr>
								<td align="right">Labs (#):</td>
								<td><input name="labs" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>';
        if ($object_type != 'host') {
            $element .= '
							<tr>
								<td align="right">VMs (#):</td>
								<td><input name="vms" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">vCPUs (#):</td>
								<td><input name="vcpu" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">vMem (GiB):</td>
								<td><input name="memory" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">vNets (#):</td>
								<td><input name="networks" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">Volumes (#):</td>
								<td><input name="volumes" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">Storage (GiB):</td>
								<td><input name="storage" value="1" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>';
        }
        $element .= '
							<tr>
								<td align="right" colspan="2">
									<button name="operation" value="quota_create" type="submit">Save</button>
									<button name="operation" value="form_cancel" type="submit">Cancel</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'quota_update_form':
        $quota_id = $_POST['quota_id'];
        $quota = ace_quota_get_info($quota_id);
        $object_display_name = '';
        switch ($quota['object_type']) {
            case 'host':
                $object_display_name = 'Host (' . ace_host_get_name_by_id($quota['object_id']) . ')';
                break;
            case 'user':
                $object_display_name = 'User (' . ace_user_get_name_by_id($quota['object_id']) . ')';
                break;
            case 'group':
                $object_display_name = 'Group (' . ace_group_get_name_by_id($quota['object_id']) . ')';
                break;
            case 'lab':
                $object_display_name = 'Lab (' . ace_user_get_name_by_id(ace_lab_get_user_id($quota['object_id'])) . ':' . ace_lab_get_display_name_by_id($quota['object_id']) . ')';
                break;
        }
        $element = '
                    <p align="center"><strong>Edit Quota</strong></p>';
        $element_column[] = $element;
        $element = '<div></div>
					<form name="admin_quota_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="quotas" type="hidden" />
						<input name="quota_id" value="' . $quota_id . '" type="hidden" />
                        <input name="object_type" value="' . $quota['object_type'] . '" type="hidden" />
                        <input name="object_id" value="' . $quota['object_id'] . '" type="hidden" />
						<table>
						    <tr>
						        <td align="right">Object:</td>
						        <td>' . $object_display_name . '</td>
						    </tr>
							<tr>
								<td align="right">Labs (#):</td>
								<td><input name="labs" value="' . $quota['labs'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>';
        if ($quota['object_type'] != 'host') {
            $element .= '
							<tr>
								<td align="right">VMs (#):</td>
								<td><input name="vms" value="' . $quota['vms'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">vCPUs (#):</td>
								<td><input name="vcpu" value="' . $quota['vcpu'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">vMem (GiB):</td>
								<td><input name="memory" value="' . $quota['memory'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">vNets (#):</td>
								<td><input name="networks" value="' . $quota['networks'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">Volumes (#):</td>
								<td><input name="volumes" value="' . $quota['volumes'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>
							<tr>
								<td align="right">Storage (GiB):</td>
								<td><input name="storage" value="' . $quota['storage'] . '" type="number" min="1" max="99" style="width: 58px;" /></td>
							</tr>';
        }
        $element .= '
							<tr>
								<td align="right" colspan="2">
									<button name="operation" value="quota_update" type="submit">Save</button>
									<button name="operation" value="form_cancel" type="submit">Cancel</button>
								</td>
							</tr>
						</table>
						<input name="nonce" value=' . $nonce . ' type="hidden" />
					</form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'labs':
        $unsorted_labs = ace_get_labs();
        $unsorted_labs_with_owner = array();
        foreach ($unsorted_labs as $lab){
            $lab['email'] = ace_user_get_name_by_id($lab['user_id']);
            $unsorted_labs_with_owner[] = $lab;
        }
        foreach ($unsorted_labs_with_owner as $key => $value) {
            $email[$key] = $value['email'];
            $display_name[$key] = $value['display_name'];
        }
        array_multisort($email, $display_name, $unsorted_labs_with_owner);
        $labs = $unsorted_labs_with_owner;
        $element = '
                    <p align="center"><strong>Labs</strong></p>';
        $element_column[] = $element;
        $element = '<div></div>
                    <form name="admin_labs_form" action="' . _ADMIN_URL_ . '" method="post">
                        <input name="display" value="labs" type="hidden" />
                        <table>
                            <tr>
                                <th></th>
                                <th>Owner</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Age(Hrs)</th>
                                <th>Host</th>
                            </tr>';
        foreach ($labs as $lab) {
            $lab_active = ($lab['state'] == 1) ? TRUE : FALSE;
            $lab_state = ($lab_active) ? 'active' : 'inactive';
            $lab_owner_email = ace_user_get_name_by_id($lab['user_id']);
            $lab_age_hours ='';
            $lab_is_aged = FALSE;
            if ($lab_active) {
                $lab_age = ace_lab_get_age($lab['id']);
                $lab_age_hours = round($lab_age/3600,0);
                $lab_is_aged = $lab_age > _LAB_AGE_MAXIMUM_ ? TRUE : FALSE;
            }
            $lab_host_name = ($lab_active) ? ace_host_get_name_by_id($lab['host_id']) : '';
            $element .= '
                            <tr class="' . $lab_state . '" style="max-height:10px;">
                                <td style="width: 30px;">
                                    <input name="lab_id" value="' . $lab['id'] . '" type="radio" />
                                </td>
                                <td>' . $lab_owner_email . '</td>
                                <td>' . $lab['display_name'] . '</td>
                                <td style="width:200px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' . $lab['description'] . '
                                <td align="center" ' . (($lab_is_aged) ? 'style="color:red"' : '') . '>' . $lab_age_hours . '</td>
                                <td>' . $lab_host_name . '</td>
                            </tr>';
        }
        $element .= '
                        </table>
                        <table>
                            <tr>
                                <td>
                                    <button name="operation" value="lab_change_state" type="submit">(De)Activate</button>
                                    <button name="display" value="lab_update_form" type="submit">Edit</button>
                                    <button name="operation" value="lab_delete" type="submit">Delete</button>
                                </td>
                                <td align="right">
                                    <button name="operation" value="labs_purge_aged" type="submit">Purge Aged Labs (>72Hrs)</button>
                                </td>
                            </tr>
                        </table>
                        <input name="nonce" value=' . $nonce . ' type="hidden" />
                    </form>';
        $element_column[] = $element;
        $element_table[] = $element_column;
        break;
    case 'lab_update_form':
        $lab_id = $_POST['lab_id'];
        $lab = ace_lab_get_info($lab_id);
        $owner_name = ace_user_get_name_by_id($lab['user_id']);
        $element = '
                    <p align="center"><strong>Edit Lab</strong></p>';
        $element_column[] = $element;
        $element = '
					<form name="lab_update_form" action="' . _ADMIN_URL_ . '" method="post">
						<input name="display" value="labs" type="hidden" />
						<input name="lab_id" value=' . $lab_id . ' type="hidden" />
						<input name="lab_user_id" value="' . $lab['user_id'] . '" type="hidden" />
						<input name="lab_host_id" value="' . $lab['host_id'] . '" type="hidden" />
						<input name="lab_name" value="' . $lab['name'] . '" type="hidden" />
						<table>
							<tr>
								<td align="right">Owner:</td>
								<td>' . $owner_name . '</td>
							</tr>
							<tr>
								<td align="right">Name:</td>
								<td><input name="lab_display_name" value="' . $lab['display_name'] . '" type="text" /></td>
							</tr>
							<tr>
								<td align="right">Description:</td>
								<td><input name="lab_description" value="' . $lab['description'] . '" type="text" size="50" /></td>
							</tr>
							<tr>
								<td align="right" colspan="2">
									<button name="operation" value="lab_update" type="submit">Save</button>
									<button name="operation" value="form_cancel" type="submit">Cancel</button>
								</td>
							</tr>
						</table>
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
<!doctype html>
<html>
    <head>
        <title>ACEITLab - Admin</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
                                <input name="nonce" value=' . $nonce . ' type="hidden"/>
                                <button name="action" value="logout" type="submit">Logout</button>
                            </form>
                        </div>
                        <div class="row_element_right">
                            <form action="<?php echo _USER_URL_; ?>" method="post">
                                <input name="nonce" value=' . $nonce . ' type="hidden"/>
                                <button name="action" value="edit_profile"
                                        type="submit"><?php echo $user_display_name; ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <!--<div class="horizontal_divider"></div>-->
        <div id="menu_bar" class="menu_bar">
            <form class="bar_form" action="<?php echo _ADMIN_URL_ ?>" method="post">
                <input name="nonce" value='<?php echo $nonce; ?>' type="hidden"/>

                <div class="element_table">
                    <div class="element_column">
                        <div class="row_element">
                            <button name="display" value="hosts" type="submit" class="<?php echo ($_POST['display'] == 'hosts') ? 'selected' : ''; ?>">Hosts</button>
                            <button name="display" value="resources" type="submit" class="<?php echo ($_POST['display'] == 'resources') ? 'selected' : ''; ?>">Resources</button>
                        </div>
                        <div class="row_element">
                            <button name="display" value="groups" type="submit" class="<?php echo ($_POST['display'] == 'groups') ? 'selected' : ''; ?>">Security Groups</button>
                            <button name="display" value="courses" type="submit" class="<?php echo ($_POST['display'] == 'courses') ? 'selected' : ''; ?>">Defined Courses</button>
                            <button name="display" value="sections" type="submit" class="<?php echo ($_POST['display'] == 'sections') ? 'selected' : ''; ?>">Current Classes</button>
                            <button name="display" value="users" type="submit" class="<?php echo ($_POST['display'] == 'users') ? 'selected' : ''; ?>">All Users</button>
                        </div>
                        <div class="row_element">
                            <button name="display" value="quotas" type="submit" class="<?php echo ($_POST['display'] == 'quotas') ? 'selected' : ''; ?>">Quotas</button>
                            <button name="display" value="labs" type="submit" class="<?php echo ($_POST['display'] == 'labs') ? 'selected' : ''; ?>">Labs</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="clear"></div>
        </div>
        <!--<div class="horizontal_divider"></div>-->
        <div id="section_main" class="section">
<?php
echo $html;
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
            <div class="clear"></div>
        </div>
        <div id="status_section" class="section">
            <div class="message_bar"><?php echo (isset($messages)) ? ace_out_messages($messages) : ''; ?></div>
        </div>
    </body>
</html>
