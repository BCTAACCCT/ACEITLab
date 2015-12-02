<?php
/**
 * an AJAX data responder
 *
 * for testing purposes
 *
 * User: Michael
 * Date: 10/4/2015
 * Time: 9:07 PM
 */

/**
 * required libraries
 */
require_once('fns.php');
session_start();

/**
 * main
 */
switch ($_GET['request']) {
    case 'current_user_id':
        $response->user_id = $_SESSION['user_id'];
        echo json_encode($response);
        break;
    case 'vm_virt_state':
        echo ace_vm_get_virt_state($_GET['vm_id']);
        break;
}
