<?php
/**
 * ACEITLab Application API
 *
 * provides system-wide highest-level functions to the ACEITLab application
 * takes calls from main application
 * makes calls to General, Output, MySQL, and Guacamole Functions, and the Hypervisor Abstractor Function (HAF)
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

/**
 * Function includes
 *
 * loads required function libraries
 */
require_once('fns_general.php');
require_once('fns_output.php');
require_once('fns_mysql.php');
require_once('fns_virt.php');
require_once('fns_guacamole.php');

/**
 * the relative path to the application from the web server's root path
 */
define("_UI_PATH_", "/rudi/");
/**
 * the file path and name of the PHP login page
 */
define("_LOGIN_URL_", _UI_PATH_ . "login.php");
/**
 * the file path and name of the PHP Admin page
 */
define("_ADMIN_URL_", _UI_PATH_ . "admin.php");
/**
 * aesthetic display name for administrators
 * * for potential future modification
 */
define("_ADMIN_ALIAS_", "Administrator");
/**
 * the file path and name of the PHP Manage page
 */
define("_MANAGER_URL_", _UI_PATH_ . "manager.php");
/**
 * aesthetic display name for managers
 * * for potential future modification
 */
define("_MANAGER_ALIAS_", "Faculty");
/**
 * the file path and name of the PHP user profile page
 */
define("_USER_URL_", _UI_PATH_ . "user.php");
/**
 * aesthetic display name for users
 * * for potential future modification
 */
define("_USER_ALIAS_", "Student");
/**
 * the file path and name of the PHP lab page
 */
define("_LAB_URL_", _UI_PATH_ . "lab.php");
/**
 * the file path and name of the PHP console page
 */
define("_CONSOLE_URL_", _UI_PATH_ . "console.php");
/**
 * the file path and name of the PHP console error page
 */
define("_CONSOLE_ERROR_URL_", _UI_PATH_ . "console_error.php");
/**
 * the URL of the Guacamole client
 */
define("_GUACAMOLE_URL_", "/guacamole/client.xhtml");
/**
 * the file path and name of the ACEITLab general log file
 */
define("_LOG_", "/ace.log");

/**
 * denotes highest security level for full system access
 */
define("_ADMIN_SECURITY_LEVEL_", 1);
/**
 * denotes management security level for lab and content creation and sharing, and user/group management
 */
define("_MANAGER_SECURITY_LEVEL_", 2);
/**
 * denotes user security level for end users, lab access and use
 */
define("_USER_SECURITY_LEVEL_", 3);

/**
 * the name of the base volume assigned to the diff volume of all tenant router vm
 */
define("_TENANT_ROUTER_BASE_VOLUME_NAME_", "0-vol-rtr");
/**
 * the aesthetic display name of the tenant-side network associated with all tenant router vm
 * * from the tenants' perspective, this is their public network
 */
define("_LAB_ROUTER_PUBLIC_NETWORK_NAME_", "Public");
/**
 * the name of the host's public bridge/interface
 */
define("_HOST_PUBLIC_NETWORK_NAME_", "public");
/**
 * the name of the host's private bridge/interface
 */
define("_HOST_PRIVATE_NETWORK_NAME_", "private");
/**
 * the name of the host's inactive bridge/interface (for disconnected networks on kvm)
 */
define("_DISCONNECTED_NETWORK_NAME_", "none");
/**
 * the start of the usable MAC address range for this ACEITLab deployment
 */
define("_MAC_POOL_START_", '52:54:80:00:00:00');
/**
 * the end of the usable MAC address range for this ACEITLab deployment
 */
define("_MAC_POOL_END_", '52:54:80:FF:FF:FF');
/**
 * the start of the usable MAC address range for this ACEITLab deployment, as an integer
 */
define("_MAC_POOL_INDEX_START_", ace_gen_convert_mac2int(_MAC_POOL_START_));
/**
 * the end of the usable MAC address range for this ACEITLab deployment, as an integer
 */
define("_MAC_POOL_INDEX_END_", ace_gen_convert_mac2int(_MAC_POOL_END_));

/**
 * the maximum allowable age of an active lab, in seconds
 * 259200 seconds is 72 hours
 */
define("_LAB_AGE_MAXIMUM_", 259200);

date_default_timezone_set("America/New_York");

#=============================================================
#SYSTEM
#=============================================================
/**
 * returns authentication of given credentials
 *
 * @api
 *
 * @param   string  $user_name      user name
 * @param   string  $user_password  user password
 *
 * @return  bool                        TRUE = authenticated, FALSE = rejected
 */
function ace_authenticate_user($user_name, $user_password)
{
    return ace_db_authenticate_user($user_name, $user_password);
}

/**
 * returns a table of known virtualization hosts
 *
 * @api
 *
 * @return  array|bool                  active host table | FALSE on error
 */
function ace_get_hosts()
{
    return ace_db_get_host_table();
}

/**
 * returns a table of active known virtualization hosts
 *
 * @api
 *
 * @return  array|bool                  active host table | FALSE on error
 */
function ace_get_active_hosts()
{
    return ace_db_get_active_host_table();
}

/**
 * returns an array of valid host roles
 *
 * @api
 *
 * @return  array|bool                  host roles table | FALSE on error
 */
function ace_get_host_roles()
{
    return ace_db_get_host_role_table();
}

/**
 * returns an array of all users
 *
 * @api
 *
 * @return  array|bool                  user table | FALSE on error
 */
function ace_get_users()
{
    return ace_db_get_user_table();
}

/**
 * returns an array of admins and managers only from all users
 *
 * @api
 *
 * @return  array|bool                  user table (admins and managers only) | FALSE on error
 */
function ace_get_user_admins_and_managers()
{
    return ace_db_get_user_admins_and_managers_table();
}

/**
 * returns an array of all groups
 *
 * @api
 *
 * @return  array|bool                  group table | FALSE on error
 */
function ace_get_groups()
{
    return ace_db_get_group_table();
}

/**
 * returns an array of security groups
 *
 * @api
 *
 * @return array|bool                   group table | FALSE on error
 */
function ace_get_security_groups()
{
    return ace_db_get_security_group_table();
}

/**
 * returns an array of academic groups
 *
 * @api
 *
 * @return array|bool                   group table | FALSE on error
 */
function ace_get_academic_groups()
{
    return ace_db_get_academic_group_table();
}

/**
 * returns an array of courses
 *
 * @api
 *
 * @return  array|bool                  course table | FALSE on error
 */
function ace_get_courses(){
    return ace_db_get_course_table();
}

/**
 * returns an array of groups and their owners
 *
 * @api
 *
 * @return array|bool                   group table (with owners) | FALSE on error
 */
function ace_get_groups_and_owners()
{
    return ace_db_get_group_table_with_owners();
}

/**
 * returns an array of labs
 *
 * @api
 *
 * @return array|bool                   lab table | FALSE on error
 */
function ace_get_labs()
{
    return ace_db_get_lab_table();
}

/**
 * @return bool
 */
function ace_get_aged_active_labs()
{
    return ace_db_get_aged_active_labs_table();
}

/**
 * @return bool
 */
function ace_get_active_labs()
{
    return ace_db_get_active_lab_table();
}

/**
 * returns an array of labs and their owners
 *
 * @api
 *
 * @return array|bool                   lab table (with owners) | FALSE on error
 */
function ace_get_labs_and_owners()
{
    return ace_db_get_lab_table_with_owners();
}

/**
 * returns an array of networks
 *
 * @api
 *
 * @return array|bool                   network table | FALSE on error
 */
function ace_get_networks()
{
    return ace_db_get_network_table();
}

/**
 * returns an array of all volumes (img and iso)
 *
 * @api
 *
 * @return array|bool                   volume table | FALSE on error
 */
function ace_get_volumes()
{
    return ace_db_get_volume_table();
}

/**
 * return an array of volumes in the images pool (img only)
 *
 * @api
 *
 * @return array|bool                   volume table (img, shared only) | FALSE on error
 */
function ace_get_shared_volume_table()
{
    return ace_db_get_shared_volume_table();
}

/**
 * returns an array volumes in the media pool (iso only)
 *
 * @api
 *
 * @return array|bool                   volume table (iso only) | FALSE on error
 */
function ace_get_iso_table()
{
    return ace_db_get_iso_table();
}

/**
 * returns an array of all vms
 *
 * @api
 *
 * @return array|bool                   vm table | FALSE on error
 */
function ace_get_vm_table()
{
    return ace_db_get_vm_table();
}

/**
 * @return array|bool
 */
function ace_get_quotas(){
    return ace_db_get_quota_table();
}

#=============================================================
#HOST
#=============================================================
/**
 * returns a host id
 *
 * @api
 *
 * @param   string $host_name host name
 *
 * @return bool|int                 host id | FALSE on error
 */
function ace_host_get_id_by_name($host_name)
{
    return ace_db_host_get_id_by_name($host_name);
}

/**
 * returns a host name
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  string|bool         host name | FALSE on error
 */
function ace_host_get_name_by_id($host_id)
{
    return ace_db_host_get_name_by_id($host_id);
}

/**
 * returns a host name
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  array|bool           host information record | FALSE on error
 */
function ace_host_get_info($host_id)
{
    return ace_db_host_get_info($host_id);
}

/**
 * returns state of host
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  bool                 active TRUE/FALSE
 */
function ace_host_is_active($host_id)
{
    $db_host_active = ace_db_host_get_state($host_id);
    $virt_host_active = ace_virt_host_is_active($host_id);
    if ($db_host_active && $virt_host_active){
        return TRUE;
    } else {
        return FALSE;
    }
    //return ace_db_host_get_state($host_id);
}

/**
 * returns a table of labs for a given host
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  array|bool           host lab table | FALSE on error
 */
function ace_host_get_lab_table($host_id)
{
    return ace_db_host_get_lab_table($host_id);
}

/**
 * returns TRUE/FALSE for a given host
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return bool                 TRUE | FALSE
 */
function ace_host_has_active_labs($host_id)
{
    return ace_db_host_has_active_labs($host_id);
}

/**
 * adds a new host
 *
 * @api
 *
 * @param   string $host_name        host name
 * @param   string $host_domain      host domain name
 * @param   string $host_description description of host
 * @param   string $host_hypervisor  hypervisor running on host
 * @param   string $host_ip_internal host's internal IP address accessible by web server
 * @param   string $host_ip_external host's external IP address (for information only)
 * @param   string $host_username    user name used by ACEITLab to access the hypervisor on the host
 * @param   string $host_password    password used by ACEITLab to access the hypervisor on the host
 * @param   int    $host_threads     maximum number of threads supported by the host
 * @param   int    $host_memory      maximum amount (GiB) of usable RAM on the host
 * @param   int    $host_storage     maximum amount (GiB) of usable storage on the host
 *
 * @return  int|bool                last insert id | FALSE on error
 */
function ace_host_add($host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage)
{
    if (ace_host_get_id_by_name($host_name)) {
        return FALSE;
    } else {
        return ace_db_host_add($host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage);
    }
}

