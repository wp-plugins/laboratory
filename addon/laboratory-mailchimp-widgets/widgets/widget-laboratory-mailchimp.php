<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}


class Colabs_Widget_MailChimp extends WP_Widget {
	private $default_failure_message;
	private $default_signup_text;
	private $default_success_message;
	private $default_title;
	private $successful_signup = false;
	private $subscribe_errors;
	private $colabs_mailchimp;
	
	public function __construct () {
		$this->default_failure_message = __('There was a problem processing your submission.');
		$this->default_signup_text = __('Join now!');
		$this->default_success_message = __('Thank you for joining our mailing list. Please check your email for a confirmation link.');
		$this->default_title = __('Sign up for our mailing list.');
		$widget_options = array('classname' => 'widget_colabs_mailchimp', 'description' => __( "Displays a sign-up form for a MailChimp mailing list.", 'colabsthemes'));
		parent::__construct(false, __('ColorLabs - MailChimp Signup', 'colabsthemes'), $widget_options);

		$this->default_loader_graphic = untrailingslashit( plugins_url( '', __FILE__ ) ).'/images/ajax-loader.gif';
		add_action('init', array(&$this, 'add_scripts'));
		add_action('parse_request', array(&$this, 'process_submission'));
	}
	
	public function add_scripts () {
		wp_enqueue_script('colabs-mc-widget', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/js/mailchimp-widget-min.js', array('jquery'), false);
	}
	
	public function get_admin_notices () {
		global $blog_id;
		$notice = '<p>';
		$notice .= __('You\'ll need to set up the MailChimp signup widget plugin options before using it. ', 'colabsthemes');
		$notice .= '</p>';
		return $notice;
	}
	
	private function get_api_key ($apikey) {
		$mcapi = new MCAPI($apikey);
		if (! empty($mcapi->api_key)) {
			return $mcapi;
		} else {
			return false;
		}
	}
	
	public function form ($instance) {
		$defaults = array(
				'failure_message' => $this->default_failure_message,
				'title' => $this->default_title,
				'signup_text' => $this->default_signup_text,
				'success_message' => $this->default_success_message,
				'collect_first' => false,
				'collect_last' => false,
				'old_markup' => false,
				'apikey' => '',
				'desc' => ''
		);
		$vars = wp_parse_args($instance, $defaults);
		extract($vars);
		$mcapi = $this->get_api_key($apikey);
		?>

			<p>
				<label for="<?php echo $this->get_field_id('api-key'); ?>"><?php echo  __('Api Key :', 'colabsthemes'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('apikey'); ?>" name="<?php echo $this->get_field_name('apikey'); ?>" type="password" value="<?php echo $apikey; ?>" />
			</p>

		<?php
		if (false == $mcapi) {
			echo $this->get_admin_notices();
		} else {
			$this->lists = $mcapi->lists();
			
			?>
					<h3><?php echo  __('General Settings', 'colabsthemes'); ?></h3>
					<p>
						<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo  __('Title :', 'colabsthemes'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('desc'); ?>"><?php _e('Description:','colabsthemes'); ?></label>
						<textarea name="<?php echo $this->get_field_name('desc'); ?>" id="<?php echo $this->get_field_id('desc'); ?>" cols="6" rows="5" class="widefat"><?php echo $desc; ?></textarea>	
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('current_mailing_list'); ?>"><?php echo __('Select a Mailing List :', 'colabsthemes'); ?></label>
						<select class="widefat" id="<?php echo $this->get_field_id('current_mailing_list');?>" name="<?php echo $this->get_field_name('current_mailing_list'); ?>">
			<?php	
			foreach ($this->lists['data'] as $key => $value) {
				$selected = (isset($current_mailing_list) && $current_mailing_list == $value['id']) ? ' selected="selected" ' : '';
				?>	
						<option <?php echo $selected; ?>value="<?php echo $value['id']; ?>"><?php echo __($value['name'], 'colabsthemes'); ?></option>
				<?php
			}
			?>
						</select>
					</p>
					<p><strong>N.B.</strong><?php echo  __('This is the list your users will be signing up for in your sidebar.', 'colabsthemes'); ?></p>
					<p>
						<label for="<?php echo $this->get_field_id('signup_text'); ?>"><?php echo __('Sign Up Button Text :', 'colabsthemes'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('signup_text'); ?>" name="<?php echo $this->get_field_name('signup_text'); ?>" value="<?php echo $signup_text; ?>" />
					</p>
					<h3><?php echo __('Personal Information', 'colabsthemes'); ?></h3>
					<p><?php echo __("These fields won't (and shouldn't) be required. Should the widget form collect users' first and last names?", 'colabsthemes'); ?></p>
					<p>
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('collect_first'); ?>" name="<?php echo $this->get_field_name('collect_first'); ?>" <?php echo  checked($collect_first, true, false); ?> />
						<label for="<?php echo $this->get_field_id('collect_first'); ?>"><?php echo  __('Collect first name.', 'colabsthemes'); ?></label>
						<br />
						<input type="checkbox" class="checkbox" id="<?php echo  $this->get_field_id('collect_last'); ?>" name="<?php echo $this->get_field_name('collect_last'); ?>" <?php echo checked($collect_last, true, false); ?> />
						<label><?php echo __('Collect last name.', 'colabsthemes'); ?></label>
					</p>
					<h3><?php echo __('Notifications', 'colabsthemes'); ?></h3>
					<p><?php echo  __('Use these fields to customize what your visitors see after they submit the form', 'colabsthemes'); ?></p>
					<p>
						<label for="<?php echo $this->get_field_id('success_message'); ?>"><?php echo __('Success :', 'colabsthemes'); ?></label>
						<textarea class="widefat" id="<?php echo $this->get_field_id('success_message'); ?>" name="<?php echo $this->get_field_name('success_message'); ?>"><?php echo $success_message; ?></textarea>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('failure_message'); ?>"><?php echo __('Failure :', 'colabsthemes'); ?></label>
						<textarea class="widefat" id="<?php echo $this->get_field_id('failure_message'); ?>" name="<?php echo $this->get_field_name('failure_message'); ?>"><?php echo $failure_message; ?></textarea>
					</p>
			<?php
			
		}
	}
	
	public function process_submission () {
		$mcapi = false;

		if( isset( $_GET['colabs_mc_apikey'] ) ) {
			$mcapi = $this->get_api_key($_GET['colabs_mc_apikey']);
		}

		if (isset($_GET[$this->id_base . '_email'])) {
			
			header("Content-Type: application/json");
			
			//Assume the worst.
			$response = '';
			$result = array('success' => false, 'error' => $this->get_failure_message($_GET['colabs_mc_number']));
			
			$merge_vars = array();
			
			if (! is_email($_GET[$this->id_base . '_email'])) { //Use WordPress's built-in is_email function to validate input.
				
				$response = json_encode($result); //If it's not a valid email address, just encode the defaults.
				
			} else {
				
				
				if (false == $mcapi) {
					
					$response = json_encode($result);
					
				} else {
					
					if (isset($_GET[$this->id_base . '_first_name']) && is_string($_GET[$this->id_base . '_first_name'])) {
						
						$merge_vars['FNAME'] = $_GET[$this->id_base . '_first_name'];
						
					}
					
					if (isset($_GET[$this->id_base . '_last_name']) && is_string($_GET[$this->id_base . '_last_name'])) {
						
						$merge_vars['LNAME'] = $_GET[$this->id_base . '_last_name'];
						
					}
					
					$subscribed = $mcapi->listSubscribe($this->get_current_mailing_list_id($_GET['colabs_mc_number']), $_GET[$this->id_base . '_email'], $merge_vars);
				
					if (false == $subscribed) {
						
						$response = json_encode($result);
						
					} else {
					
						$result['success'] = true;
						$result['error'] = '';
						$result['success_message'] =  $this->get_success_message($_GET['colabs_mc_number']);
						$response = json_encode($result);
						
					}
					
				}
				
			}
			
			exit($response);
			
		} elseif (isset($_POST[$this->id_base . '_email'])) {
			
			$this->subscribe_errors = '<div class="error">'  . $this->get_failure_message($_POST['colabs_mc_number']) .  '</div>';
			
			if (! is_email($_POST[$this->id_base . '_email'])) {
				
				return false;
				
			}
			
			if (false == $mcapi) {
				
				return false;
				
			}
			
			if (is_string($_POST[$this->id_base . '_first_name'])  && '' != $_POST[$this->id_base . '_first_name']) {
				
				$merge_vars['FNAME'] = strip_tags($_POST[$this->id_base . '_first_name']);
				
			}
			
			if (is_string($_POST[$this->id_base . '_last_name']) && '' != $_POST[$this->id_base . '_last_name']) {
				
				$merge_vars['LNAME'] = strip_tags($_POST[$this->id_base . '_last_name']);
				
			}
			
			$subscribed = $mcapi->listSubscribe($this->get_current_mailing_list_id($_POST['colabs_mc_number']), $_POST[$this->id_base . '_email'], $merge_vars);
			
			if (false == $subscribed) {

				return false;
				
			} else {
				
				$this->subscribe_errors = '';
				
				setcookie($this->id_base . '-' . $this->number, $this->hash_mailing_list_id(), time() + 31556926);
				
				$this->successful_signup = true;
				
				$this->signup_success_message = '<p>' . $this->get_success_message($_POST['colabs_mc_number']) . '</p>';
				
				return true;
				
			}	
			
		}
		
	}
	
	public function update ($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['collect_first'] = ! empty($new_instance['collect_first']);
		
		$instance['collect_last'] = ! empty($new_instance['collect_last']);
		
		$instance['current_mailing_list'] = esc_attr($new_instance['current_mailing_list']);
		
		$instance['failure_message'] = esc_attr($new_instance['failure_message']);
		
		$instance['signup_text'] = esc_attr($new_instance['signup_text']);
		
		$instance['success_message'] = esc_attr($new_instance['success_message']);
		
		$instance['title'] = esc_attr($new_instance['title']);
		
		$instance['apikey'] = $new_instance['apikey'];
		
		$instance['desc'] = $new_instance['desc'];
		
		return $instance;
		
	}
	
	public function widget ($args, $instance) {
		$mcapi = $this->get_api_key($instance['apikey']);
		
		extract($args);
		echo $before_widget . $before_title . $instance['title'] . $after_title;	
		
		if ($this->successful_signup){
		
			echo $this->signup_success_message;	
			
		} elseif (isset($_COOKIE[$this->id_base . '-' . $this->number]) && $this->hash_mailing_list_id($this->number) == $_COOKIE[$this->id_base . '-' . $this->number]) {
				
			echo '<p class="confirm-api">'.__('You are already subscribed to our newsletter.','colabsthemes').'</p>';	
				
		} elseif ( false == $mcapi) {
			
			echo '<p class="confirm-api">'.__("You'll need to set up the MailChimp signup widget plugin options before using it",'colabsthemes').'</p>';
			
		} else {
				
				?>
				<?php if($instance['desc'])echo '<p>'.$instance['desc'].'</p>'; ?>				
				<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="<?php echo $this->id_base . '_form-' . $this->number; ?>" method="post">
					<?php echo $this->subscribe_errors;?>
					<?php	
						if ($instance['collect_first']) {
					?>	
					<input type="text" name="<?php echo $this->id_base . '_first_name'; ?>" placeholder="<?php echo __('First Name', 'colabsthemes'); ?>"/>
					<?php
						}
						if ($instance['collect_last']) {
					?>	
					<input type="text" name="<?php echo $this->id_base . '_last_name'; ?>" placeholder="<?php echo __('Last Name', 'colabsthemes'); ?>"/>
					<?php	
						}
					?>
						<input type="hidden" name="colabs_mc_number" value="<?php echo $this->number; ?>" />
						<input type="hidden" name="colabs_mc_apikey" value="<?php echo $instance['apikey']; ?>" />
						<input id="<?php echo $this->id_base; ?>-email-<?php echo $this->number; ?>" type="text" name="<?php echo $this->id_base; ?>_email" placeholder="<?php echo __('Email Address', 'colabsthemes'); ?>"/>
						<a class="button-icon" href="#"><i class="icon-mail"></i></a>
						<input class="button" type="submit" name="<?php echo __($instance['signup_text'], 'colabsthemes'); ?>" value="<?php echo __($instance['signup_text'], 'colabsthemes'); ?>" />
					</form>
						<script>jQuery('#<?php echo $this->id_base; ?>_form-<?php echo $this->number; ?>').colabs_mc_widget({"url" : "<?php echo $_SERVER['PHP_SELF']; ?>", "cookie_id" : "<?php echo $this->id_base; ?>-<?php echo $this->number; ?>", "cookie_value" : "<?php echo $this->hash_mailing_list_id(); ?>", "loader_graphic" : "<?php echo $this->default_loader_graphic; ?>"}); </script>
				<?php
		}
	
		echo $after_widget;
	}
	
	private function hash_mailing_list_id () {
		
		$options = get_option($this->option_name);
		
		$hash = md5($options[$this->number]['current_mailing_list']);
		
		return $hash;
		
	}
	
	private function get_current_mailing_list_id ($number = null) {
		
		$options = get_option($this->option_name);
		
		return $options[$number]['current_mailing_list'];
		
	}
	
	private function get_failure_message ($number = null) {
		
		$options = get_option($this->option_name);
		
		return $options[$number]['failure_message'];
		
	}
	
	private function get_success_message ($number = null) {
		
		$options = get_option($this->option_name);
		
		return $options[$number]['success_message'];
		
	}
	
}