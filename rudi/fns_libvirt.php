<?php
/**
 * ACEITLab KVM Functions
 *
 * performs remote virtualization tasks against KVM hosts
 * takes calls from Hypervisor Abstractor Function (HAF)
 * makes calls to the php-libvirt API
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

/**
 * defines path to libvirt's local log file on the web server
 */
define('_LIBVIRT_LOG_FILE_', './libvirt.log');
/**
 * defines path to aceitlab's log file for remote messages from libvirt on virt hosts
 */
define('_ACE_LIBVIRT_LOG_FILE_', './libvirt_error.log');
/**
 * defines name given to libvirt storage pool for disk volumes
 */
define('_IMAGE_POOL_', '0-poo-images');
/**
 * defines name given to libvirt storage pool for CD/DVD volumes
 */
define('_MEDIA_POOL_', '0-poo-media');
/**
 * defines name given to the inactive linux bridge on virt hosts for "disconnected" NICs workaround
 */
define('_DISCONNECTED_VIRT_NETWORK_ID_', '0-net-none');

//unlink(_LIBVIRT_LOG_FILE_);
//if (!libvirt_logfile_set(_LIBVIRT_LOG_FILE_)) die('Cannot set the log file');

#=============================================================
#HOST
#=============================================================
/**
 * write last libvirt error to log file
 */
