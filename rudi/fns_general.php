<?php
/**
 * ACEITLab General Functions
 *
 * performs general tasks not attributable to other more specific libraries
 * takes calls from main application
 * makes calls to anywhere necessary
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

/**
 * returns an integer value of a delimited hexadecimal mac address
 *
 * @param   string $mac_address the mac address ('aa:bb:cc:dd:ee:ff')
 *
 * @return  int                 integer value of mac address
 */
function ace_gen_convert_mac2int($mac_address)
{
    $hex = preg_replace("/:/", '', $mac_address);
    return base_convert($hex, 16, 10);
}

/**
 * returns a delimited hexadecimal mac address
 *
 * @param   int $mac_index the mac address as an integer
 *
 * @return  string              a delimited hexadecimal mac address
 */
function ace_gen_convert_int2mac($mac_index)
{
    $hex = base_convert($mac_index, 10, 16);
    return implode(':', str_split($hex, 2));
}

/**
 * @param $success
 * @param $comment
 *
 * @return array|bool
 */
function create_message($success, $comment)
{
    if (is_bool($success)) {
        $message = array('status' => $success, 'comment' => $comment);
    } else {
        $message = FALSE;
    }
    return $message;
}

/**
 * converts a table column to an array
 *
 * @param   array  $table                a 2D array
 * @param   string $field_name_requested name of column to convert
 *
 * @return  array|bool              a list of values taken from the column
 */
function ace_gen_table_field_to_array($table, $field_name_requested)
{
    $array = array();
    $count = count($table);
    if ($count > 0) {
        for ($row = 0; $row < $count; $row++) {
            foreach ($table[ $row ] as $field_name => $field_value) {
                if ($field_name == $field_name_requested) {
                    $element = $row;
                    $array[ $element ] = $table[ $row ][ $field_name ];
                }
            }
        }
    } else {
        $array = FALSE;
    }
    return $array;
}

/**
 * determines if the current session is valid
 *
 * @param   string $required_security_level the required security level to pass validation
 *
 * @return  bool                session validated TRUE/FALSE
 */
function ace_validate_session($required_security_level)
{
    if (isset($_SESSION['user_id']) && isset($_SESSION['security_level']) && $_SESSION['security_level'] <= $required_security_level) {
        $validated = TRUE;
    } else {
        ace_out_redirect_page(_LOGIN_URL_);
        exit();
    }
    return $validated;
}

/**
 * returns home page url based on current security level
 *
 * @return  string      home page url
 */
function ace_session_get_home_page()
{
    switch ($_SESSION['security_level']) {
        case _ADMIN_SECURITY_LEVEL_ :
            $url = _ADMIN_URL_;
            break;
        case _MANAGER_SECURITY_LEVEL_ :
            $url = _MANAGER_URL_;
            break;
        case _USER_SECURITY_LEVEL_ :
            $url = _LAB_URL_;
            break;
        default :
            $url = _LOGIN_URL_;
    }
    return $url;
}

/**
 * return html formatted page links based on current security level
 *
 * @return  string      html formatted page links
 */
function ace_session_get_page_links()
{
    switch ($_SESSION['security_level']) {
        case _ADMIN_SECURITY_LEVEL_ :
            $html = '
			<div class="row_element">
				<form action="' . _ADMIN_URL_ . '" method="post">
					<button class="' . (($_SERVER['SCRIPT_NAME'] == _ADMIN_URL_) ? ' selected' : '') . '" type="submit">Admin</button>
				</form>
			</div>
			<div class="row_element">
				<form action="' . _MANAGER_URL_ . '" method="post">
					<button class="' . (($_SERVER['SCRIPT_NAME'] == _MANAGER_URL_) ? ' selected' : '') . '" type="submit">Manage</button>
				</form>
			</div>
			<div class="row_element">
				<form action="' . _LAB_URL_ . '" method="post">
					<button class="' . (($_SERVER['SCRIPT_NAME'] == _LAB_URL_) ? ' selected' : '') . '" type="submit">Lab</button>
				</form>
			</div>';
            break;
        case _MANAGER_SECURITY_LEVEL_ :
            $html = '
			<div class="row_element">
				<form action="' . _MANAGER_URL_ . '" method="post">
					<button class="' . (($_SERVER['SCRIPT_NAME'] == _MANAGER_URL_) ? ' selected' : '') . '" type="submit">Manage</button>
				</form>
			</div>
			<div class="row_element">
				<form action="' . _LAB_URL_ . '" method="post">
					<button class="' . (($_SERVER['SCRIPT_NAME'] == _LAB_URL_) ? ' selected' : '') . '" type="submit">Lab</button>
				</form>
			</div>';
            break;
        case _USER_SECURITY_LEVEL_ :
            $html = '
			<div class="row_element">
				<form action="' . _LAB_URL_ . '" method="post">
					<button class="' . (($_SERVER['SCRIPT_NAME'] == _LAB_URL_) ? ' selected' : '') . '" type="submit">Lab</button>
				</form>
			</div>';
            break;
        default :
            $html = '';
    }
    return $html;
}

/**
 * prevents the same form being submitted twice
 *
 * uses the nonce value in the _POST data to verify if the form has been re-submitted
 * resubmitted forms are rejected and the page is redirected to a safe url
 *
 * @param   string $safe_url url of a safe redirection
 *
 * @return  null                nothing to return
 */
function ace_session_redirect_form_refresh($safe_url)
{
    if (isset($_POST['nonce'])) {
        if ($_POST['nonce'] == $_SESSION['nonce']) {
            # redirect page without nonce
            ace_out_redirect_page($safe_url);
        } else {
            # store the nonce
            $_SESSION['nonce'] = $_POST['nonce'];
        }
    }
    return NULL;
}

function debug_to_console( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";
    echo $output;
}