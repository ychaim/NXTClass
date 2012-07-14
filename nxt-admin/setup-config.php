<?php
/**
 * Retrieves and creates the nxt-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the nxt-config.php to be created using this page.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package NXTClass
 * @subpackage Administration
 */

/**
 * We are installing.
 *
 * @package NXTClass
 */
define('nxt_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('nxt_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting(0);

/**#@+
 * These three defines are required to allow us to use require_nxt_db() to load
 * the database class while being nxt-content/db.php aware.
 * @ignore
 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('nxtINC', 'nxt-includes');
define('nxt_CONTENT_DIR', ABSPATH . 'nxt-content');
define('nxt_DEBUG', false);
/**#@-*/

require_once(ABSPATH . nxtINC . '/load.php');
require_once(ABSPATH . nxtINC . '/version.php');
nxt_check_php_mysql_versions();

require_once(ABSPATH . nxtINC . '/compat.php');
require_once(ABSPATH . nxtINC . '/functions.php');
require_once(ABSPATH . nxtINC . '/class-nxt-error.php');

if (!file_exists(ABSPATH . 'nxt-config-sample.php'))
	nxt_die('Sorry, I need a nxt-config-sample.php file to work from. Please re-upload this file from your NXTClass installation.');

$configFile = file(ABSPATH . 'nxt-config-sample.php');

// Check if nxt-config.php has been created
if (file_exists(ABSPATH . 'nxt-config.php'))
	nxt_die("<p>The file 'nxt-config.php' already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='install.php'>installing now</a>.</p>");

// Check if nxt-config.php exists above the root directory but is not part of another install
if (file_exists(ABSPATH . '../nxt-config.php') && ! file_exists(ABSPATH . '../nxt-settings.php'))
	nxt_die("<p>The file 'nxt-config.php' already exists one level above your NXTClass installation. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='install.php'>installing now</a>.</p>");

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;

/**
 * Display setup nxt-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package NXTClass
 * @subpackage Installer_nxt_Config
 */
function display_header() {
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>NXTClass &rsaquo; Setup Configuration File</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />

</head>
<body>
<h1 id="logo"><img alt="NXTClass" src="images/nxtclass-logo.png" /></h1>
<?php
}//end function display_header();

switch($step) {
	case 0:
		display_header();
?>

<p>Welcome to NXTClass. Before getting started, we need some information on the database. You will need to know the following items before proceeding.</p>
<ol>
	<li>Database name</li>
	<li>Database username</li>
	<li>Database password</li>
	<li>Database host</li>
	<li>Table prefix (if you want to run more than one NXTClass in a single database) </li>
</ol>
<p><strong>If for any reason this automatic file creation doesn't work, don't worry. All this does is fill in the database information to a configuration file. You may also simply open <code>nxt-config-sample.php</code> in a text editor, fill in your information, and save it as <code>nxt-config.php</code>. </strong></p>
<p>In all likelihood, these items were supplied to you by your Web Host. If you do not have this information, then you will need to contact them before you can continue. If you&#8217;re all ready&hellip;</p>

<p class="step"><a href="setup-config.php?step=1<?php if ( isset( $_GET['noapi'] ) ) echo '&amp;noapi'; ?>" class="button">Let&#8217;s go!</a></p>
<?php
	break;

	case 1:
		display_header();
	?>
<form method="post" action="setup-config.php?step=2">
	<p>Below you should enter your database connection details. If you're not sure about these, contact your host. </p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname">Database Name</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="nxtclass" /></td>
			<td>The name of the database you want to run nxt in. </td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">User Name</label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="username" /></td>
			<td>Your MySQL username</td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">Password</label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="password" /></td>
			<td>...and your MySQL password.</td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost">Database Host</label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td>You should be able to get this info from your web host, if <code>localhost</code> does not work.</td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix">Table Prefix</label></th>
			<td><input name="prefix" id="prefix" type="text" value="nxt_" size="25" /></td>
			<td>If you want to run multiple NXTClass installations in a single database, change this.</td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="true" /><?php } ?>
	<p class="step"><input name="submit" type="submit" value="Submit" class="button" /></p>
</form>
<?php
	break;

	case 2:
	$dbname  = trim($_POST['dbname']);
	$uname   = trim($_POST['uname']);
	$passwrd = trim($_POST['pwd']);
	$dbhost  = trim($_POST['dbhost']);
	$prefix  = trim($_POST['prefix']);
	if ( empty($prefix) )
		$prefix = 'nxt_';

	// Validate $prefix: it can only contain letters, numbers and underscores
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		nxt_die( /*nxt_I18N_BAD_PREFIX*/'<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.'/*/nxt_I18N_BAD_PREFIX*/ );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $passwrd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_nxt_db();
	if ( ! empty( $nxtdb->error ) ) {
		$back = '<p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button">Try Again</a></p>';
		nxt_die( $nxtdb->error->get_error_message() . $back );
	}

	// Fetch or generate keys and salts.
	$no_api = isset( $_POST['noapi'] );
	require_once( ABSPATH . nxtINC . '/plugin.php' );
	require_once( ABSPATH . nxtINC . '/l10n.php' );
	require_once( ABSPATH . nxtINC . '/pomo/translations.php' );
	if ( ! $no_api ) {
		require_once( ABSPATH . nxtINC . '/class-http.php' );
		require_once( ABSPATH . nxtINC . '/http.php' );
		nxt_fix_server_vars();
		/**#@+
		 * @ignore
		 */
		function get_bloginfo() {
			return ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . str_replace( $_SERVER['PHP_SELF'], '/nxt-admin/setup-config.php', '' ) );
		}
		/**#@-*/
		$secret_keys = nxt_remote_get( 'https://api.opensource.nxtclass.tk/secret-key/1.1/salt/' );
	}

	if ( $no_api || is_nxt_error( $secret_keys ) ) {
		$secret_keys = array();
		require_once( ABSPATH . nxtINC . '/pluggable.php' );
		for ( $i = 0; $i < 8; $i++ ) {
			$secret_keys[] = nxt_generate_password( 64, true, true );
		}
	} else {
		$secret_keys = explode( "\n", nxt_remote_retrieve_body( $secret_keys ) );
		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}
	}
	$key = 0;

	foreach ($configFile as $line_num => $line) {
		switch (substr($line,0,16)) {
			case "define('DB_NAME'":
				$configFile[$line_num] = str_replace("database_name_here", $dbname, $line);
				break;
			case "define('DB_USER'":
				$configFile[$line_num] = str_replace("'username_here'", "'$uname'", $line);
				break;
			case "define('DB_PASSW":
				$configFile[$line_num] = str_replace("'password_here'", "'$passwrd'", $line);
				break;
			case "define('DB_HOST'":
				$configFile[$line_num] = str_replace("localhost", $dbhost, $line);
				break;
			case '$table_prefix  =':
				$configFile[$line_num] = str_replace('nxt_', $prefix, $line);
				break;
			case "define('AUTH_KEY":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_KE":
			case "define('AUTH_SAL":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_SA":
				$configFile[$line_num] = str_replace('put your unique phrase here', $secret_keys[$key++], $line );
				break;
		}
	}
	if ( ! is_writable(ABSPATH) ) :
		display_header();
?>
<p>Sorry, but I can't write the <code>nxt-config.php</code> file.</p>
<p>You can create the <code>nxt-config.php</code> manually and paste the following text into it.</p>
<textarea cols="98" rows="15" class="code"><?php
		foreach( $configFile as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p>After you've done that, click "Run the install."</p>
<p class="step"><a href="install.php" class="button">Run the install</a></p>
<?php
	else :
		$handle = fopen(ABSPATH . 'nxt-config.php', 'w');
		foreach( $configFile as $line ) {
			fwrite($handle, $line);
		}
		fclose($handle);
		chmod(ABSPATH . 'nxt-config.php', 0666);
		display_header();
?>
<p>All right sparky! You've made it through this part of the installation. NXTClass can now communicate with your database. If you are ready, time now to&hellip;</p>

<p class="step"><a href="install.php" class="button">Run the install</a></p>
<?php
	endif;
	break;
}
?>
</body>
</html>
