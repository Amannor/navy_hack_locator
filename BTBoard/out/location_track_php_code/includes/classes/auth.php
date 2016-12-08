<?php

/*

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$auth->login_required();

*/

//Authentication
class auth {

	const LOGIN_TYPE_FORM = 1;
	const LOGIN_TYPE_COOKIE = 2;
	const LOGIN_TYPE_DEMO = 3;

	const LOGIN_ERROR_USERPASS = 1;

	private $username = '';
	private $password = '';
	private $authinfo = array();
	private $autologin = false;
	private $login_attempted = false;
	private $login_success = false;
	private $login_type = 0;
	private $login_error = 0;

	public function handle() {
		global $db, $tbl, $cfg;

		//If logout, delete cookie
		if ( (isset($_GET['logout'])) && ($_GET['logout']) ) {

			$this->cookie_clear();

		} else {

			//If demo login specified
			if ( (isset($_GET['demo'])) && ($_GET['demo'] == 1) ) {

				$this->username = $cfg['demo_username'];
				$this->password = md5('demo');
				$this->autologin = true;

				$this->login_attempted = true;
				$this->login_type = self::LOGIN_TYPE_DEMO;

			} else if ( (isset($_POST['login_username'])) && (isset($_POST['login_password'])) ) {
				//If username / password posted

				$this->username = $_POST['login_username'];
				$this->password = md5($_POST['login_password']);
				$this->autologin = (isset($_POST['login_auto']) && ($_POST['login_auto'])) ? true : false;

				$this->login_attempted = true;
				$this->login_type = self::LOGIN_TYPE_FORM;

			} else if (isset($_COOKIE['auth'])) {
				//If cookie is already set

				//Parse out username / password
				parse_str($_COOKIE['auth'], $cookiedata);

				$this->username = $cookiedata['username'];
				$this->password = $cookiedata['password'];
				$this->autologin = ($cookiedata['autologin']) ? true : false;

				$this->login_attempted = true;
				$this->login_type = self::LOGIN_TYPE_COOKIE;

			}

		}

		//If login attempted
		if ($this->login_attempted == true) {

			//Check username / password
			$user_result = $db->table_query($db->tbl($tbl['user']), '*', $db->cond(array("email = '".$db->es($this->username)."'", "password = '".$db->es($this->password)."'"), 'AND'), '', 0, 1);
			if ($user_record = $db->record_fetch($user_result)) {

				$this->login_success = true;
				
				$this->authinfo = $user_record;

				//Update last login
				$db->record_update($tbl['user'], $db->rec(array('lastlogin' => $db->datetimenow())), $db->cond(array("id = {$user_record['id']}"), 'AND'));

				//Save cookie
				$this->cookie_save();

			} else {

				//Otherwise if login failed

				$this->login_error = self::LOGIN_ERROR_USERPASS;

				//If cookie is set
				if ($this->login_type == self::LOGIN_TYPE_COOKIE) {

					//Clear cookie
					$this->cookie_clear();
				}

			}

		}

	}

	public function setuser($username, $password) {
		$this->username = $username;
		$this->password = $password;
		$this->autologin = false;
	}

	private function cookie_clear() {
		global $cfg;

		//Delete cookie
		$time = time() - $cfg['auth_cookie_expiry'];
		$parsedurl = parse_url($cfg['site_url']);
		$secure = ($parsedurl['scheme'] == 'https') ? true : false;
		//setcookie('auth', '', $time, $parsedurl['path'], $parsedurl['host'], $secure);
		setcookie('auth', '', $time, $parsedurl['path'], null, $secure);

	}

	public function cookie_save() {
		global $cfg;

		$cookiedata = array(
			'username' => $this->username,
			'password' => $this->password,
			'autologin' => ($this->autologin) ? 1 : 0,
		);

		if ($this->autologin) {
			$expiry = time() + $cfg['auth_cookie_expiry'];
		} else {
			$expiry = null;
		}

		$parsedurl = parse_url($cfg['site_url']);
		$secure = ($parsedurl['scheme'] == 'https') ? true : false;
		//setcookie('auth', http_build_query($cookiedata), $expiry, $parsedurl['path'], $parsedurl['host'], $secure, true);
		setcookie('auth', http_build_query($cookiedata), $expiry, $parsedurl['path'], null, $secure);

	}

	public function login_required() {

		if ($this->login_success == true) {
			//Allow continute processing....
		} else {
			$this->display_loginform();
		}

	}

	public function display_loginform() {
		global $cfg;

		$username_h = $this->username;

		//If autologin
		if ($this->autologin) {
			$autologin_checked = 'checked="checked"';
		} else {
			$autologin_checked = '';
		}

		$errormsg_html = '';
		if ($this->login_error == self::LOGIN_ERROR_USERPASS) {

			$errormsg_html = <<<EOHTML
<div class="error"><strong>Error:</strong> Username / Password invalid</div>
EOHTML;

		}


		$link_h = navpd::self_h(array('logout' => null));

		$body_html = <<<EOHTML

<div class="loginbox">

{$errormsg_html}

	<div class="loginboxcontent">

		<div class="loginboxtitle">Login</div>

		<div class="loginboxcontentinner">

			<form method="post" action="{$link_h}">

				<div>
					<label for="login_username" class="inputxttitle">Email</label>
					<input type="text" name="login_username" id="login_username" value="{$username_h}" maxlength="255" class="inputtxt" />
				</div>

				<div>
					<label for="login_password" class="inputxttitle">Password</label>
					<input type="password" name="login_password" id="login_password" value="" maxlength="255" class="inputtxt" />
				</div>

				<div class="autologin">
					<label for="login_auto">Autologin:</label> <input type="checkbox" name="login_auto" id="login_auto" {$autologin_checked} value="1" />
				</div>

				<div><input type="submit" value="Login" name="login" /></div>

			</form>

		</div>

	</div>

</div>

<script type="text/javascript">
  //<![CDATA[

	$("login_username").focus();

  //]]>
</script>

EOHTML;

		$template = new template();
		$template->settitle('Login');
		//$template->setmainnavsection($cfg['admin_home']);
		//$template->setheaderaddinhtml($headeraddin_html);
		$template->setbodyhtml($body_html);
		$template->display();

		exit;

	}

	public function getauthinfo() {
		return $this->authinfo;
	}

}

?>