function ace_kvm_log_last_error()
{
    $str = libvirt_get_last_error();
    if ($str !== NULL) {
        $str = date("[Y/m/d] H:i:s : ") . $str;
        file_put_contents(_ACE_LIBVIRT_LOG_FILE_, $str . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * connect to a virt host
 *
 * bit more info on a second line
 *
 * @param   string $host_name     name of virt host
 * @param   string $host_domain   domain of virt host
 * @param   string $host_username username used to connect to virt host
 * @param   string $host_password password of username account
 *
 * @return  resource|bool       libvirt host connection resource or FALSE if connection attempt fails
 *
 */
function ace_kvm_connect($host_name, $host_domain, $host_username, $host_password)
{
//    $host_fqdn = $host_name . '.' . $host_domain;
    $host_cred = Array($host_username, $host_password);
//    $host_uri = 'qemu+ssh://root@'.$host_fqdn.'/system';
    $host_uri = 'qemu+ssh://root@' . $host_name . '/system';
    $connect = libvirt_connect($host_uri, FALSE, $host_cred);
    ace_kvm_log_last_error();
    return $connect;
}

/**
 * determine if virt host is already connected
 *
 * @global  resource $host_conn   libvirt host connection resource
 *
 * @param   string   $host_name   name of virt host
 * @param   string   $host_domain domain of virt host
 *
 * @return  bool                host is connected?
 */
function ace_kvm_host_is_connected($host_name, $host_domain)
{
    global $host_conn;
    if (is_resource($host_conn)) {
        $connected_host_name = libvirt_connect_get_hostname($host_conn);
        ace_kvm_log_last_error();
        $fqdn = $host_name . '.' . $host_domain;
        if ($connected_host_name == $fqdn) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

/**
 * fetch the last n lines from log file
 *
 * @param   int $num_lines number of lines to fetch from log file
 *
 * @return  string              last n lines of the log file
 */
function ace_kvm_get_log_file_tail($num_lines)
{
    exec('tail -' . $num_lines . ' ' . _LIBVIRT_LOG_FILE_, $output, $err);
    return ($err == 0) ? implode('<br/>', $output) : '';
}

/**
 * fetch live OS information about the remote virt host
 *
 * @global  resource $host_conn libvirt connection
 *
 * @return  string|bool         hypervisor info or FALSE on error
 */
function ace_kvm_get_hypervisor_info()
{
    global $host_conn;
    $hypervisor_info = libvirt_connect_get_hypervisor($host_conn);
    ace_kvm_log_last_error();
    return $hypervisor_info;
}

/**
 * fetch live physical information about the remote virt host
 *
 * @global  resource @host_conn              libvirt connection
 *
 * @return  string|bool         physical host info or FALSE on error
 */
function ace_kvm_get_physical_info()
{
    global $host_conn;
    $physical_info = libvirt_node_get_info($host_conn);
    ace_kvm_log_last_error();
    return $physical_info;
}

function ace_kvm_get_storagepool_list() {
    global $host_conn;
    $storagepools_array = libvirt_list_active_storagepools($host_conn);
    ace_kvm_log_last_error();
    return $storagepools_array;
}

function ace_kvm_get_storage_info(){
    global $host_conn;
    $storage_info = array();
    //$storagepools_array = ace_kvm_get_storagepool_list();
    //foreach ($storagepools_array as $storagepool_name) {
        $storagepool_res = libvirt_storagepool_lookup_by_name($host_conn, _IMAGE_POOL_);
        ace_kvm_log_last_error();
        $storage_info = libvirt_storagepool_get_info($storagepool_res);
        ace_kvm_log_last_error();
    //}
    return $storage_info;
}

/**
 * fetch a list of network_virt_id from the virt host
 *
 * @global  resource $host_conn libvirt connection
 *
 * @return  array|bool          network_virt_id list or FALSE on error
 */
function ace_kvm_get_network_list()
{
    global $host_conn;
    $network_list = libvirt_list_networks($host_conn);
    ace_kvm_log_last_error();
    return $network_list;
}

/**
 * fetch a list of volume_virt_id from the virt host in _IMAGE_POOL_
 *
 * @global  resource $host_conn libvirt connection
 *
 * @return  array|bool          volume_virt_id list or FALSE on error
 */
function ace_kvm_get_volume_list()
{
    global $host_conn;
    $volume_pool_name = _IMAGE_POOL_;
    $volume_pool_res = libvirt_storagepool_lookup_by_name($host_conn, $volume_pool_name);
    ace_kvm_log_last_error();
    libvirt_storagepool_refresh($volume_pool_res);
    ace_kvm_log_last_error();
    $volume_list = libvirt_storagepool_list_volumes($volume_pool_res);
    ace_kvm_log_last_error();
    return $volume_list;
}

/**
 * fetch a list of volume_virt_id from the virt host in _MEDIA_POOL_
 *
 * @global  resource $host_conn libvirt connection
 *
 * @return  array|bool          volume_virt_id list or FALSE on error
 */
function ace_kvm_get_media_list()
{
    global $host_conn;
    $volume_pool_name = _MEDIA_POOL_;
    $volume_pool_res = libvirt_storagepool_lookup_by_name($host_conn, $volume_pool_name);
    ace_kvm_log_last_error();
    libvirt_storagepool_refresh($volume_pool_res);
    ace_kvm_log_last_error();
    $volume_list = libvirt_storagepool_list_volumes($volume_pool_res);
    ace_kvm_log_last_error();
    return $volume_list;
}

/**
 * fetch a list of vm_virt_id from the virt host
 *
 * @global  resource $host_conn libvirt connection
 *
 * @return  array|bool          vm_virt_id list or FALSE on error
 */
function ace_kvm_get_vm_list()
{
    global $host_conn;
    $vm_list = libvirt_list_domains($host_conn);
    ace_kvm_log_last_error();
    return $vm_list;
}

#=============================================================
#NETWORKS
#=============================================================
/**
 * determine if virtual network exists
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $network_virt_id virt_id of network
 *
 * @return bool                 network exists?
 */
function ace_kvm_network_exists($network_virt_id)
{
    echo ace_gen_debug_function_IN(__FUNCTION__, func_get_args());
    global $host_conn;
    $network_res = libvirt_network_get($host_conn, $network_virt_id);
    ace_kvm_log_last_error();
    $return = (is_resource($network_res)) ? TRUE : FALSE;
    echo ace_gen_debug_function_OUT(__FUNCTION__, $return);
    return $return;
}

/**
 * determine active state of virtual network
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $network_virt_id virt_id of network
 *
 * @return  int|bool            0 = inactive, 1 = active, FALSE on error
 */
function ace_kvm_get_network_state($network_virt_id)
{
    global $host_conn;
    $network_res = libvirt_network_get($host_conn, $network_virt_id);
    ace_kvm_log_last_error();
    if ($network_res) {
        $result = libvirt_network_get_active($network_res);
        ace_kvm_log_last_error();
        switch ($result) {
            case 1 :
                $return = 1;
                break;        // active
            case 0 :
                $return = 0;
                break;        // inactive
            default :
                $return = FALSE;
                break;    // error
        }
    } else {
        $return = FALSE;    // error (not found)
    }
    return $return;
}

/**
 * set the state of a virtual network
 *
 * @todo fix this since it doesn't work yet.
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $network_virt_id virt_id of network
 * @param   bool     $active          TRUE = active, FALSE = inactive
 *
 * @return bool                 on success
 */
function ace_kvm_set_network_state($network_virt_id, $active)
{
    /*	echo ace_gen_debug_function_IN(__FUNCTION__,func_get_args());
        global $host_conn;
        //echo 'activate?'.d($active);
        $network_res = libvirt_network_get($host_conn, $network_virt_id);
        ace_kvm_log_error();
        echo '$network_res'.d($network_res);
        $state = ace_kvm_get_network_state($network_virt_id);
        //echo 'current network state'.d($state);
        if ($state == 0) {			// is inactive
            if ($active) {			// want active
                # FAIL - this libvirt function is broken
                #  see - https://bugs.php.net/bug.php?id=68484
                # it has to do with Zend parameter parsing expecting a long instead of an int
                #  and the way PHP presents/converts int to longs.
                # an older version (using int) also had a problem of expecting 2 parameters,
                #  a network resource and a state flag, but never matched the documentation.
                # it was last patched in Nov-14, accepts only the resource (so toggles state),
                #  but still doesn't work as expected.
                #
                #$result = libvirt_network_set_active($network_res);
                ace_kvm_log_error();
                #
                # so until it gets resolved, we have to resort to a CLI call...
                $result = ace_kvm_network_activate_virsh( $network_virt_id );			// **VIRSH
                $return = $result;
                //echo 'activation result: '.d($return);
            } else {				// want inactive
                $return = false;	// state not changed, already inactive
            }
        } elseif ($state == 1) {	// is active
            if ($active) {			// want active
                $return = false;	// state not changed, already active
            } else {				// want inactive
                # the desired function below commented out for the same reasons given above...
                #
                #$result = libvirt_network_set_active($network_res);
                ace_kvm_log_error();
                #
                $result = ace_kvm_network_deactivate( $network_virt_id );			// **VIRSH
                $return = $result;
            }
        } else {
            $return = false;		// finding state resulted in error
        }
        echo ace_gen_debug_function_OUT(__FUNCTION__,$return);
        return $return;*/
}

/**
 * create and activate a virtual network
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $host_name       name of virt host (only so we can pass to VIRSH)
 * @param   string   $network_virt_id virt_id of network
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_kvm_network_create($host_name, $network_virt_id)
{
    global $host_conn;
    $xml = '<network><name>' . $network_virt_id . '</name><bridge name="' . $network_virt_id . '" /></network>';
    if ($network_res = libvirt_network_define_xml($host_conn, $xml)) {
        ace_kvm_log_last_error();
        //$success = ace_kvm_set_network_state($network_virt_id, true);
        $success = ace_kvm_network_activate_virsh($host_name, $network_virt_id);
        $return = $success;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * delete a virtual network
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $network_virt_id virt_id of network
 *
 * @return bool                 TRUE on success, FALSE on error
 */
function ace_kvm_network_delete($network_virt_id)
{
    global $host_conn;
    $network_res = libvirt_network_get($host_conn, $network_virt_id);
    ace_kvm_log_last_error();
    $success = libvirt_network_undefine($network_res);
    ace_kvm_log_last_error();
    return $success;
}

/**
 * activate a virtual network
 *
 * @todo use libvirt API function instead of VIRSH
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $host_name       name of virt host (only so we can pass to VIRSH)
 * @param   string   $network_virt_id virt_id of network
 *
 * @return bool                 TRUE on success, FALSE on error
 */
function ace_kvm_network_activate_virsh($host_name, $network_virt_id)
{                        // **VIRSH
    #global $host_conn;
    $cmd = 'virsh -c "qemu+ssh://root@' . $host_name . '/system" net-start ' . $network_virt_id;
    exec($cmd, $output, $err);
    $cmd = 'virsh -c "qemu+ssh://root@' . $host_name . '/system" net-autostart ' . $network_virt_id;
    exec($cmd, $output, $err);
    return ($err == 0) ? TRUE : FALSE;
}

/**
 * deactivate a virtual network
 *
 * @todo use libvirt API function instead of VIRSH
 *
 * @global  resource $host_conn       libvirt connection
 *
 * @param   string   $host_name       name of virt host (only so we can pass to VIRSH)
 * @param   string   $network_virt_id virt_id of network
 *
 * @return bool                 TRUE on success, FALSE on error
 */
function ace_kvm_network_deactivate_virsh($host_name, $network_virt_id)
{                        // **VIRSH
    #global $host_conn;
    $cmd = 'virsh -c "qemu+ssh://root@' . $host_name . '/system" net-autostart ' . $network_virt_id . ' --disable';
    exec($cmd, $output, $err);
    $cmd = 'virsh -c "qemu+ssh://root@' . $host_name . '/system" net-destroy ' . $network_virt_id;
    exec($cmd, $output, $err);
    return ($err == 0) ? TRUE : FALSE;
}

#=============================================================
#STORAGE POOLS
#=============================================================
/**
 * determine if a storage pool exists on a virt host
 *
 * @global  resource $host_conn    libvirt connection
 *
 * @param   string   $pool_virt_id virt_id of storage pool
 *
 * @return bool                 storage pool exists?
 */
function ace_kvm_pool_exists($pool_virt_id)
{
    global $host_conn;
    $pool_res = libvirt_storagepool_lookup_by_name($host_conn, $pool_virt_id);
    ace_kvm_log_last_error();
    return (is_resource($pool_res)) ? TRUE : FALSE;
}

#=============================================================
#VOLUMES
#=============================================================
/**
 * determine if a volume exists in a storage pool on a virt host
 *
 * @global  resource $host_conn      libvirt connection
 *
 * @param   string   $pool_virt_id   virt_id of storage pool
 * @param   string   $volume_virt_id virt_id of volume
 *
 * @return bool                 volume exists in pool?
 */
function ace_kvm_volume_exists($pool_virt_id, $volume_virt_id)
{
    global $host_conn;
    $pool_res = libvirt_storagepool_lookup_by_name($host_conn, $pool_virt_id);
    if (is_resource($pool_res)) {
        $volume_res = libvirt_storagevolume_lookup_by_name($pool_res, $volume_virt_id);
        return (is_resource($volume_res)) ? TRUE : FALSE;
    } else {
        return FALSE;
    }
}

/**
 * create a volume in _IMAGE_POOL_ on the virt host
 *
 * @global  resource $host_conn           libvirt connection
 *
 * @param   string   $volume_virt_id      virt_id to be used for the volume
 * @param   int      $size                size of volume measured in units
 * @param   string   $unit                "MB" | "GB"
 * @param   string   $volume_base_virt_id virt_id of base volume
 *
 * @return  bool                TRUE on success | FALSE on error
 */
function ace_kvm_volume_create($volume_virt_id, $size, $unit, $volume_base_virt_id)
{
    global $host_conn;
    if ($volume_base_virt_id) {
        $xml = '
<volume>
	<name></name>
	<allocation>0</allocation>
	<capacity unit=""></capacity>
	<target>
		<format type="qcow2"/>
		<permissions>
			<mode>0744</mode>
		</permissions>
	</target>
	<backingStore>
		<path></path>
		<format type="qcow2"/>
		<permissions>
			<mode>0744</mode>
			<label>virt_image_t</label>
		</permissions>
	</backingStore>
</volume>';
    } else {
        $xml = '
<volume>
	<name></name>
	<allocation>0</allocation>
	<capacity unit=""></capacity>
	<target>
		<format type="qcow2"/>
		<permissions>
			<mode>0744</mode>
		</permissions>
	</target>
</volume>';
    }
    $volumeXML = new SimpleXMLElement($xml);
    $volumeXML->name = $volume_virt_id;
    $volumeXML->capacity['unit'] = $unit;
    $volumeXML->capacity = $size;
    if ($volume_base_virt_id) {
        $volumeXML->backingStore->path = '/storage/vol02/images/' . $volume_base_virt_id;
    }
    $xml = $volumeXML->asXML();
    $volume_pool_res = libvirt_storagepool_lookup_by_name($host_conn, _IMAGE_POOL_);
    ace_kvm_log_last_error();
    $return = $volume_res = libvirt_storagevolume_create_xml($volume_pool_res, $xml) ? TRUE : FALSE;
    ace_kvm_log_last_error();
    return $return;
}

/**
 * delete a volume in _IMAGE_POOL_ on the virt host
 *
 * @global  resource $host_conn      libvirt connection
 *
 * @param   string   $volume_virt_id virt_id of the volume
 *
 * @return  bool                TRUE on success | FALSE on error
 */
function ace_kvm_volume_delete($volume_virt_id)
{
    global $host_conn;
    $volume_pool_name = _IMAGE_POOL_;
    $volume_pool_res = libvirt_storagepool_lookup_by_name($host_conn, $volume_pool_name);
    ace_kvm_log_last_error();
    $volume_res = libvirt_storagevolume_lookup_by_name($volume_pool_res, $volume_virt_id);
    ace_kvm_log_last_error();
    $success = libvirt_storagevolume_delete($volume_res);
    ace_kvm_log_last_error();
    return $success;
}

#=============================================================
#VM
#=============================================================
/**
 * create a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 * @param   int      $vcpu       number of vCPUs to assign
 * @param   int      $memory     amount of RAM to assign measured in units
 * @param   string   $unit       "KiB" | "MiB"
 * @param   string   $arch       cpu arch (e.g. "x86_64")
 *
 * @return  bool                TRUE on success | FALSE on error
 */
function ace_kvm_vm_create($vm_virt_id, $vcpu, $memory, $unit, $arch, $profile)
{
    global $host_conn;
    $xml_path = './xml/';
    switch ($profile) {
        case 'w10':
            $xml_file = 'w10_domain.xml';
            break;
        default:
            $xml_file = 'guest_domain.xml';
    };
//    $vmXML = simplexml_load_file($xml_path . 'guest_domain.xml');
    $vmXML = simplexml_load_file($xml_path . $xml_file);
    $vmXML->name = $vm_virt_id;
    $vmXML->vcpu = $vcpu;
    $vmXML->memory = $memory;
    $vmXML->memory['unit'] = $unit;
    $vmXML->os->type['arch'] = $arch;
    $xml = $vmXML->asXML();
    $return = (is_resource(libvirt_domain_define_xml($host_conn, $xml))) ? TRUE : FALSE;
    ace_kvm_log_last_error();
    return $return;
}

/**
 * delete a virtual machine from the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  bool                TRUE on success | FALSE on error
 */
function ace_kvm_vm_delete($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    ace_kvm_vm_delete_snapshots($vm_virt_id);
    ace_kvm_log_last_error();
    $return = libvirt_domain_undefine($vm_res);
    ace_kvm_log_last_error();
    return $return;
}

/**
 * determine active state of a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  int|bool            0 = inactive | 1 = active | FALSE on error
 */
function ace_kvm_get_vm_state($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    if ($vm_res) {
        $result = libvirt_domain_is_active($vm_res);
        ace_kvm_log_last_error();
        switch ($result) {
            case 1 :
                $return = 1;
                break;        // active
            case 0 :
                $return = 0;
                break;        // inactive
            default :
                $return = FALSE;
                break; // unknown
        }
    } else {
        $return = FALSE;    // error (not found)
    }
    return $return;
}

/**
 * start a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  bool                on success
 */
function ace_kvm_vm_start($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $result = libvirt_domain_create($vm_res);
    ace_kvm_log_last_error();
    return $result;
}

/**
 * match a virtual machine's NIC link states to assign networks
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 */
function ace_kvm_vm_match_nic_link_states_to_networks($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $xml = libvirt_domain_get_xml_desc($vm_res, NULL);
    ace_kvm_log_last_error();
    $vmXML = new SimpleXMLElement($xml);
    $arr_nics = $vmXML->xpath("//interface[@type='network']");
    if (is_array($arr_nics)) {
        foreach ($arr_nics as $nic) {
            if (ace_kvm_get_network_state((string)$nic->source->attributes()->network) == 0) {
                ace_kvm_vm_nic_link_down($vm_virt_id, (string)$nic->mac->attributes()->address);
            }
        }
    }
}

/**
 * stop a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  bool                 on success
 */
function ace_kvm_vm_stop($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $result = libvirt_domain_destroy($vm_res);
    ace_kvm_log_last_error();
    ace_kvm_vm_match_nic_link_states_to_networks($vm_virt_id);
    return $result;
}

/**
 * suspend a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  bool                 on success
 */
function ace_kvm_vm_suspend($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $return = libvirt_domain_suspend($vm_res);
    ace_kvm_log_last_error();
    return $return;
}

/**
 * resume a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  bool                 on success
 */
function ace_kvm_vm_resume($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $return = libvirt_domain_resume($vm_res);
    ace_kvm_log_last_error();
    return $return;
}

/**
 * send ACPI shutdown command to a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  bool                 on success
 */
function ace_kvm_vm_shutdown($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $result = libvirt_domain_shutdown($vm_res);
    ace_kvm_log_last_error();
    return $result;
}

/**
 * soft reset a virtual machine on the virt host
 *
 * @todo use libvirt API instead of VIRSH
 *
 * @global  resource $host_conn      libvirt connection
 *
 * @param   string   $vm_virt_id     virt_id of the vm
 * @param   string   $virt_host_name name of the virt host, for VIRSH
 *
 * @return  bool                on success
 */
function ace_kvm_vm_soft_reset($vm_virt_id, $virt_host_name)
{              // **VIRSH**
    //global $host_conn;
    //$vm_res = libvirt_domain_lookup_by_name($host_conn,$vm_virt_id);
    $keyseq_ctl_alt_del = '29 56 83 184 157';
    $cmd = 'virsh -c "qemu+ssh://root@' . $virt_host_name . '/system" send-key ' . $vm_virt_id . ' ' . $keyseq_ctl_alt_del;
    exec($cmd);
    return TRUE;
//    $virt_success = libvirt_domain_send_keys($vm_res, $virt_host_name, 29);
//    $virt_success = libvirt_domain_send_keys($vm_res, $virt_host_name, 56);
//    $virt_success = libvirt_domain_send_keys($vm_res, $virt_host_name, 83);
//    $virt_success = libvirt_domain_send_keys($vm_res, $virt_host_name, 184);
//    $virt_success = libvirt_domain_send_keys($vm_res, $virt_host_name, 157);
}

/**
 * fetch XML config of a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  string|bool         XML | FALSE on error
 */
function ace_kvm_get_vm_config($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $xml = libvirt_domain_get_xml_desc($vm_res, NULL);
    ace_kvm_log_last_error();
    return $xml;
}

/**
 * fetch console connection information for a virtual machine on the virt host
 *
 * @param   string $vm_virt_id virt_id of the vm
 *
 * @return array                vm vnc console info
 */
function ace_kvm_vm_get_console_info($vm_virt_id)
{
    $xml = ace_kvm_get_vm_config($vm_virt_id);
    $vmXML = new SimpleXMLElement($xml);
    $vnc_ip = (string)$vmXML->devices->graphics->listen['address'];
    $vnc_port = (string)$vmXML->devices->graphics['port'];
    $vnc_array = array('ip' => $vnc_ip, 'port' => $vnc_port);
    return $vnc_array;
}

/* function ace_kvm_vm_modify($params) {
	global $host_conn;
	$vm_name = $params['vm_name'];
	$vm_memory = $params['vm_memory'];
	$vm_memory_unit = $params['vm_memory_unit'];
	$vm_vcpu = $params['vm_vcpu'];
	$vm_arch = $params['vm_arch'];

	#fetch XML
	$p['vm_name'] = $vm_name;
	$xml = ace_kvm_vm_get_config($p);
	$p = null;

	#show XML
	echo 'original<br/>';
	show_xml($xml);

	#set changes in xml
	$vmXML = new SimpleXMLElement($xml);
	$vmXML->memory = $vm_memory;
	$vmXML->memory['unit'] = $vm_memory_unit;
	$vmXML->currentMemory = $vmXML->memory;
	$vmXML->currentMemory['unit'] = $vmXML->memory['unit'];
	$vmXML->vcpu = $vm_vcpu;
	$vmXML->os->type['arch'] = $vm_arch;

	#show XML
	echo 'altered and pending commit<br/>';
	show_xml($vmXML->asXML());

	#delete the existing vm
	$vm_res = libvirt_domain_lookup_by_name($host_conn,$vm_name);
	ace_kvm_log_error();
    $result = libvirt_domain_destroy($vm_res);
	$result = libvirt_domain_undefine($vm_res);
	ace_kvm_log_error();
    #and recreate from xml
	$vm_res = libvirt_domain_define_xml($host_conn,$vmXML->asXML());
    ace_kvm_log_error();

	return $vm_res;
} */

#=============================================================
#VM DEVICES
#=============================================================
/**
 * attach a cdrom device to a virtual machine on the virt host
 *
 * @global  resource $host_conn         libvirt connection
 *
 * @param   string   $vm_virt_id        virt_id of the vm
 * @param   int      $vm_cdrom_instance instance of device (used to determine device name)
 *
 * @return  bool                on success
 */
function ace_kvm_vm_attach_cdrom($vm_virt_id, $vm_cdrom_instance)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $valid_cdrom_devs = array('', 'hdc', 'hdd', 'hde', 'hdf');
    $virt_target_dev = $valid_cdrom_devs[ $vm_cdrom_instance ];
    $domain_xml = ace_kvm_get_vm_config($vm_virt_id);
    $sxe_domain = new SimpleXMLElement($domain_xml);
    $arr_of_sxe_devices_fragments = $sxe_domain->xpath('/domain/devices');
    $sxe_devices = $arr_of_sxe_devices_fragments[0];
    $sxe_disk = $sxe_devices->addChild('disk');
    $sxe_disk->addAttribute('type', 'file');
    $sxe_disk->addAttribute('device', 'cdrom');
    $sxe_disk_driver = $sxe_disk->addChild('driver');
    $sxe_disk_driver->addAttribute('name', 'qemu');
    $sxe_disk_driver->addAttribute('type', 'raw');
    $sxe_disk_target = $sxe_disk->addChild('target');
    $sxe_disk_target->addAttribute('dev', $virt_target_dev);
    $sxe_disk_target->addAttribute('bus', 'ide');
    $sxe_disk->addChild('readonly');
    $sxe_disk->addChild('shareable');
    $xml = $sxe_domain->asXML();
    libvirt_domain_undefine($vm_res);
    ace_kvm_log_last_error();
    $success = (is_resource(libvirt_domain_define_xml($host_conn, $xml))) ? TRUE : FALSE;
    ace_kvm_log_last_error();
    return $success;
}

/**
 * detach a cdrom device from a virtual machine on the virt host
 *
 * @global  resource $host_conn         libvirt connection
 *
 * @param   string   $vm_virt_id        virt_id of the vm
 * @param   int      $vm_cdrom_instance instance of device (used to determine device name)
 *
 * @return  bool                on success
 */
function ace_kvm_vm_detach_cdrom($vm_virt_id, $vm_cdrom_instance)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $valid_cdrom_devs = array('', 'hdc', 'hdd', 'hde', 'hdf');
    $virt_target_dev = $valid_cdrom_devs[ $vm_cdrom_instance ];
    $new_vm_res = libvirt_domain_disk_remove($vm_res, $virt_target_dev);
    ace_kvm_log_last_error();
    return (is_resource($new_vm_res)) ? TRUE : FALSE;
}

function ace_kvm_vm_get_cdrom_array($vm_virt_id)
{
    global $host_conn;
    $cdrom_array = array();
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $xml = libvirt_domain_get_xml_desc($vm_res, NULL);
    ace_kvm_log_last_error();
    $vmXML = new SimpleXMLElement($xml);
    $arr_cdroms = $vmXML->xpath("//disk[@device='cdrom']");
    $c = count($arr_cdroms);
    for ($n = 0; $n < $c; $n++) {
        $cdrom_array[ $n ]['dev'] = (string)$arr_cdroms[ $n ]->target->attributes()->dev;
        $cdrom_array[ $n ]['iso'] = (string)$arr_cdroms[ $n ]->source->attributes()->file;
    }
}

/**
 * associate a volume in _MEDIA_POOL_ with a cdrom device in a virtual machine on the virt host
 *
 * @global  resource $host_conn         libvirt connection
 *
 * @param   string   $vm_virt_id        virt_id of the vm
 * @param   int      $vm_cdrom_instance instance of device (used to determine device name)
 * @param   string   $volume_virt_id    virt id of the volume
 *
 * @return  bool                on success
 */
function ace_kvm_vm_cdrom_insert_media($vm_virt_id, $vm_cdrom_instance, $volume_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $valid_cdrom_devs = array('', 'hdc', 'hdd', 'hde', 'hdf');
    $virt_target_dev = $valid_cdrom_devs[ $vm_cdrom_instance ];
    $xml = "<disk type='file' device='cdrom'>
				<driver name='qemu' type='raw'/>
				<source file='/storage/vol01/media/$volume_virt_id'/>
				<target dev='$virt_target_dev' bus='ide'/>
				<readonly/>
			</disk>";
    $result = libvirt_domain_update_device($vm_res, $xml, VIR_DOMAIN_DEVICE_MODIFY_CONFIG);
    ace_kvm_log_last_error();
    $vm_virt_is_active = (ace_kvm_get_vm_state($vm_virt_id) === 1) ? TRUE : FALSE;
    if ($vm_virt_is_active) {
        $result = libvirt_domain_update_device($vm_res, $xml, VIR_DOMAIN_DEVICE_MODIFY_LIVE);
        ace_kvm_log_last_error();
    }
    //return (is_resource($result)) ? TRUE : FALSE;
    return $result;
}

/**
 * un-associate a volume in _MEDIA_POOL_ from a cdrom device in a virtual machine on the virt host
 *
 * @global  resource $host_conn         libvirt connection
 *
 * @param   string   $vm_virt_id        virt_id of the vm
 * @param   int      $vm_cdrom_instance instance of device (used to determine device name)
 *
 * @return  bool                on success
 */
function ace_kvm_vm_cdrom_eject_media($vm_virt_id, $vm_cdrom_instance)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $valid_cdrom_devs = array('', 'hdc', 'hdd', 'hde', 'hdf');
    $virt_target_dev = $valid_cdrom_devs[ $vm_cdrom_instance ];
    $xml = "<disk type='file' device='cdrom'>
				<driver name='qemu' type='raw'/>
				<source/>
				<target dev='$virt_target_dev' bus='ide'/>
				<readonly/>
			</disk>";
    $result = libvirt_domain_update_device($vm_res, $xml, VIR_DOMAIN_DEVICE_MODIFY_CONFIG);
    ace_kvm_log_last_error();
    $vm_virt_active = (ace_kvm_get_vm_state($vm_virt_id) === 1) ? TRUE : FALSE;
    if ($vm_virt_active) {
        $result = libvirt_domain_update_device($vm_res, $xml, VIR_DOMAIN_DEVICE_MODIFY_LIVE);
        ace_kvm_log_last_error();
    }
    return $result;
}

/**
 * attach a disk device with associated volume, to a virtual machine on the virt host
 *
 * @global  resource $host_conn        libvirt connection
 *
 * @param   string   $vm_virt_id       virt_id of the vm
 * @param   int      $vm_disk_instance instance of device (used to determine device name)
 * @param   string   $volume_virt_id   virt_id of volume
 *
 * @return  bool                on success
 */
function ace_kvm_vm_attach_disk($vm_virt_id, $vm_disk_instance, $volume_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $valid_disk_devs = array('', 'vda', 'vdb', 'vdc', 'vdd', 'vde', 'vdf', 'vdg', 'vdh');
    $virt_target_dev = $valid_disk_devs[ $vm_disk_instance ];
    $domain_xml = ace_kvm_get_vm_config($vm_virt_id);
    $sxe_domain = new SimpleXMLElement($domain_xml);
    $arr_of_sxe_devices_fragments = $sxe_domain->xpath('/domain/devices');
    $sxe_devices = $arr_of_sxe_devices_fragments[0];
    $sxe_disk = $sxe_devices->addChild('disk');
    $sxe_disk->addAttribute('type', 'volume');
    $sxe_disk->addAttribute('device', 'disk');
    $sxe_disk_driver = $sxe_disk->addChild('driver');
    $sxe_disk_driver->addAttribute('name', 'qemu');
    $sxe_disk_driver->addAttribute('type', 'qcow2');
    $sxe_disk_driver->addAttribute('cache', 'none');
    $sxe_disk_source = $sxe_disk->addChild('source');
    $sxe_disk_source->addAttribute('pool', _IMAGE_POOL_);
    $sxe_disk_source->addAttribute('volume', $volume_virt_id);
    $sxe_disk_target = $sxe_disk->addChild('target');
    $sxe_disk_target->addAttribute('dev', $virt_target_dev);
    $sxe_disk_target->addAttribute('bus', 'virtio');
    $xml = $sxe_domain->asXML();
    $success = libvirt_domain_undefine($vm_res);
    ace_kvm_log_last_error();
    $vm_res = libvirt_domain_define_xml($host_conn, $xml);
    ace_kvm_log_last_error();
    return (is_resource($vm_res)) ? TRUE : FALSE;
}

/**
 * fetch array of disk information from a virtual machine on the virt host
 *
 * @global  resource    $host_conn      libvirt_connection
 *
 * @param   string      $vm_virt_id     virt_id of the vm
 *
 * @return  array               vm disk info
 */
function ace_kvm_vm_get_disk_array($vm_virt_id)
{
    global $host_conn;
    $disk_array = array();
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $xml = libvirt_domain_get_xml_desc($vm_res, NULL);
    ace_kvm_log_last_error();
    $vmXML = new SimpleXMLElement($xml);
    $arr_disks = $vmXML->xpath("//disk[@type='volume']");
    $c = count($arr_disks);
    for ($n = 0; $n < $c; $n++) {
        $disk_array[ $n ]['dev'] = (string)$arr_disks[ $n ]->target->attributes()->dev;
        $disk_array[ $n ]['volume'] = (string)$arr_disks[ $n ]->source->attributes()->volume;
    }
    return $disk_array;
}

/**
 * detach a disk device from a virtual machine on the virt host
 *
 * @global  resource $host_conn        libvirt connection
 *
 * @param   string   $vm_virt_id       virt_id of the vm
 * @param   int      $vm_disk_instance instance of device (used to determine device name)
 *
 * @return  bool                on success
 */
function ace_kvm_vm_detach_disk($vm_virt_id, $vm_disk_instance)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $valid_disk_devs = array('', 'vda', 'vdb', 'vdc', 'vdd', 'vde', 'vdf', 'vdg', 'vdh');
    $virt_target_dev = $valid_disk_devs[ $vm_disk_instance ];
    $new_vm_res = libvirt_domain_disk_remove($vm_res, $virt_target_dev);
    ace_kvm_log_last_error();
    return (is_resource($new_vm_res)) ? TRUE : FALSE;
}

/**
 * attach NIC device to a virtual machine on the virt host
 *
 * @todo use libvirt API instead of VIRSH
 *
 * @param   string $host_name          virt_id host to pass to virsh
 * @param   string $vm_virt_id         virt_id of the vm
 * @param   string $vm_nic_mac_address mac address of NIC
 *
 * @return  bool                on success
 */
function ace_kvm_vm_attach_nic_virsh($host_name, $vm_virt_id, $vm_nic_mac_address)
{            # VIRSH - use to connect network to a nic
    $con = 'virsh -c "qemu+ssh://root@' . $host_name . '/system" ';
    $cmd = "attach-interface --domain $vm_virt_id --type network --source " . _DISCONNECTED_VIRT_NETWORK_ID_ . " --mac $vm_nic_mac_address --config";
    exec($con . $cmd, $output, $err);
    $return = ($err === 0) ? TRUE : FALSE;
    # initially set the link down since we know we just connected this nic to the "disconnected" network
    ace_kvm_vm_nic_link_down($vm_virt_id, $vm_nic_mac_address);
    return $return;
}

/**
 * attach NIC device to a virtual machine on the virt host
 *
 * @todo FIX THIS, so we can use it instead of VIRSH
 *
 * @global  resource $host_conn          libvirt connection
 *
 * @param   string   $vm_virt_id         virt_id of the vm
 * @param   string   $vm_nic_mac_address mac address of NIC
 *
 * @return  bool                on success
 */
function ace_kvm_vm_attach_nic($vm_virt_id, $vm_nic_mac_address)
{
    # PCIe address slot number clash? see the working virsh version above
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $flags = (libvirt_domain_is_active($vm_res)) ? 3 : 2;
    ace_kvm_log_last_error();
    $new_vm_res = libvirt_domain_nic_add($vm_res, $vm_nic_mac_address, _DISCONNECTED_VIRT_NETWORK_ID_, 'virtio', $flags);
    ace_kvm_log_last_error();
    return (is_resource($new_vm_res)) ? TRUE : FALSE;
}

/**
 * fetch array of NIC information from a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  array               vm nic info
 */
function ace_kvm_vm_get_nic_array($vm_virt_id)
{
    global $host_conn;
    $nic_array = array();
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $xml = libvirt_domain_get_xml_desc($vm_res, NULL);
    ace_kvm_log_last_error();
    $vmXML = new SimpleXMLElement($xml);
    $arr_nics = $vmXML->xpath("//interface[@type='network']");
    $c = count($arr_nics);
    for ($n = 0; $n < $c; $n++) {
        $nic_array[ $n ]['mac'] = (string)$arr_nics[ $n ]->mac->attributes()->address;
        $nic_array[ $n ]['network'] = (string)$arr_nics[ $n ]->source->attributes()->network;
        //$nic_array[ $n ]['model'] = (string)$arr_nics[ $n ]->model->attributes()->type;
        $nic_array[ $n ]['pxe_enable'] = (string)$arr_nics[ $n ]->rom->attributes()->bar;
        //$nic_array[ $n ]['host_interface'] = (string)$arr_nics[ $n ]->target->attributes()->dev;
        //$nic_array[ $n ]['alias'] = (string)$arr_nics[ $n ]->alias->attributes()->name;
        $nic_array[ $n ]['link_state'] = (string)$arr_nics[ $n ]->link->attributes()->state;
    }
    return $nic_array;
}

/**
 * fetch the link state for a NIC in a virtual machine on the virt host
 *
 * @param   string $vm_virt_id         virt_id of the vm
 * @param   string $vm_nic_mac_address mac address of the NIC
 *
 * @return  bool                link state
 */
function ace_kvm_vm_nic_get_link_state($vm_virt_id, $vm_nic_mac_address)
{
    $nic_array = ace_kvm_vm_get_nic_array($vm_virt_id);
    $link_state = FALSE;
    for ($n = 0; $n < count($nic_array); $n++) {
        if ($nic_array[ $n ]['mac'] == $vm_nic_mac_address) {
            $ls = $nic_array[ $n ]['link_state'];
            $link_state = ($ls == '' || $ls == 'up') ? TRUE : FALSE;
        }
    }
    return $link_state;
}

/**
 * fetch the network virt_id associated with a NIC in a virtual machine on the virt host
 *
 * @param   string $vm_virt_id         virt_id of the vm
 * @param   string $vm_nic_mac_address mac address of the NIC
 *
 * @return  NULL|string         NULL | name of network
 */
function ace_kvm_vm_get_nic_network_by_mac($vm_virt_id, $vm_nic_mac_address)
{
    $network = NULL;
    $nic_array = ace_kvm_vm_get_nic_array($vm_virt_id);
    if (is_array($nic_array)) {
        for ($n = 0; $n < count($nic_array); $n++) {
            if ($nic_array[ $n ]['mac'] == $vm_nic_mac_address) {
                $network = $nic_array[ $n ]['network'];
            }
        }
    } else {
        $network = NULL;
    }
    return $network;
}

/**
 * set link state UP of a NIC in a virtual machine on the virt host
 *
 * @global  resource $host_conn          libvirt connection
 *
 * @param   string   $vm_virt_id         virt_id of the vm
 * @param   string   $vm_nic_mac_address mac address of the NIC
 *
 * @return  bool                on success
 */
function ace_kvm_vm_nic_link_up($vm_virt_id, $vm_nic_mac_address)
{
    global $host_conn;
    $network = ace_kvm_vm_get_nic_network_by_mac($vm_virt_id, $vm_nic_mac_address);
    $xml = "<interface type='network'>
                <source network='$network'/>
                <mac address='$vm_nic_mac_address'/>
                <link state='up'/>
            </interface>";
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $new_vm_res = libvirt_domain_update_device($vm_res, $xml, VIR_DOMAIN_DEVICE_MODIFY_CURRENT);
    ace_kvm_log_last_error();
    return (is_resource($new_vm_res)) ? TRUE : FALSE;
}

/**
 * set link state DOWN of a NIC in a virtual machine on the virt host
 *
 * @global  resource $host_conn          libvirt connection
 *
 * @param   string   $vm_virt_id         virt_id of the vm
 * @param   string   $vm_nic_mac_address mac address of the NIC
 *
 * @return  bool                on success
 */
function ace_kvm_vm_nic_link_down($vm_virt_id, $vm_nic_mac_address)
{
    global $host_conn;
    $network = ace_kvm_vm_get_nic_network_by_mac($vm_virt_id, $vm_nic_mac_address);
    $xml = "<interface type='network'>
                <source network='$network'/>
                <mac address='$vm_nic_mac_address'/>
                <link state='down'/>
            </interface>";
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $new_vm_res = libvirt_domain_update_device($vm_res, $xml, VIR_DOMAIN_DEVICE_MODIFY_CURRENT);
    ace_kvm_log_last_error();
    return (is_resource($new_vm_res)) ? TRUE : FALSE;
}

/**
 * detach a NIC device from a virtual machine on the virt host
 *
 * @todo use libvirt API instead of VIRSH
 *
 * @param   string $host_name          virt_id of host (to pass to virsh)
 * @param   string $vm_virt_id         virt_id of the vm
 * @param   string $vm_nic_mac_address mac address of the NIC
 *
 * @return  bool                on success
 */
function ace_kvm_vm_detach_nic_virsh($host_name, $vm_virt_id, $vm_nic_mac_address)
{        # VIRSH - use to disconnect network from a nic
    $con = 'virsh -c "qemu+ssh://root@' . $host_name . '/system" ';
    $cmd = "detach-interface --domain $vm_virt_id --type network --mac '$vm_nic_mac_address' --config";
    exec($con . $cmd, $output, $err);
    return ($err === 0) ? TRUE : FALSE;
}

/**
 * detach a NIC device from a virtual machine on the virt host
 *
 * @todo FIX THIS so we can use it instead of VIRSH
 *
 * @global  resource $host_conn          libvirt connection
 *
 * @param   string   $vm_virt_id         virt_id of the vm
 * @param   string   $vm_nic_mac_address mac address of the NIC
 *
 * @return  bool                on success
 */
function ace_kvm_vm_detach_nic($vm_virt_id, $vm_nic_mac_address)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $flags = (libvirt_domain_is_active($vm_res)) ? 3 : 2;
    ace_kvm_log_last_error();
    $new_vm_res = libvirt_domain_nic_remove($vm_res, $vm_nic_mac_address, $flags);
    ace_kvm_log_last_error();
    return (is_resource($new_vm_res)) ? TRUE : FALSE;
}

