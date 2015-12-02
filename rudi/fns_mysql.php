<?php
/**
 * ACEITLab DB Functions
 *
 * performs database tasks against DB host
 * takes calls from Application API
 * makes calls to mysqli
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

$db_conn = NULL;

#=============================================================
#SYSTEM
#=============================================================
/**
 * connect to a db
 *
 * @return mysqli|bool          a mysqli connection object | FALSE on error
 */
function ace_db_connect()
{
    $mysql_conn = new mysqli('sql02.aceitlab.lan', 'aceadmin', 'phitz101', 'ACEITLab');
    return ($mysql_conn) ? $mysql_conn : FALSE;
}

/**
 * perform sql query against a mysqli connection
 *
 * @return bool|object a db return object or FALSE on error
 * <pre>
 * object
 * ->table::array      (2D array)
 * ->row_count::int
 * ->last_insert_id::int
 * or
 * FALSE on error
 * </pre>
 * @internal param string $sql sql query
 * @internal param int $flag optional return type flag
 *
 */
function ace_db_query()
{
    $args = func_get_args();
    global $db_conn;
    if ($args[0] && (gettype($args[0]) == "string")) {
        $sql = $args[0];
    } else {
        return FALSE;
    }
    $return = (object)array();
    $array_type_requested = MYSQLI_ASSOC;
    if (isset($args[1])) {
        switch ($args[1]) {
            case 0 :
                $array_type_requested = MYSQLI_ASSOC;
                break;
            case 1 :
                $array_type_requested = MYSQLI_NUM;
                break;
            case 2 :
                $array_type_requested = MYSQLI_BOTH;
                break;
            default :
                $array_type_requested = MYSQLI_ASSOC;
                break;
        }
    }
    if (!is_object($db_conn)) {
        $db_conn = ace_db_connect();
    }
    $db_result = $db_conn->query($sql);
    if ($db_result) {
        $sql_bits = explode(' ', $sql, 2);
        $cmd = strtolower($sql_bits[0]);
        if ($cmd == 'select' || $cmd == 'show') {
            $return->table = $db_result->fetch_all($array_type_requested);
            $return->row_count = $db_result->num_rows;
        } else {
            $return->table = NULL;
            $return->row_count = $db_conn->affected_rows;
            $return->last_insert_id = $db_conn->insert_id;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * @param $arg
 */
function ace_db_esc(&$arg)
{
    global $db_conn;
    if (!is_object($db_conn)) {
        $db_conn = ace_db_connect();
    }
    $arg = mysqli_real_escape_string($db_conn,$arg);
}

/**
 * fetch a table of all hosts
 *
 * @return  array|bool                  2D array | FALSE on error
 */
function ace_db_get_host_table()
{
    $sql = 'SELECT *
    		FROM host
    		ORDER BY name';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of active hosts
 *
 * @return  array|bool               active host table | FALSE on error
 */
function ace_db_get_active_host_table()
{
    $sql = "SELECT `host`.*
			FROM `host`
				INNER JOIN `mapHostRoles`
					ON `host`.`id` = `mapHostRoles`.`host_id`
				INNER JOIN `host_role`
					ON `mapHostRoles`.`host_role_id` = `host_role`.`id`
			WHERE `host_role`.`name` = 'lab host'
				AND `host`.`state` = 1";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all host roles
 *
 * @return  array|bool               host roles table | FALSE on error
 */
function ace_db_get_host_role_table()
{
    $sql = 'SELECT *
    		FROM host_role
    		ORDER BY name';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all users
 *
 * @return  array|bool                user table | FALSE on error
 */
function ace_db_get_user_table()
{
    $sql = 'SELECT *
            FROM `user`
            ORDER BY `name`';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of admins and managers only, from all users
 *
 * @return  array|bool                 user table (admins and managers only) | FALSE on error
 */
function ace_db_get_user_admins_and_managers_table()
{
    $sql = "SELECT `user`.id,
					`user`.name,
					`user`.first,
					`user`.last
			FROM `group`
			INNER JOIN `mapGroupsUsers` ON `group`.`id` = `mapGroupsUsers`.`group_id`
			INNER JOIN `user` ON `mapGroupsUsers`.`user_id` = `user`.`id`
			WHERE `group`.`name` = 'Admins' OR `group`.`name` = 'Managers'
			ORDER BY `user`.`last`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all groups
 *
 * @return  array|bool                  group table | FALSE on error
 */
function ace_db_get_group_table()
{
    $sql = 'SELECT * FROM `group` ORDER BY `name`';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * @return bool
 */
function ace_db_get_security_group_table()
{
    $sql = "SELECT * FROM `group` WHERE `category` = 'security' ORDER BY `name`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * @return bool
 */
function ace_db_get_academic_group_table()
{
    $sql = "SELECT * FROM `group` WHERE `category` = 'academic' ORDER BY `name`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all groups and their respective owners
 *
 * @return  array|bool                  group table (with owners) | FALSE on error
 */
function ace_db_get_group_table_with_owners()
{
    $sql = 'SELECT `group`.`id` AS `id`,
                `group`.`name` AS `name`,
                `group`.`state` AS `state`,
                `group`.`category` AS `category`,
                `user`.`id` AS `user_id`,
                `user`.`name` AS `user_name`,
                `user`.`state` AS `user_state`
			FROM `group`
			INNER JOIN `user` ON `group`.`owner` = `user`.`id`
			ORDER BY `group`.`name` ASC';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all labs
 *
 * @return  array|bool                   lab table | FALSE on error
 */
function ace_db_get_lab_table()
{
    $sql = 'SELECT * FROM lab';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * @return bool
 */
function ace_db_get_aged_active_labs_table()
{
    $sql = 'SELECT *
            FROM `lab`
            WHERE `state` = 1
              AND `last_activated` < ' . (time() - _LAB_AGE_MAXIMUM_);
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * @return bool
 */
function ace_db_get_active_lab_table()
{
    $sql = 'SELECT * FROM lab WHERE `state`=1';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * @return bool
 */
function ace_db_get_course_table(){
    $sql = 'SELECT * FROM `course` ORDER BY `courseID`';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all labs and their respective owners
 *
 * @return  array|bool                   lab table (with owners) | FALSE on error
 */
function ace_db_get_lab_table_with_owners()
{
    $sql = 'SELECT l.id AS lab_id,
				l.instance AS lab_instance,
				l.`name` AS lab_name,
				l.description AS lab_description,
				l.state AS lab_state,
				u.id AS user_id,
				u.`name` AS user_name,
				u.`first` AS user_first,
				u.last AS user_last,
				u.state AS user_state
			FROM `user` AS u
			INNER JOIN lab AS l ON u.id = l.user_id
			ORDER BY user_name ASC';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all networks
 *
 * @return  array|bool                   network table | FALSE on error
 */
function ace_db_get_network_table()
{
    $sql = 'SELECT * FROM network';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all volumes
 *
 * @return  array|bool                   volume table | FALSE on error
 */
function ace_db_get_volume_table()
{
    $sql = 'SELECT * FROM `volume` ORDER BY `display_name`';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all shared volumes
 *
 * @return  array|bool                   volume table (img, shared only) | FALSE on error
 */
function ace_db_get_shared_volume_table()
{
    $sql = "SELECT * FROM volume
    		WHERE `lab_id`=0
    			AND `type`='img'
    			AND `user_visible`=1
    		ORDER BY `display_name`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all  'iso' volumes
 *
 * @return  array|bool                   volume table (iso only) | FALSE on error
 */
function ace_db_get_iso_table()
{
    $sql = "SELECT *
    		FROM volume
    		WHERE `type`='iso'
    		ORDER BY `display_name`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all virtual machines
 *
 * @return  array|bool                  vm table | FALSE on error
 */
function ace_db_get_vm_table()
{
    $sql = 'SELECT * FROM `vm`';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of all quotas
 *
 * @return array|bool                   quota table | FALSE on error
 */
function ace_db_get_quota_table(){
    $sql = 'SELECT * FROM `quota` ORDER BY `id`';
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * better version
 * that will, in conjunction with other functions, allow (un)locking hosts
 *
*@return bool|int|null
 */
function ace_db_get_best_host_2()
{
    $best_host_id = NULL;
    $host_shortlist = array();
    $host_table = ace_db_get_active_host_table();
    if (is_array($host_table)) {
        foreach ($host_table as $host) {
            //$host_reservation_percentage = 0;
            $host_lab_quota = ace_db_host_get_quota($host['id']);
            if ( ($host_lab_quota !== FALSE) && ($host_lab_quota['labs'] != 0) ) {
                $lab_table = ace_db_host_get_lab_table($host['id']);
                if (is_array($lab_table)) {
                    $host_lab_count = count($lab_table);
                    $host_reservation_percentage = intval(ceil($host_lab_count / $host_lab_quota) * 100);
                } else {
                    $host_reservation_percentage = 0;
                }
                $host_shortlist[] = array('id' => $host['id'], 'percentage' => $host_reservation_percentage);
            }
        }
        if ( (is_array($host_shortlist)) && (count($host_shortlist) > 0) ) {
            $lowest_reservation_percentage = 100;
            foreach ($host_shortlist as $host) {
                if ($host['percentage'] < $lowest_reservation_percentage) {
                    $lowest_reservation_percentage = $host['percentage'];
                    $best_host_id = intval($host['id']);
                }
            }
            if ($lowest_reservation_percentage = 100) {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
    return ($best_host_id !== NULL) ? $best_host_id : FALSE;
}

/**
 * determine best host candidate prior to deploying a lab
 *
 * @return  int|bool                     host id | FALSE on error
 */
function ace_db_get_best_host()
{
    $host_table = ace_db_get_active_host_table();
    $vcpu_per_thread = 4;
    $lab['vcpu'] = 6;
    $lab['memory'] = 5.5;
    $lab['storage'] = 42;
    $winning_host = NULL;
    #create a shortlist of viable hosts
    $host_shortlist = array();
    foreach ($host_table as $host) {
        # number of labs this host can support
        $host_max_labs_by_vcpu = intval(($host['threads'] * $vcpu_per_thread) / $lab['vcpu']);
        $host_max_labs_by_memory = intval($host['memory'] / $lab['memory']);
        $host_max_labs_by_storage = intval($host['storage'] / $lab['storage']);
        $host_max_labs = min($host_max_labs_by_vcpu, $host_max_labs_by_memory, $host_max_labs_by_storage);
        # number of labs currently assigned to this host
        $lab_table = ace_host_get_lab_table($host['id']);
        //if ($lab_table === FALSE) {
        if (!is_array($lab_table)) {
            $use_percentage = 0;
            $host_shortlist[] = array('id' => $host['id'], 'percentage' => $use_percentage);
        } else {
            $host_lab_count = count($lab_table);
            # usage as percentage
            if ($host_lab_count < $host_max_labs) {
                $use_percentage = intval(ceil(($host_lab_count / $host_max_labs) * 100));
                $host_shortlist[] = array('id' => $host['id'], 'percentage' => $use_percentage);
            } else {
                $use_percentage = 100;
                $host_shortlist[] = array('id' => $host['id'], 'percentage' => $use_percentage);
            }
        }
    }
    # apply algorithm to determine host with lowest use_percentage
    if (count($host_shortlist) > 0) {
        $lowest_percentage = 100;
        foreach ($host_shortlist as $host) {
            if ($host['percentage'] < $lowest_percentage) {
                $lowest_percentage = $host['percentage'];
                $best_host_id = intval($host['id']);
            }
        }
        if ($lowest_percentage == 100) {
            $best_host_id = FALSE;
        }
    } else {
        $best_host_id = FALSE;
    }
    return !empty($best_host_id) ? $best_host_id : FALSE;
}

/**
 * authenticate user
 *
 * @param   string $user_name     user name
 * @param   string $user_password user password
 *
 * @return  bool                on success
 */
function ace_db_authenticate_user($user_name, $user_password)
{
    if (isset($user_name) AND isset($user_password) AND ($user_name != '') AND ($user_password != '')) {
        $sql = "SELECT u1.id AS user_id,
					g.id AS group_id
				FROM `user` AS u1
					INNER JOIN mapGroupsUsers ON u1.id = mapGroupsUsers.user_id
					INNER JOIN `group` AS g ON mapGroupsUsers.group_id = g.id
				WHERE u1.`name` = '$user_name'
					AND u1.`state` = 1
					AND u1.`password` = SHA1('$user_password')
					AND g.`state` = 1";
        $db_result = ace_db_query($sql);
        if ($db_result->row_count > 0) {
            $table = $db_result->table;
            $user_groups = NULL;
            foreach ($table as $row) {
                $user_groups[] = $row['group_id'];
            }

            // if you're a student (minimum group id is 2) then you must belong to at least one other group (a class)
            if ( ( min($user_groups) == 3 ) && ( count($table) < 2 ) ) {
                $authenticated = FALSE;
            } else {
                $_SESSION['user_id'] = $table[0]['user_id'];
                $_SESSION['user_groups'] = $user_groups;
                $_SESSION['security_level'] = min($user_groups);
                $authenticated = TRUE;
            }

        } else {
            #user not found, or isn't active, or doesn't belong to at least one active group
            $authenticated = FALSE;
        }
    } else {
        $authenticated = FALSE;
    }
    return $authenticated;
}


#=============================================================
#HOST
#=============================================================
/**
 * fetch host id by providing a host name
 *
 * @param   string $host_name host name
 *
 * @return  int|bool            host id | FALSE on error
 */
function ace_db_host_get_id_by_name($host_name)
{
    $sql = "SELECT id
            FROM host
            WHERE name='$host_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $id = $row['id'];
    } else {
        $id = FALSE;
    }
    return $id;
}

/**
 * fetch host name by providing a host id
 *
 * @param   int $host_id host id
 *
 * @return  string|bool         host name | FALSE on error
 */
function ace_db_host_get_name_by_id($host_id)
{
    $sql = "SELECT name
			FROM host
			WHERE id=$host_id";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $host_name = $row['name'];
    } else {
        $host_name = FALSE;
    }
    return $host_name;
}

/**
 * fetch a table of information about a host
 *
 * @param   int $host_id host id
 *
 * @return  array|bool           host information record | FALSE on error
 */
function ace_db_host_get_info($host_id)
{
    $sql = "SELECT *
    		FROM `host`
    		WHERE `id`=$host_id
    		ORDER BY `name`";
    $db_result = ace_db_query($sql);
    if (count($db_result->table) == 1) {
        $return = $db_result->table[0];
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch state of host
 *
 * @param   int $host_id host id
 *
 * @return  bool                 active TRUE/FALSE
 */
function ace_db_host_get_state($host_id)
{
    $sql = "SELECT state
            FROM host
            WHERE id=$host_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * fetch a table of labs for a given host
 *
 * @param   int $host_id host id
 *
 * @return  array|bool           host lab table | FALSE on error
 */
function ace_db_host_get_lab_table($host_id)
{
    $sql = "SELECT *
            FROM lab
            WHERE host_id=$host_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * @param $host_id
 *
 * @return bool
 */
function ace_db_host_get_quota($host_id) {
    $sql = "SELECT labs
            FROM `quota`
            WHERE `object_type`='host'
              AND `object_id`=$host_id
            LIMIT 1";
    $db_result = ace_db_query($sql);
    $host_lab_quota = ($db_result->row_count > 0) ? $db_result->table[0] : FALSE;
    return $host_lab_quota;
}

/**
 * @param $host_id
 *
 * @return bool
 */
function ace_db_host_has_active_labs($host_id) {
    $sql = "SELECT COUNT(*) as num_labs
            FROM lab
            WHERE host_id=$host_id";
    $db_result = ace_db_query($sql);
    $table = $db_result->table;
    if ($table[0]['num_labs'] > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * add a new host
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
function ace_db_host_add($host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage)
{
    //$args = [
    //    'host_name' => $host_name,
    //    'host_domain' => $host_domain,
    //    'host_description' => $host_description
    //];
    //ace_db_esc_array($args);

    ace_db_esc($host_name);
    ace_db_esc($host_domain);
    ace_db_esc($host_description);
    ace_db_esc($host_hypervisor);
    ace_db_esc($host_ip_internal);
    ace_db_esc($host_ip_external);
    ace_db_esc($host_username);
    ace_db_esc($host_password);

    $sql = "INSERT INTO host (
                `name`,
                `domain`,
                `description`,
                `hypervisor`,
                `ip_internal`,
                `ip_external`,
                `username`,
                `password`,
                `threads`,
                `memory`,
                `storage`,
                `state`)
            VALUES (
                '$host_name',
                '$host_domain',
                '$host_description',
                '$host_hypervisor',
                '$host_ip_internal',
                '$host_ip_external',
                '$host_username',
                '$host_password',
                $host_threads,
                $host_memory,
                $host_storage,
                0)";
    $db_result = ace_db_query($sql);
    if ($db_result->last_insert_id != 0) {
        $return = $db_result->last_insert_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * update a host record
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
function ace_db_host_update($host_id, $host_name, $host_domain, $host_description, $host_hypervisor, $host_ip_internal, $host_ip_external, $host_username, $host_password, $host_threads, $host_memory, $host_storage)
{
    ace_db_esc($host_name);
    ace_db_esc($host_domain);
    ace_db_esc($host_description);
    ace_db_esc($host_hypervisor);
    ace_db_esc($host_ip_internal);
    ace_db_esc($host_ip_external);
    ace_db_esc($host_username);
    ace_db_esc($host_password);

    $sql = "UPDATE `host` SET `name`='$host_name',
                            `domain`='$host_domain',
                            `description`='$host_description',
                            `hypervisor`='$host_hypervisor',
                            `ip_internal`='$host_ip_internal',
                            `ip_external`='$host_ip_external',
                            `username`='$host_username',
                            `password`='$host_password',
                            `threads`=$host_threads,
                            `memory`=$host_memory,
                            `storage`=$host_storage
            WHERE `id`=$host_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * fetch a table of roles for a given host
 *
 * @param  int $host_id host id
 *
 * @return array|bool               host owned roles table | FALSE on error
 */
function ace_db_host_get_roles($host_id)
{
    $sql = "SELECT mapHostRoles.host_id AS `host_id`,
				mapHostRoles.host_role_id AS `host_role_id`,
				host_role.`name` AS `host_role_name`
			FROM mapHostRoles
				INNER JOIN host_role
				ON mapHostRoles.`host_role_id` = `host_role`.`id`
			WHERE mapHostRoles.`host_id` = $host_id
			ORDER BY `host_role`.`name` ASC";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * assign a given role to a given host
 *
 * @param   int $host_id      host id
 * @param   int $host_role_id host role id
 *
 * @return  bool                    on success
 */
function ace_db_host_add_role($host_id, $host_role_id)
{
    $sql = "SELECT * FROM mapHostRoles
			WHERE host_id=$host_id
				AND host_role_id=$host_role_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 0) {
        $sql = "INSERT INTO mapHostRoles (`host_id`,`host_role_id`)
				VALUES ($host_id,$host_role_id)";
        $db_result = ace_db_query($sql);
        $return = $db_result->last_insert_id != 0 ? TRUE : FALSE;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * un-assign a given role from a given host
 *
 * @param   int $host_id      host id
 * @param   int $host_role_id host role id
 *
 * @return  bool                    on success
 */
function ace_db_host_remove_role($host_id, $host_role_id)
{
    $sql = "DELETE FROM mapHostRoles
			WHERE host_id=$host_id
				AND host_role_id=$host_role_id";
    $db_result = ace_db_query($sql);
    return $db_result->row_count > 0 ? TRUE : FALSE;
}

/**
 * un-assign all roles from a given host
 *
 * @param   int $host_id host id
 *
 * @return  bool                    on success
 */
function ace_db_host_remove_all_roles($host_id)
{
    $sql = "DELETE FROM mapHostRoles
			WHERE host_id=$host_id";
    ace_db_query($sql);
    return TRUE;
}

/**
 * set state of a given host
 *
 * @param   int  $host_id    host id
 * @param   bool $host_state TRUE = active | FALSE = inactive
 *
 * @return  bool                    on success
 */
function ace_db_host_set_state($host_id, $host_state)
{
    $state_sql = ($host_state) ? 1 : 0;
    $sql = "UPDATE host SET state=$state_sql WHERE id=$host_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * remove a given host
 *
 * @param   int $host_id host id
 *
 * @return  bool                    on success
 */
function ace_db_host_remove($host_id)
{
    $sql = 'DELETE FROM host WHERE id=' . $host_id;
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#USER
#=============================================================
/**
 * fetch user id for given user name
 *
 * @param   string $user_name user name
 *
 * @return  int|bool                user id | FALSE on error
 */
function ace_db_user_get_id_by_name($user_name)
{
    $sql = "SELECT id FROM user WHERE name='$user_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $id = $row['id'];
    } else {
        $id = FALSE;
    }
    return $id;
}

/**
 * fetch a user name for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  string|bool             user name | FALSE on error
 */
function ace_db_user_get_name_by_id($user_id)
{
    $sql = "SELECT `name` FROM `user` WHERE `id`='$user_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $name = $row['name'];
    } else {
        $name = FALSE;
    }
    return $name;
}

/**
 * fetch a user display name for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  string|bool             user display name | FALSE on error
 */
function ace_db_user_get_display_name_by_id($user_id)
{
    $sql = "SELECT `first`,`last` FROM `user` WHERE `id`='$user_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $name = $row['first'] . ' ' . $row['last'];
    } else {
        $name = FALSE;
    }
    return $name;
}

/**
 * fetch user record for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  bool|array              array of user information | FALSE on error
 */
function ace_db_user_get_info($user_id)
{
    $sql = 'SELECT * FROM user WHERE id=' . $user_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $record = $db_result->table[0];
        $return = $record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch user state for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  bool                    TRUE = active | FALSE = inactive|error
 */
function ace_db_user_get_state($user_id)
{
    $sql = "SELECT state FROM user WHERE id=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * fetch the highest security level group for a specified user
 *
 * @param   int $user_id    user id
 *
 * @return int | FALSE                  user security level (1=Admin|2=Manager|3=User) | FALSE on error
 */
function ace_db_user_get_security_level($user_id)
{
    $sql = "SELECT MIN(`mapGroupsUsers`.`group_id`) as `security_level`
            FROM `mapGroupsUsers`
            WHERE `mapGroupsUsers`.`user_id`=".$user_id;
    $db_result = ace_db_query($sql);
    return $db_result->table[0]['security_level'];
}

/**
 * fetch group membership array for a given user id
 *
 * @param   int $user_id user id
 *
 * @return array|bool               array of group id | FALSE on error
 */
function ace_db_user_get_group_ids($user_id)
{
    $sql = "SELECT mapGroupsUsers.group_id
			FROM mapGroupsUsers
			INNER JOIN `group` AS g ON mapGroupsUsers.group_id = g.id
			WHERE mapGroupsUsers.user_id=$user_id
			ORDER BY group_id ASC";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
        foreach ($table as $row) {
            $group_ids[] = $row['group_id'];
        }
    } else {
        $group_ids = FALSE;
    }
    return (!empty($group_ids)) ? $group_ids : FALSE;
}

/**
 * fetch a group membership table for a given user id
 *
 * @param   int $user_id user id
 *
 * @return array|bool               table of group information | FALSE on error
 */
function ace_db_user_get_groups($user_id)
{
    $sql = "SELECT mapGroupsUsers.group_id,
					g.`name`,
					g.`owner`,
					g.`state`
			FROM mapGroupsUsers
			INNER JOIN `group` AS g ON mapGroupsUsers.group_id = g.id
			WHERE mapGroupsUsers.user_id=$user_id
			ORDER BY g.`name` ASC";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table : FALSE;
}

/**
 * fetch a table of lab information for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              table of lab information | FALSE on error
 */
function ace_db_user_get_lab_table($user_id)
{
    $sql = "SELECT *
    		FROM `lab`
    		WHERE `user_id`=$user_id
    		ORDER BY `display_name`";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table : FALSE;
}

/**
 * fetch next available (within quota) lab instance number for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  int|bool                lab instance | FALSE on error
 */
function ace_db_user_get_available_lab_instance($user_id)
{
    $quota_array = ace_db_user_get_quota_array($user_id);
    $lab_quota = $quota_array['labs'];
    $instance_list = array();
    $user_lab_table = ace_db_user_get_lab_table($user_id);
    if (is_array($user_lab_table)) {
        for ($row = 0; $row < count($user_lab_table); $row++) {
            $instance_list[ $row ] = $user_lab_table[ $row ]['instance'];
        }
        $valid_instances = range(1, $lab_quota);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 1;
    }
    return $return;
}

/**
 * fetch a table of owned groups for a give user id
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              table of owned groups | FALSE on error
 */
function ace_db_user_get_owned_groups($user_id)
{
/*    $sql = 'SELECT g.id AS group_id,
					g.`name` AS group_name,
					g.state AS group_state
			FROM `user` AS u
				INNER JOIN `group` AS g ON u.id = g.`owner`
			WHERE u.id = ' . $user_id . '
			ORDER BY g.`name`';*/
    $sql = 'SELECT *
            FROM `group`
            WHERE `owner`=' . $user_id . '
            ORDER BY name';
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table : FALSE;
}

/**
 * @param $user_id
 *
 * @return bool
 */
function ace_db_user_get_owned_academic_groups($user_id)
{
    $sql = "SELECT *
            FROM `group`
            WHERE `owner` = " . $user_id . "
                AND `category` = 'academic'";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table : FALSE;
}

/**
 * fetch a table of owned labs for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              table of owned labs | FALSE on error
 */
function ace_db_user_get_owned_labs($user_id)
{
    $sql = 'SELECT l.id AS lab_id,
					l.instance AS lab_instance,
					l.`name` AS lab_name,
					l.state AS lab_state,
					u.id AS user_id
			FROM `user` AS u
				INNER JOIN lab AS l ON u.id = l.user_id
			WHERE u.id = ' . $user_id . '
			ORDER BY l.`name`';
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table : FALSE;
}

/**
 * determine if a user exists in the database for a given user name
 *
 * @param   string $user_name user name
 *
 * @return  bool                    user exists TRUE/FALSE
 */
function ace_db_user_exists($user_name)
{
    $sql = "SELECT * FROM user WHERE name='$user_name'";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * fetch an array of quotas for a give user id
 *
 * @param   int $user_id user id
 *
 * @return  array|bool              array of quotas | FALSE on error
 */
function ace_db_user_get_quota_array($user_id)
{
    $group_id_array = ace_db_user_get_group_ids($user_id);
    $str_group_ids = implode(',', $group_id_array);
    $sql = "SELECT MIN(`labs`) as `labs`,
					MIN(`vms`) as `vms`,
					MIN(`vcpu`) as `vcpu`,
					MIN(`memory`) as `memory`,
					MIN(`networks`) as `networks`,
					MIN(`volumes`) as `volumes`,
					MIN(`storage`) as `storage`
			FROM quota
			WHERE (user_id=$user_id OR group_id IN ($str_group_ids) )";
    $db_result = ace_db_query($sql);
    return $db_result->row_count > 0 ? $db_result->table[0] : FALSE;
}

/**
 * add a new user
 *
 * @param   string $user_name  user name
 * @param   string $user_first user first name (for display name)
 * @param   string $user_last  user last name (for display name)
 *
 * @return  array                   array with user id and initial password (user id is FALSE on error)
 */
function ace_db_user_create($user_name, $user_first, $user_last)
{
    ace_db_esc($user_name);
    ace_db_esc($user_first);
    ace_db_esc($user_last);
    $user_name = trim($user_name);
    $user_first = trim($user_first);
    $user_last = trim($user_last);
    if (filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
        if (!ace_db_user_exists($user_name)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $initial_password = substr(str_shuffle($chars), 0, 8);
            $enc_initial_password = sha1($initial_password);
            $sql = "INSERT INTO user (`name`,`first`,`last`,`password`,`state`)
		            VALUES ('$user_name','$user_first','$user_last','$enc_initial_password',1)";
            $db_result = ace_db_query($sql);
            if ($db_result->last_insert_id != 0) {
                $return['password'] = $initial_password;
                $user_id = $db_result->last_insert_id;
                $group_id = ace_db_group_get_id_by_name('Users');
                ace_db_group_add_user($group_id, $user_id);
            } else {
                $user_id = FALSE;
            }
        } else {
            $user_id = FALSE;
        }
    } else {
        $user_id = FALSE;
    }
    $return['user_id'] = $user_id;
    return $return;
}

/**
 * update a user record for a given user id
 *
 * @param   int    $user_id    user id
 * @param   string $user_name  user name
 * @param   string $user_first user first name (for display name)
 * @param   string $user_last  user last name (for display name)
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_db_user_update($user_id, $user_name, $user_first, $user_last)
{
    ace_db_esc($user_name);
    ace_db_esc($user_first);
    ace_db_esc($user_last);
    $sql = "UPDATE user
	        SET `name`='$user_name',
                `first`='$user_first',
                `last`='$user_last'
            WHERE `id`=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * @param $user_id
 *
 * @return bool|string
 */
function ace_db_user_reset_password($user_id){
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $initial_password = substr(str_shuffle($chars), 0, 8);
    $enc_initial_password = sha1($initial_password);
    $sql = "UPDATE `user`
            SET `password`='$enc_initial_password'
            WHERE `id`=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $initial_password : FALSE;
}

/**
 * update a user password for a give user id
 *
 * @param   int    $user_id       user id
 * @param   string $user_password user password
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_db_user_update_password($user_id, $user_password)
{
    ace_db_esc($user_password);
    $sql = "UPDATE user
			SET password=SHA1('$user_password')
			WHERE id=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set user state for a given user id
 *
 * @param   int  $user_id    user id
 * @param   bool $user_state user state (TRUE = active)
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_db_user_set_state($user_id, $user_state)
{
    $state_sql = ($user_state) ? 1 : 0;
    $sql = "UPDATE user
            SET state=$state_sql
            WHERE id=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * delete a user record for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  bool                    success TRUE/FALSE
 */
function ace_db_user_delete($user_id)
{
    $sql = 'DELETE FROM user
            WHERE id=' . $user_id;
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#GROUP
#=============================================================
/**
 * fetch group id for a given group name
 *
 * @param   string $group_name group name
 *
 * @return  int|bool                group id | FALSE on error
 */
function ace_db_group_get_id_by_name($group_name)
{
    $sql = "SELECT `id` FROM `group` WHERE `name`='$group_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $id = $row['id'];
    } else $id = FALSE;
    return $id;
}

/**
 * fetch group name for a given group id
 *
 * @param   int $group_id group id
 *
 * @return  string|bool             group name | FALSE on error
 */
function ace_db_group_get_name_by_id($group_id)
{
    $sql = "SELECT `name` FROM `group` WHERE `id`='$group_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $name = $row['name'];
    } else $name = FALSE;
    return $name;
}

/**
 * fetch group information for a give group id
 *
 * @param   int $group_id group id
 *
 * @return array|bool               array of group information | FALSE
 */
function ace_db_group_get_info($group_id)
{
    $sql = 'SELECT * FROM `group` WHERE `id`=' . $group_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $record = $db_result->table[0];
        $return = $record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * determine group state for a given group id
 *
 * @param   int $group_id group id
 *
 * @return  bool                    TRUE = active | FALSE = inactive
 */
function ace_db_group_get_state($group_id)
{
    $sql = "SELECT `state` FROM `group` WHERE `id`=$group_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * fetch array of user id for a given group id
 *
 * @param   int $group_id group id
 *
 * @return  array|bool              array of user id | FALSE on error
 */
function ace_db_group_get_user_ids($group_id)
{
    $sql = "SELECT user_id FROM mapGroupsUsers WHERE group_id=$group_id";
    $array = array();
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
        foreach ($table as $row) {
            $array[] = $row['user_id'];
        }
    } else {
        $array = FALSE;
    }
    return $array;
}

/**
 * fetch a table of membership information for a given group id
 *
 * @param   int $group_id group id
 *
 * @return  array|bool               table of group members | FALSE on error
 */
function ace_db_group_get_members_table($group_id)
{
    $sql = 'SELECT `user`.id AS user_id,
				`user`.`name` AS user_name,
				`user`.`first` AS user_first,
				`user`.last AS user_last,
				`user`.state AS user_state,
				`group`.`name` AS group_name,
				`group`.`owner` AS group_owner,
				`group`.state AS group_state
			FROM `user`
			INNER JOIN mapGroupsUsers ON `user`.id = mapGroupsUsers.user_id
			INNER JOIN `group` ON mapGroupsUsers.group_id = `group`.id
			WHERE `group`.id = ' . $group_id . '
			ORDER BY `user`.`name` ASC';
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table : FALSE;
}

/**
 * returns whether a section record with a specified group id already exists
 *
 * @param int   $group_id
 *
 * @return bool                     TRUE | FALSE
 */
function ace_db_group_section_exists($group_id){
    $sql = "SELECT `id`
            FROM `section`
            WHERE `group_id` = " . $group_id;
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    return ($count > 0) ? TRUE : FALSE;
}

/**
 * fetch a record of section info for a given group id
 *
 * @param   int $group_id group id
 *
 * @return array|bool               array of section info | FALSE on error
 */
function ace_db_group_get_section_info($group_id){
    $sql = 'SELECT *
            FROM `section`
            WHERE `group_id` = ' . $group_id;
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? $db_result->table[0] : FALSE;
}

/**
 * @param $group_name
 *
 * @return bool
 */
function ace_db_create_security_group($group_name)
{
    ace_db_esc($group_name);
    if (!ace_db_group_get_id_by_name($group_name)) {
        $sql = "INSERT INTO `group` (`name`,`owner`,`state`,`category`)
	            VALUES ('$group_name',1,1,'security')";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $group_id = $db_result->last_insert_id;
        } else {
            $group_id = FALSE;
        }
    } else {
        $group_id = FALSE;
    }
    return $group_id;
}

/**
 * @param $group_name
 * @param $group_owner_id
 *
 * @return bool
 */
function ace_db_create_academic_group($group_name, $group_owner_id)
{
    ace_db_esc($group_name);
    if (!ace_db_group_get_id_by_name($group_name)) {
        $sql = "INSERT INTO `group` (`name`,`owner`,`state`,`category`)
	            VALUES ('$group_name',$group_owner_id,1,'academic')";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $group_id = $db_result->last_insert_id;
        } else {
            $group_id = FALSE;
        }
    } else {
        $group_id = FALSE;
    }
    return $group_id;
}

/**
 * creates a group section info record
 *
 * @param int $group_id
 * @param string $courseID
 * @param string $sectionID
 * @param string $schedule
 * @param string $comment
 *
 * @return int|bool                     section id | FALSE on error
 */
function ace_db_group_create_section_info($group_id, $courseID, $sectionID, $schedule, $comment)
{
    ace_db_esc($courseID);
    ace_db_esc($sectionID);
    ace_db_esc($schedule);
    ace_db_esc($comment);
    if (!ace_db_group_section_exists($group_id)) {
        $sql = "INSERT INTO `section` (`group_id`, `courseID`, `sectionID`, `schedule`, `comment`)
                VALUES ($group_id, '$courseID', '$sectionID', '$schedule', '$comment')";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $section_id = $db_result->last_insert_id;
        } else {
            $section_id = FALSE;
        }
    } else {
        $section_id = FALSE;
    }
    return $section_id;
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
function ace_db_group_update_section_info($group_id, $courseID, $sectionID, $schedule, $comment)
{
    ace_db_esc($courseID);
    ace_db_esc($sectionID);
    ace_db_esc($schedule);
    ace_db_esc($comment);
    $success = TRUE;
    if (ace_db_group_section_exists($group_id)) {
        $sql = "UPDATE `section`
                SET `courseID` = '$courseID',
                    `schedule` = '$schedule',
                    `comment` = '$comment'
                WHERE `group_id` = $group_id
                AND `sectionID` = '$sectionID'";
        $db_result = ace_db_query($sql);
        $success = ($db_result->row_count > 0) ? TRUE : FALSE;
    }
    return $success;
}

/**
 * @param $group_id
 *
 * @return bool
 */
function ace_db_group_delete_section_info($group_id){
    $success = TRUE;
    if (ace_db_group_section_exists($group_id)){
        $sql = "DELETE
                FROM `section`
                WHERE `group_id` = ".$group_id;
        $db_result = ace_db_query($sql);
        $success = ($db_result->row_count > 0) ? TRUE : FALSE;
    }
    return $success;
}

/**
 * fetch a table of labs associated with a given group id
 *
 * @param   int $group_id group id
 *
 * @return  array|bool              table of lab information | FALSE on error
 */
function ace_db_group_get_lab_table($group_id)
{
    $sql = "SELECT lab.id AS id,
					lab.display_name AS display_name,
					lab.state AS state
			FROM mapGroupsLabs
			INNER JOIN lab ON mapGroupsLabs.lab_id = lab.id
			WHERE mapGroupsLabs.group_id = $group_id
			ORDER BY display_name ASC";
    $db_result = ace_db_query($sql);
    return $db_result->row_count > 0 ? $db_result->table : FALSE;
}

/**
 * create a new group
 *
 * @param   string $group_name     group name
 * @param   int    $group_owner_id user id of group owner
 *
 * @return  int|bool                new group id | FALSE on error
 */
function ace_db_group_create($group_name, $group_owner_id)
{
    ace_db_esc($group_name);
    if (!ace_db_group_get_id_by_name($group_name)) {
        $sql = "INSERT INTO `group` (`name`,`owner`,`state`)
	            VALUES ('$group_name','$group_owner_id',1)";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $group_id = $db_result->last_insert_id;
        } else {
            $group_id = FALSE;
        }
    } else {
        $group_id = FALSE;
    }
    return $group_id;
}

/**
 * update a group
 *
 * @param   int    $group_id    group id
 * @param   string $group_name  group name
 * @param   int    $group_owner user id of group owner
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_update($group_id, $group_name, $group_owner)
{
    ace_db_esc($group_name);
    $sql = "UPDATE `group` SET `name`='$group_name',
                            `owner`=$group_owner
            WHERE `id`=$group_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set group state for a given group id
 *
 * @param   int  $group_id    group id
 * @param   bool $group_state group state (active = TRUE)
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_set_state($group_id, $group_state)
{
    $state_sql = ($group_state) ? 1 : 0;
    $sql = "UPDATE `group` SET `state`=$state_sql WHERE `id`=$group_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * determine membership in group for a given user id
 *
 * @param   int $group_id group id
 * @param   int $user_id  user id
 *
 * @return  bool                    TRUE = is a member | FALSE = is NOT a member
 */
function ace_db_group_user_is_member($group_id, $user_id)
{
    $sql = "SELECT * FROM `mapGroupsUsers`
			WHERE `group_id`=$group_id
				AND `user_id`=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * add user to a group
 *
 * @param   int $group_id group id
 * @param   int $user_id  user_id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_add_user($group_id, $user_id)
{
    if (!ace_db_group_user_is_member($group_id, $user_id)) {
        $sql = "INSERT INTO `mapGroupsUsers` (`group_id`,`user_id`)
				VALUES ($group_id,$user_id)";
        $db_result = ace_db_query($sql);
        $return = ($db_result->row_count == 1) ? TRUE : FALSE;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * remove user from a group
 *
 * @param   int $group_id group id
 * @param   int $user_id  user_id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_remove_user($group_id, $user_id)
{
    $sql = "DELETE FROM `mapGroupsUsers`
			WHERE `group_id`=$group_id
				AND `user_id`=$user_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * determine if lab is a member of a group
 *
 * @param   int $group_id group id
 * @param   int $lab_id   lab_id
 *
 * @return  bool                    TRUE = is a member  | FALSE = is NOT a member
 */
function ace_db_group_lab_is_member($group_id, $lab_id)
{
    $sql = "SELECT * FROM `mapGroupsLabs`
			WHERE `group_id`=$group_id
				AND `lab_id`=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * associate a lab with a group
 *
 * @param   int $group_id group id
 * @param   int $lab_id   lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_add_lab($group_id, $lab_id)
{
    if (!ace_db_group_lab_is_member($group_id, $lab_id)) {
        $sql = "INSERT INTO `mapGroupsLabs` (`group_id`,`lab_id`)
				VALUES ($group_id,$lab_id)";
        $db_result = ace_db_query($sql);
        $return = ($db_result->row_count == 1) ? TRUE : FALSE;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * un-associate a lab from a group
 *
 * @param   int $group_id group id
 * @param   int $lab_id   lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_remove_lab($group_id, $lab_id)
{
    $sql = "DELETE FROM `mapGroupsLabs`
			WHERE `group_id`=$group_id
				AND `lab_id`=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * delete a group
 *
 * @param   int $group_id group id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_group_delete($group_id)
{
    $sql = 'DELETE FROM `group` WHERE `id`=' . $group_id;
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#COURSE
#=============================================================

/**
 * @param $course_id
 *
 * @return bool
 */
function ace_db_course_get_ref_by_id($course_id){
    $sql = "SELECT `courseID` FROM `course` WHERE `id`=$course_id";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $course_ref = $row['courseID'];
    } else {
        $course_ref = FALSE;
    }
    return $course_ref;
}

/**
 * @param $course_ref
 *
 * @return bool
 */
function ace_db_course_get_display_name_by_ref($course_ref){
    $sql = "SELECT `courseDisplayName` FROM `course` WHERE `courseID`='$course_ref'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $course_display_name = $row['courseDisplayName'];
    } else {
        $course_display_name = FALSE;
    }
    return $course_display_name;
}

/**
 * fetch course record for a give course id
 *
 * @param   int $course_id  course id
 *
 * @return array|bool               array of course information | FALSE on error
 */
function ace_db_course_get_info($course_id){
    $sql = 'SELECT * FROM `course` WHERE `id`='. $course_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $record = $db_result->table[0];
        $return = $record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * @param $course_id
 *
 * @return bool
 */
function ace_db_course_get_section_table($course_id){
    $course_ref = ace_db_course_get_ref_by_id($course_id);
    $sql = "SELECT * FROM `section` WHERE `courseID`='$course_ref'";
    $db_result = ace_db_query($sql);
    return $db_result->row_count > 0 ? $db_result->table : FALSE;
}

/**
 * @param $course_ref
 * @param $course_name
 *
 * @return bool
 */
function ace_db_course_create($course_ref, $course_name)
{
    ace_db_esc($course_ref);
    ace_db_esc($course_name);
    $sql = "INSERT INTO `course` (courseID, courseDisplayName)
            VALUES ('$course_ref', '$course_name')";
    $db_result = ace_db_query($sql);
    if ($db_result->last_insert_id != 0) {
        $return = $db_result->last_insert_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * @param $course_id
 * @param $course_ref
 * @param $course_name
 *
 * @return bool
 */
function ace_db_course_update($course_id, $course_ref, $course_name)
{
    ace_db_esc($course_ref);
    ace_db_esc($course_name);
    $sql = "UPDATE `course`
            SET `courseID`='$course_ref',
              `courseDisplayName`='$course_name'
            WHERE `id`=$course_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * @param $course_id
 *
 * @return bool
 */
function ace_db_course_delete($course_id){
    $sql = 'DELETE FROM `course` WHERE `id`=' . $course_id;
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#QUOTA
#=============================================================
/**
 * @param $quota_id
 *
 * @return bool
 */
function ace_db_quota_get_info($quota_id){
    $sql = 'SELECT * FROM `quota` WHERE `id`=' . $quota_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $record = $db_result->table[0];
        $return = $record;
    } else {
        $return = FALSE;
    }
    return $return;
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
function ace_db_quota_create($object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage){
    if ($object_type == 'host') {
        $sql = "INSERT INTO `quota` (`object_type`, `object_id`, `labs`)
                VALUES ('$object_type', $object_id, $labs)";
    } else {
        $sql = "INSERT INTO `quota` (`object_type`, `object_id`, `labs`, `vms`, `vcpu`, `memory`, `networks`, `volumes`, `storage`)
                VALUES ('$object_type', $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage)";
    }
    $db_result = ace_db_query($sql);
    if ($db_result->last_insert_id != 0) {
        $return = $quota_id = $db_result->last_insert_id;
        switch ($object_type){
            case 'group':
                $sql2 = "UPDATE `quota` SET `group_id`=" . $object_id . "WHERE `id`=" . $quota_id;
                break;
            case 'lab':
                $sql2 = "UPDATE `quota` SET `lab_id`=" . $object_id . "WHERE `id`=" . $quota_id;
                break;
            case 'user':
                $sql2 = "UPDATE `quota` SET `user_id`=" . $object_id . "WHERE `id`=" . $quota_id;
                break;
        }
        if (isset($sql2)) ace_db_query($sql2);
    } else {
        $return = FALSE;
    }
    return $return;
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
function ace_db_quota_update($quota_id, $object_type, $object_id, $labs, $vms, $vcpu, $memory, $networks, $volumes, $storage){
    if ($object_type == 'host') {
        $sql = "UPDATE `quota`
                SET `object_type`='$object_type',
                    `object_id`=$object_id,
                    `labs`=$labs
                WHERE `id`=$quota_id";
    } else {
        $sql = "UPDATE `quota`
            SET `object_type`='$object_type',
                `object_id`=$object_id,
                `labs`=$labs,
                `vms`=$vms,
                `vcpu`=$vcpu,
                `memory`=$memory,
                `networks`=$networks,
                `volumes`=$volumes,
                `storage`=$storage
            WHERE `id`=$quota_id";
    }
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * @param $quota_id
 *
 * @return bool
 */
function ace_db_quota_delete($quota_id){
    $sql = "DELETE FROM `quota` WHERE `id`=$quota_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#LAB
#=============================================================
/**
 * fetch lab id for a given lab name
 *
 * @param   string $lab_name lab name
 *
 * @return  int|bool                lab id | FALSE on error
 */
function ace_db_lab_get_id_by_name($lab_name)
{
    $sql = "SELECT id FROM lab WHERE name='$lab_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $lab_id = $row['id'];
    } else {
        $lab_id = FALSE;
    }
    return $lab_id;
}

/**
 * fetch lab name for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  string|bool             lab name | FALSE on error
 */
function ace_db_lab_get_name_by_id($lab_id)
{
    $sql = "SELECT name FROM lab WHERE id='$lab_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $lab_name = $row['name'];
    } else {
        $lab_name = FALSE;
    }
    return $lab_name;
}

/**
 * fetch lab display name for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  string|bool             lab display name | FALSE on error
 */
function ace_db_lab_get_display_name_by_id($lab_id)
{
    $sql = "SELECT display_name FROM lab WHERE id='$lab_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $lab_name = $row['display_name'];
    } else {
        $lab_name = FALSE;
    }
    return $lab_name;
}

/**
 * fetch lab record for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              array of lab information | FALSE on error
 */
function ace_db_lab_get_info($lab_id)
{
    $sql = 'SELECT * FROM lab WHERE id=' . $lab_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $record = $db_result->table[0];
        $return = $record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * @param $lab_id
 *
 * @return array|bool
 */
function ace_db_lab_get_group_ids($lab_id)
{
    $group_ids = array();
    $sql = "SELECT group_id FROM mapGroupsLabs WHERE lab_id=$lab_id";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $table = $db_result->table;
        foreach ($table as $row) {
            $group_ids[] = $row['group_id'];
        }
    } else {
        $group_ids = FALSE;
    }
    return (!empty($group_ids)) ? $group_ids : FALSE;
}

/**
 * determine state of lab for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  bool                    active TRUE/FALSE
 */
function ace_db_lab_get_state($lab_id)
{
    $sql = "SELECT state FROM lab WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * @param $lab_id
 *
 * @return bool
 */
function ace_db_lab_get_last_activated($lab_id){
    $sql = "SELECT `last_activated` FROM lab WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $last_activated = $db_result->table[0]['last_activated'];
    } else {
        $last_activated = FALSE;
    }
    return $last_activated;
}

/**
 * @param $lab_id
 *
 * @return bool
 */
function ace_db_lab_is_published($lab_id)
{
    $sql = "SELECT * FROM mapGroupsLabs WHERE `lab_id` = $lab_id";
    $db_result = ace_db_query($sql);
    return ( ( count($db_result->table) ) > 0 ) ? TRUE : FALSE;
}

/**
 * @param $lab_id
 *
 * @return mixed
 */
function ace_db_published_class_count($lab_id)
{
    $sql = "SELECT * FROM mapGroupsLabs WHERE `lab_id` = $lab_id";
    $db_result = ace_db_query($sql);
    return $db_result->row_count;
}

/**
 * fetch owner user id for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                owner user id | FALSE on error
 */
function ace_db_lab_get_user_id($lab_id)
{
    $sql = "SELECT user_id FROM lab WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $id = $db_result->table[0]['user_id'];
        $return = $id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch host_id for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                host id | FALSE on error
 */
function ace_db_lab_get_host_id($lab_id)
{
    $sql = "SELECT host_id FROM lab WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $id = $db_result->table[0]['host_id'];
        $return = $id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch table of networks associated with a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              table of networks | FALSE on error
 */
function ace_db_lab_get_network_table($lab_id)
{
    $sql = "SELECT *
    		FROM `network`
    		WHERE `lab_id`=$lab_id
    		ORDER BY `instance`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch table of volumes associated with a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              table of volumes | FALSE on error
 */
function ace_db_lab_get_volume_table($lab_id)
{
    $sql = "SELECT v1.id,
				v1.`lab_id`,
				v1.`instance`,
				v1.`type`,
				v1.`name`,
				v1.`virt_id`,
				v1.`display_name`,
				v1.`size`,
				v1.`size_on_disk`,
				v1.`unit`,
				v1.`base_id`,
				v1.`user_visible`,
				v1.`state`,
				v2.`name` AS `base_name`,
				v2.`virt_id` AS `base_virt_id`,
				v2.`display_name` AS `base_display_name`,
				v2.`size` AS `base_size`,
				v2.`size_on_disk` AS `base_size_on_disk`,
				v2.`unit` AS `base_unit`
			FROM `volume` AS v1
				LEFT JOIN `volume` AS v2 ON v1.`base_id` = v2.`id`
			WHERE v1.`lab_id` = $lab_id
			ORDER BY v1.`instance`,v1.`display_name` ASC";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch a table of vm associated with a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              table of vm | FALSE on error
 */
function ace_db_lab_get_vm_table($lab_id)
{
    $sql = "SELECT *
    		FROM `vm`
    		WHERE `lab_id`=$lab_id
    		ORDER BY `instance`";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch next available (within quota) network instance number for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                network instance number | FALSE on error
 */
function ace_db_lab_get_available_network_instance($lab_id)
{
    $quota_array = ace_db_lab_get_quota_array($lab_id);
    $network_quota = $quota_array['networks'];
    $instance_list = array();
    $lab_network_table = ace_db_lab_get_network_table($lab_id);
    if (is_array($lab_network_table)) {
        for ($row = 0; $row < count($lab_network_table); $row++) {
            $instance_list[ $row ] = $lab_network_table[ $row ]['instance'];
        }
        $valid_instances = range(0, $network_quota);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 0;
    }
    return $return;
}

/**
 * fetch next available (within quota) volume instance number for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                volume instance number | FALSE on error
 */
function ace_db_lab_get_available_volume_instance($lab_id)
{
    $quota_array = ace_db_lab_get_quota_array($lab_id);
    $volume_quota = $quota_array['volumes'];
    $instance_list = array();
    $lab_volume_table = ace_db_lab_get_volume_table($lab_id);
    if (is_array($lab_volume_table)) {
        for ($row = 0; $row < count($lab_volume_table); $row++) {
            $instance_list[ $row ] = $lab_volume_table[ $row ]['instance'];
        }
        $valid_instances = range(0, $volume_quota);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 0;
    }
    return $return;
}

/**
 * fetch next available (within quota) vm instance number for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                vm instance number | FALSE on error
 */
function ace_db_lab_get_available_vm_instance($lab_id)
{
    $quota_array = ace_db_lab_get_quota_array($lab_id);
    $vm_quota = $quota_array['vms'];
    $instance_list = array();
    $table = ace_db_lab_get_vm_table($lab_id);
    if (is_array($table)) {
        for ($row = 0; $row < count($table); $row++) {
            $instance_list[ $row ] = $table[ $row ]['instance'];
        }
        $valid_instances = range(0, $vm_quota);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 0;
    }
    return $return;
}

/**
 * fetch quota array for a given lab_id
 *
 * @param   int $lab_id lab id
 *
 * @return  array|bool              array of quotas | FALSE on error
 */
function ace_db_lab_get_quota_array($lab_id)
{
    $user_id = ace_db_lab_get_user_id($lab_id);
    $group_id_array = ace_db_user_get_group_ids($user_id);
    $str_group_ids = implode(',', $group_id_array);
    $sql = "SELECT MIN(`labs`) as `labs`,
					MIN(`vms`) as `vms`,
					MIN(`vcpu`) as `vcpu`,
					MIN(`memory`) as `memory`,
					MIN(`networks`) as `networks`,
					MIN(`volumes`) as `volumes`,
					MIN(`storage`) as `storage`
			FROM quota
			WHERE (lab_id=$lab_id OR user_id=$user_id OR group_id IN ($str_group_ids) )";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $quota_array = $db_result->table[0];
    } else {
        $quota_array = FALSE;
    }
    return $quota_array;
}

/**
 * @param $lab_id
 *
 * @return bool|int
 */
function ace_db_lab_get_age($lab_id)
{
    $sql = "SELECT `last_activated` FROM `lab` WHERE `id`=$lab_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $last_activated = $db_result->table[0]['last_activated'];
        $age = time() - $last_activated;
        $return = $age;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * create new lab for a given user id
 *
 * @param   int $user_id user id
 *
 * @return  int|bool                new lab id | FALSE on error
 */
function ace_db_lab_create($user_id)
{
    $lab_instance = ace_db_user_get_available_lab_instance($user_id);
    if ($lab_instance !== FALSE) {
        # compute lab-name from user_id and instance
        $lab_name = str_pad($user_id, 5, '0', STR_PAD_LEFT) . '-lab-' . str_pad($lab_instance, 2, '0', STR_PAD_LEFT);
        $display_name = 'lab-' . str_pad($lab_instance, 2, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO lab (`user_id`,`instance`,`name`,`display_name`,`state`)
	            VALUES ($user_id,$lab_instance,'$lab_name','$display_name',0)";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $return = $db_result->last_insert_id;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * rename a lab for a given lab id
 *
 * @param   int    $lab_id           lab id
 * @param   string $lab_display_name lab display name
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_lab_rename($lab_id, $lab_display_name)
{
    ace_db_esc($lab_display_name);
    $sql = "UPDATE lab SET display_name='$lab_display_name'
			WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * duplicate a lab and associate with a user
 *
 * @param   int $from_lab_id lab id
 * @param   int $to_user_id  user id
 *
 * @return  int|bool                new lab id | FALSE on error
 */
function ace_db_lab_duplicate($from_lab_id, $to_user_id)
{
    $from_lab_info = ace_db_lab_get_info($from_lab_id);
    $to_lab_instance = ace_db_user_get_available_lab_instance($to_user_id);
    if ($to_lab_instance !== FALSE) {
        $to_lab_name = str_pad($to_user_id, 5, '0', STR_PAD_LEFT) . '-lab-' . str_pad($to_lab_instance, 2, '0', STR_PAD_LEFT);
        $to_lab_display_name = $from_lab_info['display_name'];
        $sql = "INSERT INTO lab (
					`user_id`,
					`instance`,
					`name`,
					`display_name`,
					`state`)
				VALUES (
					$to_user_id,
					$to_lab_instance,
					'$to_lab_name',
					'$to_lab_display_name',
					0)";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $return = $db_result->last_insert_id;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * update a lab record
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
function ace_db_lab_update($lab_id, $lab_user_id, $lab_host_id, $lab_name, $lab_display_name, $lab_description)
{
    ace_db_esc($lab_name);
    ace_db_esc($lab_display_name);
    ace_db_esc($lab_description);
    $sql = "UPDATE lab
            SET `user_id`='$lab_user_id',
                `host_id`='$lab_host_id',
                `name`='$lab_name',
                `display_name`='$lab_display_name',
                `description`='$lab_description'
			WHERE `id`=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set state of a lab
 *
 * @param   int  $lab_id    lab id
 * @param   bool $lab_state lab state (active = TRUE)
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_lab_set_state($lab_id, $lab_state)
{
    $state = ($lab_state) ? 1 : 0;
    $timestamp = time();
    $sql = "UPDATE lab
            SET `state`=$state,
            `last_activated`=$timestamp
            WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * set host associated with lab
 *
 * @param   int $lab_id  lab id
 * @param   int $host_id host id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_lab_set_host_id($lab_id, $host_id)
{
    if ($host_id === NULL) $host_id = 'null';
    $sql = "UPDATE lab
            SET host_id=$host_id
            WHERE id=$lab_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * delete a lab
 *
 * @param   int $lab_id lab id
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_lab_delete($lab_id)
{
    $sql = 'DELETE
            FROM lab
            WHERE id=' . $lab_id;
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#NETWORK
#=============================================================
/**
 * fetch network id for a given network name
 *
 * @param   string $network_name network name
 *
 * @return  int|bool                network id | FALSE on error
 */
function ace_db_network_get_id_by_name($network_name)
{
    $sql = "SELECT id
            FROM network
            WHERE name='$network_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $network_id = $row['id'];
    } else {
        $network_id = FALSE;
    }
    return $network_id;
}

/**
 * fetch network id for a given lab id and network instance
 *
 * @param   int $lab_id   lab id
 * @param   int $instance network instance
 *
 * @return  int|bool                network id | FALSE on error
 */
function ace_db_network_get_id_by_lab_instance($lab_id, $instance)
{
    $sql = "SELECT id
			FROM network
			WHERE lab_id=$lab_id
				AND instance=$instance";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $network_id = $row['id'];
    } else {
        $network_id = FALSE;
    }
    return $network_id;
}

/**
 * fetch network name for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  string|bool                 network name | FALSE on error
 */
function ace_db_network_get_name_by_id($network_id)
{
    $sql = "SELECT name
            FROM network
            WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $network_name = $row['name'];
    } else {
        $network_name = FALSE;
    }
    return $network_name;
}

/**
 * fetch network display name for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  string|bool                 network display name | FALSE on error
 */
function ace_db_network_get_display_name_by_id($network_id)
{
    $sql = "SELECT display_name
            FROM network
            WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $network_name = $row['display_name'];
    } else {
        $network_name = FALSE;
    }
    return $network_name;
}

/**
 * fetch network information for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  array|bool              network information | FALSE on error
 */
function ace_db_network_get_info($network_id)
{
    $sql = 'SELECT * FROM network WHERE id=' . $network_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $network_record = $db_result->table[0];
        $return = $network_record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * determine network state for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  bool                    active = TRUE | inactive = FALSE
 */
function ace_db_network_get_state($network_id)
{
    $sql = "SELECT state FROM network WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * fetch lab id for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  int|bool                lab id | FALSE on error
 */
function ace_db_network_get_lab_id($network_id)
{
    $sql = "SELECT lab_id FROM network WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $lab_id = $db_result->table[0]['lab_id'];
        $return = $lab_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch network virt id for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  string|bool             network virt id | FALSE on error
 */
function ace_db_network_get_virt_id($network_id)
{
    $sql = "SELECT `virt_id` FROM `network` WHERE `id`=$network_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $network_virt_id = $db_result->table[0]['virt_id'];
        $return = $network_virt_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * create a network for a given lab id
 *
 * @param   int $lab_id lab id
 *
 * @return  int|bool                new network id | FALSE on error
 */
function ace_db_network_create($lab_id)
{
    $network_instance = ace_db_lab_get_available_network_instance($lab_id);
    if ($network_instance !== FALSE) {
        $network_name = str_pad($lab_id, 5, '0', STR_PAD_LEFT) . '-net-' . str_pad($network_instance, 2, '0', STR_PAD_LEFT);
        $virt_id = $network_name;
        $display_name = ($network_instance == 0) ? _LAB_ROUTER_PUBLIC_NETWORK_NAME_ : ('net-' . str_pad($network_instance, 2, '0', STR_PAD_LEFT));
        $sql = "INSERT INTO network (`lab_id`,`instance`,`name`,`virt_id`,`display_name`,`user_visible`,`state`)
	            VALUES ($lab_id,$network_instance,'$network_name','$virt_id','$display_name',1,1)";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $return = $db_result->last_insert_id;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * duplicate a network for a given network id into a given lab id
 *
 * @param   int $from_network_id network id
 * @param   int $to_lab_id       lab id
 *
 * @return  int|bool                new network id | FALSE on error
 */
function ace_db_network_duplicate($from_network_id, $to_lab_id)
{
    $from_network_info = ace_db_network_get_info($from_network_id);
    $to_network_instance = $from_network_info['instance'];
    $to_network_name = str_pad($to_lab_id, 5, '0', STR_PAD_LEFT) . '-net-' . str_pad($to_network_instance, 2, '0', STR_PAD_LEFT);
    $to_network_virt_id = $to_network_name;
    $sql = "INSERT INTO `network` (
				`lab_id`,
				`instance`,
				`name`,
				`virt_id`,
				`display_name`,
				`user_visible`,
				`state`)
			VALUES (
				" . $to_lab_id . ",
				" . $to_network_instance . ",
				'" . $to_network_name . "',
				'" . $to_network_virt_id . "',
				'" . $from_network_info['display_name'] . "',
				" . $from_network_info['user_visible'] . ",
				" . $from_network_info['state'] . ")";
    $db_result = ace_db_query($sql);
    if ($db_result->last_insert_id != 0) {
        $return = $db_result->last_insert_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * rename (display name) a network for a given network id
 *
 * @param   int    $network_id       network id
 * @param   string $network_new_name display name
 *
 * @return  bool                    on success TRUE/FALSE
 */
function ace_db_network_rename($network_id, $network_new_name)
{
    ace_db_esc($network_new_name);
    $sql = "UPDATE network
			SET `display_name`='$network_new_name'
			WHERE `id`=$network_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set user visibility flag for a given network id
 *
 * @param   int  $network_id   network id
 * @param   bool $user_visible visibility flag
 *
 * @return  bool               on success TRUE/FALSE
 */
function ace_db_network_set_user_visible($network_id, $user_visible)
{
    $sql_user_visible = ($user_visible) ? 1 : 0;
    $sql = "UPDATE network
            SET `user_visible`=$sql_user_visible
			WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set active state for a give network id
 *
 * @param   int  $network_id network id
 * @param   bool $state      state flag
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_network_set_state($network_id, $state)
{
    $state_sql = ($state) ? 1 : 0;
    $sql = "UPDATE network
            SET state=$state_sql
            WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * delete a network for a given network id
 *
 * @param   int $network_id network id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_network_delete($network_id)
{
    # remove network from vms first
    $sql = "SELECT vm_id FROM vnic WHERE network_id=$network_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        foreach ($db_result->table as $row) {
            ace_db_vm_detach_nic($row['vm_id'], $row['instance']);
        }
    }
    $sql = "DELETE FROM network WHERE id=$network_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#VOLUME
#=============================================================
/**
 * fetch volume id for a given volume name
 *
 * @param   string $volume_name network name
 *
 * @return  int|bool            network id | FALSE on error
 */
function ace_db_volume_get_id_by_name($volume_name)
{
    $sql = "SELECT id
            FROM volume
            WHERE name='$volume_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $volume_id = $row['id'];
    } else {
        $volume_id = FALSE;
    }
    return $volume_id;
}

/**
 * fetch volume id fro a given lab volume instance
 *
 * @param   int $lab_id   lab id
 * @param   int $instance lab volume instance
 *
 * @return  int|bool            volume id | FALSE on error
 */
function ace_db_volume_get_id_by_lab_instance($lab_id, $instance)
{
    $sql = "SELECT id
			FROM volume
			WHERE lab_id=$lab_id
				AND instance=$instance";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $volume_id = $row['id'];
    } else {
        $volume_id = FALSE;
    }
    return $volume_id;
}

/**
 * fetch volume name for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return  string|bool         volume name | FALSE on error
 */
function ace_db_volume_get_name_by_id($volume_id)
{
    $sql = "SELECT name
            FROM volume
            WHERE id='$volume_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $volume_name = $row['name'];
    } else $volume_name = FALSE;
    return $volume_name;
}

/**
 * fetch volume display name for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return  string|bool         volume display name | FALSE on error
 */
function ace_db_volume_get_display_name_by_id($volume_id)
{
    $sql = "SELECT display_name
            FROM volume
            WHERE id='$volume_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $volume_name = $row['display_name'];
    } else $volume_name = FALSE;
    return $volume_name;
}

/**
 * fetch volume information for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return array|bool           volume information | FALSE on error
 */
function ace_db_volume_get_info($volume_id)
{
    $sql = 'SELECT *
            FROM volume
            WHERE id=' . $volume_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $volume_record = $db_result->table[0];
        $return = $volume_record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch vm assignments for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return  array|bool          volume assignments | FALSE on error
 */
function ace_db_volume_get_vm_assignments($volume_id)
{
    $vm_assignments = array();
    $sql = "SELECT vm_id
            FROM vdisk
            WHERE volume_id=$volume_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        foreach ($db_result->table as $row) {
            $vm_assignments[] = $row['vm_id'];
        }
        $return = $vm_assignments;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * determine active state for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return  bool                TRUE = active | FALSE = inactive
 */
function ace_db_volume_get_state($volume_id)
{
    $sql = "SELECT state
            FROM volume
            WHERE id=$volume_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * fetch lab id for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return  int|bool            lab id | FALSE on error
 */
function ace_db_volume_get_lab_id($volume_id)
{
    $sql = "SELECT lab_id
            FROM volume
            WHERE id=$volume_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $lab_id = $db_result->table[0]['lab_id'];
        $return = $lab_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch virt id for a given volume id
 *
 * @param   int $volume_id volume id
 *
 * @return  string|bool         virt id | FALSE on error
 */
function ace_db_volume_get_virt_id($volume_id)
{
    $sql = "SELECT virt_id
            FROM volume
            WHERE id=$volume_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $virt_id = $db_result->table[0]['virt_id'];
        $return = $virt_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * create a volume
 *
 * @param   int $lab_id         lab id
 * @param   int $volume_size    volume size (measured in units)
 * @param   int $volume_unit    "M" | "G"
 * @param   int $volume_base_id volume id
 *
 * @return  int|bool            new volume id | FALSE on error
 */
function ace_db_volume_create($lab_id, $volume_size, $volume_unit, $volume_base_id)
{
    $volume_instance = ace_db_lab_get_available_volume_instance($lab_id);
    if ($volume_instance !== FALSE) {
        $volume_name = str_pad($lab_id, 5, '0', STR_PAD_LEFT) . '-vol-' . str_pad($volume_instance, 2, '0', STR_PAD_LEFT);
        $virt_id = $volume_name;
        $display_name = 'vol-' . str_pad($volume_instance, 2, '0', STR_PAD_LEFT);
        $sql_base_id = ($volume_base_id == NULL) ? 'NULL' : $volume_base_id;
        $sql = "INSERT INTO volume (
                    `lab_id`,
                    `instance`,
                    `type`,
                    `name`,
                    `virt_id`,
                    `display_name`,
                    `size`,
                    `unit`,
                    `base_id`,
                    `user_visible`,
                    `state`)
	            VALUES (
	                $lab_id,
	                $volume_instance,
	                'img',
	                '$volume_name',
	                '$virt_id',
	                '$display_name',
	                $volume_size,
	                '$volume_unit',
	                $sql_base_id,
	                1,
	                1)";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $return = $db_result->last_insert_id;
        } else {
            $return = FALSE;
        }
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * duplicate an existing volume into a lab
 *
 * @param   int $from_volume_id volume id
 * @param   int $to_lab_id      lab id
 *
 * @return  int|bool            new volume id | FALSE on error
 */
function ace_db_volume_duplicate($from_volume_id, $to_lab_id)
{
    $from_volume_info = ace_db_volume_get_info($from_volume_id);
    $to_volume_instance = $from_volume_info['instance'];
    $to_volume_name = str_pad($to_lab_id, 5, '0', STR_PAD_LEFT) . '-vol-' . str_pad($to_volume_instance, 2, '0', STR_PAD_LEFT);
    $to_volume_virt_id = $to_volume_name;
    $sql = "INSERT INTO `volume` (
				`lab_id`,
				`instance`,
				`type`,
				`name`,
				`virt_id`,
				`display_name`,
				`size`,
				`size_on_disk`,
				`unit`,
				`base_id`,
				`user_visible`,
				`state`)
			VALUES (
				" . $to_lab_id . ",
				" . $to_volume_instance . ",
				'" . $from_volume_info['type'] . "',
				'" . $to_volume_name . "',
				'" . $to_volume_virt_id . "',
				'" . $from_volume_info['display_name'] . "',
				" . $from_volume_info['size'] . ",
				" . (($from_volume_info['size_on_disk'] !== NULL) ? $from_volume_info['size_on_disk'] : 'NULL') . ",
				'" . $from_volume_info['unit'] . "',
				" . (($from_volume_info['base_id'] !== NULL) ? $from_volume_info['base_id'] : 'NULL') . ",
				" . $from_volume_info['user_visible'] . ",
				" . $from_volume_info['state'] . ")";
    $db_result = ace_db_query($sql);
    if ($db_result->last_insert_id != 0) {
        $return = $db_result->last_insert_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * update volume display name for a given volume id
 *
 * @param   int    $volume_id           volume id
 * @param   string $volume_display_name volume display name
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_volume_update($volume_id, $volume_display_name)
{
    ace_db_esc($volume_display_name);
    $sql = "UPDATE volume
            SET `display_name`='$volume_display_name'
			WHERE `id`=$volume_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set user visibility for a given volume id
 *
 * @param   int  $volume_id    volume id
 * @param   bool $user_visible visibility state flag
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_volume_set_user_visible($volume_id, $user_visible)
{
    $sql_user_visible = ($user_visible) ? 1 : 0;
    $sql = "UPDATE volume
            SET user_visible=$sql_user_visible
			WHERE id=$volume_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set active state for a given volume
 *
 * @param   int  $volume_id volume id
 * @param   bool $state     active state flag
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_volume_set_state($volume_id, $state)
{
    $state_sql = ($state) ? 1 : 0;
    $sql = "UPDATE volume
            SET state=$state_sql
            WHERE id=$volume_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count == 1) ? TRUE : FALSE;
}

/**
 * delete a volume
 *
 * @param   int $volume_id volume id
 *
 * @return bool                 on success TRUE/FALSE
 */
function ace_db_volume_delete($volume_id)
{
    # remove volume from vms first
    $sql = "SELECT *
            FROM vdisk
            WHERE volume_id=$volume_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        foreach ($db_result->table as $row) {
            ace_db_vm_detach_disk($row['vm_id'], $row['instance']);
        }
    }
    $sql = "DELETE
            FROM volume
            WHERE id=$volume_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}


#=============================================================
#VM
#=============================================================
/**
 * fetch vm id for a given vm name
 *
 * @param   string $vm_name vm name
 *
 * @return  int|bool            vm id | FALSE on error
 */
function ace_db_vm_get_id_by_name($vm_name)
{
    $sql = "SELECT id
            FROM vm
            WHERE name='$vm_name'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $vm_id = $row['id'];
    } else {
        $vm_id = FALSE;
    }
    return $vm_id;
}

/**
 * fetch vm name for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  string|bool         vm name | FALSE on error
 */
function ace_db_vm_get_name_by_id($vm_id)
{
    $sql = "SELECT name
            FROM vm
            WHERE id='$vm_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $vm_name = $row['name'];
    } else {
        $vm_name = FALSE;
    }
    return $vm_name;
}

/**
 * fetch vm display name for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  string|bool         vm display name | FALSE on error
 */
function ace_db_vm_get_display_name_by_id($vm_id)
{
    $sql = "SELECT display_name
            FROM vm
            WHERE id='$vm_id'";
    $db_result = ace_db_query($sql);
    $count = count($db_result->table);
    if ($count > 0) {
        $row = $db_result->table[0];
        $vm_name = $row['display_name'];
    } else {
        $vm_name = FALSE;
    }
    return $vm_name;
}

/**
 * fetch vm information for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          vm information | FALSE on error
 */
function ace_db_vm_get_info($vm_id)
{
    $sql = 'SELECT *
            FROM vm
            WHERE id =' . $vm_id;
    $db_result = ace_db_query($sql);
    if ($db_result->row_count == 1) {
        $vm_record = $db_result->table[0];
        $return = $vm_record;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * determine active state for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                TRUE = active | FALSE = inactive
 */
function ace_db_vm_get_state($vm_id)
{
    $sql = "SELECT state
            FROM vm
            WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->table[0]['state'] == 1) ? TRUE : FALSE;
}

/**
 * fetch lab id for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            lab id | FALSE on error
 */
function ace_db_vm_get_lab_id($vm_id)
{
    $sql = "SELECT lab_id
            FROM vm
            WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $vm_lab_id = $db_result->table[0]['lab_id'];
        $return = $vm_lab_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch vm virt id for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  string|bool         vm virt id | FALSE on error
 */
function ace_db_vm_get_virt_id($vm_id)
{
    $sql = "SELECT virt_id
            FROM vm
            WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $vm_virt_id = $db_result->table[0]['virt_id'];
        $return = $vm_virt_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch table of vm cdrom devices associated with a vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          table of vm cdrom devices | FALSE on error
 */
function ace_db_vm_get_cdrom_table($vm_id)
{
    $sql = "SELECT *
            FROM vcdrom
            WHERE vm_id=$vm_id
            ORDER by instance";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch vm cdrom information
 *
 * @param   int $vm_cdrom_id vm cdrom id
 *
 * @return  array|bool          table of vm cdrom info | FALSE on error
 */
function ace_db_vm_cdrom_get_info($vm_cdrom_id)
{
    $sql = "SELECT *
            FROM vcdrom
            WHERE id=$vm_cdrom_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $record = $db_result->table[0];
    } else {
        $record = FALSE;
    }
    return $record;
}

/**
 * determine next available (within quota) instance number for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            vm cdrom instance | FALSE on error
 */
function ace_db_vm_get_available_cdrom_instance($vm_id)
{
    $max_cdroms = 4;
    $instance_list = array();
    $table = ace_db_vm_get_cdrom_table($vm_id);
    if (is_array($table)) {
        for ($row = 0; $row < count($table); $row++) {
            $instance_list[ $row ] = $table[ $row ]['instance'];
        }
        $valid_instances = range(1, $max_cdroms);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 1;
    }
    return $return;
}

/**
 * fetch table of vm disk devices associated with a vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          table of vm disk devices | FALSE on error
 */
function ace_db_vm_get_disk_table($vm_id)
{
    $sql = "SELECT *
            FROM vdisk
            WHERE vm_id=$vm_id
            ORDER by instance";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch vm disk information
 *
 * @param   int $vm_disk_id vm disk id
 *
 * @return array|bool           table of vm disk information | FALSE on error
 */
function ace_db_vm_disk_get_info($vm_disk_id)
{
    $sql = "SELECT *
            FROM vdisk
            WHERE id=$vm_disk_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $record = $db_result->table[0];
    } else {
        $record = FALSE;
    }
    return $record;
}

/**
 * determine next available (within quota) vm disk instance for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return int|bool             vm disk instance | FALSE on error
 */
function ace_db_vm_get_available_disk_instance($vm_id)
{
    $max_disks = 4;
    $instance_list = array();
    $table = ace_db_vm_get_disk_table($vm_id);
    if (is_array($table)) {
        for ($row = 0; $row < count($table); $row++) {
            $instance_list[ $row ] = $table[ $row ]['instance'];
        }
        $valid_instances = range(1, $max_disks);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 1;
    }
    return $return;
}

/**
 * fetch table of vm nic device for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  array|bool          vm nic table | FALSE on error
 */
function ace_db_vm_get_nic_table($vm_id)
{
    $sql = "SELECT *,
                int2mac(mac_index) AS mac_address
            FROM vnic
            WHERE vm_id=$vm_id
            ORDER BY instance";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $table = $db_result->table;
    } else {
        $table = FALSE;
    }
    return $table;
}

/**
 * fetch vm nic information
 *
 * @param   int $vm_nic_id vm nic id
 *
 * @return  array|bool          table of vm nic information | FALSE on error
 */
function ace_db_vm_nic_get_info($vm_nic_id)
{
    $sql = "SELECT *,
                int2mac(mac_index) AS mac_address
            FROM vnic
            WHERE id=$vm_nic_id";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $record = $db_result->table[0];
    } else {
        $record = FALSE;
    }
    return $record;
}

/**
 * determine next available (within quota) nic instance number for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            vm nic instance number | FALSE on error
 */
function ace_db_vm_get_available_nic_instance($vm_id)
{
    $max_nics = 4;
    $instance_list = array();
    $table = ace_db_vm_get_nic_table($vm_id);
    if (is_array($table)) {
        for ($row = 0; $row < count($table); $row++) {
            $instance_list[ $row ] = $table[ $row ]['instance'];
        }
        $valid_instances = range(1, $max_nics);
        $available_instances = array_diff($valid_instances, $instance_list);
        if (count($available_instances) > 0) {
            $available_instance = reset($available_instances);
            $return = $available_instance;
        } else {
            $return = FALSE;
        }
    } else {
        $return = 1;
    }
    return $return;
}

/**
 * fetch mac address assigned to a vm nic instance for a given vm id
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  string|bool         vm nic mac address | FALSE on error
 */
function ace_db_vm_nic_get_mac_address($vm_id, $vm_nic_instance)
{
    $sql = "SELECT mac_index
            FROM vnic
            WHERE vm_id=$vm_id
              AND instance=$vm_nic_instance";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $mac_index = $db_result->table[0]['mac_index'];
        $mac_address = ace_gen_convert_int2mac($mac_index);
        $return = $mac_address;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * fetch network id associated with a vm nic for a given vm id and vm nic instance
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  int|bool            network id | FALSE on error
 */
function ace_db_vm_nic_get_network_id($vm_id, $vm_nic_instance)
{
    $sql = "SELECT network_id
            FROM vnic
            WHERE vm_id=$vm_id
              AND instance=$vm_nic_instance";
    $db_result = ace_db_query($sql);
    if ($db_result->row_count > 0) {
        $network_id = $db_result->table[0]['network_id'];
        $return = $network_id;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * update vm nic mac address
 *
 * @param   int    $vm_id              vm id
 * @param   int    $vm_nic_instance    vm nic instance
 * @param   string $vm_nic_mac_address vm nic mac address
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_nic_update($vm_id, $vm_nic_instance, $vm_nic_mac_address)
{
    $sql = "UPDATE vnic
            SET `mac_index` = mac2int('$vm_nic_mac_address')
			WHERE `vm_id`=$vm_id
			  AND `instance`=$vm_nic_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * create a vm in a given lab id
 *
 * @param   int    $lab_id    lab id
 * @param   int    $vm_vcpu   number of vcpu
 * @param   int    $vm_memory amount of memory (measured in units)
 * @param   string $vm_unit   "M" | "G"
 * @param   string $vm_profile "linux" | "w8" | et al
 *
 * @return  int|bool            new vm id | FALSE on error
 */
function ace_db_vm_create($lab_id, $vm_vcpu, $vm_memory, $vm_unit, $vm_profile)
{
    $vm_instance = ace_db_lab_get_available_vm_instance($lab_id);
    if ($vm_instance === FALSE) {
        $return = FALSE;
    } else {
        $vm_name = str_pad($lab_id, 5, '0', STR_PAD_LEFT) . '-vm-' . str_pad($vm_instance, 2, '0', STR_PAD_LEFT);
        $virt_id = $vm_name;
        $display_name = 'vm-' . str_pad($vm_instance, 2, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO vm (`lab_id`,`instance`,`name`,`virt_id`,`display_name`,`vcpu`,`memory`,`unit`,`arch`,`profile`,`user_visible`,`state`)
	            VALUES ($lab_id,$vm_instance,'$vm_name','$virt_id','$display_name',$vm_vcpu,$vm_memory,'$vm_unit','x86_64','$vm_profile',1,0)";
        $db_result = ace_db_query($sql);
        if ($db_result->last_insert_id != 0) {
            $return = $db_result->last_insert_id;
        } else {
            $return = FALSE;
        }
    }
    return $return;
}

/**
 * duplicate an existing vm to a given lab id
 *
 * @param   int $from_vm_id vm id
 * @param   int $to_lab_id  lab id
 *
 * @return  int|bool            new vm id | FALSE on error
 */
function ace_db_vm_duplicate($from_vm_id, $to_lab_id)
{
    $from_vm_info = ace_db_vm_get_info($from_vm_id);
    $to_vm_instance = $from_vm_info['instance'];
    $to_vm_name = str_pad($to_lab_id, 5, '0', STR_PAD_LEFT) . '-vm-' . str_pad($to_vm_instance, 2, '0', STR_PAD_LEFT);
    $to_vm_virt_id = $to_vm_name;
    $sql = "INSERT INTO `vm` (
				`lab_id`,
				`instance`,
				`name`,
				`virt_id`,
				`display_name`,
				`vcpu`,
				`memory`,
				`unit`,
				`arch`,
				`profile`,
				`user_visible`,
				`state`)
			VALUES (
				$to_lab_id,
				$to_vm_instance,
				'$to_vm_name',
				'$to_vm_virt_id',
				'" . $from_vm_info['display_name'] . "',
				" . $from_vm_info['vcpu'] . ",
				" . $from_vm_info['memory'] . ",
				'" . $from_vm_info['unit'] . "',
				'" . $from_vm_info['arch'] . "',
				'" . $from_vm_info['profile'] . "',
				" . $from_vm_info['user_visible'] . ",
				" . $from_vm_info['state'] . ")";
    $db_result = ace_db_query($sql);
    if ($db_result->last_insert_id != 0) {
        $to_vm_id = $db_result->last_insert_id;

        $from_vm_vnic_table = ace_db_vm_get_nic_table($from_vm_id);
        foreach ($from_vm_vnic_table as $from_vm_vnic) {
            $from_vm_vnic_network_info = ace_db_network_get_info($from_vm_vnic['network_id']);
            if ($from_vm_vnic_network_info['lab_id'] == 0) {
                $to_vm_vnic_network_id = $from_vm_vnic['network_id'];
            } else {
                $to_vm_vnic_network_id = ace_db_network_get_id_by_lab_instance($to_lab_id, $from_vm_vnic_network_info['instance']);
            }
            //$sql = "INSERT INTO vnic (
				//		`vm_id`,
				//		`instance`,
				//		`network_id`)
				//	VALUES (
				//		$to_vm_id,
				//		" . $from_vm_vnic['instance'] . ",
				//		" . $to_vm_vnic_network_id . ")";
            $sql = "INSERT INTO vnic (
						`vm_id`,
						`instance`,
						`mac_index`,
						`network_id`)
					VALUES (
						$to_vm_id,
						" . $from_vm_vnic['instance'] . ",
						(SELECT t1.mac_index + 1 AS mac_index
                            FROM `vnic` AS t1
                            LEFT JOIN `vnic` AS t2 ON t1.mac_index + 1 = t2.mac_index
                            WHERE t2.mac_index IS NULL
                                AND t1.mac_index >= " . _MAC_POOL_INDEX_START_ . "
                                AND t1.mac_index < " . _MAC_POOL_INDEX_END_ . "
                            ORDER BY t1.mac_index
                            LIMIT 1
                        ),
						" . $to_vm_vnic_network_id . ")";
            ace_db_query($sql);
            //$db_result = ace_db_query($sql);
            //if ($db_result->last_insert_id != 0) {
            //	$to_vm_vnic_id = $db_result->last_insert_id;
            //} else {
            //	$to_vm_vnic_id = FALSE;
            //}
        }

        $from_vm_vcdrom_table = ace_db_vm_get_cdrom_table($from_vm_id);
        foreach ($from_vm_vcdrom_table as $from_vm_vcdrom) {
            $sql = "INSERT INTO vcdrom (
						`vm_id`,
						`instance`,
						`volume_id`)
					VALUES (
						$to_vm_id,
						" . $from_vm_vcdrom['instance'] . ",
						" . (($from_vm_vcdrom['volume_id'] != NULL) ? $from_vm_vcdrom['volume_id'] : 'NULL') . ")";
            ace_db_query($sql);
            //$db_result = ace_db_query($sql);
            //if ($db_result->last_insert_id != 0) {
            //	$to_vm_vcdrom_id = $db_result->last_insert_id;
            //} else {
            //	$to_vm_vcdrom_id = FALSE;
            //}
        }

        $from_vm_vdisk_table = ace_db_vm_get_disk_table($from_vm_id);
        foreach ($from_vm_vdisk_table as $from_vm_vdisk) {
            $from_vm_vdisk_volume_info = ace_db_volume_get_info($from_vm_vdisk['volume_id']);
            $from_vm_vdisk_volume_instance = $from_vm_vdisk_volume_info['instance'];

            # find volume_id by lab and instance
            $to_vm_vdisk_volume_id = ace_db_volume_get_id_by_lab_instance($to_lab_id, $from_vm_vdisk_volume_instance);

            # find new volume_id backed by from_vm_vdisk['volume_id']
            /* $sql = "SELECT id
                    FROM volume
                    WHERE lab_id=$to_lab_id
                        AND base_id=" .	$from_vm_vdisk['volume_id']; */
            // echo '$sql' . d($sql);
            // $db_result = ace_db_query($sql);
            // echo '$db_result' . d($db_result);
            // $to_vm_vdisk_volume_id = $db_result->table[0]['id'];
            // echo '$to_vm_vdisk_volume_id' . d($to_vm_vdisk_volume_id);

            $sql = "INSERT INTO vdisk (
						`vm_id`,
						`instance`,
						`volume_id`)
					VALUES (
						$to_vm_id,
						" . $from_vm_vdisk['instance'] . ",
						$to_vm_vdisk_volume_id)";
            // echo '$sql' . d($sql);
            ace_db_query($sql);
            //$db_result = ace_db_query($sql);
            // echo '$db_result' . d($db_result);
            //if ($db_result->last_insert_id != 0) {
            //	$to_vm_vdisk_id = $db_result->last_insert_id;
            //} else {
            //	$to_vm_vdisk_id = FALSE;
            //}
        }
    } else {
        $to_vm_id = FALSE;
    }
    return $to_vm_id;
}

/**
 * update a vm
 *
 * @param   int    $vm_id           vm id
 * @param   string $vm_display_name vm display name
 * @param   int    $vm_vcpu         number of vcpu
 * @param   int    $vm_memory       amount of memory (measured in units)
 * @param   string $vm_unit         "M" | "G"
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_update($vm_id, $vm_display_name, $vm_vcpu, $vm_memory, $vm_unit)
{
    ace_db_esc($vm_display_name);
    $sql = "UPDATE vm
            SET `display_name`='$vm_display_name',
                `vcpu`='$vm_vcpu',
                `memory`='$vm_memory',
                `unit`='$vm_unit'
			WHERE `id`=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * rename a vm (display name) for a given vm id
 *
 * @param   int    $vm_id       vm id
 * @param   string $vm_new_name vm display name
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_rename($vm_id, $vm_new_name)
{
    ace_db_esc($vm_new_name);
    $sql = "UPDATE vm
            SET display_name='$vm_new_name'
			WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set user visibility flag for a given vm id
 *
 * @param   int  $vm_id        vm id
 * @param   bool $user_visible visibility state flag
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_set_user_visible($vm_id, $user_visible)
{
    $sql_user_visible = ($user_visible) ? 1 : 0;
    $sql = "UPDATE vm
            SET user_visible=$sql_user_visible
			WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * attach a vm cdrom device to a vm for a given vm id
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            new vm cdrom device instance | FALSE on error
 */
function ace_db_vm_attach_cdrom($vm_id)
{
    if ($vm_cdrom_instance = ace_db_vm_get_available_cdrom_instance($vm_id)) {
        $sql = "INSERT INTO vcdrom (
                  `vm_id`,
                  `instance`)
                VALUES (
                  $vm_id,
                  $vm_cdrom_instance)";
        $db_result = ace_db_query($sql);
        $return = ($db_result->row_count > 0) ? $vm_cdrom_instance : FALSE;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * detach a vm cdrom device from a vm
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom device instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_detach_cdrom($vm_id, $vm_cdrom_instance)
{
    $sql = "DELETE FROM vcdrom
            WHERE vm_id=$vm_id
              AND instance=$vm_cdrom_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * associate a volume with a vm cdrom device
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom instance
 * @param   int $volume_id         volume id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_cdrom_insert_media($vm_id, $vm_cdrom_instance, $volume_id)
{
    $sql = "UPDATE vcdrom
            SET volume_id=$volume_id
            WHERE vm_id=$vm_id
              AND instance=$vm_cdrom_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * remove volume association from a vm cdrom device
 *
 * @param   int $vm_id             vm id
 * @param   int $vm_cdrom_instance vm cdrom instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_cdrom_eject_media($vm_id, $vm_cdrom_instance)
{
    $sql = "UPDATE vcdrom
            SET volume_id=NULL
            WHERE vm_id=$vm_id
              AND instance=$vm_cdrom_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * attach a vm disk device to a vm
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            new vm disk instance | FALSE on error
 */
function ace_db_vm_attach_disk($vm_id)
{
    if ($vm_disk_instance = ace_db_vm_get_available_disk_instance($vm_id)) {
        $sql = "INSERT INTO vdisk (
                  `vm_id`,
                  `instance`)
                VALUES (
                  $vm_id,
                  $vm_disk_instance)";
        $db_result = ace_db_query($sql);
        $return = ($db_result->row_count > 0) ? $vm_disk_instance : FALSE;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * detach a vm disk device from a vm
 *
 * @param   int $vm_id            vm id
 * @param   int $vm_disk_instance vm disk instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_detach_disk($vm_id, $vm_disk_instance)
{
    $sql = "DELETE FROM vdisk
            WHERE vm_id=$vm_id
              AND instance=$vm_disk_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * associate a volume with a vm disk device
 *
 * @param   int $vm_id            vm id
 * @param   int $vm_disk_instance vm disk instance
 * @param   int $volume_id        volume id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_disk_assign_volume($vm_id, $vm_disk_instance, $volume_id)
{
    $sql = "UPDATE vdisk
            SET volume_id=$volume_id
            WHERE vm_id=$vm_id
              AND instance=$vm_disk_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * remove volume association from a vm a disk device
 *
 * @param   int $vm_id            vm id
 * @param   int $vm_disk_instance vm disk instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_disk_unassign_volume($vm_id, $vm_disk_instance)
{
    $sql = "UPDATE vdisk
            SET volume_id=NULL
            WHERE vm_id=$vm_id
              AND instance=$vm_disk_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * attach a vm nic device to a vm
 *
 * * atomically creates a new mac address AND inserts it into the new vm nic device
 *
 * @param   int $vm_id vm id
 *
 * @return  int|bool            new vm nic instance | FALSE on error
 */
function ace_db_vm_attach_nic($vm_id)
{
    if ($vm_nic_instance = ace_db_vm_get_available_nic_instance($vm_id)) {
        $sql = "INSERT INTO vnic (`vm_id`, `instance`, `mac_index`, `network_id`)
				VALUES (
                    $vm_id,
                    $vm_nic_instance,
                    (SELECT t1.mac_index + 1 AS mac_index
                        FROM `vnic` AS t1
                        LEFT JOIN `vnic` AS t2 ON t1.mac_index + 1 = t2.mac_index
                        WHERE t2.mac_index IS NULL
                            AND t1.mac_index >= " . _MAC_POOL_INDEX_START_ . "
                            AND t1.mac_index < " . _MAC_POOL_INDEX_END_ . "
                        ORDER BY t1.mac_index
                        LIMIT 1
                    ),
                    3)";
        $db_result = ace_db_query($sql);
        $return = ($db_result->row_count > 0) ? $vm_nic_instance : FALSE;
    } else {
        $return = FALSE;
    }
    return $return;
}

/**
 * detach a vm nic device from a vm
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_detach_nic($vm_id, $vm_nic_instance)
{
    $sql = "DELETE FROM vnic
            WHERE vm_id=$vm_id
              AND instance=$vm_nic_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * associate a network with a vm nic device
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 * @param   int $network_id      network id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_nic_connect_network($vm_id, $vm_nic_instance, $network_id)
{
    $sql = "UPDATE vnic
            SET network_id=$network_id
            WHERE vm_id=$vm_id
              AND instance=$vm_nic_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * remove network association from a vm nic device
 *
 * @param   int $vm_id           vm id
 * @param   int $vm_nic_instance vm nic instance
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_nic_disconnect($vm_id, $vm_nic_instance)
{
    $sql = "UPDATE vnic
            SET network_id=3
            WHERE vm_id=$vm_id
              AND instance=$vm_nic_instance";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}

/**
 * set vm state active
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_activate($vm_id)
{
    $sql = "UPDATE vm
            SET state=1
            WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count === 1) ? TRUE : FALSE;
}

/**
 * set vm state inactive
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on success TRUE/FALSE
 */
function ace_db_vm_deactivate($vm_id)
{
    $sql = "UPDATE vm
            SET state=0
			WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count === 1) ? TRUE : FALSE;
}

/**
 * delete a vm
 *
 * @param   int $vm_id vm id
 *
 * @return  bool                on  success TRUE/FALSE
 */
function ace_db_vm_delete($vm_id)
{
    $sql = "DELETE FROM vm
            WHERE id=$vm_id";
    $db_result = ace_db_query($sql);
    return ($db_result->row_count > 0) ? TRUE : FALSE;
}
