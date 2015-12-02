<?php
/**
 * ACEITLab Hypervisor Abstractor Function (HAF)
 *
 * abstracts ace_ system virt function calls, redirecting them to hypervisor-specific libraries
 * takes calls from Application API
 * makes calls to ace_kvm_, ace_xen_, ace_hyperv_, et al
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

require_once('fns_libvirt.php');
// require_once('fns_xen.php');
// require_once('fns_hyperv.php');
// etc.

$host_info = array();
$host_conn = NULL;

/**
 * Hypervisor Abstractor
 *
 * abstracts virt function calls so they are executed using the relevant library for the host
 *
 * @global  resource $host_conn a libvirt host connection resource
 * @global  array    $host_info an array of host information
 *
 * param string $command virt command
 * param mixed $params command-associated parameters
 *
 * @return mixed
 */
function ace_virt()
{
    $args = func_get_args();
    global $host_conn, $host_info;
    $result = FALSE;
    $command = $args[0];
    $host_id = $args[1];
    if ($host_info['id'] !== $host_id) {
        $host_info = ace_db_host_get_info($host_id);
    }
    switch ($host_info['hypervisor']) {
        case 'kvm' :
            if (!ace_kvm_host_is_connected($host_info['name'], $host_info['domain'])) {
                $host_conn = ace_kvm_connect($host_info['name'], $host_info['domain'], $host_info['username'], $host_info['password']);
            }
            if (is_resource($host_conn)) {
                switch ($command) {
                    // case 'host_hostname' :
                    //  $result = ace_kvm_host_hostname(); break;
                    // case 'host_conn_info' :
                    //  $result = ace_kvm_host_conn_info(); break;
                    case 'test_connection' :
                        $result = TRUE;
                        break;
                    case 'get_log_file_tail' :
                        $num_lines = $args[2];
                        $result = ace_kvm_get_log_file_tail($num_lines);
                        break;
                    case 'get_hypervisor_info' :
                        $result = ace_kvm_get_hypervisor_info();
                        break;
                    case 'get_physical_info' :
                        $result = ace_kvm_get_physical_info();
                        break;

                    case 'get_network_list' :
                        $result = ace_kvm_get_network_list();
                        break;
                    case 'network_exists' :
                        $network_virt_id = $args[2];
                        $result = ace_kvm_network_exists($network_virt_id);
                        break;
                    case 'get_network_state' :
                        $network_virt_id = $args[2];
                        $result = ace_kvm_get_network_state($network_virt_id);
                        break;
                    case 'set_network_state' :
                        $network_virt_id = $args[2];
                        $active = $args[3];
                        if ($active) {
                            $result = ace_kvm_network_activate_virsh($host_info['name'], $network_virt_id);
                        } else {
                            $result = ace_kvm_network_deactivate_virsh($host_info['name'], $network_virt_id);
                        }
                        break;
                    case 'create_network' :
                        $network_virt_id = $args[2];
                        $result = ace_kvm_network_create($host_info['name'], $network_virt_id);
                        break;
                    // case 'update_network' :				$network_virt_id = $args[2]; $result = ace_kvm_network_update($network_virt_id); break;
                    case 'activate_network' :
                        $network_virt_id = $args[2];
                        $result = ace_kvm_network_activate_virsh($host_info['name'], $network_virt_id);
                        break;
                    case 'deactivate_network' :
                        $network_virt_id = $args[2];
                        $result = ace_kvm_network_deactivate_virsh($host_info['name'], $network_virt_id);
                        break;
                    case 'delete_network' :
                        $network_virt_id = $args[2];
                        $result = ace_kvm_network_delete($network_virt_id);
                        break;

                    case 'get_storage_info' :
                        $result = ace_kvm_get_storage_info();
                        break;
                    case 'get_volume_list' :
                        $result = ace_kvm_get_volume_list();
                        break;
                    case 'get_media_list' :
                        $result = ace_kvm_get_media_list();
                        break;
                    case 'create_volume' :
                        $virt_id = $args[2];
                        $size = $args[3];
                        $unit = $args[4];
                        $base_virt_id = $args[5];
                        $result = ace_kvm_volume_create($virt_id, $size, $unit, $base_virt_id);
                        break;
                    // case 'update_volume' :				$volume_virt_id = $args[2]; $result = ace_kvm_volume_update($volume_virt_id); break;
                    case 'delete_volume' :
                        $volume_virt_id = $args[2];
                        $result = ace_kvm_volume_delete($volume_virt_id);
                        break;

                    case 'get_vm_list' :
                        $result = ace_kvm_get_vm_list();
                        break;
                    case 'get_vm_state' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_get_vm_state($vm_virt_id);
                        break;
                    case 'get_vm_nics' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_get_nic_array($vm_virt_id);
                        break;
                    case 'get_vm_disks' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_get_disk_array($vm_virt_id);
                        break;

                    // case 'get_vm_config' :				$result = ace_kvm_get_vm_config($params); break;
                    case 'create_vm' :
                        $virt_id = $args[2];
                        $vcpu = $args[3];
                        $memory = $args[4];
                        $unit = $args[5];
                        $arch = $args[6];
                        $profile = $args[7];
                        $result = ace_kvm_vm_create($virt_id, $vcpu, $memory, $unit, $arch, $profile);
                        break;
                    case 'get_vm_console_info' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_get_console_info($vm_virt_id);
                        break;

                    // case 'vm_modify' :					$result = ace_kvm_vm_modify($params); break;
                    case 'delete_vm' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_delete($vm_virt_id);
                        break;
                    case 'activate_vm' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_start($vm_virt_id);
                        break;
                    case 'deactivate_vm' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_stop($vm_virt_id);
                        break;

                    case 'shutdown_vm' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_shutdown($vm_virt_id);
                        break;
                    case 'soft_reset_vm' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_soft_reset($vm_virt_id, $host_info['name']);
                        break;
                    // case 'get_vm_disk_table' :			$result = ace_kvm_get_vm_disk_table($params); break;
                    // case 'vm_disk_add' :				$result = ace_kvm_vm_disk_add($params); break;
                    // case 'vm_disk_remove' :				$result = ace_kvm_vm_disk_remove($params); break;

                    // case 'get_vm_nic_table' :			$result = ace_kvm_get_vm_nic_table($params); break;
                    // case 'vm_nic_add' :					$result = ace_kvm_vm_nic_add($params); break;
                    // case 'vm_nic_remove' :				$result = ace_kvm_vm_nic_remove($params); break;

                    // case 'vm_get_media_table' :			$result = ace_kvm_get_vm_media_table($params); break;
                    case 'vm_attach_cdrom' :
                        $vm_virt_id = $args[2];
                        $vm_cdrom_instance = $args[3];
                        $result = ace_kvm_vm_attach_cdrom($vm_virt_id, $vm_cdrom_instance);
                        break;
                    case 'vm_detach_cdrom' :
                        $vm_virt_id = $args[2];
                        $vm_cdrom_instance = $args[3];
                        $result = ace_kvm_vm_detach_cdrom($vm_virt_id, $vm_cdrom_instance);
                        break;
                    case 'vm_cdrom_insert_media' :
                        $vm_virt_id = $args[2];
                        $vm_cdrom_instance = $args[3];
                        $volume_virt_id = $args[4];
                        $result = ace_kvm_vm_cdrom_insert_media($vm_virt_id, $vm_cdrom_instance, $volume_virt_id);
                        break;
                    case 'vm_cdrom_eject_media' :
                        $vm_virt_id = $args[2];
                        $vm_cdrom_instance = $args[3];
                        //$volume_virt_id = $args[4];
                        $result = ace_kvm_vm_cdrom_eject_media($vm_virt_id, $vm_cdrom_instance);
                        break;
                    case 'vm_attach_disk' :
                        $vm_virt_id = $args[2];
                        $vm_disk_instance = $args[3];
                        $volume_virt_id = $args[4];
                        $result = ace_kvm_vm_attach_disk($vm_virt_id, $vm_disk_instance, $volume_virt_id);
                        break;
                    case 'vm_detach_disk' :
                        $vm_virt_id = $args[2];
                        $vm_disk_instance = $args[3];
                        $result = ace_kvm_vm_detach_disk($vm_virt_id, $vm_disk_instance);
                        break;
                    case 'vm_attach_nic' :
                        $vm_virt_id = $args[2];
                        //$vm_nic_instance = $args[3];
                        $vm_nic_mac_address = $args[4];
                        $result = ace_kvm_vm_attach_nic_virsh($host_info['name'], $vm_virt_id, $vm_nic_mac_address);
//                    $result = ace_kvm_vm_attach_nic($vm_virt_id,$vm_nic_mac_address);
                        break;
                    case 'vm_detach_nic' :
                        $vm_virt_id = $args[2];
                        //$vm_nic_instance = $args[3];
                        $vm_nic_mac_address = $args[4];
//                    $result = ace_kvm_vm_detach_nic_virsh($host_info['name'],$vm_virt_id, $vm_nic_mac_address);
                        $result = ace_kvm_vm_detach_nic($vm_virt_id, $vm_nic_mac_address);
                        break;
                    case 'vm_nic_connect_network' :
                        $vm_virt_id = $args[2];
                        $vm_nic_instance = $args[3];
                        $vm_nic_mac_address = $args[4];
                        $network_virt_id = $args[5];
                        $result = ace_kvm_vm_nic_connect_network($host_info['name'], $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, $network_virt_id);
                        break;
                    case 'vm_nic_disconnect' :
                        $vm_virt_id = $args[2];
                        $vm_nic_instance = $args[3];
                        $vm_nic_mac_address = $args[4];
//                    $network_virt_id = $args[5];
//                    $result = ace_kvm_vm_nic_disconnect($vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, $network_virt_id);
//                    $result = ace_kvm_vm_nic_disconnect_patch($host_info['name'],$vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, $network_virt_id);
                        $result = ace_kvm_vm_nic_connect_network($host_info['name'], $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, _DISCONNECTED_VIRT_NETWORK_ID_);
                        break;
                    case 'vm_nic_get_link_state' :
                        $vm_virt_id = $args[2];
                        $vm_nic_mac_address = $args[3];
                        $result = ace_kvm_vm_nic_get_link_state($vm_virt_id, $vm_nic_mac_address);
                        break;
                    case 'vm_nic_link_up' :
                        $vm_virt_id = $args[2];
                        $vm_nic_mac_address = $args[3];
                        $result = ace_kvm_vm_nic_link_up($vm_virt_id, $vm_nic_mac_address);
                        break;
                    case 'vm_nic_link_down' :
                        $vm_virt_id = $args[2];
                        $vm_nic_mac_address = $args[3];
                        $result = ace_kvm_vm_nic_link_down($vm_virt_id, $vm_nic_mac_address);
                        break;
                    case 'vm_screenshot' :
                        $vm_virt_id = $args[2];
                        $max_width = $args[3];
                        $result = ace_kvm_vm_screenshot($vm_virt_id, $host_info['name'], $max_width);
                        break;
                    case 'vm_get_snapshot_list' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_get_snapshot_list($vm_virt_id);
                        break;
                    case 'vm_create_snapshot' :
                        $vm_virt_id = $args[2];
                        $result = ace_kvm_vm_create_snapshot($vm_virt_id);
                        break;
                    case 'vm_snapshot_revert' :
                        $vm_virt_id = $args[2];
                        $vm_snapshot_instance = $args[3];
                        $result = ace_kvm_vm_snapshot_revert($vm_virt_id, $vm_snapshot_instance);
                        break;
                    case 'vm_snapshot_delete' :
                        $vm_virt_id = $args[2];
                        $vm_snapshot_instance = $args[3];
                        $result = ace_kvm_vm_snapshot_delete($vm_virt_id, $vm_snapshot_instance);
                        break;
                }
            } else {
                return FALSE;
            }
            break;
        case 'xen' :
            switch ($command) {
                case 'vm_create' :
                    //$result = ace_xen_vm_create($params);
                    break; #e.g.
            }
            break;
        case 'hyperv' :
            switch ($command) {
                case 'vm_create' :
                    //$result = ace_hyperv_vm_create($params);
                    break; #e.g.
            }
            break;
    }
    return $result;
}