/**
 * connect a NIC to a network in a virtual machine on the virt host
 *
 * @todo FIX the VIRSH portion of this function
 *
 * @global  resource $host_conn           libvirt connection
 *
 * @param   string   $host_name           virt_id of host (to pass to virsh for activation only)
 * @param   string   $vm_virt_id          virt_id of the vm
 * @param   int      $vm_nic_instance     instance of NIC device
 * @param   string   $vm_nic_mac_address  mac address of the NIC
 * @param   string   $new_network_virt_id virt_id of network
 *
 * @return  bool                on success
 */
function ace_kvm_vm_nic_connect_network($host_name, $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, $new_network_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $vm_is_active = (libvirt_domain_is_active($vm_res) == 1) ? TRUE : FALSE;
    ace_kvm_log_last_error();
    $old_network_virt_id = ace_kvm_vm_get_nic_network_by_mac($vm_virt_id, $vm_nic_mac_address);
    $old_network_res = libvirt_network_get($host_conn, $old_network_virt_id);
    ace_kvm_log_last_error();
    $old_virt_network_is_active = (libvirt_network_get_active($old_network_res) == 1) ? TRUE : FALSE;
    ace_kvm_log_last_error();
    $new_network_res = libvirt_network_get($host_conn, $new_network_virt_id);
    ace_kvm_log_last_error();
    $new_virt_network_is_active = (libvirt_network_get_active($new_network_res) == 1) ? TRUE : FALSE;
    ace_kvm_log_last_error();
    $link_state = ($new_virt_network_is_active) ? 'up' : 'down';
    $xml = "<interface type='network'>
				<mac address='$vm_nic_mac_address'/>
				<source network='$new_network_virt_id'/>
				<link state='$link_state'/>
			</interface>";
    $libvirt_flags = ($vm_is_active) ? 3 : 2;    # config + live : config only

    # temporarily activate old and/or new networks (excluding _DISCONNECTED.._) if either are currently deactivated
    if (!$old_virt_network_is_active && ($old_network_virt_id !== _DISCONNECTED_VIRT_NETWORK_ID_)) {
        ace_kvm_network_activate_virsh($host_name, $old_network_virt_id);
    }
    if (!$new_virt_network_is_active && ($new_network_virt_id !== _DISCONNECTED_VIRT_NETWORK_ID_)) {
        ace_kvm_network_activate_virsh($host_name, $new_network_virt_id);
    }

    $virt_success = libvirt_domain_update_device($vm_res, $xml, $libvirt_flags);
    ace_kvm_log_last_error();

    # deactivate old and/or new networks (excluding _DISCONNECTED.._) if either was temporarily activated
    if (!$old_virt_network_is_active && ($old_network_virt_id !== _DISCONNECTED_VIRT_NETWORK_ID_)) {
        ace_kvm_network_deactivate_virsh($host_name, $old_network_virt_id);
    }
    if (!$new_virt_network_is_active && ($new_network_virt_id !== _DISCONNECTED_VIRT_NETWORK_ID_)) {
        ace_kvm_network_deactivate_virsh($host_name, $new_network_virt_id);
    }

    return $virt_success;
}

