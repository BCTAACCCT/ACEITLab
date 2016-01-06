<?php
/**
 * ACEITLab Application - Lab Module
 *
 * provides interface for performing lab related tasks
 * requires USER security level or higher
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

require_once('fns.php');
session_start();
ace_validate_session(_USER_SECURITY_LEVEL_);
ace_session_redirect_form_refresh(_LAB_URL_ . ((isset($_SESSION['current_lab_id'])) ? '?lab_id=' . $_SESSION['current_lab_id'] : ''));

$nonce = rand();
$success = NULL;

if (isset($_POST['lab_id']) && ($_POST['lab_id'] != 'null')) {
    $lab_id = $_POST['lab_id'];
    $_SESSION['current_lab_id'] = $lab_id;
} else {
    $lab_id = NULL;
    $_SESSION['current_lab_id'] = NULL;
}

# BEGIN COMMAND PROCESSING
switch ($_POST['action']) {
    case 'lab_create' :
        // create a new lab
        $lab_id = ace_lab_create($_SESSION['user_id']);
        $success = ($lab_id !== FALSE) ? TRUE : FALSE;
        if ($success) {
            $_SESSION['current_lab_id'] = $lab_id;
            $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
            $message = create_message($success, "creating $lab_display_name");
        } else {
            $lab_id = NULL;
            $_SESSION['current_lab_id'] = NULL;
            $message = create_message($success, "creating lab, no lab_id returned");
        }
        break;
    case 'duplicate_group_lab' :
        // copy an existing lab
        if (isset($_POST['from_lab_id']) && ($_POST['from_lab_id'] != 'none')) {
            $from_lab_display_name = ace_lab_get_display_name_by_id($_POST['from_lab_id']);
            $to_user_name = ace_user_get_display_name_by_id($_POST['to_user_id']);
            # create a new lab based on this group lab definition
            $to_lab_id = ace_lab_duplicate($_POST['from_lab_id'], $_POST['to_user_id']);
            if ($to_lab_id !== FALSE) {
                // $_SESSION['current_lab_id'] = $to_lab_id;
                $to_lab_display_name = ace_lab_get_display_name_by_id($to_lab_id);
                $message = create_message(TRUE, "duplicating group lab $from_lab_display_name to user $to_user_name as lab $to_lab_display_name");
            } else {
                $to_lab_id = NULL;
                $message = create_message(FALSE, 'duplicating group lab, lab creation failed');
            }
            $_SESSION['current_lab'] = $to_lab_id;
            $lab_id = $to_lab_id;
        } else {
            $message = create_message(FALSE, 'duplicating group lab, no group lab_id specified');
        }
        break;
    case 'load_student_lab':
        if (isset($_POST['student_lab_id']) && ($_POST['student_lab_id'] != 'none')) {
            $student_lab_display_name = ace_lab_get_display_name_by_id($_POST['student_lab_id']);
            $_SESSION['current_lab'] = $_POST['student_lab_id'];
            $lab_id = $_POST['student_lab_id'];
        } else {
            $messages[] = create_message(FALSE, 'loading student lab, no lab id specified');
        }
        break;
    case 'lab_delete' :
        // delete an existing lab
        if (isset($lab_id)) {
            $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
            $success = ace_lab_delete($lab_id);
            $message = create_message($success, "deleting lab '$lab_display_name'");
            $_SESSION['current_lab_id'] = NULL;
            $lab_id = NULL;
        } else {
            $message = create_message(FALSE, 'deleting lab, no lab_id specified');
        }
        break;
    case 'lab_rename' :
        if (isset($lab_id)) {
            $old_lab_display_name = ace_lab_get_display_name_by_id($lab_id);
            $new_lab_display_name = $_POST['lab_display_name'];
            if ($old_lab_display_name != $new_lab_display_name) {
                $success = ace_lab_rename($lab_id, $new_lab_display_name);
                $message = create_message($success, "renaming lab '$old_lab_display_name' to '$new_lab_display_name'");
            } else {
                $message = create_message(FALSE, 'renaming lab, name did not change');
            }
        } else {
            $message = create_message(FALSE, 'renaming lab, no lab_id specified');
        }
        break;
    case 'lab_unpublish':
        if (isset($lab_id)) {
            if (ace_lab_is_published($lab_id)) {
                $lab_group_ids = ace_lab_get_group_ids($lab_id);
                $group_count = count($lab_group_ids);
                if ($lab_group_ids !== FALSE) {
                    foreach ($lab_group_ids as $group_id) {
                        ace_group_remove_lab($group_id,$lab_id);
                    }
                    $messages[] = create_message(TRUE, 'un-publishing lab, lab was unpublished from ' . $group_count . ' group(s)');
                } else {
                    $messages[] = create_message(FALSE, 'un-publishing lab, lab was marked as published but no published groups were found');
                }
            } else {
                $messages[] = create_message(FALSE, 'un-publishing lab, lab was not published');
            }
        } else {
            $messages[] = create_message(FALSE, 'un-publishing lab, no lab_id apecified');
        }
        break;
    case 'lab_activate' :
        // activate an existing lab
        if (isset($lab_id)) {
            $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
            $success = ace_lab_activate($lab_id);
            $message = create_message($success, "activating $lab_display_name");
        } else {
            $message = create_message(FALSE, "activating lab, no lab_id_specified");
        }
        break;
    case 'lab_deactivate' :
        // deactivate an existing lab
        if (isset($lab_id)) {
            $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
            $success = ace_lab_deactivate($lab_id);
            $message = create_message($success, "deactivating $lab_display_name");
        } else {
            $message = create_message(FALSE, "deactivating lab, no lab_id specified");
        }
        break;
    case 'lab_create_volume' :
        if (isset($lab_id, $_POST['size'], $_POST['unit'])) {
            $quota_array = ace_lab_get_quota_array($lab_id);
            $total_lab_volumes_reservations_quota = $quota_array['storage'];
            $total_lab_volumes_reservations_GiB = ace_lab_get_volumes_reservation_size($lab_id);
            $available_storage_reservation_GiB = $total_lab_volumes_reservations_quota - $total_lab_volumes_reservations_GiB;
            $requested_reservation_GiB = ($_POST['unit'] == 'M') ? round($_POST['size'] / 1024, 2) : $_POST['size'];
            if ($requested_reservation_GiB <= $available_storage_reservation_GiB) {
                $volume_id = ace_volume_create($lab_id, $_POST['size'], $_POST['unit'], NULL);
                $success = ($volume_id !== FALSE) ? TRUE : FALSE;
                if ($success) {
                    $volume_display_name = ace_volume_get_display_name_by_id($volume_id);
                    $message = create_message(TRUE, "creating $volume_display_name");
                } else {
                    $message = create_message(FALSE, "creating volume, volume_id not returned");
                }
            } else {
                $message = create_message(FALSE, "creating LAB Volume, request too large for quota");
                $volume_quota_class .= ' fail';
            }
        } else {
            $message = create_message(FALSE, "creating volume, size not specified");
        }
        break;
    case 'lab_create_diff_volume' :
        if (isset($lab_id, $_POST['volume_id'])) {
            $quota_array = ace_lab_get_quota_array($lab_id);
            $total_lab_volumes_reservations_quota = $quota_array['storage'];
            $total_lab_volumes_reservations_GiB = ace_lab_get_volumes_reservation_size($lab_id);
            $available_storage_reservation_GiB = $total_lab_volumes_reservations_quota - $total_lab_volumes_reservations_GiB;
            $base_volume = ace_volume_get_info($_POST['volume_id']);
            $base_volume_size_GiB = ($base_volume['unit'] == 'M') ? round($base_volume['size'] / 1024, 2) : $base_volume['size'];
            $base_volume_size_on_disk_GiB = ($base_volume['unit'] == 'M') ? round($base_volume['size_on_disk'] / 1024, 2) : $base_volume['size_on_disk'];
            $requested_reservation_GiB = $base_volume_size_GiB - $base_volume_size_on_disk_GiB;
            if ($requested_reservation_GiB <= $available_storage_reservation_GiB) {
                $diff_volume_id = ace_volume_create($lab_id, $base_volume['size'], $base_volume['unit'], $base_volume['id']);
                $success = ($diff_volume_id !== FALSE) ? TRUE : FALSE;
                if ($success) {
                    $diff_volume_display_name = ace_volume_get_display_name_by_id($diff_volume_id);
                    $message = create_message(TRUE, "creating differencing volume $diff_volume_display_name");
                } else {
                    $message = create_message(FALSE, "creating differencing volume, volume id not returned");
                }
            } else {
                $message = create_message(FALSE, "creating differencing volume, request too large for quota");
                $volume_quota_class .= ' fail';
            }
        } else {
            $message = create_message(FALSE, "creating differencing volume, lab and/or base volume not specified");
        }
        break;
    case 'lab_delete_volume' :
        if (isset($_POST['volume_id'])) {
            # ensure volume not in use by any vms
            $vm_assignment_array = ace_volume_get_vm_assignments($_POST['volume_id']);
            if ($vm_assignment_array) {
                $message = create_message(FALSE, "deleting volume, volume still assigned to vm(s)");
            } else {
                $volume_display_name = ace_volume_get_display_name_by_id($_POST['volume_id']);
                $success = ace_volume_delete($_POST['volume_id']);
                $message = create_message($success, "deleting $volume_display_name");
            }
        } else {
            $message = create_message(FALSE, "deleting volume, no volume_id specified");
        }
        break;
    case 'lab_create_network' :
        if (isset($lab_id)) {
            $network_id = ace_network_create($lab_id);
            $success = ($network_id !== FALSE) ? TRUE : FALSE;
            if ($success) {
                $network_display_name = ace_network_get_name_by_id($network_id);
                $message = create_message($success, "creating $network_display_name");
            } else {
                $message = create_message($success, "creating network, network_id not returned");
            }
        } else {
            $message = create_message(FALSE, "creating network, no lab_id specified");
        }
        break;
    case 'network_change_state' :
        if (isset($_POST['network_id'])) {
            $network = ace_network_get_info($_POST['network_id']);
            $network_display_name = $network['display_name'];
            if ($network['instance'] != 0) {    // make sure we're not modifying the tenant-public network
                if (ace_network_is_active($_POST['network_id'])) {
                    $success = ace_network_deactivate($_POST['network_id']);
                    $message = create_message($success, "deactivating $network_display_name");
                } else {
                    $success = ace_network_activate($_POST['network_id']);
                    $message = create_message($success, "activating $network_display_name");
                }
            } else {
                $message = create_message(FALSE, "deactivating $network_display_name, not allowed");
            }
        } else {
            $message = create_message(FALSE, "activating network, no network_id specified");
        }
        break;
    case 'network_rename' :
        if (isset($_POST['network_id'])) {
            if (($_POST['network_new_name'] == '..new name..') || ($_POST['network_new_name'] == '')) {
                $message = create_message(FALSE, "renaming network, new name not specified");
            } else {
                $network = ace_network_get_info($_POST['network_id']);
                $network_display_name = $network['display_name'];
                if ($network['instance'] != 0) {    // make sure we're not modifying the tenant-public network
                    $success = ace_network_rename($_POST['network_id'], $_POST['network_new_name']);
                    $message = create_message($success, "renaming network $network_display_name to " . $_POST['network_new_name']);
                } else {
                    $message = create_message(FALSE, "renaming network, cannot rename the public network");
                }
            }
        } else {
            $message = create_message(FALSE, "renaming network, no network_id specified");
        }
        break;
    case 'lab_delete_network' :
        if (isset($_POST['network_id'])) {
            $network = ace_network_get_info($_POST['network_id']);
            $network_display_name = $network['display_name'];
            //$network_display_name = ace_network_get_name_by_id($_POST['network_id']);
            if ($network['instance'] != 0) {
                $success = ace_network_delete($_POST['network_id']);
                $message = create_message($success, "deleting $network_display_name");
            } else {
                $message = create_message(FALSE, "deleting $network_display_name, not allowed");
            }
        } else {
            $message = create_message($success, "deleting network, no network_id specified");
        }
        break;
    case 'lab_create_vm' :
        if (isset($lab_id, $_POST['vm_vcpu'], $_POST['vm_memory'], $_POST['vm_unit'])) {
            $quota_array = ace_lab_get_quota_array($lab_id);
            //get quota
            $lab_vm_vcpu_quota = $quota_array['vcpu'];  // quota not including tenant router
            //get total in use
            $lab_vm_vcpu_count = ace_lab_get_vm_vcpu_count($lab_id) - 1;  // adjusted to exclude tenant router
            //find available balance
            $available_lab_vm_vcpu = $lab_vm_vcpu_quota - $lab_vm_vcpu_count;
            //get quota
            $lab_vm_memory_quota = $quota_array['memory'];  //quota not including tenant router
            //get total in use
            $lab_vm_memory_count = ace_lab_get_vm_memory_count($lab_id) - 0.5;  // adjusted to exclude tenant router
            //find available balance
            $available_lab_vm_memory = $lab_vm_memory_quota - $lab_vm_memory_count;
            //if either vcpu or memory exceeds available quota, fail
            if ($_POST['vm_vcpu'] <= $available_lab_vm_vcpu) {
                if ($_POST['vm_unit'] == 'M') {
                    $memory_GiB = $_POST['vm_memory'] / 1024;
                } else {  // assume unit 'G'
                    $memory_GiB = $_POST['vm_memory'];
                }
                if ($memory_GiB <= $available_lab_vm_memory) {
                    $vm_id = ace_vm_create($lab_id, $_POST['vm_vcpu'], $_POST['vm_memory'], $_POST['vm_unit'],$_POST['vm_profile']);
                    $success = ($vm_id !== FALSE) ? TRUE : FALSE;
                    if ($success) {
                        $vm_display_name = ace_vm_get_display_name_by_id($vm_id);
                        $messages[] = create_message($success, "creating $vm_display_name");
                    } else {
                        $messages[] = create_message($success, "creating vm, no vm_id returned");
                    }
                } else {
                    $messages[] = create_message(FALSE, 'creating vm, memory exceeded lab quota');
                }
            } else {
                $messages[] = create_message(FALSE, 'creating vm, vcpu exceeded lab quota');
            }
        } else {
            $messages[] = create_message(FALSE, "creating vm, vcpu, mem, and/or unit not specified");
        }
        break;
    case 'lab_delete_vm' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_delete($_POST['vm_id']);
            $message = create_message($success, "deleting $vm_display_name");
        } else {
            $message = create_message(FALSE, "deleting vm, no vm_id specified");
        }
        break;
    case 'vm_rename' :
        if (isset($_POST['vm_id'])) {
            if (($_POST['vm_new_name'] == '..new name..') || ($_POST['vm_new_name'] == '')) {
                $message = create_message(FALSE, "renaming vm, new name not specified");
            } else {
                $vm_old_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
                $success = ace_vm_rename($_POST['vm_id'], $_POST['vm_new_name']);
                $message = create_message($success, "renaming $vm_old_display_name to " . $_POST['vm_new_name']);
            }
        } else {
            $message = create_message(FALSE, "renaming vm, no vm_id specified");
        }
        break;
    case 'vm_start' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_activate($_POST['vm_id']);
            $message = create_message($success, "starting $vm_display_name");
        } else {
            $message = create_message(FALSE, "starting vm, no vm_id specified");
        }
        break;
    case 'vm_stop' :
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
    case 'vm_attach_cdrom' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $vm_cdrom_instance = ace_vm_attach_cdrom($_POST['vm_id']);
            $success = ($vm_cdrom_instance !== FALSE) ? TRUE : FALSE;
            $message = create_message($success, "attaching CD$vm_cdrom_instance to $vm_display_name");
        } else {
            $message = create_message(FALSE, "attaching cdrom to vm, no vm_id specified");
        }
        break;
    case 'vm_detach_cdrom' :
        if (isset($_POST['vm_id'], $_POST['vm_cdrom_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_detach_cdrom($_POST['vm_id'], $_POST['vm_cdrom_instance']);
            $message = create_message($success, "detaching CD" . $_POST['vm_cdrom_instance'] . " from $vm_display_name");
        } else {
            $message = create_message(FALSE, "detaching cdrom from vm, no vm_id and/or cdrom specified");
        }
        break;
    case 'vm_cdrom_insert_media' :
        if (isset($_POST['vm_cdrom_combo'], $_POST['volume_id'])) {
            $combo_split = explode('_', $_POST['vm_cdrom_combo'], 2);
            $vm_id = $combo_split[0];
            $vm_cdrom_instance = $combo_split[1];
            $vm_display_name = ace_vm_get_display_name_by_id($vm_id);
            $volume_display_name = ace_volume_get_display_name_by_id($_POST['volume_id']);
            $success = ace_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $_POST['volume_id']);
            $message = create_message($success, "inserting $volume_display_name into CD$vm_cdrom_instance in $vm_display_name");
        } else {
            $message = create_message(FALSE, "inserting media in cdrom, no vm_id, cdrom, and/or volume specified");
        }
        break;
    case 'vm_cdrom_eject_media' :
        if (isset($_POST['vm_id'], $_POST['vm_cdrom_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_cdrom_eject_media($_POST['vm_id'], $_POST['vm_cdrom_instance']);
            $message = create_message($success, "ejecting CD" . $_POST['vm_cdrom_instance'] . " in $vm_display_name");
        } else {
            $message = create_message(FALSE, "ejecting media from cdrom, no vm_id and/or cdrom specified");
        }
        break;
    case 'vm_attach_disk' :
        if (isset($_POST['vm_id'], $_POST['volume_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $volume_display_name = ace_volume_get_display_name_by_id($_POST['volume_id']);
            $vm_disk_instance = ace_vm_attach_disk($_POST['vm_id'], $_POST['volume_id']);
            $success = ($vm_disk_instance !== FALSE) ? TRUE : FALSE;
            $message = create_message($success, "attaching disk$vm_disk_instance with $volume_display_name to $vm_display_name");
        } else {
            $message = create_message(FALSE, "attaching disk to vm, no vm_id and/or volume specified");
        }
        break;
    case 'vm_detach_disk' :
        if (isset($_POST['vm_id'], $_POST['vm_disk_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_detach_disk($_POST['vm_id'], $_POST['vm_disk_instance']);
            $message = create_message($success, "detaching disk" . $_POST['vm_disk_instance'] . " from $vm_display_name");
        } else {
            $message = create_message(FALSE, "detaching disk from vm, no vm_id and/or disk specified");
        }
        break;
    case 'vm_attach_nic' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $vm_nic_instance = ace_vm_attach_nic($_POST['vm_id']);
            $success = ($vm_nic_instance !== FALSE) ? TRUE : FALSE;
            $message = create_message($success, "attaching nic$vm_nic_instance to $vm_display_name ");
        } else {
            $message = create_message(FALSE, "attaching nic to vm, no vm_id specified");
        }
        break;
    case 'vm_detach_nic' :
        if (isset($_POST['vm_id'], $_POST['vm_nic_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_detach_nic($_POST['vm_id'], $_POST['vm_nic_instance']);
            $message = create_message($success, "detaching nic" . $_POST['vm_nic_instance'] . " from $vm_display_name");
        } else {
            $message = create_message(FALSE, "detaching nic from vm, no vm_id and/or nic specified");
        }
        break;
    case 'vm_nic_connect_network' :
        if (isset($_POST['vm_id_and_nic_instance'], $_POST['network_id'])) {
            $vm_id_and_nic_instance_exploded = explode('_', $_POST['vm_id_and_nic_instance'], 2);
            $vm_id = $vm_id_and_nic_instance_exploded[0];
            $vm_display_name = ace_vm_get_display_name_by_id($vm_id);
            $vm_nic_instance = $vm_id_and_nic_instance_exploded[1];
            $network_display_name = ace_network_get_display_name_by_id($_POST['network_id']);
            $success = ace_vm_nic_connect_network($vm_id, $vm_nic_instance, $_POST['network_id']);
            $message = create_message($success, "connecting $network_display_name to nic$vm_nic_instance in $vm_display_name");
        } else {
            $message = create_message(FALSE, "connecting nic to network, no vm_id, nic, and/or network specified");
        }
        break;
    case 'vm_nic_disconnect' :
        if (isset($_POST['vm_id'], $_POST['vm_nic_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_nic_disconnect($_POST['vm_id'], $_POST['vm_nic_instance']);
            $message = create_message($success, "disconnecting nic" . $_POST['vm_nic_instance'] . " from $vm_display_name");
        } else {
            $message = create_message(FALSE, "disconnecting nic from network, no vm_id and/or nic specified");
        }
        break;
    case 'vm_create_a_snapshot' :
        if (isset($_POST['vm_id'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_create_snapshot($_POST['vm_id']);
            $message = create_message($success, "generating snapshot of $vm_display_name");
        } else {
            $message = create_message(FALSE, "reverting to snapshot, no vm_id specified");
        }
        break;
    case 'vm_revert_to_snapshot' :
        if (isset($_POST['vm_id'], $_POST['vm_snapshot_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_snapshot_revert($_POST['vm_id'], $_POST['vm_snapshot_instance']);
            $message = create_message($success, "reverting to snapshot of $vm_display_name");
        } else {
            $message = create_message(FALSE, "reverting to snapshot, no snapshot specified");
        }
        break;
    case 'vm_delete_a_snapshot' :
        if (isset($_POST['vm_id'], $_POST['vm_snapshot_instance'])) {
            $vm_display_name = ace_vm_get_display_name_by_id($_POST['vm_id']);
            $success = ace_vm_snapshot_delete($_POST['vm_id'], $_POST['vm_snapshot_instance']);
            $message = create_message($success, "deleting snapshot from $vm_display_name");
        } else {
            $message = create_message(FALSE, "deleting snapshot, no snapshot specified");
        }
}
if (isset($message)) $messages[] = $message;
unset($message);

# END COMMAND PROCESSING

# BEGIN PAGE DATA
$nonce = rand();
$lab_age_display = '';
$lab_age_maximum = '';

$lab_state = FALSE;
$lab_is_published = FALSE;
if (isset($lab_id) && $lab_id != 'null' && $lab_id != NULL) {
    $valid_lab_selected = TRUE;
    $lab_display_name = ace_lab_get_display_name_by_id($lab_id);
    // $_SESSION['current_lab_id'] = $lab_id;
    $lab_state = ace_lab_is_active($lab_id);
    //$body_class .= ($lab_state) ? ' active' : ' inactive';
    $lab_is_published = ace_lab_is_published($lab_id);
    $body_class .= ($lab_state) ? ' active' : '';
    if ($lab_state) {
        $lab_age_in_seconds = time() - ace_lab_get_last_activated($lab_id);
        $lab_age_display = sprintf('%02d', ($lab_age_in_seconds/3600));
        $lab_age_maximum = sprintf('%02d', (_LAB_AGE_MAXIMUM_/3600));
    }
} else {
    $valid_lab_selected = FALSE;
}

$user_id = $_SESSION['user_id'];
$user_info = ace_user_get_info($user_id);
if (($user_info['first'] . $user_info['last']) <> '') {
    $user_display_name = $user_info['first'].' '.$user_info['last'];
} else {
    $user_display_name = $user_info['name'];
}
$page_links = ace_session_get_page_links();

// $html_user_lab_dropdown_list = null;
// $html_user_lab_dropdown_list .= "<option value=null>select a lab...</option>";
$user_lab_table = ace_user_get_lab_table($_SESSION['user_id']);
if ($user_lab_table) {
    $num_user_labs = count($user_lab_table);
} else {
    $num_user_labs = 0;
}
$user_quota_array = ace_user_get_quota($user_id);
// foreach ($user_lab_table as $lab) {
// $html_user_lab_dropdown_list .= '<option value="' . $lab['id'] . '">' . $lab['display_name'] . '</option>';
//}

$html_user_lab_radios = '';
foreach ($user_lab_table as $lab) {
    $html_user_lab_radios .= '<td class="' . (($lab['state'] == 1) ? 'active' : 'inactive') . '">';
    $html_user_lab_radios .= '<input name="lab_id" value="' . $lab['id'] . '" type="radio" />' . $lab['display_name'];
    $html_user_lab_radios .= '</td>';
}

$html_select_lab_buttons = '';
foreach ($user_lab_table as $lab) {
    $active = (($lab['state'] == 1) ? ' active' : ' inactive');
    $selected = (($lab['id'] == $lab_id) ? 'selected' : '');
    $html_select_lab_buttons .= '
						<div class="row_element">
							<form name="select_lab_form" action="' . _LAB_URL_ . '" method="post">
								<input name="action" value="lab_load" type="hidden" />
								<button name="lab_id" value="' . $lab['id'] . '" class="' . $selected . '" type="submit">' . $lab['display_name'] . '</button>
								<input name="nonce" value=' . $nonce . ' type="hidden" />
							</form>
							<div class="lab_active_button_indicator' . $active . '"></div>
						</div>';
}

$user_group_table = ace_user_get_groups($_SESSION['user_id']);
$html_select_users_group_labs = '<option value="none">select lab...</option>';
foreach ($user_group_table as $group) {
    $group_active = ($group['state'] == 1) ? TRUE : FALSE;
    if ($group_active) {
        $group_lab_table = ace_group_get_lab_table($group['group_id']);
        foreach ($group_lab_table as $lab) {
            $html_select_users_group_labs .= '<option value="' . $lab['id'] . '">' . $group['name'] . ':' . $lab['display_name'] . '</option>';
        }
    }
}
$create_lab_button_disabled = ($num_user_labs >= $user_quota_array['labs']) ? 'disabled' : '';

$select_student_lab_count = 0;
$select_class_table = ace_user_get_owned_academic_groups($user_id);
$html_select_class_labs_none = '
                        <option value="none">select lab...</option>';
$html_selected_option_class = '';
$html_select_class_labs_options = '';
foreach ($select_class_table as $select_class) {
    $select_student_table = ace_group_get_members_table($select_class['id']);
    foreach ($select_student_table as $select_student) {
        $select_lab_table = ace_user_get_lab_table($select_student['user_id']);
        foreach ($select_lab_table as $select_lab) {
            $select_student_lab_count++;
            if ($select_lab['state'] == 1) {
                $html_select_option_class = 'active';
            } else {
                $html_select_option_class = 'inactive';
            }
            if ($select_lab['id'] == $lab_id) {
                $html_select_option_selected = ' selected';
                $html_selected_option_class = $html_select_option_class;
            } else {
                $html_select_option_selected = '';
            }
            $html_select_class_labs_options .= '
                        <option value="' . $select_lab['id'] . '" class="' . $html_select_option_class . '"' . $html_select_option_selected . '>' . $select_class['name'] . ':' . $select_student['user_name'] . ':' . $select_lab['display_name'] . '</option>';
        }
    }
}
$html_select_class_labs = $html_select_class_labs_none . $html_select_class_labs_options;
$load_student_lab_button_disabled = ($select_student_lab_count == 0) ? 'disabled' : '';


# END PAGE DATA

?>
<!-- HTML PAGE - headers and menu -->
<!doctype html>
<html>
<head profile="http://www.w3.org/2005/10/profile">
    <title>ACEITLab - Lab</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="css/rudi.css"/>
    <link rel="icon" type="image/png" href="icon/favicon-32x32.png"/>
</head>
<body class="<?php echo $body_class; ?>">
<!-- BEGIN PAGE -->
<div id="page" class="page">
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
                    <form action="<?php echo _USER_URL_; ?>" method="post">
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
<?php
if ($_SESSION['security_level'] == 3 ) {
    echo '
        <div class="element_column" style="height: 42px;">
            <div id="menu_bar_group_labs_title" class="row_element">
                <p>Published Labs</p>
            </div>
            <div id="load_group_lab_div" class="row_element">
                <form name="load_group_lab_form" action="' . _LAB_URL_ . '" method="post">
                    <input name="to_user_id" value="' . $_SESSION['user_id'] . '" type="hidden"/>
                    <select name="from_lab_id" title="from lab id">
                        ' . $html_select_users_group_labs . '
                    </select>
                    <button name="action" value="duplicate_group_lab" class="ace_button" type="submit" ' . $create_lab_button_disabled . '>Copy to My Labs</button>
                    <input name="nonce" value="' . $nonce . '" type="hidden" title="nonce"/>
                </form>
            </div>
        </div>';
} else {
    echo '
        <div class="element_column" style="height: 42px;">
            <div id="menu_bar_student_labs_title" class="row_element">
                <p>My Student' . "'" . 's Labs</p>
            </div>
            <div id="load_student_lab_div" class="row_element">
                <form name="load_student_lab_form" action="' . _LAB_URL_ . '" method="post">
                    <select name="student_lab_id" title="student_lab_id" onchange="this.className=this.options[this.selectedIndex].className" class="' . $html_selected_option_class . '">
                        ' . $html_select_class_labs . '
                    </select>
                    <button name="action" value="load_student_lab" class="ace_button" type="submit" ' . $load_student_lab_button_disabled . '>Load</button>
                    <input name="nonce" value="' . $nonce . '" type="hidden" title="nonce"/>
                </form>
            </div>
        </div>';
}
?>
        <div class="element_column_right">
            <div id="menu_bar_my_labs_title" class="row_element">
                <p>My Labs</p>
            </div>
            <div id="create_new_lab_button" class="row_element">
                <form name="create_lab_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <button name="action" value="lab_create" class="ace_button" type="submit" <?php echo $create_lab_button_disabled; ?>>Create</button>
                    <input name="nonce" value="<?php echo $nonce; ?>" type="hidden" title="nonce" />
                </form>
            </div>
            <?php echo $html_select_lab_buttons; ?>
            <div id="menu_bar_lab_quota" class="row_element">
                <p>(<?php echo $num_user_labs . '/' . $user_quota_array['labs']; ?>)</p>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<!-- END MENU BAR 1 -->
<!--<div class="horizontal_divider"></div>-->
<?php
if ($valid_lab_selected) {
    # LAB INFO GATHERING
    //$lab_state = ace_lab_is_active($lab_id);
    // $lab_activate_disabled = ($lab_state) ? 'disabled' : '';
    // $lab_deactivate_disabled = ($lab_state) ? '' : 'disabled';
    ?>
    <!-- BEGIN MENU BAR 2 -->
<div id="menu_bar2" class="menu_bar">
    <div class="element_table">
        <form name="menu_form2" class="bar_form" action="<?php echo _LAB_URL_; ?>" method="post">
            <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
            <div class="element_column">
                <div class="row_element">
                    <?php
                    if ($lab_state) {
                        echo '
                        <button name="action" value="lab_deactivate" type="submit">Deactivate</button>';
                    } else {
                        echo '
                        <button name="action" value="lab_activate" type="submit">Activate</button>';
                    }
                    ?>
                </div>
                <?php
                if ($lab_state) {
                echo '
                <div id="active_lab_age_title" class="row_element" style="min-height:21px;">
                    <p>Age(Hrs)  : ' . $lab_age_display . ' / ' . $lab_age_maximum . '</p>
                </div>';
                }
                ?>
            </div>
            <div class="element_column_right">
                <div class="element">
                    <?php
                    if ($lab_state) {
                        echo '
                        <button disabled>Delete</button>';
                    } else {
                        echo '
                        <button name="action" value="lab_delete" type="submit">Delete</button>';
                    }
                    ?>
                </div>
            </div>
            <div class="element_column_right">
                <?php $lab_display_name = ace_lab_get_display_name_by_id($lab_id); ?>
                <div class="element">
                    <input name="lab_display_name" value="<?php echo $lab_display_name; ?>" type="text" title="lab_display_name"/>
                    <button name="action" value="lab_rename" type="submit">Rename</button>
                </div>
            </div>
            <?php
            if ($_SESSION['security_level'] <= _MANAGER_SECURITY_LEVEL_) {
                echo '
            <div class="element_column_right" >
                <div class="element" >';
                if ($lab_is_published) {
                    echo '
                    <button name="action" value="lab_unpublish" type="submit">Un-Publish</button>';
                } else {
                    echo '
                    <button disabled>Un-Publish</button>';
                }
                echo '
                </div>
            </div>';
            }
            ?>
            <input name="nonce" value=<?php echo $nonce; ?> type="hidden" title="nonce"/>
        </form>
    </div>
    <div class="clear"></div>
</div>
    <!-- END MENU BAR 2 -->

    <?php
    $quota_array = ace_lab_get_quota_array($lab_id);
    $last_error = $_POST['last_error'];
    $iso_table = ace_get_iso_table();
    $ace_volume_table = ace_get_shared_volume_table();
    $lab_volume_table = ace_lab_get_volume_table($lab_id);
    $lab_network_table = ace_lab_get_network_table($lab_id);
    $lab_vm_table = ace_lab_get_vm_table($lab_id);
    # POPULATE FORM CONTROLS

    # VM SELECT OPTIONS
    $html_vm_cdrom_dropdown_list = NULL;
    foreach ($lab_vm_table as $vm) {
        if ($vm['user_visible'] == 1) {
            $vm_cdroms = ace_vm_get_cdrom_table($vm['id']);
            foreach ($vm_cdroms as $vm_cdrom) {
                $html_vm_cdrom_dropdown_list .= '<option value="' . $vm_cdrom['vm_id'] . '_' . $vm_cdrom['instance'] . '">' . $vm['display_name'] . ' : cd' . $vm_cdrom['instance'] . '</option>';
            }
        }
    }

//    $vm_is_running = (ace_vm_get_virt_state($vm['id']) == 1) ? TRUE : FALSE;
    $html_vm_nic_dropdown_list = NULL;
    foreach ($lab_vm_table as $vm) {
        $vm_is_running = (ace_vm_get_virt_state($vm['id']) == 1) ? TRUE : FALSE;
//		if (($vm['user_visible'] == 1) && !$vm_is_running) {
        if ($vm['user_visible'] == 1) {
            $vm_nics = ace_vm_get_nic_table($vm['id']);
            foreach ($vm_nics as $vm_nic) {
                $html_vm_nic_dropdown_list .= '<option value="' . $vm_nic['vm_id'] . '_' . $vm_nic['instance'] . '">' . $vm['display_name'] . ' : nic' . $vm_nic['instance'] . '</option>';
            }
        }
    }

    $html_vm_dropdown_list = NULL;
    foreach ($lab_vm_table as $vm) {
        $vm_is_running = (ace_vm_get_virt_state($vm['id']) == 1) ? TRUE : FALSE;
        if (($vm['user_visible'] == 1) && !$vm_is_running) {
            $html_vm_dropdown_list .= '<option value="' . $vm['id'] . '">' . $vm['display_name'] . '</option>';
        }
    }

    # ACE MEDIA RADIOS
    $html_lab_iso_radios = NULL;
    $num_isos = 0;
    foreach ($iso_table as $iso) {
        $table_part_2 .= '<tr class="' . (($iso['state'] == 1) ? 'active' : 'inactive') . '">';
        $table_part_2 .= '<td><input name="volume_id" value="' . $iso['id'] . '" type="radio" />' . $iso['display_name'] . '</td>';
        $table_part_2 .= '</tr>';
        $num_isos++;
    }
    $table_part_1 = '<table><tr><th></th></tr>';
    $table_part_3 = '</table>';
    $html_lab_iso_radios = $table_part_1 . $table_part_2 . $table_part_3;
    $table_part_1 = $table_part_2 = $table_part_3 = NULL;
    $iso = NULL;

    # ACE VOLUME RADIOS
    $html_ace_volume_radios = NULL;
    $num_ace_volumes = 0;
    foreach ($ace_volume_table as $volume) {
        if ($volume['user_visible'] == 1) {
            $table_part_2 .= '<tr class="' . (($volume['state'] == 1) ? 'active' : 'inactive') . '">';
            $table_part_2 .= '<td>';
            $table_part_2 .= '<input name="volume_id" value="' . $volume['id'] . '" type="radio" />';
            $table_part_2 .= $volume['display_name'];
            $table_part_2 .= '</td>';
            $table_part_2 .= '<td style="text-align:center;">';
            $volume_size = ($volume['unit'] == 'M') ? round($volume['size'] / 1024, 2) : $volume['size'];
            $table_part_2 .= $volume_size;
            $table_part_2 .= '</td>';
            $table_part_2 .= '<td style="text-align:center;">';
            $volume_size_used = ($volume['unit'] == 'M') ? round($volume['size_on_disk'] / 1024, 2) : $volume['size_on_disk'];
            $table_part_2 .= $volume_size_used;
            $table_part_2 .= '</td>';
            $table_part_2 .= '<td style="text-align:center;">';
            $volume_size_free = $volume_size - $volume_size_used;
            $table_part_2 .= $volume_size_free;
            $table_part_2 .= '</td>';
            $table_part_2 .= '</tr>';
            $num_ace_volumes++;
        }
    }
    $table_part_1 = '<table><tr><th></th><th>Size</th><th>Used</th><th>Free</th></tr>';
    $table_part_1 .= '<tr><th>Volume</th><th>GiB</th><th>GiB</th><th>GiB</th></tr>';
    $table_part_3 = '<tr><th></th><th></th></tr></table>';
    $html_ace_volume_radios = $table_part_1 . $table_part_2 . $table_part_3;
    $table_part_1 = $table_part_2 = $table_part_3 = NULL;
    $volume = NULL;
    $volume_size = NULL;

    # LAB VOLUME RADIOS
    $html_str = NULL;
    $html_lab_volume_radios = NULL;
    $num_user_volumes = 0;
    $sum_all_volume_sizes = 0;
    $volume_quota = $quota_array['volumes'];
    $storage_quota = $quota_array['storage'];
    foreach ($lab_volume_table as $volume) {
        if ($volume['user_visible'] == 1) {
            $table_part_2 .= '<tr class="' . (($volume['state'] == 1) ? 'active' : 'inactive') . '">';
            $table_part_2 .= '<td>';
            $table_part_2 .= '<input name="volume_id" value="' . $volume['id'] . '" type="radio" />';
            $table_part_2 .= $volume['display_name'];
            $table_part_2 .= '</td>';
            if ($volume['base_id'] !== NULL) {
                $base_volume_display_name = ace_volume_get_display_name_by_id($volume['base_id']);
                $base_volume = ace_volume_get_info($volume['base_id']);
                $base_volume_size = ($base_volume['unit'] == 'M') ? round($base_volume['size'] / 1024, 2) : $base_volume['size'];
                $base_volume_used = ($base_volume['unit'] == 'M') ? round($base_volume['size_on_disk'] / 1024, 2) : $base_volume['size_on_disk'];
                $base_volume_free = $base_volume_size - $base_volume_used;
                $lab_volume_cost = $base_volume_free;
            } else {
                $base_volume_display_name = '';
                $volume_size = ($volume['unit'] == 'M') ? round($volume['size'] / 1024, 2) : $volume['size'];
                $lab_volume_cost = $volume_size;
            }
            $table_part_2 .= '<td>' . $base_volume_display_name . '</td>';
            $table_part_2 .= '<td style="text-align:center;">' . $lab_volume_cost . '</td>';
            //$sum_all_volume_sizes += ($volume['base_id'] === NULL) ? $lab_volume_cost : 0;
            $sum_all_volume_sizes += $lab_volume_cost;
            // if ($vm_assignment_array = ace_volume_get_vm_assignments($volume['id'])) {
            // foreach ($vm_assignment_array as $element) {
            // $vol_vm_display_name = ace_vm_get_display_name_by_id($element);
            // $table_part_2 .= $vol_vm_display_name . ' ';
            // }
            // }
            $table_part_2 .= '</tr>';
            $num_user_volumes++;
        }
    }
    $table_part_1 = '<table><tr><th></th><th></th><th>Rsv.</th></tr>';
    $table_part_1 .= '<tr><th>Vol</th><th>Base</th><th>GiB</th></tr>';
    $table_part_3 = '<tr><th></th><th></th><th class="' . $volume_quota_class . '">(' . $sum_all_volume_sizes . '/' . $storage_quota . ')</th></tr>';
    $table_part_3 .= '</table>';
    $html_lab_volume_radios = $table_part_1 . $table_part_2 . $table_part_3;
    $table_part_1 = $table_part_2 = $table_part_3 = NULL;
    $volume = NULL;
    $volume_size = NULL;

    # NETWORK RADIOS
    $html_lab_network_radios = NULL;
    $num_user_networks = 0;
    $network_quota = $quota_array['networks'];
    foreach ($lab_network_table as $network) {
        if ($network['user_visible'] == 1) {
            $table_part_2 .= '<tr class="' . (($network['state'] == 1) ? 'active' : 'inactive') . '">';
            $table_part_2 .= '<td><input name="network_id" value="' . $network['id'] . '" type="radio" />' . $network['display_name'] . '</td>';
            $table_part_2 .= '</tr>';
            $num_user_networks++;
        }
    }
    $table_part_1 = '<table><tr><th></th></tr>';
    $table_part_3 = '</table>';
    $html_lab_network_radios = $table_part_1 . $table_part_2 . $table_part_3;
    $table_part_1 = $table_part_2 = $table_part_3 = NULL;
    $network_is_active = NULL;
    $network = NULL;

    # VM RADIOS
    $html_lab_vm_radios = NULL;
    $num_user_vms = 0;
    $sum_all_vm_vcpu = 0;
    $sum_all_vm_memory = 0;
    $vm_quota = $quota_array['vms'];
    $vcpu_quota = $quota_array['vcpu'];
    $memory_quota = $quota_array['memory'];
    foreach ($lab_vm_table as $vm) {
        if ($vm['user_visible'] == 1) {
            $vm_id = $vm['id'];
            $vm_state = ($vm['state'] == 1) ? TRUE : FALSE;
            $virt_vm_state = (ace_vm_get_virt_state($vm_id) == 1) ? TRUE : FALSE;
            if ($vm_state && !$virt_vm_state) {
                $db_success = ace_vm_deactivate($vm_id);
                $vm_state = FALSE;
            }
            $table_part_2 .= '<tr class="' . (($vm_state) ? 'active' : 'inactive') . '">';
            $table_part_2 .= '<td><input name="vm_id" value="' . $vm_id . '" type="radio" />' . $vm['display_name'] . '</td>';
            $vm_vcpu = $vm['vcpu'];
            $table_part_2 .= '<td style="text-align:center;">' . $vm_vcpu . '</td>';
            $table_part_2 .= '<td style="text-align:center;">';
            $vm_memory = ($vm['unit'] == 'M') ? round($vm['memory'] / 1024, 2) : $vm['memory'];
            $table_part_2 .= $vm_memory;
            $table_part_2 .= '</td>';
            $table_part_2 .= '</tr>';
            $sum_all_vm_vcpu += $vm_vcpu;
            $sum_all_vm_memory += $vm_memory;
            $num_user_vms++;
        }
    }
    $table_part_1a = '<table><tr><th></th><th>vCPU</th><th>GiB</th></tr>';
    $table_part_3 = '<tr><th></th><th>(' . $sum_all_vm_vcpu . '/' . $vcpu_quota . ')</th><th>(' . $sum_all_vm_memory . '/' . $memory_quota . ')</th></tr>';
    $table_part_3 .= '</table>';
    $html_lab_vm_radios = $table_part_1a . $table_part_1b . $table_part_2 . $table_part_3;
    $table_part_1a = $table_part_1b = $table_part_2 = $table_part_3 = NULL;
    $vm = NULL;
    $vm_memory = NULL;
    $vm_vcpu = NULL;
    ?>
<!-- BEGIN MAIN BLOCK -->
<div id="main" class="main">
    <!-- BEGIN LAB RESOURCE SECTION -->
    <div id="lab_resource" class="element_table">
        <!-- BEGIN ISO Volumes COLUMN -->
        <div class="element_column<?php echo ($lab_state) ? ' active' : ''; ?>" draggable="true">
            <div class="element">
                <table>
                    <tr>
                        <th align="center">ACE CD/DVDs</th>
                    </tr>
                </table>
            </div>
            <div class="element">
                <form name="iso_form" class="column_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $disabled = ($num_isos == 0 || $num_user_vms == 0) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <?php echo $html_lab_iso_radios; ?>
                    <table>
                        <tr>
                            <td>
                                <button name="action" value="vm_cdrom_insert_media" type="submit" <?php echo $disabled; ?>>Insert</button>
                            </td>
                            <td>
                                <select name="vm_cdrom_combo" <?php echo $disabled; ?> title="vm_cdrom_combo">
                                    <option value=null>into a drive...</option>
                                    <?php echo $html_vm_cdrom_dropdown_list; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <input name="nonce" value=<?php echo $nonce; ?> type="hidden" title="nonce"/>
                </form>
            </div>
        </div>
        <!-- END ISO Volumes COLUMN -->
        <!-- BEGIN ACE Volumes COLUMN -->
        <div class="element_column">
            <div class="element">
                <table>
                    <tr>
                        <th>ACE Volumes</th>
                    </tr>
                </table>
            </div>
            <div class="element">
                <form name="ace_volumes_form" class="column_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $ace_volume_assign_disabled = ($num_ace_volumes == 0 || $num_user_vms == 0) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <?php echo $html_ace_volume_radios; ?>
                    <table>
                        <tr>
                            <td>
                                <button name="action" value="lab_create_diff_volume" type="submit">Create LAB Volume</button>
                            </td>
                        </tr>
                    </table>
                    <input name="nonce" value=<?php echo $nonce; ?> type="hidden" title="nonce"/>
                </form>
            </div>
        </div>
        <!-- END ACE Volumes COLUMN -->
        <!-- BEGIN Lab Volumes COLUMN -->
        <div class="element_column">
            <div class="element">
                <table>
                    <tr>
                        <th>LAB Volumes</th>
                        <th>(<?php echo $num_user_volumes . '/' . $quota_array['volumes']; ?>)</th>
                    </tr>
                </table>
            </div>
            <div class="element">
                <!--<form name="lab_volumes_form" class="column_form" action="--><?php //echo _LAB_URL_; ?><!--" method="post">-->
                <!--    --><?php //$volume_assign_disabled = ($num_user_volumes == 0 || $num_user_vms == 0) ? 'disabled' : ''; ?>
                <!--    --><?php //$volume_delete_disabled = ($num_user_volumes == 0) ? 'disabled' : ''; ?>
                <!--    <input name="lab_id" value="--><?php //echo $lab_id; ?><!--" type="hidden"/>-->
                <!--    --><?php //echo $html_lab_volume_radios; ?>
                <!--    <button name="action" value="vm_attach_disk" type="submit" --><?php //echo $volume_assign_disabled; ?><!-- >Assign</button>-->
                <!--    <select name="vm_id" --><?php //echo $volume_assign_disabled; ?><!-- title="vm_id">-->
                <!--        <option value=null>to a vm...</option>-->
                <!--        --><?php //echo $html_vm_dropdown_list; ?>
                <!--    </select>-->
                <!--    <br/>-->
                <!--    <button name="action" value="lab_delete_volume" type="submit" --><?php //echo $volume_delete_disabled; ?><!-- >Delete</button>-->
                <!--    <input name="nonce" value=--><?php //echo $nonce; ?><!-- type="hidden" title="nonce"/>-->
                <!--</form>-->

                <form name="lab_volumes_form" class="column_form" action="<?php echo _LAB_URL_; ?>" method="post">
                        <?php $volume_assign_disabled = ($num_user_volumes == 0 || $num_user_vms == 0) ? 'disabled' : ''; ?>
                        <?php $volume_delete_disabled = ($num_user_volumes == 0) ? 'disabled' : ''; ?>
                        <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                        <?php echo $html_lab_volume_radios; ?>
                    <table style="width: auto">
                        <tr>
                            <td>
                                <button name="action" value="vm_attach_disk" type="submit" <?php echo $volume_assign_disabled; ?>>Assign</button>
                            </td>
                            <td>
                                <select name="vm_id" <?php echo $volume_assign_disabled; ?> title="vm_id">
                                    <option value=null>to a vm...</option>
                                    <?php echo $html_vm_dropdown_list; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="action" value="lab_delete_volume" type="submit" <?php echo $volume_delete_disabled; ?>>Delete</button>
                                <input name="nonce" value=<?php echo $nonce; ?> type="hidden" title="nonce"/>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="element">
                <form name="create_volume_form" class="column_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $volume_create_disabled = ($num_user_volumes >= $volume_quota || $sum_all_volume_sizes >= $storage_quota) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <table>
                        <tr>
                            <td>
                                <label for="size">Size:</label>
                                <input id="size" name="size" value="<?php echo (isset($_POST['size'])) ? $_POST['size'] : 1; ?>" type="text" size="4" title="size" <?php echo $volume_create_disabled; ?> />
                                <select name="unit" <?php echo $volume_create_disabled; ?> title="unit">
                                    <option value="M">MB</option>
                                    <option value="G" selected>GB</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="action" value="lab_create_volume"
                                        type="submit" <?php echo $volume_create_disabled; ?>>Create
                                </button>
                            </td>
                        </tr>
                    </table>
                    <input name="nonce" type="hidden" title="nonce" value=<?php echo $nonce; ?> />
                </form>
            </div>
        </div>
        <!-- END Lab Volumes COLUMN -->
        <!-- BEGIN Lab Networks COLUMN -->
        <div class="element_column">
            <div class="element">
                <table>
                    <tr>
                        <th class="column_title">LAB Networks</th>
                        <th class="column_quota">(<?php echo $num_user_networks . '/' . $network_quota; ?>)</th>
                    </tr>
                </table>
            </div>
            <div class="element">
                <form class="column_form" name="network_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $network_change_state_disabled = ($num_user_networks <= 1) ? 'disabled' : ''; ?>
                    <?php $network_assign_disabled = ($num_user_vms == 0) ? 'disabled' : ''; ?>
                    <?php $network_delete_disabled = ($num_user_networks <= 1) ? 'disabled' : ''; ?>
                    <?php $network_rename_disabled = ($num_user_networks <= 1) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <?php echo $html_lab_network_radios; ?>
                    <!--<button name="action" value="network_change_state"-->
                    <!--        type="submit" --><?php //echo $network_change_state_disabled; ?><!-- >Change State-->
                    <!--</button>-->
                    <!--<br/>-->
                    <!---->
                    <!--<input id="network_new_name" name="network_new_name" value="..new name.." type="text"-->
                    <!--       size="14" --><?php //echo $network_rename_disabled; ?><!--  title="network_new_name" />-->
                    <!--<button name="action" value="network_rename"-->
                    <!--        type="submit" --><?php //echo $network_rename_disabled; ?><!-- >Rename-->
                    <!--</button>-->
                    <!--<br/>-->
                    <!---->
                    <!--<button name="action" value="vm_nic_connect_network"-->
                    <!--        type="submit" --><?php //echo $network_assign_disabled; ?><!-- >Assign-->
                    <!--</button>-->
                    <!--<select name="vm_id_and_nic_instance" --><?php //echo $network_assign_disabled; ?><!-- title="vm_id_and_nic_instance">-->
                    <!--    <option value=null>to a nic...</option>-->
                    <!--    --><?php //echo $html_vm_nic_dropdown_list; ?>
                    <!--</select>-->
                    <!--<br/>-->
                    <!--<button name="action" value="lab_delete_network"-->
                    <!--        type="submit" --><?php //echo $network_delete_disabled; ?><!-- >Delete-->
                    <!--</button>-->
                    <!--<br/>-->
                    <!--<input name="nonce" type="hidden" title="nonce" value=--><?php //echo $nonce; ?><!-- />-->
                    <table style="width: auto;">
                        <tr>
                            <td>
                                <button name="action" value="network_change_state" type="submit" <?php echo $network_change_state_disabled; ?>>Change State</button>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                    <table style="width: auto;">
                        <tr>
                            <td>
                                <input id="network_new_name" name="network_new_name" value="..new name.." type="text" size="14" <?php echo $network_rename_disabled; ?>  title="network_new_name" />
                            </td>
                            <td>
                                <button name="action" value="network_rename" type="submit" <?php echo $network_rename_disabled; ?>>Rename</button>
                            </td>
                        </tr>
                    </table>
                    <table style="width: auto;">
                        <tr>
                            <td>
                                <button name="action" value="vm_nic_connect_network" type="submit" <?php echo $network_assign_disabled; ?>>Assign</button>
                            </td>
                            <td>
                                <select name="vm_id_and_nic_instance" <?php echo $network_assign_disabled; ?> title="vm_id_and_nic_instance">
                                    <option value=null>to a nic...</option>
                                    <?php echo $html_vm_nic_dropdown_list; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="action" value="lab_delete_network" type="submit" <?php echo $network_delete_disabled; ?>>Delete</button>
                            </td>
                        </tr>
                    </table>
                    <input name="nonce" type="hidden" title="nonce" value=<?php echo $nonce; ?> />
                </form>
            </div>
            <div class="element">
                <form class="column_form" name="create_network_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $network_create_disabled = ($num_user_networks >= $network_quota) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <table>
                        <tr>
                            <td>
                                <button name="action" value="lab_create_network" type="submit" <?php echo $network_create_disabled; ?>>Create</button>
                            </td>
                        </tr>
                    </table>
                    <input name="nonce" type="hidden" title="none" value=<?php echo $nonce; ?> />
                </form>
            </div>
        </div>
        <!-- END Lab Networks COLUMN -->
        <!-- BEGIN Lab VMs COLUMN -->
        <div class="element_column">
            <div class="element">
                <table>
                    <tr>
                        <th class="column_title">LAB VMs</th>
                        <th class="column_quota">(<?php echo $num_user_vms . '/' . $quota_array['vms']; ?>)</th>
                    </tr>
                </table>
            </div>
            <div class="element">
                <form class="column_form" name="vm_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $vm_rename_disabled = ($num_user_vms == 0) ? 'disabled' : ''; ?>
                    <?php $vm_delete_disabled = ($num_user_vms == 0) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <?php echo $html_lab_vm_radios; ?>
                    <!--<input id="vm_new_name" name="vm_new_name" value="..new name.." type="text"-->
                    <!--       size="14" --><?php //echo $vm_rename_disabled; ?><!-- title="vm_new_name" />-->
                    <!--<button name="action" value="vm_rename" type="submit" --><?php //echo $vm_rename_disabled; ?><!-- >-->
                    <!--    Rename-->
                    <!--</button>-->
                    <!--<br/>-->
                    <!--<button name="action" value="lab_delete_vm" type="submit" --><?php //echo $vm_delete_disabled; ?><!-- >-->
                    <!--    Delete-->
                    <!--</button>-->
                    <!--<br/>-->
                    <!--<input name="nonce" type="hidden" title="nonce" value=--><?php //echo $nonce; ?><!-- />-->
                    <table style="width: auto;">
                        <tr>
                            <td>
                                <input id="vm_new_name" name="vm_new_name" value="..new name.." type="text" size="14" <?php echo $vm_rename_disabled; ?> title="vm_new_name" />
                            </td>
                            <td>
                                <button name="action" value="vm_rename" type="submit" <?php echo $vm_rename_disabled; ?>>Rename</button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="action" value="lab_delete_vm" type="submit" <?php echo $vm_delete_disabled; ?>>Delete</button>
                            </td>
                        </tr>
                    </table>
                    <input name="nonce" type="hidden" title="nonce" value=<?php echo $nonce; ?> />
                </form>
            </div>
            <div class="element">
                <form class="column_form" name="create_vm_form" action="<?php echo _LAB_URL_; ?>" method="post">
                    <?php $vm_create_disabled = ($num_user_vms >= $vm_quota || $sum_all_vm_vcpu >= $vcpu_quota || $sum_all_vm_memory >= $memory_quota) ? 'disabled' : ''; ?>
                    <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                    <table width="100%">
                        <tr>
                            <td>vCPU:</td>
                            <td>
                                <input name="vm_vcpu" value="1" type="text" size="4" <?php echo $vm_create_disabled; ?> title="vm_vcpu" />
                            </td>
<!--                                <td></td>-->
                        </tr>
                        <tr>
                            <td>Memory:</td>
                            <td>
                                <input name="vm_memory" value="1" type="text" size="4" <?php echo $vm_create_disabled; ?> title="vm_memory" />
<!--                                </td>-->
<!--                                <td align="left">-->
                                <select name="vm_unit" <?php echo $vm_create_disabled; ?> title="vm_unit">
                                    <option value="M">MiB</option>
                                    <option value="G" selected>GiB</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>HW Profile:</td>
                            <td>
                                <select name="vm_profile" <?php echo $vm_create_disabled; ?> title="vm_profile">
                                    <option value="linux">Linux</option>
                                    <option value="w7">Windows 7 / Server 2008</option>
                                    <option value="w8">Windows 8 / Server 2012</option>
                                    <option value="w10">Windows 10 / Server 2016</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="action" value="lab_create_vm" type="submit" <?php echo $vm_create_disabled; ?>>Create</button>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                    <input name="nonce" value="<?php echo $nonce; ?>" type="hidden"/>
                </form>
            </div>
        </div>
        <!-- END Lab VMs COLUMN -->
    </div>
    <!-- END LAB RESOURCE SECTION -->
    <div class="clear"></div>


    <!-- BEGIN VM HEADER ROW SECTION -->
    <!--		<div id="vm_thumbnails" class="element_table"> -->
    <?php
    // $debug = true;
    //	$lab_state = ace_lab_is_active($lab_id);
    //	$lab_vm_table = ace_lab_get_vm_table($lab_id);
    //	foreach ($lab_vm_table as $vm) {
    //if ($vm['user_visible'] == 1) {
    //$vm_id = $vm['id'];
    //$vm_state = ($vm['state'] == 1) ? true : false;
    //$virt_vm_state = (ace_vm_get_virt_state($vm_id) == 1) ? true : false;

    //$html_screenshot_input = '<input src="" type="image" />';

    // $screenshot_filename = null;
    // $html_screenshot_input = null;
    // if ($lab_state && $vm_state) {
    // $console_class = ' active';
    // $screenshot_filename = ace_vm_screenshot($vm_id,180);
    // $html_screenshot_input = '<input src="' . $screenshot_filename . '" type="image" />';
    // } else {
    // $console_class = ' inactive';
    // }

    //$vm_console_info = null;
    //if ($lab_state && $vm_state) {
    //$vm_console_info = ace_vm_get_console_info($vm_id);
    //$vm_console_ip = $vm_console_info['ip'];
    //$vm_console_port = $vm_console_info['port'];
    //$vm_console_url = ace_vm_get_console_url($vm_id);
    //} else {
    //$vm_console_ip = 'none';
    //$vm_console_port = 'none';
    //$vm_console_url = '';
    //}
    ?>
    <!--			<div class='element_column vm'>
                        <div class="element">
                            <!-- BEGIN VM TITLE -->
    <!--					<table>
                        <th><?php //echo $vm['display_name']; ?></th>
                    </table>
                    <!-- END VM TITLE -->
    <!--				</div>
                        <div class="element" style="padding:0px;">
                            <!-- BEGIN VM SCREENSHOT -->
    <!--					<div class="vm_thumbnail_container<?php //echo $console_class; ?>">
                        <div class="vm_thumbnail">
                            <form id="vm_console_connect_form" action="<?php //echo _CONSOLE_URL_; ?>" method="post">
                                <input name="lab_id" value="<?php //echo $lab_id; ?>" type="hidden" />
                                <input name="vm_id" value="<?php //echo $vm_id; ?>" type="hidden" />
                                <?php //echo $html_screenshot_input; ?>
                            </form>
                        </div>
                    </div> -->
    <!-- END VM SCREENSHOT -->
    <!--				</div>
                    </div>-->
    <?php
    //	}
    //	}
    ?>
    <!--			<div class="clear"></div>
                </div> -->
    <!-- END VM HEADER ROW SECTION-->


    <!-- BEGIN VM DETAIL SECTION -->
    <!-- any number of vertical divs here depending on number of vms -->
    <div id="lab_vm" class="element_table">
        <?php
        $lab_state = ace_lab_is_active($lab_id);
        $lab_vm_table = ace_lab_get_vm_table($lab_id);
        foreach ($lab_vm_table as $vm) {
            if ($vm['user_visible'] == 1) {
                $vm_id = $vm['id'];
                $vm_active = ($vm['state'] == 1) ? TRUE : FALSE;
                if ($lab_state) {
                    $virt_vm_active = ace_vm_get_virt_state($vm_id);
                    if (!$virt_vm_active && $vm_active) {
                        $db_success = ace_vm_deactivate($vm_id);
                        $vm['state'] = 0;
                        $vm_active = FALSE;
                    }
                    if ($virt_vm_active && !$vm_active) {
                        $db_success = ace_vm_activate($vm_id);
                        $vm['state'] = 1;
                        $vm_active = TRUE;
                    }
                }
                $vm_change_state_disabled = ($lab_state) ? '' : 'disabled';
                $vm_shutdown_button_disabled = ($vm_active) ? '' : 'disabled';
                $vm_console_disabled = ($lab_state) ? '' : 'disabled';
                $num_vm_cdroms = ($vm_cdroms = ace_vm_get_cdrom_table($vm_id)) ? count($vm_cdroms) : 0;
                $vm_cdrom_attach_disabled = ($vm_active) ? 'disabled' : '';
                $vm_cdrom_detach_disabled = (($num_vm_cdroms == 0) || $vm_active) ? 'disabled' : '';
                $vm_cdrom_eject_disabled = ($num_vm_cdroms == 0) ? 'disabled' : '';
                $num_vm_disks = ($vm_disks = ace_vm_get_disk_table($vm_id)) ? count($vm_disks) : 0;
                $vm_disk_attach_disabled = ($vm_active) ? 'disabled' : '';
                $vm_disk_detach_disabled = (($num_vm_disks == 0) || $vm_active) ? 'disabled' : '';
                $num_vm_nics = ($vm_nics = ace_vm_get_nic_table($vm_id)) ? count($vm_nics) : 0;
                $vm_nic_attach_disabled = ($vm_active) ? 'disabled' : '';
                $vm_nic_detach_disabled = (($num_vm_nics == 0) || $vm_active) ? 'disabled' : '';
                $vm_nic_disconnect_disabled = ($num_vm_nics == 0) ? 'disabled' : '';
                $vm_state = ($vm['state'] == 1) ? TRUE : FALSE;

                $vm_snapshots = ace_vm_get_snapshot_list($vm_id);
                $num_vm_snapshots = ($vm_snapshots) ? count($vm_snapshots) : 0;
                $vm_snapshot_button_disabled = ((!$lab_state) || ($virt_vm_active)) ? 'disabled' : '';
                $vm_snapshot_revert_button_disabled = (($num_vm_snapshots == 0) || ($virt_vm_active) || (!$lab_state)) ? 'disabled' : '';
                $vm_snapshot_delete_button_disabled = (($num_vm_snapshots == 0) || ($virt_vm_active) || (!$lab_state)) ? 'disabled' : '';

                $html_vm_cdrom_radios = NULL;
                foreach ($vm_cdroms as $vm_cdrom) {
                    $html_vm_cdrom_radios .= '<input name="vm_cdrom_instance" value="' . $vm_cdrom['instance'] . '" type="radio" />';
                    $html_vm_cdrom_radios .= 'cd' . $vm_cdrom['instance'];
                    $volume_display_name = ace_volume_get_display_name_by_id($vm_cdrom['volume_id']);
                    $html_vm_cdrom_radios .= ' : ' . $volume_display_name . '<br/>';
                }

                $html_vm_disk_radios = NULL;
                foreach ($vm_disks as $vm_disk) {
                    $html_vm_disk_radios .= '<input name="vm_disk_instance" value="' . $vm_disk['instance'] . '" type="radio" />';
                    //$html_vm_disk_radios .= 'disk' . $vm_disk['instance'];
                    $html_vm_disk_radios .= $vm_disk['instance'];
                    $volume_display_name = ace_volume_get_display_name_by_id($vm_disk['volume_id']);
                    $html_vm_disk_radios .= ' : ' . $volume_display_name . '<br/>';
                }

                $html_vm_nic_radios = NULL;
                /*			foreach ($vm_nics as $vm_nic) {
                                $html_vm_nic_radios .= '<input name="vm_nic_instance" value="'.$vm_nic['instance'].'" type="radio" />';
                                $html_vm_nic_radios .= 'nic'.$vm_nic['instance'];
                //				$vm_nic_mac = ace_vm_nic_get_mac_address($vm['id'],$vm_nic['instance']);
                                $vm_nic_mac = ace_gen_convert_int2mac($vm_nic['mac_index']);
                                $html_vm_nic_radios .= ': (' . $vm_nic_mac . ')';
                                $vm_nic_link_state = '?';
                                $html_vm_nic_radios .= $vm_nic_link_state;
                                $network_display_name = ace_network_get_display_name_by_id($vm_nic['network_id']);
                                $html_vm_nic_radios .= ' = '.$network_display_name.'<br/>';
                            }*/

                if ($num_vm_nics > 0) {
                    //$table = '<table class="element_table">
                    //            <tr>
                    //                <th></th>
                    //                <th>#</th>
                    //                <th>Mac</th>
                    //                <th>Net</th>
                    //            </tr>';
                    $table = '<table class="element_table">';
                    foreach ($vm_nics as $vm_nic) {
                        $html_link_class = '';
                        if ($lab_state) {
                            $vm_nic_link_state = ace_vm_nic_get_link_state($vm_id, $vm_nic['instance']);
                            $html_link_class = ($vm_nic_link_state) ? 'active' : 'inactive';
                        }
                        $table .= '<tr class="' . $html_link_class . '">';
                        $table .= '<td><input name="vm_nic_instance" value="' . $vm_nic['instance'] . '" type="radio" /></td>';
                        //$table .= '<td>nic' . $vm_nic['instance'] . '</td>';
                        $table .= '<td align="center">' . $vm_nic['instance'] . '</td>';
                        $table .= '<td align="center" style="font-size: 60%">' . ace_gen_convert_int2mac($vm_nic['mac_index']) . '</td>';
                        $table .= '<td align="center">' . ace_network_get_display_name_by_id($vm_nic['network_id']) . '</td>';
                        $table .= '</tr>';
                    }
                    $table .= '</table>';
                    $html_vm_nic_radios = $table;
                }


                $html_vm_snapshot_radios = NULL;
                for ($vm_snapshot_instance = 0; $vm_snapshot_instance < $num_vm_snapshots; $vm_snapshot_instance++) {
                    $html_vm_snapshot_radios .= '<input name="vm_snapshot_instance" value="' . $vm_snapshot_instance . '" type="radio" />';
                    $tz_set = date_default_timezone_set('America/New_York');
                    $html_vm_snapshot_radios .= date("Y-m-d H:i", $vm_snapshots[ $vm_snapshot_instance ]) . '<br/>';
                }

                switch ($vm['profile']) {
                    case 'linux':
                        $vm_profile = 'Linux';
                        break;
                    case 'w7':
                        $vm_profile = 'Windows 7';
                        break;
                    case 'w8':
                        $vm_profile = 'Windows 8';
                        break;
                    case 'w10':
                        $vm_profile = 'Windows 10';
                        break;
                    default:
                        $vm_profile = 'Undefined';
                }
                ?>
                <!-- BEGIN VM CONFIG COLUMN -->
                <div class='element_column vm'>
                    <!-- BEGIN VM CONNECTION INFO -->
                    <div class="element">
                        <div class="vm_active_indicator<?php echo(($virt_vm_active) ? ' active' : ' inactive'); ?>">
                            <!-- BEGIN VM TITLE -->
                            <table>
                                <tr>
                                    <th><?php echo $vm['display_name']; ?></th>
                                </tr>
                            </table>
                            <!-- END VM TITLE -->
                        </div>
                    </div>
                    <div class="element">
                        <!--<div style="float:left">-->
                        <!--    <form name="vm_control_form" action="--><?php //echo _LAB_URL_; ?><!--" method="post">-->
                        <!--        <input name="lab_id" value="--><?php //echo $lab_id; ?><!--" type="hidden"/>-->
                        <!--        <input name="vm_id" value="--><?php //echo $vm_id; ?><!--" type="hidden"/>-->
                        <!--        <button name="action" value="--><?php //echo(($vm_state) ? 'vm_stop' : 'vm_start'); ?><!--"-->
                        <!--                type="submit" --><?php //echo $vm_change_state_disabled; ?><!-- > --><?php //echo(($vm_state) ? 'Off' : 'On'); ?><!--</button>-->
                        <!--        <input name="nonce" value="--><?php //echo $nonce; ?><!--" type="hidden"/>-->
                        <!--    </form>-->
                        <!--</div>-->
                        <!--<div style="float:left">-->
                        <!--    <form id="vm_console_connect_form" action="--><?php //echo _CONSOLE_URL_; ?><!--" method="post">-->
                        <!--        <input name="lab_id" value="--><?php //echo $lab_id; ?><!--" type="hidden"/>-->
                        <!--        <input name="vm_id" value="--><?php //echo $vm_id; ?><!--" type="hidden"/>-->
                        <!--        <button name="action" value="" type="submit" --><?php //echo $vm_console_disabled; ?> <!-- > -->
                        <!--            Console-->
                        <!--        </button>-->
                        <!--    </form>-->
                        <!--</div>-->
                        <!--<div class="clear"></div>-->
                        <table style="width:auto">
                            <tr>
                                <td>
                                    <form name="vm_control_form" action="<?php echo _LAB_URL_; ?>" method="post">
                                        <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                                        <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                                        <button name="action" value="<?php echo(($vm_state) ? 'vm_stop' : 'vm_start'); ?>" type="submit" <?php echo $vm_change_state_disabled; ?>><?php echo(($vm_state) ? 'Off' : 'On'); ?></button>
                                        <input name="nonce" value="<?php echo $nonce; ?>" type="hidden"/>
                                    </form>
                                </td>
                                <td>
                                    <form id="vm_console_connect_form" action="<?php echo _CONSOLE_URL_; ?>" method="post">
                                        <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                                        <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                                        <button name="action" value="" type="submit" <?php echo $vm_console_disabled; ?>>Console</button>
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- END VM CONNECTION INFO -->
                    <!-- BEGIN VM CONFIGURATION -->
                    <div class="element">
                        <table width="100%">
                            <tr>
                                <td>vCPUs:</td>
                                <td><?php echo $vm['vcpu']; ?></td>
                            </tr>
                            <tr>
                                <td>Memory:</td>
                                <td><?php echo $vm['memory'] . ' ' . (($vm['unit'] == 'M') ? 'MiB' : 'GiB'); ?></td>
                            </tr>
                            <tr>
                                <td>Arch:</td>
                                <td><?php echo $vm['arch']; ?></td>
                            </tr>
                            <tr>
                                <td>HW Profile:</td>
                                <td><?php echo $vm_profile; ?></td>
                            </tr>
                        </table>
                    </div>
                    <!-- END VM CONFIGURATION -->
                    <!-- BEGIN VM SNAPSHOT INFO -->
                    <div class="element">
                        <form class="column_form" name="vm_snapshot_form" action="<?php echo _LAB_URL_; ?>" method="post">
                            <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                            <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                            <table style="width: auto;">
                                <tr>
                                    <th>
                                        <p class="label">SNAPSHOTS (<?php echo $num_vm_snapshots; ?>)</p>
                                    </th>
                                </tr>
                            </table>
                            <?php echo $html_vm_snapshot_radios; ?>
                            <table style="width: auto;">
                                <tr>
                                    <td>
                                        <button name="action" value="vm_create_a_snapshot" type="submit" <?php echo $vm_snapshot_button_disabled; ?>>+</button>
                                    </td>
                                    <td>
                                        <button name="action" value="vm_delete_a_snapshot" type="submit" <?php echo $vm_snapshot_delete_button_disabled; ?>>-</button>
                                    </td>
                                    <td>
                                        <button name="action" value="vm_revert_to_snapshot" type="submit" <?php echo $vm_snapshot_revert_button_disabled; ?>>Revert</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                    <!-- END VM SNAPSHOT INFO -->
                    <!-- BEGIN VM ISOs -->
                    <div class="element">
                        <table style="width: auto;">
                            <tr>
                                <th>
                                    <p class="label">CD/DVDs</p>
                                </th>
                            </tr>
                        </table>
                        <form name="vm_iso_form" action="<?php echo _LAB_URL_; ?>" method="post">
                            <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                            <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                            <?php echo $html_vm_cdrom_radios; ?>
                            <table style="width: auto;">
                                <tr>
                                    <td>
                                        <button name="action" value="vm_attach_cdrom" type="submit" <?php echo $vm_cdrom_attach_disabled; ?>>+</button>
                                    </td>
                                    <td>
                                        <button name="action" value="vm_detach_cdrom" type="submit" <?php echo $vm_cdrom_detach_disabled; ?>>-</button>
                                    </td>
                                    <td>
                                        <button name="action" value="vm_cdrom_eject_media" type="submit" <?php echo $vm_cdrom_eject_disabled; ?>>Eject</button>
                                    </td>
                                </tr>
                            </table>
                            <input name="nonce" value="<?php echo $nonce; ?>" type="hidden"/>
                        </form>
                    </div>
                    <!-- END VM ISOs -->
                    <!-- BEGIN VM Disks -->
                    <div class="element">
                        <table style="width: auto;">
                            <tr>
                                <th>
                                    <p class="label">DISKs</p>
                                </th>
                            </tr>
                        </table>
                        <form name="vm_disk_form" action="<?php echo _LAB_URL_; ?>" method="post">
                            <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                            <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                            <?php echo $html_vm_disk_radios; ?>
                            <table style="width: auto;">
                                <tr>
                                    <td>
                                        <button name="action" value="vm_detach_disk" type="submit" <?php echo $vm_disk_detach_disabled; ?>>-</button>
                                    </td>
                                </tr>
                            </table>
                            <input name="nonce" value="<?php echo $nonce; ?>" type="hidden"/>
                        </form>
                    </div>
                    <!-- END VM Disks -->
                    <!-- BEGIN VM Nics -->
                    <div class="element">
                        <table style="width: auto;">
                            <tr>
                                <th>
                                    <p class="label">NICs</p>
                                </th>
                            </tr>
                        </table>
                        <form name="vm_nic_form" action="<?php echo _LAB_URL_; ?>" method="post">
                            <input name="lab_id" value="<?php echo $lab_id; ?>" type="hidden"/>
                            <input name="vm_id" value="<?php echo $vm_id; ?>" type="hidden"/>
                            <?php echo $html_vm_nic_radios; ?>
                            <table style="width: auto;">
                                <tr>
                                    <td>
                                        <button name="action" value="vm_attach_nic" type="submit" <?php echo $vm_nic_attach_disabled; ?>>+</button>
                                    </td>
                                    <td>
                                        <button name="action" value="vm_detach_nic" type="submit" <?php echo $vm_nic_detach_disabled; ?>>-</button>
                                    </td>
                                    <td>
                                        <button name="action" value="vm_nic_disconnect" type="submit" <?php echo $vm_nic_disconnect_disabled; ?>>Disconnect</button>
                                    </td>
                                </tr>
                            </table>
                            <input name="nonce" value="<?php echo $nonce; ?>" type="hidden"/>
                        </form>
                    </div>
                    <!-- END VM Nics -->
                </div>
                <!-- END VM CONFIG COLUMN -->
            <?php
            }
        }
        ?>
    </div>
    <!-- END VM DETAIL SECTION -->
    <div class="clear"></div>

</div>
<!-- END MAIN BLOCK -->

<div class="clear"></div>
    <?php
}
?>
<!-- BEGIN STATUS SECTION -->
<div id="status_section" class="section">
    <!--<div class="horizontal_divider"></div>-->
    <div class="message_bar"><?php echo (isset($messages)) ? ace_out_messages($messages) : ''; ?></div>
</div>
<!-- END STATUS SECTION -->

<?php
if ($lab_is_published) {
    echo '
<div id="div_published_stamp">
    <p id="stamp_text">Published</p>

</div>';
}
?>
</div>
<!-- END PAGE -->
<script src="javascript/jquery-1.11.3.min.js"></script>
<script>
    $(document).ready(function (){
//        $('#network_new_name').focusin(function () {
//            this.value = '';
//        });
//        $('#vm_new_name').focusin(function () {
//            this.value = '';
//        })
    })
</script>
</body>
</html>