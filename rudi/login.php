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
	<head>
		<title>ACEITLab</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/rudi.css" />
	</head>
	<body>
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
									<td align="left"><label>
                                            <input name="username" type="text" tabindex="1"/>
                                        </label></td>
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
			</div>
		</div>
		<div id="footer" class="footer"></div>
	</body>
</html>
