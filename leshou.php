<?php
/*
Plugin Name: 乐收同步插件
Plugin URI: http://plugins.191998.net/leshou
Description: 同步发表到乐收,初次安装必须设置后才能使用。
Version: 1.0.1
Author: yekong
Author URI: http://plugins.191998.net/leshou
*/

class leshouhi{
	public  $useragent="Nokia"; //定义要模拟的浏览器名称
	private $token="";
	private $ch;	//CURL对象句柄
	private $cookie;	//保存Cookie的临时文件
	private $data;	//临时数据保存地址
	public $sblog_class;
	public function login($blogurl,$user,$pass)
	{

		$d = tempnam('../tmp/', 'cookie.txt');  //创建随机临时文件保存cookie.
		$this->cookie=$d;
	    $ch = curl_init("http://leshou.com/");
	    $this->ch=$ch;
	    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
	    curl_exec($ch);
	    curl_close($ch);
	    unset($this->ch);


	    $ch = curl_init($this->ch);
		$posturl="http://leshou.com/login";
		$post="username=".$user."&password=".$pass."&sub=%E7%99%BB+%E9%99%86&act=login";

	    curl_setopt($ch, CURLOPT_REFERER, "http://site.leshou.com/yezhikong/");
	    curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_POST, 1); // how many parameters to post
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	   // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
	    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
	   	curl_exec($ch);

 		curl_close($ch);

		unset($this->ch);
		$ch = curl_init($this->ch);
 		$creaturl="http://site.leshou.com/yezhikong/add";
 		$reff="http://site.leshou.com/yezhikong/add";
	    curl_setopt($ch, CURLOPT_URL, $creaturl);
	    curl_setopt($ch, CURLOPT_REFERER,$reff);
	    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
	   	$data= curl_exec($ch);
		curl_close($ch);
	   	preg_match_all( "/name=\"bdstoken\" value=\"(.*?)\"\/\>/s",$data, $tokens );
	   	$this->token=$tokens[1][0];

		unset($this->ch);

	}


public function send($blogurl,$title,$content)
	{

		$creaturl="http://site.leshou.com/yezhikong/add";

		$posturl="http://site.leshou.com/yezhikong/add";
		$post="url=".$blogurl."&title=".urlencode($title)."&intro=".urlencode($title)."&sub=%E6%8F%90+%E4%BA%A4&oname=yezhikong&act=save";
		$ch = curl_init($this->ch);
   		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_POST, 1); // how many parameters to post
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_REFERER, $creaturl);
		curl_setopt($ch, CURLOPT_COOKIEJAR,  $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_exec($ch);
		curl_close($ch);
		unset($this->ch);
	}

	public function logoff()
	{
		unset($this->ch);
		unlink($this->cookie);
	}

}
?>
<?php
        add_action('admin_menu', 'mt_add_leshou_pages');
        add_action('publish_post', 'publish_post_2_hileshou');
        add_action('xmlrpc_public_post', 'publish_post_2_hileshou');
     function mt_add_leshou_pages() {
    //call register settings function
	add_action( 'admin_init', 'register_wpleshou_settings' );
	// Add a new submenu under Options:
    add_options_page('WP2leshou Options', '乐收', 'administrator', 'wpleshou', 'mt_wpleshou_page');
     }

    function register_wpleshou_settings() {
	//register our settings
	register_setting( 'WP2leshou-settings-group', 'wp2leshouuser' );
	register_setting( 'WP2leshou-settings-group', 'wp2leshoupass' );
   }
// mt_options_page() displays the page content for the Test Options submenu
     function mt_wpleshou_page() {

    if (!function_exists("curl_init"))
   {
?>

<div class="wrap">
<h2>您的服务器不支持cURL库，插件WP2Hileshou无法工作，请禁用该插件。</h2><br />
</div>

<?php
 }
 else
 {

?>
<div class="wrap">
<h2>乐收 选项</h2>
如果你不是乐收vip那么这个插件并不适合您请删除,谢谢!<br/><br/>
设置仅适用于乐收，不支持Wordpress的定时发布功能。<br/><br/>
同步仅同步标题和链接不会同步内容，目的是为网站带来流量!<br/><br/>
<form method="post" action="options.php">

  <?php settings_fields( 'WP2leshou-settings-group' ); ?>
   <table class="form-table">
   		<tr valign="top">
        <th scope="row">乐收的登录名</th>
        <td>
			<input name="wp2leshouuser" type="text" id="wp2leshouuser" value="<?php form_option('wp2leshouuser'); ?>" class="regular-text" />

		</td>
		</tr>
		<tr valign="top">
        <th scope="row">乐收的登录密码</th>
        <td>
			<input name="wp2leshoupass" type="password" id="wp2leshouuser" value="<?php form_option('wp2leshoupass'); ?>" class="regular-text" />

		</td>

		</tr>
    </table>
  <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php
 }
}

function publish_post_2_hileshou($post_ID) {
	global $revision;
	if ($_POST['_inline_edit'] || $_POST['post_password'] || $revision) {
		return;
	} 
	$post = get_post($post_ID);
	$status = $post -> post_status;
	if (defined('DOING_CRON') || ($status == 'publish' && $post -> post_date == $post -> post_modified)) {
		$title = $post -> post_title;
		if (strlen($title) == 0) {
			$title = "无题  ";
		} 
		$content = $post -> post_content;
		$sendurl = get_option('leshou_sdurl');
		if ($sendurl == 1) {
			$content = "查看原文：<a href=" . get_permalink($post_ID) . ">" . get_permalink($post_ID) . "</a><br/>" . $content;
		} elseif ($sendurl == 2) {
			$content .= "<br/>查看原文：<a href=" . get_permalink($post_ID) . ">" . get_permalink($post_ID) . "</a>";
		} else {
			if (strlen($content) == 0) {
				$content = "a blank ";
			} 
		} 
		$wp2leshouuser = get_option('wp2leshouuser');
		$wp2leshoupass = get_option('wp2leshoupass');
		if (strlen($wp2leshouuser) > 1) {
			if (strlen($wp2leshoupass) > 3) {
				if (!function_exists('iconv')) {
					require_once(dirname(__FILE__) . '/iconv.php');
				} 
				$user = $wp2leshouuser;
				$pass = $wp2leshoupass;
				$blogurl = get_permalink($post_ID);
				$blog = new leshouhi();
				$blog -> login($blogurl, $user, $pass);
				$blog -> send($blogurl, $title, $content);
				$blog -> logoff();
			} 
		} 
	} 
} 

?>