/**
 * updates a host
 *
 * @api
 *
 * @param   int    $host_id          host id
 * @param   string $host_name        host name
 * @param   string $host_domain      host domain name
 * @param   string $host_description description of host
 * @param   string $host_hypervisor  hypervisor running on host
 * @param   string $host_ip_internal host's internal IP address accessible by web server
 * @param   string $host_ip_external host's external IP address (for information only)
 * @param   string $host_username    user name used by ACEITLab to access the hypervisor on the host
 * @param   string $host_password    password used by ACEITLab to access the hypervisor on the host
 * @param   int    $host_threads     maximum number of threads supported by the host
 * @param   int    $host_memory      maximum amount (GiB) of usable RAM on the host
 * @param   int    $host_storage     maximum amount (GiB) of usable storage on the host
 *
 * @return  bool                    on success
 */
function ace_host_update($host_id, $host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage)
{
    return ace_db_host_update($host_id, $host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage);
}

/**
 * returns a table of roles for a given host
 *
 * @api
 *
 * @param  int $host_id host id
 *
 * @return array|bool               host owned roles table | FALSE on error
 */
function ace_host_get_roles($host_id)
{
    return ace_db_host_get_roles($host_id);
}

/**
 * assigns a given role to a given host
 *
 * @api
 *
 * @param   int $host_id      host id
 * @param   int $host_role_id host role id
 *
 * @return  bool                    on success
 */
function ace_host_add_role($host_id, $host_role_id)
{
    return ace_db_host_add_role($host_id, $host_role_id);
}

/**
 * un-assigns a given role from a given host
 *
 * @api
 *
 * @param   int $host_id      host id
 * @param   int $host_role_id host role id
 *
 * @return  bool                    on success
 */
function ace_host_remove_role($host_id, $host_role_id)
{
    return ace_db_host_remove_role($host_id, $host_role_id);
}

/**
 * un-assigns all roles from a given host
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  bool                    on success
 */
function ace_host_remove_all_roles($host_id)
{
    return ace_db_host_remove_all_roles($host_id);
}

/**
 * sets host online, ready for service
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  bool                    on success
 */
function ace_host_activate($host_id)
{
    return ace_db_host_set_state($host_id, TRUE);
}

/**
 * @param $host_id
 *
 * @return mixed
 */
function ace_host_test_connection($host_id)
{
    return ace_virt_host_is_active($host_id);
}

/**
 * sets host offline
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  bool                    on success
 */
function ace_host_deactivate($host_id)
{
    if (ace_host_get_lab_table($host_id) === FALSE) {
        return ace_db_host_set_state($host_id, FALSE);
    } else {
        return FALSE;
    }
}

/**
 * removes a given host
 *
 * @api
 *
 * @param   int $host_id host id
 *
 * @return  bool                    on success
 */