#=============================================================
#VM ACTIONS
#=============================================================
/**
 * fetch and store a screenshot of an active virtual machine on the virt host
 *
 * @global  resource $host_conn      libvirt connection
 *
 * @param   string   $vm_virt_id     virt_id of the vm
 * @param   string   $virt_host_name virt_id of host
 * @param   int      $max_width      width of screenshot (height determined by ratio)
 *
 * @return  string              filename of JPG
 */
function ace_kvm_vm_screenshot($vm_virt_id, $virt_host_name, $max_width)
{
    global $host_conn;
    // $xSize = 160;
    // $ySize = 120;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $screenshot_dimensions = libvirt_domain_get_screen_dimensions($vm_res, $virt_host_name);
    ace_kvm_log_last_error();
    $screenshot_width = $screenshot_dimensions['width'];
    $screenshot_height = $screenshot_dimensions['height'];
    $resize_factor = ($screenshot_width / $max_width);
    $desired_screenshot_width = ceil($screenshot_width / $resize_factor);
    $desired_screenshot_height = ceil($screenshot_height / $resize_factor);
    //$time[] = microtime(TRUE);

    $screenshot = libvirt_domain_get_screenshot_api($vm_res, 0);
    ace_kvm_log_last_error();

    if ($screenshot) {
        //$time[] = microtime(TRUE);
        $temp_filename = $screenshot['file'];
        //echo d($temp_filename);
        $image = new Imagick($temp_filename);
        $image->setFormat('jpeg');
        #resizeImage(xRes,yRes,FILTER,sharp/blurry,bestfit)
        //$image->resizeImage($desired_screenshot_width,$desired_screenshot_height,Imagick::FILTER_POINT,0.5,TRUE);
        //$time[] = microtime(TRUE);
        //echo d($image);

        $image->thumbnailimage(180, 0);
        //$time[] = microtime(TRUE);

        $jpg_filename = './screenshots/' . $vm_virt_id . '_thumb.jpg';
        $image->writeImage($jpg_filename);
        //$time[] = microtime(TRUE);

        $image->clear();
        $image->destroy();
        //$time[] = microtime(TRUE);

        unlink($temp_filename); #delete the /tmp/ file
        //$time[] = microtime(TRUE);
    } else {
        $jpg_filename = '';
    }
    /*
    echo '<pre>Screenshot timing';
    echo '<table>';
    echo '<tr><td>libvirt_domain_get_screenshot_api</td><td align="right">' . round((($time[1] - $time[0]) * 1000),3) . '</td></tr>';
    echo '<tr><td>$image = new Imagick($temp_filename)</td><td align="right">' . round((($time[2] - $time[1]) * 1000),3) . '</td></tr>';
    echo '<tr><td>$image->thumbnailimage(180,0)</td><td align="right">' . round((($time[3] - $time[2]) * 1000),3) . '</td></tr>';
    echo '<tr><td>$image->writeImage($jpg_filename)</td><td align="right">' . round((($time[4] - $time[3]) * 1000),3) . '</td></tr>';
    echo '<tr><td>$image->destroy()</td><td align="right">' . round((($time[5] - $time[4]) * 1000),3) . '</td></tr>';
    echo '<tr><td>unlink($temp_filename)</td><td align="right">' . round((($time[6] - $time[5]) * 1000),3) . '</td></tr>';
    echo '<tr><td>TOTAL</td><td align="right">' . round((($time[6] - $time[0]) * 1000),3) . '</td></tr>';
    echo '</table></pre>';
    echo d($time);
    */
    return $jpg_filename;
}

