<?php
/**
 * ACEITLab Application - Login Module
 *
 * provides interface for initial authentication of users
 *
 * @author  Michael White-Webster
 * @version 0.7.4
 * @access  private
 */

require_once('fns.php');
session_start();
$_SESSION = array();

if (isset($_POST['username'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	if (ace_authenticate_user($username, $password)) {
		$url = ace_session_get_home_page();
	} else {
		$url = _LOGIN_URL_;
	}
	ace_out_redirect_page($url);
}
?>
<!doctype html>
<html>
<head profile="http://www.w3.org/2005/10/profile">
    <title>ACEITLab - Login</title>
    <meta name="description" content="ACEITLab is an Open Source online virtual lab environment for networking students.  Login to begin creating your virtual labs."/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="css/rudi.css" />
    <link rel="icon" type="image/png" href="icon/favicon-32x32.png"/>
</head>
<body>
    <div id="page" class="page">
        <div id="section_header" class="section" style="height: 45px;">
            <div class="header_left">
                <div class="element_table">
                    <div class="element_column">
                        <div id="site_title" class="element">
                            <p>ACEITLab</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header_left"></div>
        <div class="header_right"></div>
        <div class="clear"></div>
        <!--<div class="horizontal_divider"></div>-->
        <div id="main" class="main">
            <div class="element_table">
                <div class="element_column">
                    <div class="element">
                        <table>
                            <tr>
                                <th>Login</th>
                            </tr>
                        </table>
                    </div>
                    <div class="element">
                        <form action="<?php echo _LOGIN_URL_; ?>" method="post">
                            <table class='login'>
                                <tr>
                                    <td align="right">Username:</td>
                                    <td align="left">
                                        <label>
                                            <input name="username" type="text" tabindex="1"/>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">Password:</td>
                                    <td align="left"><input name="password" type="password" tabindex="2"  title="Password"/></td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <button name="login_submit_button" value="submit" type="submit">Login</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
                <div id="about_box" class="element_column">
                    <div class="element">
                         <table>
                             <tr>
                                 <th>About</th>
                             </tr>
                         </table>
                    </div>
                    <div class="element">
                        <h4>Online Virtual Networking Lab</h4>
                        <ul>
                            <li>An Open Source project to create an online lab environment for networking students.</li>
                            <li>ACEITLab provides Students with the ability to create, configure, and control remote virtual resources such as VMs, Networks, Volumes, and Snapshots.</li>
                            <li>Access to those VMs is integrated into the browser via the excellent Guacamole project (http://guac-dev.org/).</li>
                            <li>Though currently focused on KVM and the libvirt-php API (https://libvirt.org/php/), this project can be expanded to other hypervisors through its abstraction model and the addition of further libraries.</li>
                            <li>Links:
                                <ul>
                                    <li>Software repo: <a href="https://github.com/BCTAACCCT/ACEITLab">GitHub</a></li>
                                    <li>Instructor video: <a href="https://youtu.be/a9m9kss7klQ">YouTube</a> with more videos to come.</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="footer" class="footer"></div>
    </div>
</body>
</html>
