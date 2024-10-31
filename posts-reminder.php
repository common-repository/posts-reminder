<?php
/*
Plugin Name: Posts reminder
Plugin URI: http://www.lukti.com/posts-reminder.html
Description: Plugin that notifies by email that you don't write any posts today. Just notifies you if there is no post from 10PM. 
Author: Lucas Garcia
Version: 0.20
Author URI: http://www.lukti.com
*/

add_action('admin_menu', 'pr_add_page');
add_action('admin_head', 'pr_do_header');
// Now we set that function up to execute when the admin_footer action is called
add_action('get_footer', 'pr_do_reminder');

function pr_add_page() {
    add_options_page('Posts Reminder', 'Posts Reminder Options', 8, 'proptions', 'pr_options_page');
}

function pr_check_posts() {
        global $wpdb;
		$posts = $wpdb->get_var("SELECT COUNT(*) FROM wp_posts WHERE post_date > date_sub(now(), interval 1 day) and post_date < now() and post_status = 'publish' and post_type = 'post'");
        return $posts;
}


// This just echoes the chosen line, we'll position it later
function pr_do_reminder() {
		if (date("H") >= 22) {

			$defaults = array(
				'notify' => null,
				'email' => get_option('admin_email'),
				'subject' => 'No posts today in ' . get_option('blogname'), 
				'mail_body' => 'Please <a href="'.get_option('siteurl').'/wp-admin/post-new.php">write a post</a> in ' . get_option('blogname') 
			);
		
			$options = get_option('template_pr');
			
			$args = wp_parse_args( $args, $options, $defaults);
			extract($args);
			
			
			if ($notify) {
			
	        if (date("d-m-Y") != $notified) {
			
	        $posts = pr_check_posts();

	        if ($posts == 0) {
				$Name = get_option('blogname');
				$mail = get_option('admin_email'); 
				$recipient = $email; 
				$header = "From: ". $Name . " <" . $mail . ">\r\n"; 
				ini_set('sendmail_from', $mail); 
				mail($recipient, $subject, $mail_body, $header); 
				$options['notified'] = date("d-m-Y");
				update_option('template_pr', $options);
	        } else
	            return false;

			} else 
				return false;
			} else 
				return false;
		} 
			return false;
}



// We need some CSS to position the paragraph
function pr_do_header() {
   
}

function pr_options_page() {
    // variables for the field and option names 
	$options = $newoptions = get_option('template_pr');
	if ( $_POST['pr-submit'] ) {
		$newoptions['email'] = strip_tags(stripslashes($_POST['pr-email']));
		$newoptions['mail_body'] = strip_tags(stripslashes($_POST['pr-mail_body']));
		$newoptions['subject'] = strip_tags(stripslashes($_POST['pr-subject']));
		$newoptions['notify'] = strip_tags(stripslashes($_POST['pr-notify']));
		echo '<div class="updated"><p><strong>Posts Reminder options saved</strong></p></div>';
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('template_pr', $options);
	}
	
	$email = htmlspecialchars($options['email'], ENT_QUOTES);
	$mail_body = htmlspecialchars($options['mail_body'], ENT_QUOTES);
	$subject = htmlspecialchars($options['subject'], ENT_QUOTES);
	$notify = htmlspecialchars($options['notify'], ENT_QUOTES);
	
?>

    <div class="wrap">
    <h2>Configure Posts Reminder</h2>
		<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="pr-submit" id="pr-submit" value="1" />
			<?php wp_nonce_field('update-options') ?>
			<table>
				<tr><td valign="top"><strong>Send email notification</strong><span style="font-size:0.75em">You want to be notified by email?</span></td>
					<td><input type="checkbox" id="pr-notify" name="pr-notify" <?php if ($options['notify']) { ?>checked="checked"<?php } ?> /></td>
					
				</tr>		
				<tr><td>&nbsp;</td></tr>
				<tr><td valign="top"><strong>Email for notification</strong><br /><span style="font-size:0.75em">If you set this, we'll send you an email 2 hours before 23:59 of today.</span></td>
					<td><input type="text" id="pr-email" size="35" name="pr-email" value="<?php echo wp_specialchars($options['email'], true); ?>" /></td>
					
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td valign="top"><strong>Subject</strong><br /><span style="font-size:0.75em">Subject for the email notification.</span></td>
					<td><input type="text" id="pr-subject" size="35" name="pr-subject" value="<?php echo wp_specialchars($options['subject'], true); ?>" /></td>
					
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td valign="top"><strong>Mail body</strong><br /><span style="font-size:0.75em">Body for the email notification.</span></td>
					<td><textarea id="pr-mail_body" cols="35" rows="6" name="pr-mail_body"><?php echo wp_specialchars($options['mail_body'], true); ?></textarea>
					
				</tr>
				<tr><td>&nbsp;</td></tr>				
			</table>
			<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Posts Reminder Options &raquo;') ?>" /></p>
		</form>
	</div>
<?php
}
?>