/**
 * fetch snapshot list of a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return  array               snapshot list
 */
function ace_kvm_vm_get_snapshot_list($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $array = libvirt_list_domain_snapshots($vm_res);
    ace_kvm_log_last_error();
    sort($array);
    return $array;
}

/**
 * create snapshot of a virtual machine on the virt host
 *
 * @global  resource $host_conn  libvirt connection
 *
 * @param   string   $vm_virt_id virt_id of the vm
 *
 * @return bool                 on success
 */
function ace_kvm_vm_create_snapshot($vm_virt_id)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $vm_is_active = (ace_kvm_get_vm_state($vm_virt_id) == 1) ? TRUE : FALSE;
    if ($vm_is_active) {
        $vm_snap_res = libvirt_domain_snapshot_create($vm_res);
        ace_kvm_log_last_error();
        $return = ($vm_snap_res) ? TRUE : FALSE;
    } else {
        #start
        ace_kvm_vm_start($vm_virt_id);
        #suspend
        ace_kvm_vm_suspend($vm_virt_id);
        #take snapshot
        $vm_snap_res = libvirt_domain_snapshot_create($vm_res);
        ace_kvm_log_last_error();
        $return = (is_resource($vm_snap_res)) ? TRUE : FALSE;
        #stop
        ace_kvm_vm_stop($vm_virt_id);
    }
    return $return;
}