function ace_host_remove($host_id){
    #check for active labs on this host
    if (!ace_host_has_active_labs($host_id)) {
        #remove all host role mappings first, then remove the host
        if (ace_db_host_remove_all_roles($host_id)) {
            return ace_db_host_remove($host_id);
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

/**
 * returns live OS information about the remote virt host
 *
 * @api
 *
 * @param   int $host_id
 *
 * @return  string|bool         hypervisor info or FALSE on error
 */
function ace_host_get_hypervisor_info($host_id)
{
    return ace_virt('get_hypervisor_info', $host_id);
}

/**
 * returns live physical information about the remote virt host
 *
 * @api
 *
 * @param   int $host_id
 *
 * @return  string|bool         physical host info or FALSE on error
 */
function ace_host_get_physical_info($host_id)
{
    return ace_virt('get_physical_info', $host_id);
}

/**
 * return a list of network_virt_id from the virt host
 *
 * @api
 *
 * @param   int $host_id
 *
 * @return  array|bool          network_virt_id list or FALSE on error
 */
function ace_host_get_virt_network_list($host_id)
{
    return ace_virt('get_network_list', $host_id);
}

/**
 * @param $host_id
 *
 * @return mixed
 */
function ace_host_get_virt_storage_info($host_id){
    return ace_virt_host_get_storage_info($host_id);
}

/**
 * returns a list of volume_virt_id from the host's IMAGES pool
 *
 * @api
 *
 * @param   int $host_id
 *
 * @return  array|bool          volume_virt_id list or FALSE on error
 */
function ace_host_get_virt_volume_list($host_id)
{
    return ace_virt('get_volume_list', $host_id);
}

/**
 * returns a list of volume_virt_id from the host's MEDIA pool
 *
 * @api
 *
 * @param   int $host_id
 *
 * @return  array|bool          volume_virt_id list or FALSE on error
 */
function ace_host_get_virt_media_list($host_id)
{
    return ace_virt('get_media_list', $host_id);
}

/**
 * returns a list of vm_virt_id from the virt host
 *
 * @api
 *
 * @param   int $host_id
 *
 * @return  array|bool          vm_virt_id list or FALSE on error
 */
function ace_host_get_virt_vm_list($host_id)
{
    return ace_virt('get_vm_list', $host_id);
}

/**
 * @param $host_id
 *
 * @return mixed
 */
function ace_virt_host_is_active($host_id) {
    return ace_virt('test_connection', $host_id);
}

/**
 * @param $host_id
 *
 * @return mixed
 */
function ace_virt_host_get_storage_info($host_id){
    return ace_virt('get_storage_info', $host_id);
}
#=============================================================
#USER
#=============================================================
/**
 * returns a user id
 *
 * @api
 *
 * @param   string $user_name user name
 *
 * @return  int|bool                user id | FALSE on error
 */
function ace_user_get_id_by_name($user_name)
{
    return ace_db_user_get_id_by_name($user_name);
}

/**
 * returns a user name
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  string|bool             user name | FALSE on error
 */
function ace_user_get_name_by_id($user_id)
{
    return ace_db_user_get_name_by_id($user_id);
}

/**
 * returns a user display name
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  string|bool             user display name | FALSE on error
 */
function ace_user_get_display_name_by_id($user_id)
{
    return ace_db_user_get_display_name_by_id($user_id);
}

/**
 * returns a user record
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  bool|array              array of user information | FALSE on error
 */
function ace_user_get_info($user_id)
{
    return ace_db_user_get_info($user_id);
}

/**
 * returns the highest security level for a specified user
 *
 * @api
 *
 * @param int   $user_id    user id
 *
 * @return FALSE|int                    user security level (1=Admin|2=Manager|3=User) | FALSE on error
 */
function ace_user_get_security_level($user_id)
{
    return ace_db_user_get_security_level($user_id);
}

/**
 * returns user state
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  bool                    TRUE = active | FALSE = inactive|error
 */
function ace_user_get_state($user_id)
{
    return ace_db_user_get_state($user_id);
}

/**
 * @param $user_id
 * @param $user_state
 *
 * @return bool
 */
function ace_user_set_state($user_id, $user_state){
    return ace_db_user_set_state($user_id, $user_state);
}

/**
 * returns a table of lab information
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              table of lab information | FALSE on error
 */
function ace_user_get_lab_table($user_id)
{
    return ace_db_user_get_lab_table($user_id);
}

/**
 * returns a group membership table
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return array|bool               table of group information | FALSE on error
 */
function ace_user_get_groups($user_id)
{
    return ace_db_user_get_groups($user_id);
}

/**
 * returns a table of owned groups
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              table of owned groups | FALSE on error
 */
function ace_user_get_owned_groups($user_id)
{
    return ace_db_user_get_owned_groups($user_id);
}

/**
 * returns a table of owned academic groups
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              table of owned groups | FALSE on error
 */
function ace_user_get_owned_academic_groups($user_id)
{
    return ace_db_user_get_owned_academic_groups($user_id);
}

/**
 * returns a table of owned groups for a given user
 *
 * @api
 *
 * @param   int     $user_id    user id
 *
 * @return array|bool               table of owned labs | FALSE on error
 */
function ace_user_get_owned_labs($user_id)
{
    return ace_db_user_get_owned_labs($user_id);
}

/**
 * returns if a given user account already exists
 *
 * @api
 *
 * @param $user_name
 *
 * @return bool                     TRUE | FALSE
 */
function ace_user_exists($user_name){
    return ace_db_user_exists($user_name);
}

/**
 * returns an array of quotas
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              array of quotas | FALSE on error
 */
function ace_user_get_quota($user_id)
{
    return ace_db_user_get_quota_array($user_id);
}

/**
 * creates a new user
 *
 * @api
 *
 * @param   string $user_name  user name
 * @param   string $user_first user first name (for display name)
 * @param   string $user_last  user last name (for display name)
 *
 * @return  array                   array with user id and initial password (user id is FALSE on error)
 */
function ace_user_create($user_name, $user_first, $user_last)
{
    return ace_db_user_create($user_name, $user_first, $user_last);
}

/**
 * updates a user record
 *
 * @api
 *
 * @param   int    $user_id    user id
 * @param   string $user_name  user name
 * @param   string $user_first user first name (for display name)
 * @param   string $user_last  user last name (for display name)
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_user_update($user_id, $user_name, $user_first, $user_last)
{
    return ace_db_user_update($user_id, $user_name, $user_first, $user_last);
}

/**
 * @param $user_id
 *
 * @return bool|string
 */
function ace_user_reset_password($user_id){
    return ace_db_user_reset_password($user_id);
}

/**
 * updates a user's password
 *
 * @api
 *
 * @param   int    $user_id       user id
 * @param   string $user_password user password
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_user_update_password($user_id, $user_password)
{
    return ace_db_user_update_password($user_id, $user_password);
}

/**
 * activates a user
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_user_activate($user_id)
{
    return ace_db_user_set_state($user_id, TRUE);
}

/**
 * de-activates a user
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_user_deactivate($user_id)
{
    return ace_db_user_set_state($user_id, FALSE);
}

/**
 * deletes a user
 *
 * @api
 *
 * @param   int $user_id user id
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_user_delete($user_id)
{
    $owned_labs = ace_user_get_owned_labs($user_id);
    if (is_array($owned_labs)) {
        foreach ($owned_labs as $lab) {
            ace_lab_delete($lab['lab_id']);
        }
    }
    $owned_groups = ace_user_get_owned_groups($user_id);
    if (is_array($owned_groups)) {
        foreach ($owned_groups as $group) {
            ace_group_delete($group['group_id']);
        }
    }
    $groups = ace_user_get_groups($user_id);
    if (is_array($groups)) {
        foreach ($groups as $group) {
            ace_group_remove_user($group['group_id'], $user_id);
        }
    }
    return ace_db_user_delete($user_id);
}

#=============================================================
#GROUP
#=============================================================
/**
 * returns a group id
 *
 * @api
 *
 * @param   string $group_name group name
 *
 * @return  int|bool                group id | FALSE on error
 */
function ace_group_get_id_by_name($group_name)
{
    return ace_db_group_get_id_by_name($group_name);
}

/**
 * returns a group name
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return  string|bool             group name | FALSE on error
 */
function ace_group_get_name_by_id($group_id)
{
    return ace_db_group_get_name_by_id($group_id);
}

/**
 * returns group information
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return array|bool               array of group information | FALSE
 */
function ace_group_get_info($group_id)
{
    return ace_db_group_get_info($group_id);
}

/**
 * determines group state
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return  bool                    TRUE = active | FALSE = inactive
 */
function ace_group_get_state($group_id)
{
    return ace_db_group_get_state($group_id);
}

/**
 * returns an array of users in a group
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return  array|bool              array of user id | FALSE on error
 */
function ace_group_get_user_ids($group_id)
{
    return ace_db_group_get_user_ids($group_id);
}

/**
 * returns a table of group membership information
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return  array|bool               table of group members | FALSE on error
 */
function ace_group_get_members_table($group_id)
{
    return ace_db_group_get_members_table($group_id);
}

/**
 * returns a table of section information
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return array|bool               table of section info | FALSE on error
 */
function ace_group_get_section_info($group_id){
    return ace_db_group_get_section_info($group_id);
}

/**
 * @param $group_name
 *
 * @return bool
 */
function ace_create_security_group($group_name){
    return ace_db_create_security_group($group_name);
}

/**
 * @param $group_name
 * @param $group_owner_id
 *
 * @return bool
 */
function ace_create_academic_group($group_name, $group_owner_id){
    return ace_db_create_academic_group($group_name,$group_owner_id);
}

/**
 * @param $group_id
 * @param $courseID
 * @param $sectionID
 * @param $schedule
 * @param $comment
 *
 * @return bool|int
 */
function ace_group_create_section_info($group_id, $courseID, $sectionID, $schedule, $comment){
    return ace_db_group_create_section_info($group_id, $courseID, $sectionID, $schedule, $comment);
}

/**
 * @param $group_id
 * @param $courseID
 * @param $sectionID
 * @param $schedule
 * @param $comment
 *
 * @return bool
 */
function ace_group_update_section_info($group_id, $courseID, $sectionID, $schedule, $comment){
    return ace_db_group_update_section_info($group_id, $courseID, $sectionID, $schedule, $comment);
}

/**
 * @param $group_id
 *
 * @return bool
 */
function ace_group_delete_section_info($group_id){
    return ace_db_group_delete_section_info($group_id);
}

/**
 * returns a table of labs associated with a group
 *
 * @api
 *
 * @param   int $group_id group id
 *
 * @return  array|bool              table of lab information | FALSE on error
 */
function ace_group_get_lab_table($group_id)
{
    return ace_db_group_get_lab_table($group_id);
}

/**
 * creates a group
 *
 * @api
 *
 * @param   string $group_name     group name
 * @param   int    $group_owner_id user id of group owner
 *
 * @return  int|bool                new group id | FALSE on error
 */
function ace_group_create($group_name, $group_owner_id)
{
    return ace_db_group_create($group_name, $group_owner_id);
}

/**
 * updates a group
 *
 * @api
 *
 * @param   int    $group_id    group id
 * @param   string $group_name  group name
 * @param   int    $group_owner user id of group owner
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_update($group_id, $group_name, $group_owner)
{
    return ace_db_group_update($group_id, $group_name, $group_owner);
}

/**
 * sets the state of a group
 *
 * @api
 *
 * @param   int  $group_id    group id
 * @param   bool $group_state group state (active = TRUE)
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_set_state($group_id, $group_state)
{
    return ace_db_group_set_state($group_id, $group_state);
}

/**
 * determines if a user is a member of a group
 *
 * @api
 *
 * @param   int $group_id group id
 * @param   int $user_id  user id
 *
 * @return  bool                    TRUE = is a member | FALSE = is NOT a member
 */
function ace_group_user_is_member($group_id, $user_id)
{
    return ace_db_group_user_is_member($group_id, $user_id);
}

/**
 * adds user to a group
 *
 * @api
 *
 * @param   int $group_id group id
 * @param   int $user_id  user_id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_add_user($group_id, $user_id)
{
    return ace_db_group_add_user($group_id, $user_id);
}

/**
 * removes user from a group
 *
 * @api
 *
 * @param   int $group_id group id
 * @param   int $user_id  user_id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_remove_user($group_id, $user_id)
{
    $success = ace_db_group_remove_user($group_id, $user_id);
    $user_groups = ace_user_get_groups($user_id);
    if (count($user_groups) == 1) {
        $user_security_level = ace_user_get_security_level($user_id);
        if ($user_security_level == 3) {
            ace_user_delete($user_id);
        }
    }
    return ($success) ? TRUE : FALSE;
}

/**
 * determines if a lab is associated with a group
 *
 * @api
 *
 * @param   int $group_id group id
 * @param   int $lab_id   lab_id
 *
 * @return  bool                    TRUE = is a member  | FALSE = is NOT a member
 */
function ace_group_lab_is_member($group_id, $lab_id)
{
    return ace_db_group_lab_is_member($group_id, $lab_id);
}

/**
 * associates a lab with a group
 *
 * @api
 *
 * @param   int $group_id group id
 * @param   int $lab_id   lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_add_lab($group_id, $lab_id)
{
    return ace_db_group_add_lab($group_id, $lab_id);
}

/**
 * un-associates a lab from a group
 *
 * @param   int $group_id group id
 * @param   int $lab_id   lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_remove_lab($group_id, $lab_id)
{
    return ace_db_group_remove_lab($group_id, $lab_id);
}

/**
 * deletes a group
 *
 * @param   int $group_id group id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_group_delete($group_id)
{
    $group_members = ace_group_get_members_table($group_id);
    foreach ($group_members as $member) {
        $user_id = $member['user_id'];
        ace_group_remove_user($group_id,$user_id);
    }
    ace_group_delete_section_info($group_id);
    return ace_db_group_delete($group_id);
}


#=============================================================
#COURSE
#=============================================================
/**
 * @param $course_id
 *
 * @return bool
 */
function ace_course_get_ref_by_id($course_id){
    return ace_db_course_get_ref_by_id($course_id);
}

/**
 * @param $course_ref
 *
 * @return bool
 */
function ace_course_get_display_name_by_ref($course_ref) {
    return ace_db_course_get_display_name_by_ref($course_ref);
}

/**
 * @param $course_id
 *
 * @return array|bool
 */
function ace_course_get_info($course_id){
    return ace_db_course_get_info($course_id);
}

/**
 * @param $course_id
 *
 * @return bool
 */
function ace_course_get_section_table($course_id){
    return ace_db_course_get_section_table($course_id);
}

/**
 * @param $course_ref
 * @param $course_name
 *
 * @return bool
 */
function ace_course_create($course_ref, $course_name){
    return ace_db_course_create($course_ref, $course_name);
}

/**
 * @param $course_id
 * @param $course_ref
 * @param $course_name
 *
 * @return bool
 */
function ace_course_update($course_id, $course_ref, $course_name){
    return ace_db_course_update($course_id, $course_ref, $course_name);
}

/**
 * @param $course_id
 *
 * @return bool
 */
function ace_course_delete($course_id){
    return ace_db_course_delete($course_id);
}


#=============================================================
#QUOTA
#=============================================================
/**
 * @param $quota_id
 *
 * @return bool
 */
function ace_quota_get_info($quota_id) {
    return ace_db_quota_get_info($quota_id);
}

/**
 * @param $object_type
 * @param $object_id
 * @param $labs
 * @param $vms
 * @param $vcpu
 * @param $memory
 * @param $networks
 * @param $volumes
 * @param $storage
 *
 * @return bool
 */
function ace_quota_create($object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage){
    return ace_db_quota_create($object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage);
}

/**
 * @param $quota_id
 * @param $object_type
 * @param $object_id
 * @param $labs
 * @param $vms
 * @param $vcpu
 * @param $memory
 * @param $networks
 * @param $volumes
 * @param $storage
 *
 * @return bool
 */
function ace_quota_update($quota_id, $object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage){
    return ace_db_quota_update($quota_id, $object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage);
}

/**
 * @param $quota_id
 *
 * @return bool
 */
function ace_quota_delete($quota_id){
    return ace_db_quota_delete($quota_id);
}


#=============================================================
#LAB
#=============================================================
/**
 * returns a lab id
 *
 * @api
 *
 * @param   string $lab_name lab name
 *
 * @return  int|bool                lab id | FALSE on error
 */
function ace_lab_get_id_by_name($lab_name)
{
    return ace_db_lab_get_id_by_name($lab_name);
}

/**
 * returns a lab name
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  string|bool             lab name | FALSE on error
 */
function ace_lab_get_name_by_id($lab_id)
{
    return ace_db_lab_get_name_by_id($lab_id);
}

/**
 * returns a lab display name
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  string|bool             lab display name | FALSE on error
 */
function ace_lab_get_display_name_by_id($lab_id)
{
    return ace_db_lab_get_display_name_by_id($lab_id);
}

/**
 * returns an array of group_id associated with the given lab_id
 *
 * @api
 *
 * @param   int $lab_id lab_id
 *
 * @return array|bool               array of group_id | FALSE on error
 */
function ace_lab_get_group_ids($lab_id)
{
    return ace_db_lab_get_group_ids($lab_id);
}

/**
 * returns a lab record
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              array of lab information | FALSE on error
 */
function ace_lab_get_info($lab_id)
{
    return ace_db_lab_get_info($lab_id);
}

/**
 * determines state of lab
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  bool                    active TRUE/FALSE
 */
function ace_lab_is_active($lab_id)
{
    return ace_db_lab_get_state($lab_id);
}

/**
 * @param $lab_id
 *
 * @return bool
 */
function ace_lab_get_last_activated($lab_id){
    return ace_db_lab_get_last_activated($lab_id);
}

/**
 * @param $lab_id
 *
 * @return bool
 */
function ace_lab_is_published($lab_id)
{
    return ace_db_lab_is_published($lab_id);
}

/**
 * @param $lab_id
 *
 * @return mixed
 */
function ace_lab_published_class_count($lab_id)
{
    return ace_db_published_class_count($lab_id);
}

/**
 * returns lab owner's user id
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                owner user id | FALSE on error
 */
function ace_lab_get_user_id($lab_id)
{
    return ace_db_lab_get_user_id($lab_id);
}

/**
 * returns lab host's host id
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                host id | FALSE on error
 */
function ace_lab_get_host_id($lab_id)
{
    return ace_db_lab_get_host_id($lab_id);
}

/**
 * returns a table of networks associated with a lab
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              table of networks | FALSE on error
 */
function ace_lab_get_network_table($lab_id)
{
    return ace_db_lab_get_network_table($lab_id);
}

/**
 * returns a table of volumes associated with a lab
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              table of volumes | FALSE on error
 */
function ace_lab_get_volume_table($lab_id)
{
    return ace_db_lab_get_volume_table($lab_id);
}

/**
 * returns total volume storage reservation for a lab
 *
 * @api
 *
 * @param   int $lab_id lab_id
 *
 * @return float|int                total volume reservation size for the lab (in GiB)
 */
function ace_lab_get_volumes_reservation_size($lab_id)
{
    $lab_volumes_reservation_GiB = 0;
    $volume_table = ace_db_lab_get_volume_table($lab_id);
    if (is_array($volume_table)) {
        foreach ($volume_table as $volume) {
            if ($volume['user_visible'] == 1) {
                if ($volume['base_id'] === NULL) {
                    $volume_reservation_GiB = ($volume['unit'] == 'M') ? round($volume['size'] / 1024, 2) : $volume['size'];
                } else {
                    $base_volume = ace_volume_get_info($volume['base_id']);
                    $base_volume_size_GiB = ($base_volume['unit'] == 'M') ? round($base_volume['size'] / 1024, 2) : $base_volume['size'];
                    $base_volume_size_on_disk_GiB = ($base_volume['unit'] == 'M') ? round($base_volume['size_on_disk'] / 1024, 2) : $base_volume['size_on_disk'];
                    $volume_reservation_GiB = $base_volume_size_GiB - $base_volume_size_on_disk_GiB;
                }
                $lab_volumes_reservation_GiB += $volume_reservation_GiB;
            }
        }
    }
    return $lab_volumes_reservation_GiB;
}

/**
 * returns a table of vm associated with a lab
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              table of vm | FALSE on error
 */
function ace_lab_get_vm_table($lab_id)
{
    return ace_db_lab_get_vm_table($lab_id);
}

/**
 * @param $lab_id
 *
 * @return int
 */
function ace_lab_get_vm_vcpu_count($lab_id) {
    $lab_vm_vcpu_count = 0;
    $vm_table = ace_lab_get_vm_table($lab_id);
    foreach ($vm_table as $vm) {
        $lab_vm_vcpu_count += $vm['vcpu'];
    }
    return $lab_vm_vcpu_count;
}

/**
 * @param $lab_id
 *
 * @return float|int
 */
function ace_lab_get_vm_memory_count($lab_id) {
    $lab_vm_memory_count = 0;
    $vm_table = ace_lab_get_vm_table($lab_id);
    foreach ($vm_table as $vm) {
        if ($vm['unit'] == 'M') {
            $lab_vm_memory_count += ($vm['memory'] / 1024);
        } else { // assume unit is 'G'
            $lab_vm_memory_count += $vm['memory'];
        }
    }
    return $lab_vm_memory_count; // in GiB
}

/**
 * returns quota array for a lab
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              array of quotas | FALSE on error
 */
function ace_lab_get_quota_array($lab_id)
{
    return ace_db_lab_get_quota_array($lab_id);
}

/**
 * @param $lab_id
 *
 * @return bool|int
 */
function ace_lab_get_age($lab_id)
{
    return ace_db_lab_get_age($lab_id);
}

/**
 * creates a lab
 *
 * @api
 *
 * @param   int $user_id user id (will be the lab owner)
 *
 * @return  int|bool                new lab id | FALSE on error
 */
function ace_lab_create($user_id)
{
    $lab_id = ace_db_lab_create($user_id);
    // create tenant NAT router private network (lab's public)
    $private_network_id = ace_db_network_create($lab_id);
    ace_db_network_set_state($private_network_id, TRUE);
    ace_db_network_set_user_visible($private_network_id, TRUE);
    // create tenant NAT router volume
    $volume_base_id = ace_db_volume_get_id_by_name(_TENANT_ROUTER_BASE_VOLUME_NAME_);
    $volume_id = ace_db_volume_create($lab_id, 20, 'G', $volume_base_id);
    ace_db_volume_set_user_visible($volume_id, FALSE);
    // create tenant NAT router VM
    $vm_id = ace_db_vm_create($lab_id, 1, 512, 'M', 'linux');
    ace_db_vm_set_user_visible($vm_id, FALSE);
    ace_db_vm_activate($vm_id);
    // add volume to VM
    $vm_disk_instance = ace_db_vm_attach_disk($vm_id);
    ace_db_vm_disk_assign_volume($vm_id, $vm_disk_instance, $volume_id);
    // add nic and connect it to the public network
    $vm_nic_instance = ace_db_vm_attach_nic($vm_id);
    $public_network_id = ace_db_network_get_id_by_name(_LAB_ROUTER_PUBLIC_NETWORK_NAME_);
    ace_db_vm_nic_connect_network($vm_id, $vm_nic_instance, $public_network_id);
    // add a nic and connect it to the lab's private network (created earlier)
    $vm_nic_instance = ace_db_vm_attach_nic($vm_id);
    ace_db_vm_nic_connect_network($vm_id, $vm_nic_instance, $private_network_id);
    return $lab_id;
}

/**
 * renames a lab
 *
 * @api
 *
 * @param   int    $lab_id           lab id
 * @param   string $lab_display_name lab display name
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_lab_rename($lab_id, $lab_display_name)
{
    return ace_db_lab_rename($lab_id, $lab_display_name);
}

/**
 * duplicates a lab and associates the new lab with a user
 *
 * @api
 *
 * @param   int $from_lab_id lab id
 * @param   int $to_user_id  user id
 *
 * @return  int|bool                new lab id | FALSE on error
 */
function ace_lab_duplicate($from_lab_id, $to_user_id)
{
    $to_lab_id = ace_db_lab_duplicate($from_lab_id, $to_user_id);
    //$to_lab_info = ace_db_lab_get_info($to_lab_id);
    $from_lab_network_table = ace_db_lab_get_network_table($from_lab_id);
    foreach ($from_lab_network_table as $from_network) {
        ace_db_network_duplicate($from_network['id'], $to_lab_id);
    }
    $from_lab_volume_table = ace_db_lab_get_volume_table($from_lab_id);
    foreach ($from_lab_volume_table as $from_volume) {
        $to_volume_id = ace_db_volume_duplicate($from_volume['id'], $to_lab_id);
        if ($from_volume['user_visible'] == 0) {
            ace_db_volume_set_user_visible($to_volume_id, FALSE);
        }
        /*
        # instead, create new to_lab_volumes with from_lab_volumes as backing stores
        $from_volume_info = ace_db_volume_get_info($from_volume['id']);
        $to_volume_id = ace_db_volume_create($to_lab_id, $from_volume_info['size'], $from_volume_info['unit'], $from_volume['id']);
        if (!$from_volume['user_visible']) {
            $db_success = ace_db_volume_set_user_visible($to_volume_id, FALSE);
        } */
    }
    $from_lab_vm_table = ace_db_lab_get_vm_table($from_lab_id);
    foreach ($from_lab_vm_table as $from_vm) {
        ace_db_vm_duplicate($from_vm['id'], $to_lab_id);
    }
    return $to_lab_id;
}

/**
 * updates a lab record
 *
 * @api
 *
 * @param   int $lab_id           lab id
 * @param   int $lab_user_id      user id
 * @param   int $lab_host_id      host id
 * @param   int $lab_name         lab name
 * @param   int $lab_display_name lab display name
 * @param   int $lab_description  lab description
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_lab_update($lab_id, $lab_user_id, $lab_host_id, $lab_name, $lab_display_name, $lab_description)
{
    return ace_db_lab_update($lab_id, $lab_user_id, $lab_host_id, $lab_name, $lab_display_name, $lab_description);
}

/**
 * activates a lab (deploys to a host)
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_lab_activate($lab_id)
{
    # only if user has no active labs (i.e. must deactivate other labs first)
    $user_id = ace_lab_get_user_id($lab_id);
    $user_lab_table = ace_user_get_lab_table($user_id);
    $another_user_lab_is_active = FALSE;
    foreach ($user_lab_table as $lab) {
        if ($lab['state'] == 1) { $another_user_lab_is_active = TRUE; }
    }
    if (!$another_user_lab_is_active) {
        # determine best host, and update lab record
        $host_id = ace_db_get_best_host();
        if (is_numeric($host_id)) {
            # set lab as activated in db
            $db_success = ace_db_lab_set_state($lab_id, TRUE);
            # this lab was inactive, and was successfully activated
            $activated = $db_success;
            # update lab record with host_id
            ace_db_lab_set_host_id($lab_id, $host_id);
            # construct lab on host
            $net_table = ace_db_lab_get_network_table($lab_id);
            foreach ($net_table as $net) {
                # create (define) each network
                ace_virt_network_create($net['id']);
                # start each network if marked active in db
                if ($net['state'] == 1) {
                    ace_virt_network_activate($net['id']);
                }
            }
            $vol_table = ace_db_lab_get_volume_table($lab_id);
            foreach ($vol_table as $vol) {
                # create each volume
                ace_virt_volume_create($vol['id']);
            }
            $vm_table = ace_db_lab_get_vm_table($lab_id);
            foreach ($vm_table as $vm) {
                # create (define) each vm
                ace_virt_vm_create($vm['id']);
                $vm_cdrom_table = ace_db_vm_get_cdrom_table($vm['id']);
                foreach ($vm_cdrom_table as $vm_cdrom) {
                    ace_virt_vm_attach_cdrom($vm['id'], $vm_cdrom['instance']);
                    if ($vm_cdrom['volume_id'] != NULL) {
                        ace_virt_vm_cdrom_insert_media($vm['id'], $vm_cdrom['instance'], $vm_cdrom['volume_id']);
                    }
                }
                $vm_disk_table = ace_db_vm_get_disk_table($vm['id']);
                foreach ($vm_disk_table as $vm_disk) {
                    ace_virt_vm_attach_disk($vm['id'], $vm_disk['instance'], $vm_disk['volume_id']);
                }
                $vm_nic_table = ace_db_vm_get_nic_table($vm['id']);
                foreach ($vm_nic_table as $vm_nic) {
                    $vm_nic_mac_address = ace_gen_convert_int2mac($vm_nic['mac_index']);
                    ace_virt_vm_attach_nic($vm['id'], $vm_nic['instance'], $vm_nic_mac_address);
                    ace_virt_vm_nic_connect_network($vm['id'], $vm_nic['instance'], $vm_nic['network_id']);
                }
                # set each vm state
                if ($vm['state'] == 1) {
                    ace_vm_activate($vm['id']);
                }
            }
        } else {
            $activated = FALSE;
        }
    } else {
        $activated = FALSE;
    }
    return $activated;
}

/**
 * de-activates a lab (remove lab from host)
 *
 * @api
 *
 * @param   int $lab_id lab_id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_lab_deactivate($lab_id)
{
    # more than one way to skin this cat ...
    # I could instead, simply search the host's list of objects for lab_id prefix, and stop and remove those.
    # it might be more inclusive of 'lost' or 'disconnected' virtual objects
    # OR
    # I could simply use the method above as a check against the success of the method below
    # deconstruct lab on host if it is currently active
    $lab_state = ace_db_lab_get_state($lab_id);
    # only act if this lab is active
    if ($lab_state) {
        # get list of lab's vms from db
        $vm_table = ace_db_lab_get_vm_table($lab_id);
        if (is_array($vm_table)) {
            foreach ($vm_table as $vm) {
                # if the virtual object exists
                if (ace_virt_vm_exists($vm['id'])){
                    # if the vm is running, stop it
                    if (ace_virt_vm_get_state($vm['id']) == 1) {
                        # stop (destroy) each vm
                        ace_virt_vm_deactivate($vm['id']);
                    }
                    # delete (undefine) each vm
                    ace_virt_vm_delete($vm['id']);
                }
            }
        }
        # get list of volumes
        $vol_table = ace_db_lab_get_volume_table($lab_id);
        if (is_array($vol_table)) {
            foreach ($vol_table as $vol) {
                # delete each volume
                ace_virt_volume_delete($vol['id']);
            }
        }
        # get list of networks
        $net_table = ace_db_lab_get_network_table($lab_id);
        foreach ($net_table as $net) {
            # stop (destroy) each network
            ace_virt_network_deactivate($net['id']);
            # delete (undefine) each network
            ace_virt_network_delete($net['id']);
        }
        # set lab as deactivated in db
        ace_db_lab_set_state($lab_id, FALSE);
        # set host_id to null in db
        $db_success = ace_db_lab_set_host_id($lab_id, NULL);
        # this lab was active, and was successfully deactivated
        $deactivated = $db_success;
    } else {
        # this lab wasn't active
        $deactivated = FALSE;
    }
    return $deactivated;
}

/**
 * deletes a lab
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_lab_delete($lab_id)
{
    if (ace_lab_is_active($lab_id)) {
        ace_lab_deactivate($lab_id);
    }

    $lab_group_ids = ace_lab_get_group_ids($lab_id);
    if (is_array($lab_group_ids)) {
        foreach ($lab_group_ids as $group_id) {
            ace_group_remove_lab($group_id, $lab_id);
        }
    }

    $vm_table = ace_db_lab_get_vm_table($lab_id);
    foreach ($vm_table as $vm) {
        $cdrom_table = ace_db_vm_get_cdrom_table($vm['id']);
        foreach ($cdrom_table as $cdrom) {
            ace_db_vm_detach_cdrom($cdrom['vm_id'], $cdrom['instance']);
        }
        $disk_table = ace_db_vm_get_disk_table($vm['id']);
        foreach ($disk_table as $disk) {
            ace_db_vm_detach_disk($disk['vm_id'], $disk['instance']);
        }
        $nic_table = ace_db_vm_get_nic_table($vm['id']);
        foreach ($nic_table as $nic) {
            ace_db_vm_detach_nic($nic['vm_id'], $nic['instance']);
        }
        ace_db_vm_delete($vm['id']);
    }
    $volume_table = ace_db_lab_get_volume_table($lab_id);
    foreach ($volume_table as $volume) {
        ace_db_volume_delete($volume['id']);
    }
    $network_table = ace_db_lab_get_network_table($lab_id);
    foreach ($network_table as $network) {
        ace_db_network_delete($network['id']);
    }
    return ace_db_lab_delete($lab_id);
}

/**
 * returns a list of virt network ids associated with a live lab
 *
 * @param   int         $lab_id         lab id
 *
 * @return array|bool           an array of virt_network_id or FALSE on error
 */
function ace_lab_get_virt_network_list($lab_id)
{
    $formatted_lab_id = str_pad($lab_id,5,'0',STR_PAD_LEFT);
    $lab_network_list = array();
    if (ace_lab_is_active($lab_id)) {
        $host_id = ace_lab_get_host_id($lab_id);
        $virt_network_list = ace_virt('get_network_list', $host_id);
        if (is_array($virt_network_list)) {
            foreach ($virt_network_list as $virt_network_name) {
                if (substr($virt_network_name,0,5) == $formatted_lab_id) {
                    $lab_network_list[] = $virt_network_name;
                }
            }
            if (count($lab_network_list) > 0) {
                return $lab_network_list;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

/**
 * returns a list of virt volume ids associated with a live lab
 *
 * @param   int         $lab_id         lab id
 *
 * @return array|bool           an array of virt_volume_name or FALSE on error
 */
function ace_lab_get_virt_volume_list($lab_id)
{
    $formatted_lab_id = str_pad($lab_id,5,'0',STR_PAD_LEFT);
    $lab_volume_list = array();
    if (ace_lab_is_active($lab_id)) {
        $host_id = ace_lab_get_host_id($lab_id);
        $virt_volume_list = ace_virt('get_volume_list', $host_id);
        if (is_array($virt_volume_list)) {
            foreach ($virt_volume_list as $virt_volume_name) {
                if (substr($virt_volume_name,0,5) == $formatted_lab_id) {
                    $lab_volume_list[] = $virt_volume_name;
                }
            }
            if (count($lab_volume_list) > 0) {
                return $lab_volume_list;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

/**
 * returns a list of virt vm ids associated with a live lab
 *
 * @param   int         $lab_id         lab id
 *
 * @return array|bool           an array of virt_vm_id or FALSE on error
 */
function ace_lab_get_virt_vm_list($lab_id)
{
    $formatted_lab_id = str_pad($lab_id,5,'0',STR_PAD_LEFT);
    $lab_vm_list = array();
    if (ace_lab_is_active($lab_id)) {
        $host_id = ace_lab_get_host_id($lab_id);
        $virt_vm_list = ace_virt('get_vm_list', $host_id);
        if (is_array($virt_vm_list)) {
            foreach ($virt_vm_list as $virt_vm_name) {
                if (substr($virt_vm_name,0,5) == $formatted_lab_id) {
                    $lab_vm_list[] = $virt_vm_name;
                }
            }
            if (count($lab_vm_list) > 0) {
                return $lab_vm_list;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}


#=============================================================
#NETWORK
#=============================================================
/**
 * returns a network id
 *
 * @api
 *
 * @param   string $network_name network name
 *
 * @return  int|bool                network id | FALSE on error
 */
function ace_network_get_id_by_name($network_name)
{
    return ace_db_network_get_id_by_name($network_name);
}

/**
 * returns a network'a name
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  string|bool                 network name | FALSE on error
 */
function ace_network_get_name_by_id($network_id)
{
    return ace_db_network_get_name_by_id($network_id);
}

/**
 * returns a network's display name
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  string|bool                 network display name | FALSE on error
 */
function ace_network_get_display_name_by_id($network_id)
{
    return ace_db_network_get_display_name_by_id($network_id);
}

/**
 * returns a network information record
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  array|bool              network information | FALSE on error
 */
function ace_network_get_info($network_id)
{
    return ace_db_network_get_info($network_id);
}

/**
 * determines a network's state
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  bool                    active = TRUE | inactive = FALSE
 */
function ace_network_is_active($network_id)
{
    return ace_db_network_get_state($network_id);
}

/**
 * returns the lab id associated with a network
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  int|bool                lab id | FALSE on error
 */
function ace_network_get_lab_id($network_id)
{
    return ace_db_network_get_lab_id($network_id);
}

/**
 * returns a network's virt id
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  string|bool             network virt id | FALSE on error
 */
function ace_network_get_virt_id($network_id)
{
    return ace_db_network_get_virt_id($network_id);
}

/**
 * creates a network
 *
 * @api
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                new network id | FALSE on error
 */
function ace_network_create($lab_id)
{
    if ($network_id = ace_db_network_create($lab_id)) {
        $return = $network_id;
        if (ace_lab_is_active($lab_id)) {
            $virt_success = ace_virt_network_create($network_id);
            if ($virt_success) {
                $return = $network_id;
            } else {
                $return = FALSE;
            }
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * sets a network's user visibility flag
 *
 * @api
 *
 * @param   int  $network_id   network id
 * @param   bool $user_visible visibility flag
 *
 * @return  bool               on success TRUE/FALSE
 */
function ace_network_set_user_visible($network_id, $user_visible)
{
    return ace_db_network_set_user_visible($network_id, $user_visible);
}

/**
 * renames a network's display name
 *
 * @api
 *
 * @param   int    $network_id       network id
 * @param   string $network_new_name display name
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_network_rename($network_id, $network_new_name)
{
    return ace_db_network_rename($network_id, $network_new_name);
}

/**
 * activates a network
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_network_activate($network_id)
{
    $lab_id = ace_network_get_lab_id($network_id);
    $lab_is_active = ace_lab_is_active($lab_id);
    $virt_success = FALSE;
    if ($lab_is_active) {
        $virt_success = ace_virt_network_activate($network_id);
        $lab_vm_table = ace_lab_get_vm_table($lab_id);
        if (is_array($lab_vm_table)) {
            foreach ($lab_vm_table as $lab_vm) {
                $vm_nic_array = ace_vm_get_nic_table($lab_vm['id']);
                if (is_array($vm_nic_array)) {
                    foreach ($vm_nic_array as $vm_nic) {
                        if ($vm_nic['network_id'] == $network_id) {
                            ace_virt_vm_nic_link_up($lab_vm['id'], $vm_nic['instance']);
                        }
                    }
                }
            }
        }
    }
    if (!$lab_is_active || ($lab_is_active && $virt_success)) {
        $success = ace_db_network_set_state($network_id, TRUE);
        $return = $success;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * de-activates a network
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_network_deactivate($network_id)
{
    $lab_id = ace_network_get_lab_id($network_id);
    $lab_is_active = ace_lab_is_active($lab_id);
    $virt_success = FALSE;
    if ($lab_is_active) {
        $virt_success = ace_virt_network_deactivate($network_id);
        $lab_vm_table = ace_lab_get_vm_table($lab_id);
        if (is_array($lab_vm_table)) {
            foreach ($lab_vm_table as $lab_vm) {
                $vm_nic_array = ace_vm_get_nic_table($lab_vm['id']);
                if (is_array($vm_nic_array)) {
                    foreach ($vm_nic_array as $vm_nic) {
                        if ($vm_nic['network_id'] == $network_id) {
                            ace_virt_vm_nic_link_down($lab_vm['id'], $vm_nic['instance']);
                        }
                    }
                }
            }
        }
    }
    if (!$lab_is_active || ($lab_is_active && $virt_success)) {
        $success = ace_db_network_set_state($network_id, FALSE);
        $return = $success;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * deletes a network
 *
 * @api
 *
 * @param   int $network_id network id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_network_delete($network_id)
{
    $lab_id = ace_network_get_lab_id($network_id);
    if (ace_lab_is_active($lab_id)) {
        # deactivate and delete virtual network from host
        if (ace_network_is_active($network_id)) {
            ace_network_deactivate($network_id);
        }
        $virt_success = ace_virt_network_delete($network_id);
        if ($virt_success) {
            $return = ace_db_network_delete($network_id);
        } else {
            $return = FALSE;
        }
    } else {
        $return = ace_db_network_delete($network_id);
    }
    return $return;
}

/**
 * creates a virtual network
 *
 * @param   int $network_id network id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_virt_network_create($network_id)
{
    $network_info = ace_network_get_info($network_id);
    $network_lab_id = $network_info['lab_id'];
    $lab_host_id = ace_lab_get_host_id($network_lab_id);
    $network_virt_id = $network_info['virt_id'];
    return ace_virt('create_network', $lab_host_id, $network_virt_id);
}

/**
 * updates a virtual network
 *
 * @param   int $network_id network_id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_virt_network_update($network_id)
{
    $network_info = ace_network_get_info($network_id);
    $network_lab_id = $network_info['lab_id'];
    $lab_host_id = ace_lab_get_host_id($network_lab_id);
    $network_virt_id = $network_info['virt_id'];
    return ace_virt('update_network', $lab_host_id, $network_virt_id);
}

/**
 * determines state of a virtual network
 *
 * @param   int $network_id network_id
 *
 * @return  int|bool            0 = inactive, 1 = active, FALSE on error
 */
function ace_virt_network_get_state($network_id)
{
    $lab_id = ace_network_get_lab_id($network_id);
    if (ace_lab_is_active($lab_id)) {
        $host_id = ace_lab_get_host_id($lab_id);
        $virt_network_id = ace_db_network_get_virt_id($network_id);
        $virt_network_state = ace_virt('get_network_state', $host_id, $virt_network_id);
        $return = $virt_network_state;
    } else {
        $return = FALSE;
    }
    return $return;
}

/*
function ace_virt_network_set_state($network_id,$active) {
    echo ace_gen_debug_function_IN(__FUNCTION__,func_get_args());
    $lab_id = ace_network_get_lab_id($network_id);
    $virt_network_id = ace_db_network_get_virt_id($network_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $virt_network_state = ace_virt('set_network_state',$host_id,$virt_network_id,$active);
    $return = $virt_network_state;
    echo ace_gen_debug_function_OUT(__FUNCTION__,$return);
    return $return;
}
*/
/**
 * activates a virtual network
 *
 * @param   int $network_id network_id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_virt_network_activate($network_id)
{
    $network_info = ace_network_get_info($network_id);
    $network_lab_id = $network_info['lab_id'];
    $lab_host_id = ace_lab_get_host_id($network_lab_id);
    $network_virt_id = $network_info['virt_id'];
    # this function not available due to bug, see comments in deeper function
    #$virt_network_success = ace_virt('set_network_state',$lab_host_id,$network_virt_id,true);
    return ace_virt('activate_network', $lab_host_id, $network_virt_id);
}

/**
 * de-activates a virtual network
 *
 * @param   int $network_id network_id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_virt_network_deactivate($network_id)
{
    $network_info = ace_network_get_info($network_id);
    $network_lab_id = $network_info['lab_id'];
    $lab_host_id = ace_lab_get_host_id($network_lab_id);
    $network_virt_id = $network_info['virt_id'];
    # this function not available due to bug, see comments in deeper function
    #$virt_network_success = ace_virt('set_network_state',$lab_host_id,$network_virt_id,false);
    return ace_virt('deactivate_network', $lab_host_id, $network_virt_id);
}

/**
 * deletes a virtual network
 *
 * @param   int $network_id network_id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_virt_network_delete($network_id)
{
    $network_info = ace_network_get_info($network_id);
    $network_lab_id = $network_info['lab_id'];
    $lab_host_id = ace_lab_get_host_id($network_lab_id);
    $network_virt_id = $network_info['virt_id'];
    return ace_virt('delete_network', $lab_host_id, $network_virt_id);
}

#=============================================================
#VOLUME
#=============================================================
/**
 * returns a volume's id
 *
 * @api
 *
 * @param   string $volume_name network name
 *
 * @return  int|bool            network id | FALSE on error
 */
function ace_volume_get_id_by_name($volume_name)
{
    return ace_db_volume_get_id_by_name($volume_name);
}

/**
 * returns a volume's name
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return  string|bool         volume name | FALSE on error
 */
function ace_volume_get_name_by_id($volume_id)
{
    return ace_db_volume_get_name_by_id($volume_id);
}

/**
 * returns a volume's display name
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return  string|bool         volume display name | FALSE on error
 */
function ace_volume_get_display_name_by_id($volume_id)
{
    return ace_db_volume_get_display_name_by_id($volume_id);
}

/**
 * returns a volume information record
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return array|bool           volume information | FALSE on error
 */
function ace_volume_get_info($volume_id)
{
    return ace_db_volume_get_info($volume_id);
}

/**
 * returns a volume's vm assignments
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return  array|bool          volume assignments | FALSE on error
 */
function ace_volume_get_vm_assignments($volume_id)
{
    return ace_db_volume_get_vm_assignments($volume_id);
}

/**
 * determine active state of a volume
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return  bool                TRUE = active | FALSE = inactive
 */
function ace_volume_is_active($volume_id)
{
    return ace_db_volume_get_state($volume_id);
}

/**
 * returns a volume's lab id
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return  int|bool            lab id | FALSE on error
 */
function ace_volume_get_lab_id($volume_id)
{
    return ace_db_volume_get_lab_id($volume_id);
}

/**
 * returns a volume's virt id
 *
 * @param   int $volume_id volume id
 *
 * @return  string|bool         virt id | FALSE on error
 */
function ace_volume_get_virt_id($volume_id)
{
    return ace_db_volume_get_virt_id($volume_id);
}

/**
 * creates a volume
 *
 * @api
 *
 * @param   int $lab_id         lab id
 * @param   int $volume_size    volume size (measured in units)
 * @param   int $volume_unit    "M" | "G"
 * @param   int $volume_base_id volume id
 *
 * @return  int|bool            new volume id | FALSE on error
 */
function ace_volume_create($lab_id, $volume_size, $volume_unit, $volume_base_id)
{
    if ($volume_id = ace_db_volume_create($lab_id, $volume_size, $volume_unit, $volume_base_id)) {
        $return = $volume_id;
        if (ace_lab_is_active($lab_id)) {
            $virt_success = ace_virt_volume_create($volume_id);
            if ($virt_success) {
                $return = $volume_id;
            } else {
                $return = FALSE;
            }
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * updates a volume's display name
 *
 * @api
 *
 * @param   int    $volume_id           volume id
 * @param   string $volume_display_name volume display name
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_volume_update($volume_id, $volume_display_name)
{
    return ace_db_volume_update($volume_id, $volume_display_name);
}

/**
 * sets a volume's user visibility flag
 *
 * @api
 *
 * @param   int  $volume_id    volume id
 * @param   bool $user_visible visibility state flag
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_volume_set_user_visible($volume_id, $user_visible)
{
    return ace_db_volume_set_user_visible($volume_id, $user_visible);
}

/*
function ace_volume_activate($volume_id) {
	return NULL;
}
*/

/*
function ace_volume_deactivate($volume_id) {
	return NULL;
}
*/

/**
 * deletes a volume
 *
 * @api
 *
 * @param   int $volume_id volume id
 *
 * @return bool                 on success TRUE/FALSE
 */
function ace_volume_delete($volume_id)
{
    $lab_id = ace_db_volume_get_lab_id($volume_id);
    if (ace_lab_is_active($lab_id)) {
        $virt_success = ace_virt_volume_delete($volume_id);
        if ($virt_success) {
            $success = ace_db_volume_delete($volume_id);
            if ($success) {
                $return = TRUE;
            } else {
                $return = FALSE;
            }
        } else {
            $return = FALSE;
        }
    } else {
        $success = ace_db_volume_delete($volume_id);
        if ($success) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
    }
    return $return;
}

/**
 * creates a virtual volume
 *
 * @param   int $volume_id volume id
 *
 * @return  bool                TRUE on success | FALSE on error
 */
function ace_virt_volume_create($volume_id)
{
    $volume_info = ace_volume_get_info($volume_id);
    $lab_id = $volume_info['lab_id'];
    $host_id = ace_lab_get_host_id($lab_id);
    $virt_id = $volume_info['virt_id'];
    $size = $volume_info['size'];
    $unit = $volume_info['unit'];
    $base_id = $volume_info['base_id'];
    $base_virt_id = ace_volume_get_virt_id($base_id);
    //$state = $volume_info['state'];
    return ace_virt('create_volume', $host_id, $virt_id, $size, $unit, $base_virt_id);
}

/*		function ace_virt_volume_update($volume_id) {
			$volume_info = ace_network_get_info($volume_id);
			$volume_lab_id = $volume_info['lab_id'];
			$lab_host_id = ace_lab_get_host_id($volume_lab_id);
			$volume_virt_id = $volume_info['virt_id'];
            return ace_virt('update_volume',$lab_host_id,$volume_virt_id);
		}*/

/**
 * delete a virtual volume
 *
 * @param   int $volume_id volume id
 *
 * @return  bool                TRUE on success | FALSE on error
 */
function ace_virt_volume_delete($volume_id)
{
    $volume_info = ace_volume_get_info($volume_id);
    $volume_lab_id = $volume_info['lab_id'];
    $volume_virt_id = $volume_info['virt_id'];
    $volume_host_id = ace_lab_get_host_id($volume_lab_id);
    return ace_virt('delete_volume', $volume_host_id, $volume_virt_id);
}

#=============================================================
#VM
#=============================================================
/**
 * returns a vm's id
 *
 * @api
 *
 * @param   string $vm_name vm name
 *
 * @return  int|bool            vm id | FALSE on error
 */
function ace_vm_get_id_by_name($vm_name)
{
    return ace_db_vm_get_id_by_name($vm_name);
}

/**
 * returns a vm's name
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  string|bool         vm name | FALSE on error
 */
function ace_vm_get_name_by_id($vm_id)
{
    return ace_db_vm_get_name_by_id($vm_id);
}

/**
 * returns a vm's display name
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  string|bool         vm display name | FALSE on error
 */
function ace_vm_get_display_name_by_id($vm_id)
{
    return ace_db_vm_get_display_name_by_id($vm_id);
}

/**
 * returns a vm information record
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          vm information | FALSE on error
 */
function ace_vm_get_info($vm_id)
{
    return ace_db_vm_get_info($vm_id);
}

/**
 * determines a vm's state
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                TRUE = active | FALSE = inactive
 */
function ace_vm_is_active($vm_id)
{
    return ace_db_vm_get_state($vm_id);
}

/**
 * determines a vm's virt state
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                TRUE = active | FALSE = inactive
 */
function ace_vm_get_virt_state($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_state = ace_db_lab_get_state($lab_id);
    if ($lab_state) {
        $virt_vm_state = ace_virt_vm_get_state($vm_id);
    } else {
        $virt_vm_state = FALSE;
    }
    return $virt_vm_state;
}

/**
 * returns a vm's lab id
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            lab id | FALSE on error
 */
function ace_vm_get_lab_id($vm_id)
{
    return ace_db_vm_get_lab_id($vm_id);
}

/**
 * returns a vm's virt id
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  string|bool         vm virt id | FALSE on error
 */
function ace_vm_get_virt_id($vm_id)
{
    return ace_db_vm_get_virt_id($vm_id);
}

/**
 * returns a vm's cdrom device table
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          table of vm cdrom devices | FALSE on error
 */
function ace_vm_get_cdrom_table($vm_id)
{
    return ace_db_vm_get_cdrom_table($vm_id);
}

/**
 * returns a vm's disk device table
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          table of vm disk devices | FALSE on error
 */
function ace_vm_get_disk_table($vm_id)
{
    return ace_db_vm_get_disk_table($vm_id);
}

/**
 * returns a vm's nic device table
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          vm nic table | FALSE on error
 */
function ace_vm_get_nic_table($vm_id)
{
    return ace_db_vm_get_nic_table($vm_id);
}

/**
 * returns a vm's remote console information
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return array                vm remote console info
 */
function ace_vm_get_console_info($vm_id)
{
    return ace_virt_vm_get_console_info($vm_id);
}

/**
 * grabs and stores a vm screenshot, returns the filename
 *
 * @api
 *
 * @param   int $vm_id     vm id
 * @param   int $max_width width of screenshot (height determined by ratio)
 *
 * @return  string              filename of JPG
 */
function ace_vm_screenshot($vm_id, $max_width)
{
    return ace_virt_vm_screenshot($vm_id, $max_width);
}

/**
 * returns a vm's guacamole console url
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return string|void           guacamole console url
 */
function ace_vm_get_console_url($vm_id)
{
    return ace_virt_vm_get_console_url($vm_id);
}

/**
 * returns a vm's snapshot list
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return array                vm snapshot list
 */
function ace_vm_get_snapshot_list($vm_id)
{
    return ace_virt_vm_get_snapshot_list($vm_id);
}

/**
 * creates a vm
 *
 * @api
 *
 * @param   int    $lab_id    lab id
 * @param   int    $vm_vcpu   number of vcpu
 * @param   int    $vm_memory amount of memory (measured in units)
 * @param   string $vm_unit   "M" | "G"
 * @param   string $vm_profile "linux" | "w8" | et al
 *
 * @return int|bool             vm id | FALSE on error
 */
function ace_vm_create($lab_id, $vm_vcpu, $vm_memory, $vm_unit, $vm_profile)
{
    if ($vm_id = ace_db_vm_create($lab_id, $vm_vcpu, $vm_memory, $vm_unit, $vm_profile)) {
        $return = $vm_id;
        if (ace_lab_is_active($lab_id)) {
            $virt_success = ace_virt_vm_create($vm_id);
            if ($virt_success) {
                $return = $vm_id;
            } else {
                $return = FALSE;
            }
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * updates a vm
 *
 * @api
 *
 * @param   int    $vm_id           vm id
 * @param   string $vm_display_name vm display name
 * @param   int    $vm_vcpu         number of vcpu
 * @param   int    $vm_memory       amount of memory (measured in units)
 * @param   string $vm_unit         "M" | "G"
 *
 * @return bool
 */
function ace_vm_update($vm_id, $vm_display_name, $vm_vcpu, $vm_memory, $vm_unit)
{
    #do something here to avoid trying to update a LIVE vm
    return ace_db_vm_update($vm_id, $vm_display_name, $vm_vcpu, $vm_memory, $vm_unit);
}

/**
 * renames a vm
 *
 * @api
 *
 * @param   int    $vm_id       vm id
 * @param   string $vm_new_name vm display name
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_rename($vm_id, $vm_new_name)
{
    return ace_db_vm_rename($vm_id, $vm_new_name);
}

/**
 * set user visibility flag for a given vm id
 *
 * @api
 *
 * @param   int  $vm_id        vm id
 * @param   bool $user_visible visibility state flag
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_set_user_visible($vm_id, $user_visible)
{
    return ace_db_vm_set_user_visible($vm_id, $user_visible);
}

/**
 * start a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_activate($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $network_ids_to_toggle = array();
        $vm_nics = ace_vm_get_nic_table($vm_id);
        if (is_array($vm_nics)) {
            foreach ($vm_nics as $vm_nic) {
                if (!ace_network_is_active($vm_nic['network_id'])) {
                    $network_ids_to_toggle[] = $vm_nic['network_id'];
                }
            }
        }
        if (is_array($network_ids_to_toggle)) {
            # start all associated inactive networks
            foreach ($network_ids_to_toggle as $network_id) {
                ace_network_activate($network_id);
            }
        }
        # start the vm
        if (ace_virt_vm_activate($vm_id)) {
            $db_success = ace_db_vm_activate($vm_id);
            $return = $db_success;
        } else {
            $return = FALSE;
        }
        if (is_array($network_ids_to_toggle)) {
            # stop all associated inactive networks
            foreach ($network_ids_to_toggle as $network_id) {
                ace_network_deactivate($network_id);
            }
        }
    } else {
        $db_success = ace_db_vm_activate($vm_id);
        $return = $db_success;
    }
    return $return;
}

/**
 * stop a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_deactivate($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        //if (ace_virt_vm_deactivate($vm_id)) {
        ace_virt_vm_deactivate($vm_id);
            $db_success = ace_db_vm_deactivate($vm_id);
            $return = $db_success;
        //} else {
        //    $return = FALSE;
        //}
    } else {
        $db_success = ace_db_vm_deactivate($vm_id);
        $return = $db_success;
    }
    return $return;
}

/**
 * send an ACPI shutdown signal to a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_shutdown($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    //$db_vm_state = ace_db_vm_get_state($vm_id);
    $virt_success = FALSE;
    if ($lab_active) {
        if (ace_virt_vm_get_state($vm_id)) {
            ace_virt_vm_shutdown($vm_id);
            $max_seconds_to_wait = 10;
            $interval = 2;
            $count = 0;
            while ((ace_virt_vm_get_state($vm_id)) && ($count < $max_seconds_to_wait)) {
                sleep($interval);
                $count += $interval;
            }
            if (ace_virt_vm_get_state($vm_id)) {
                //$virt_success = FALSE;
                ace_virt_vm_deactivate($vm_id);
                ace_db_vm_deactivate($vm_id);
            } else {
                $virt_success = TRUE;
                ace_db_vm_deactivate($vm_id);
            }
        }
    }
    //$success = ace_db_vm_deactivate($vm_id);
    return $virt_success;
}

/**
 * send a soft reset signal (Ctrl-Alt-Del) to a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_soft_reset($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    $virt_success = FALSE;
    if ($lab_active) {
        $virt_success = ace_virt_vm_soft_reset($vm_id);
    }
    return $virt_success;
}

/**
 * adds a cdrom device to a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_attach_cdrom($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_vm_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
    } else {
        $virt_vm_active = FALSE;
    }
    if ($virt_vm_active) {
        $success = FALSE;
    } else {
        if ($vm_cdrom_instance = ace_db_vm_attach_cdrom($vm_id)) {
            if ($lab_active) {
                ace_virt_vm_attach_cdrom($vm_id, $vm_cdrom_instance);
            }
            $success = $vm_cdrom_instance;
        } else {
            $success = FALSE;
        }
    }
    return $success;
}

/**
 * removes a cdrom device from a vm
 *
 * @api
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_detach_cdrom($vm_id, $vm_cdrom_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_vm_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
    } else {
        $virt_vm_active = FALSE;
    }
    if ($virt_vm_active) {
        $success = FALSE;
    } else {
        if ($lab_active) {
            ace_virt_vm_detach_cdrom($vm_id, $vm_cdrom_instance);
        }
        $success = ace_db_vm_detach_cdrom($vm_id, $vm_cdrom_instance);
    }
    return $success;
}

/**
 * associates a volume with a vm cdrom device
 *
 * @api
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 * @param   int $volume_id         volume id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $volume_id)
{
    //$cdrom = ace_db_vm_cdrom_get_info($vm_cdrom_id);
    //$vm_id = $cdrom['vm_id'];
    //$vm_cdrom_instance = $cdrom['instance'];
    $virt_success = FALSE;
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_success = ace_virt_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $volume_id);
    }
    if (!$lab_active || ($virt_success)) {
        $db_success = ace_db_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $volume_id);
        if ($db_success) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * removes a volume association from a vm cdrom device
 *
 * @api
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_cdrom_eject_media($vm_id, $vm_cdrom_instance)
{
    //$cdrom = ace_db_vm_cdrom_get_info($vm_cdrom_id);
    //$vm_id = $cdrom['vm_id'];
    //$vm_cdrom_instance = $cdrom['instance'];
    $virt_success = FALSE;
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_success = ace_virt_vm_cdrom_eject_media($vm_id, $vm_cdrom_instance);
    }
    if (!$lab_active || ($virt_success)) {
        $db_success = ace_db_vm_cdrom_eject_media($vm_id, $vm_cdrom_instance);
        if ($db_success) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * adds a vm disk device with volume association to a vm
 *
 * @api
 *
 * @param   int $vm_id     vm id
 * @param   int $volume_id volume id
 *
 * @return  int|bool                vm disk instance | FALSE on error
 */
function ace_vm_attach_disk($vm_id, $volume_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_vm_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
    } else {
        $virt_vm_active = FALSE;
    }
    if ($virt_vm_active) {
        $success = FALSE;
    } else {
        if ($vm_disk_instance = ace_db_vm_attach_disk($vm_id)) {
            ace_db_vm_disk_assign_volume($vm_id, $vm_disk_instance, $volume_id);
            if ($lab_active) {
                ace_virt_vm_attach_disk($vm_id, $vm_disk_instance, $volume_id);
            }
            $success = $vm_disk_instance;
        } else {
            $success = FALSE;
        }
    }
    return $success;
}

/**
 * removes a vm disk device from a vm
 *
 * @api
 *
 * @param   int $vm_id            vm id
 * @param   int $vm_disk_instance vm disk instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_detach_disk($vm_id, $vm_disk_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_vm_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
    } else {
        $virt_vm_active = FALSE;
    }
    if ($virt_vm_active) {
        $success = FALSE;
    } else {
        if ($lab_active) {
            ace_virt_vm_detach_disk($vm_id, $vm_disk_instance);
        }
        ace_db_vm_disk_unassign_volume($vm_id, $vm_disk_instance);
        $success = ace_db_vm_detach_disk($vm_id, $vm_disk_instance);
    }
    return $success;
}

/* function ace_vm_disk_assign_volume($vm_id,$vm_disk_instance,$volume_id) {
 	
}
*/
/* function ace_vm_disk_unassign_volume($vm_id,$vm_disk_instance) {
	
}
*/
/**
 * adds a vm nic device to a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            new vm nic instance | FALSE on error
 */
function ace_vm_attach_nic($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_vm_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
    } else {
        $virt_vm_active = FALSE;
    }
    if ($virt_vm_active) {
        $return = FALSE;
    } else {
        if ($vm_nic_instance = ace_db_vm_attach_nic($vm_id)) {
            if ($lab_active) {
                $vm_nic_mac_address = ace_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
                ace_virt_vm_attach_nic($vm_id, $vm_nic_instance, $vm_nic_mac_address);
            }
            $return = $vm_nic_instance;
        } else {
            $return = FALSE;
        }
    }
    return $return;
}

/**
 * removes a vm nic device from a vm
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_detach_nic($vm_id, $vm_nic_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    if ($lab_active) {
        $virt_vm_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
    } else {
        $virt_vm_active = FALSE;
    }
    if ($virt_vm_active) {
        $success = FALSE;
    } else {
        if ($lab_active) {
            ace_virt_vm_detach_nic($vm_id, $vm_nic_instance);
        }
        $db_success = ace_db_vm_detach_nic($vm_id, $vm_nic_instance);
        $success = $db_success;
    }
    return $success;
}

/**
 * returns a mac address assigned to a vm nic device
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  string|bool         vm nic mac address | FALSE on error
 */
function ace_vm_nic_get_mac_address($vm_id, $vm_nic_instance)
{
    return ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
}

/**
 * returns a network id associated with a vm nic device
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  int|bool            network id | FALSE on error
 */
function ace_vm_nic_get_network_id($vm_id, $vm_nic_instance)
{
    return ace_db_vm_nic_get_network_id($vm_id, $vm_nic_instance);
}

/**
 * connect a network to a vm nic device
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 * @param   int $network_id      network id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_nic_connect_network($vm_id, $vm_nic_instance, $network_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_db_lab_get_state($lab_id);
    $virt_success = FALSE;
    if ($lab_active) {
        $virt_success = ace_virt_vm_nic_connect_network($vm_id, $vm_nic_instance, $network_id);
    }
    if (!$lab_active || ($lab_active && $virt_success)) {
        $db_success = ace_db_vm_nic_connect_network($vm_id, $vm_nic_instance, $network_id);
        if ($db_success) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * disconnects a network from a vm nic device
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_vm_nic_disconnect($vm_id, $vm_nic_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $network_id = ace_db_vm_nic_get_network_id($vm_id, $vm_nic_instance);
    $lab_active = ace_db_lab_get_state($lab_id);
    $virt_success = FALSE;
    if ($lab_active) {
        $virt_success = ace_virt_vm_nic_disconnect($vm_id, $vm_nic_instance, $network_id);
    }
    if (!$lab_active || ($lab_active && $virt_success)) {
        $db_success = ace_db_vm_nic_disconnect($vm_id, $vm_nic_instance);
        if ($db_success) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * returns vm nic device link state
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    TRUE = up | FALSE = down
 */
function ace_vm_nic_get_link_state($vm_id, $vm_nic_instance)
{
    return ace_virt_vm_nic_get_link_state($vm_id, $vm_nic_instance);
}

/**
 * sets a vm nic device link state up
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_nic_link_up($vm_id, $vm_nic_instance)
{
    return ace_virt_vm_nic_link_up($vm_id, $vm_nic_instance);
}

/**
 * sets a vm nic device link state down
 *
 * @api
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_nic_link_down($vm_id, $vm_nic_instance)
{
    return ace_virt_vm_nic_link_down($vm_id, $vm_nic_instance);
}

/**
 * create a vm snapshot
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_create_snapshot($vm_id)
{
    return ace_virt_vm_create_snapshot($vm_id);
}

/**
 * reverts a vm to a snapshot
 *
 * @api
 *
 * @param   int $vm_id                vm id
 * @param   int $vm_snapshot_instance vm snapshot instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_snapshot_revert($vm_id, $vm_snapshot_instance)
{
    return ace_virt_vm_snapshot_revert($vm_id, $vm_snapshot_instance);
}

/**
 * delete a vm snapshot
 *
 * @api
 *
 * @param   int $vm_id                vm id
 * @param   int $vm_snapshot_instance vm snapshot instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_snapshot_delete($vm_id, $vm_snapshot_instance)
{
    return ace_virt_vm_snapshot_delete($vm_id, $vm_snapshot_instance);
}

/**
 * deletes a vm
 *
 * @api
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_vm_delete($vm_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $lab_active = ace_lab_is_active($lab_id);
    if ($lab_active) {
        $virt_vm_is_active = (ace_virt_vm_get_state($vm_id) == 1) ? TRUE : FALSE;
        if ($virt_vm_is_active) {
            ace_virt_vm_deactivate($vm_id);
        }
        ace_virt_vm_delete($vm_id);
    }
    $cdrom_table = ace_db_vm_get_cdrom_table($vm_id);
    foreach ($cdrom_table as $cdrom) {
        ace_db_vm_detach_cdrom($vm_id, $cdrom['instance']);
    }
    $disk_table = ace_db_vm_get_disk_table($vm_id);
    foreach ($disk_table as $disk) {
        ace_db_vm_detach_disk($vm_id, $disk['instance']);
    }
    $nic_table = ace_db_vm_get_nic_table($vm_id);
    foreach ($nic_table as $nic) {
        ace_db_vm_detach_nic($vm_id, $nic['instance']);
    }
    $success = ace_db_vm_delete($vm_id);
    return ($success) ? TRUE : FALSE;
}


/**
 * @param $vm_id
 *
 * @return bool
 */
function ace_virt_vm_exists($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $host_vm_list = ace_host_get_virt_vm_list($host_id);
    if (is_array($host_vm_list)) {
        if (in_array($vm_virt_id,$host_vm_list)) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}
/**
 * creates a live vm on a host
 *
 * @param   int $vm_id vm id
 *
 * @return bool                     on success TRUE/FALSE
 */
function ace_virt_vm_create($vm_id)
{
    $vm_info = ace_vm_get_info($vm_id);
    $lab_id = $vm_info['lab_id'];
    $host_id = ace_lab_get_host_id($lab_id);
    $virt_id = $vm_info['virt_id'];
    $vcpu = $vm_info['vcpu'];
    $memory = $vm_info['memory'];
    $unit = $vm_info['unit'];
//    $arch = 'x86_64';
    $arch = $vm_info['arch'];
    $profile = $vm_info['profile'];
    return ace_virt('create_vm', $host_id, $virt_id, $vcpu, $memory, $unit, $arch, $profile);
}

/**
 * destroys a live vm on a host
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_delete($vm_id)
{
    $vm_info = ace_vm_get_info($vm_id);
    $lab_id = $vm_info['lab_id'];
    $host_id = ace_lab_get_host_id($lab_id);
    $virt_id = $vm_info['virt_id'];
    $success = ace_virt('delete_vm', $host_id, $virt_id);
    return $success;
}

/**
 * returns a live vm's virt state
 *
 * @param   int $vm_id vm id
 *
 * @return bool                     TRUE = active | FALSE = inactive
 */
function ace_virt_vm_get_state($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $virt_vm_state = ace_virt('get_vm_state', $host_id, $vm_virt_id);
    return $virt_vm_state;
}

/**
 * @param $vm_id
 *
 * @return mixed
 */
function ace_virt_vm_get_nics($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $virt_vm_nics = ace_virt('get_vm_nics', $host_id, $vm_virt_id);
    return $virt_vm_nics;
}

/**
 * @param $vm_id
 *
 * @return mixed
 */
function ace_virt_vm_get_cdroms($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $virt_vm_cdroms = ace_virt('get_vm_cdroms', $host_id, $vm_virt_id);
    return $virt_vm_cdroms;
}

/**
 * @param $vm_id
 *
 * @return mixed
 */
function ace_virt_vm_get_disks($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $virt_vm_disks = ace_virt('get_vm_disks', $host_id, $vm_virt_id);
    return $virt_vm_disks;
}

/**
 * starts a live vm
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_activate($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $result = ace_virt('activate_vm', $host_id, $vm_virt_id);
    return $result;
}

/**
 * stops a live vm
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_deactivate($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('deactivate_vm', $host_id, $vm_virt_id);
}

/**
 * sends an ACPI shutdown signal to a live vm
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_shutdown($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('shutdown_vm', $host_id, $vm_virt_id);
}

/**
 * sends a soft reset (Ctrl-Alt-Del) to a live vm
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_soft_reset($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('soft_reset_vm', $host_id, $vm_virt_id);
}

/**
 * adds a cdrom device to a vm
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_attach_cdrom($vm_id, $vm_cdrom_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_attach_cdrom', $host_id, $vm_virt_id, $vm_cdrom_instance);
}

/**
 * removes a cdrom device from a vm
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_detach_cdrom($vm_id, $vm_cdrom_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_detach_cdrom', $host_id, $vm_virt_id, $vm_cdrom_instance);
}

/**
 * associates a volume with a vm cdrom device
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 * @param   int $volume_id         volume id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $volume_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $virt_volume_id = ace_db_volume_get_virt_id($volume_id);
    return ace_virt('vm_cdrom_insert_media', $host_id, $vm_virt_id, $vm_cdrom_instance, $virt_volume_id);
}

/**
 * removes a volume association from a vm cdrom device
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_cdrom_eject_media($vm_id, $vm_cdrom_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_cdrom_eject_media', $host_id, $vm_virt_id, $vm_cdrom_instance);
}

/**
 * adds a disk device with volume association to a vm
 *
 * @param   int $vm_id            vm id
 * @param   int $vm_disk_instance vm disk device instance
 * @param   int $volume_id        volume id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_attach_disk($vm_id, $vm_disk_instance, $volume_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $volume_virt_id = ace_db_volume_get_virt_id($volume_id);
    return ace_virt('vm_attach_disk', $host_id, $vm_virt_id, $vm_disk_instance, $volume_virt_id);
}

/**
 * removes a disk device from a vm
 *
 * @param   int $vm_id            vm id
 * @param   int $vm_disk_instance vm disk device instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_detach_disk($vm_id, $vm_disk_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_detach_disk', $host_id, $vm_virt_id, $vm_disk_instance);
}

/**
 * adds a nic device to a vm
 *
 * @param   int    $vm_id              vm id
 * @param   int    $vm_nic_instance    vm nic instance
 * @param   string $vm_nic_mac_address vm nice mac address
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_attach_nic($vm_id, $vm_nic_instance, $vm_nic_mac_address)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_attach_nic', $host_id, $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address);
}

/**
 * removes a nic device from a vm
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_detach_nic($vm_id, $vm_nic_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $vm_nic_mac_address = ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
    return ace_virt('vm_detach_nic', $host_id, $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address);
}

/**
 * associate a network with a vm nic device
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 * @param   int $network_id      network id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_nic_connect_network($vm_id, $vm_nic_instance, $network_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $network_virt_id = ace_db_network_get_virt_id($network_id);
    $vm_nic_mac_address = ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
    return ace_virt('vm_nic_connect_network', $host_id, $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, $network_virt_id);
}

/**
 * remove network association from a vm nic device
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 * @param   int $network_id      network id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_nic_disconnect($vm_id, $vm_nic_instance, $network_id)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $network_virt_id = ace_db_network_get_virt_id($network_id);
    $vm_nic_mac_address = ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
    return ace_virt('vm_nic_disconnect', $host_id, $vm_virt_id, $vm_nic_instance, $vm_nic_mac_address, $network_virt_id);
}

/**
 * returns vm nic device link state
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    TRUE = up | FALSE = down
 */
function ace_virt_vm_nic_get_link_state($vm_id, $vm_nic_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $vm_nic_mac_address = ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
    return ace_virt('vm_nic_get_link_state', $host_id, $vm_virt_id, $vm_nic_mac_address);
}

/**
 * sets a vm nic device link state up
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_nic_link_up($vm_id, $vm_nic_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $vm_nic_mac_address = ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
    return ace_virt('vm_nic_link_up', $host_id, $vm_virt_id, $vm_nic_mac_address);
}

/**
 * sets a vm nic device link state down
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_nic_link_down($vm_id, $vm_nic_instance)
{
    $lab_id = ace_db_vm_get_lab_id($vm_id);
    $host_id = ace_db_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $vm_nic_mac_address = ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance);
    return ace_virt('vm_nic_link_down', $host_id, $vm_virt_id, $vm_nic_mac_address);
}

/**
 * returns a vm's remote console connection information
 *
 * @param   int $vm_id vm id
 *
 * @return array                vm remote console connection information
 */
function ace_virt_vm_get_console_info($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('get_vm_console_info', $host_id, $vm_virt_id);
}

/**
 * grab and store a screenshot of a live vm
 *
 * @param   int $vm_id                          vm id
 * @param   int $max_width                      desired width of screenshot image
 *                                              (height is automatically determined by a 4:3 ratio)
 *
 * @return  string              filename of JPG
 */
function ace_virt_vm_screenshot($vm_id, $max_width)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_screenshot', $host_id, $vm_virt_id, $max_width);
}

/**
 * returns a vm's guacamole console url
 *
 * @param   int $vm_id vm id
 *
 * @return string|void          guacamole console URL
 */
function ace_virt_vm_get_console_url($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    $virt_vm_vnc_info = ace_virt('get_vm_console_info', $host_id, $vm_virt_id);
    $base_url = _GUACAMOLE_URL_;
    $conn_id = $vm_virt_id;
    $protocol = 'vnc';
    $hostname = $virt_vm_vnc_info['ip'];
    $port = $virt_vm_vnc_info['port'];
    $secret = 'secret';
    $console_url = guacamole_url($base_url, $conn_id, $protocol, $hostname, $port, $secret);
    return $console_url;
}

/**
 * returns a vm's snapshot list
 *
 * @param   int $vm_id vm id
 *
 * @return  array               vm snapshot list
 */
function ace_virt_vm_get_snapshot_list($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_get_snapshot_list', $host_id, $vm_virt_id);
}

/**
 * create a vm snapshot
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_create_snapshot($vm_id)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_create_snapshot', $host_id, $vm_virt_id);
}

/**
 * reverts a vm to a snapshot
 *
 * @param   int $vm_id                vm id
 * @param   int $vm_snapshot_instance vm snapshot instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_snapshot_revert($vm_id, $vm_snapshot_instance)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_snapshot_revert', $host_id, $vm_virt_id, $vm_snapshot_instance);
}

/**
 * delete a vm snapshot
 *
 * @param   int $vm_id                vm id
 * @param   int $vm_snapshot_instance vm snapshot instance
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_virt_vm_snapshot_delete($vm_id, $vm_snapshot_instance)
{
    $lab_id = ace_vm_get_lab_id($vm_id);
    $host_id = ace_lab_get_host_id($lab_id);
    $vm_virt_id = ace_db_vm_get_virt_id($vm_id);
    return ace_virt('vm_snapshot_delete', $host_id, $vm_virt_id, $vm_snapshot_instance);
}