/**
 * revert to a snapshot of a virtual machine on the virt host
 *
 * @todo TIDY UP to remove bugged code
 *
 * @global  resource $host_conn                         libvirt connection
 *
 * @param   string   $vm_virt_id                        virt_id of the vm
 * @param   int      $vm_snapshot_instance_to_revert_to snapshot instance
 *
 * @return  bool                on success
 */
function ace_kvm_vm_snapshot_revert($vm_virt_id, $vm_snapshot_instance_to_revert_to)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $vm_snapshot_list = ace_kvm_vm_get_snapshot_list($vm_virt_id);
    $num_vm_snapshots = count($vm_snapshot_list);
    $max_snapshot_instance = $num_vm_snapshots - 1;
    if (($num_vm_snapshots > 0)
        && ($vm_snapshot_instance_to_revert_to >= 0)
        && ($vm_snapshot_instance_to_revert_to <= $max_snapshot_instance)
    ) {
        $vm_snapshot_name_to_revert_to = $vm_snapshot_list[ $vm_snapshot_instance_to_revert_to ];
        $vm_snapshot_res_to_revert_to = libvirt_domain_snapshot_lookup_by_name($vm_res, $vm_snapshot_name_to_revert_to);
        ace_kvm_log_last_error();
        $revert_result = libvirt_domain_snapshot_revert($vm_snapshot_res_to_revert_to);
        ace_kvm_log_last_error();
        # resume, since the vm was 'paused' when the snapshot was taken
        ace_kvm_vm_resume($vm_virt_id);
        # then stop, since we only work with snapshots when vm is inactive
        ace_kvm_vm_stop($vm_virt_id);
        # if $vm_snapshot_instance < $num_snaps then delete snap[instance+1]-with children
        # if (($num_vm_snapshots > 0) && ($vm_snapshot_instance < ($num_vm_snapshots - 1))) {
        if ($vm_snapshot_instance_to_revert_to < $max_snapshot_instance) {
            $all_delete_results = TRUE;
            for ($i = $max_snapshot_instance; $i > $vm_snapshot_instance_to_revert_to; $i--) {
                $vm_snapshot_name_to_delete = $vm_snapshot_list[ $i ];
                $vm_snapshot_res_to_delete = libvirt_domain_snapshot_lookup_by_name($vm_res, $vm_snapshot_name_to_delete);
                ace_kvm_log_last_error();
                $delete_result = libvirt_domain_snapshot_delete($vm_snapshot_res_to_delete);
                ace_kvm_log_last_error();
                $all_delete_results = $all_delete_results && $delete_result;
            }
            # the following FAILS - it has to do with the children flag
            //$result = libvirt_domain_snapshot_delete($vm_snapshot_res_to_delete,VIR_SNAPSHOT_DELETE_CHILDREN);
            #$result = libvirt_domain_snapshot_delete($vm_snapshot_res_to_delete);
            //ace_kvm_log_error();
        } else {
            $all_delete_results = TRUE;
        }
    } else {
        $revert_result = FALSE;
    }
    return $revert_result && $all_delete_results;
}

/**
 * delete a snapshot from a virtual machine on the virt host
 *
 * @global  resource $host_conn            libvirt connection
 *
 * @param   string   $vm_virt_id           virt_id of the vm
 * @param   int      $vm_snapshot_instance snapshot instance
 *
 * @return  bool                on success
 */
function ace_kvm_vm_snapshot_delete($vm_virt_id, $vm_snapshot_instance)
{
    global $host_conn;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $vm_snapshot_list = libvirt_list_domain_snapshots($vm_res);
    ace_kvm_log_last_error();
    $num_vm_snapshots = count($vm_snapshot_list);
    $max_snapshot_instance = $num_vm_snapshots - 1;
    if (($num_vm_snapshots > 0)
        && ($vm_snapshot_instance >= 0)
        && ($vm_snapshot_instance <= $max_snapshot_instance)
    ) {
        $vm_snapshot_name = $vm_snapshot_list[ $vm_snapshot_instance ];
        $vm_snapshot_res = libvirt_domain_snapshot_lookup_by_name($vm_res, $vm_snapshot_name);
        ace_kvm_log_last_error();
        # there's a problem with the flag VIR_SNAPSHOT_DELETE_CHILDREN here
        $result = libvirt_domain_snapshot_delete($vm_snapshot_res);
        ace_kvm_log_last_error();
    } else {
        $result = FALSE;
    }
    $return = $result;
    return $return;
}

function ace_kvm_vm_delete_snapshots($vm_virt_id)
{
    global $host_conn;
    $result = FALSE;
    $vm_res = libvirt_domain_lookup_by_name($host_conn, $vm_virt_id);
    ace_kvm_log_last_error();
    $vm_snapshot_list = libvirt_list_domain_snapshots($vm_res);
    ace_kvm_log_last_error();
    foreach ($vm_snapshot_list as $vm_snapshot) {
        $vm_snapshot_res = libvirt_domain_snapshot_lookup_by_name($vm_res, $vm_snapshot);
        ace_kvm_log_last_error();
        # there's a problem with the flag VIR_SNAPSHOT_DELETE_CHILDREN here
        $result = libvirt_domain_snapshot_delete($vm_snapshot_res);
        ace_kvm_log_last_error();
    }
    $return = $result;
    return $return;
}
