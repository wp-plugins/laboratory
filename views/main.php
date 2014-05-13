<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $laboratory;
if ( $this->model->current_action_response == 'cred' ) {
	return; 
}


?>
<div class="wrap">
	<h2></h2>
	<div class="laboratory_twitter_stream updated">

		<div class="stream-label"><?php _e('News On Twitter:','colabsthemes');?></div>				
		
			<?php 
			$laboratory_twit = new laboratory_twitter();
			$user_timeline = $laboratory_twit->laboratory_get_user_timeline( 'colorlabs', 5 );
			if( isset( $user_timeline['error'] ) ) : ?>
			<p><?php echo $user_timeline['error']; ?></p>
			<?php 
			else : 
			$laboratory_twit->laboratory_build_twitter_markup( $user_timeline );
			endif; 
			?>
		

	</div>
	<!-- .colabs_twitter-stream -->
	<div class="laboratory-wrapper">
		<div id="laboratory-container" class="wrap">    
			<div class="laboratory-sidebar">
				<div class="laboratory-logo">
					<h3>
						<img src="<?php echo plugins_url();; ?>/laboratory/assets/images/logo.png">
						<a href="#" title="ColorLabs & Company"><?php echo esc_html( $this->name ); ?></a> <span class="version"><?php echo esc_html( $laboratory->version ); ?></span>
						<a href="#" class="menu-mobile"><span></span><span></span><span></span></a>
					</h3>
				</div>
				<ul class="laboratory-menu">
					<?php
					foreach ( $this->model->components as $k => $v ) {
					foreach ( $v as $i => $j ) {
						echo '
						<li>
							<a id="item-'.esc_attr( $i ).'" class="menu-item" href="#'.esc_attr( $i ).'" >
								<span class="menu-text">'.esc_html( $j->title ).'</span>
								<span class="menu-arrow"></span>
								<span class="menu-hover"></span>
							</a>
						</li>';				
					}
					}
					?>
				</ul>
				
			</div>
			
			<?php
				foreach ( $this->model->components as $k => $v ) {
					if ( count( $v ) > 0 ) {
						include( $this->views_path . 'section.php' );
					}
				}
			?>
			
			
		</div><!--/#laboratory .wrap-->
		<div class="laboratory-footnote">
			<ul>
				<li class="docs"><a title="Theme Documentation" href="http://colorlabsproject.com/documentation/laboratory/" target="_blank">View Documentation</a></li>
				<li class="forum"><a href="http://colorlabsproject.com/resolve/" target="_blank">Submit a Support Ticket</a></li>
			</ul>
		</div>
		
	</div><!-- .outer-wrapper -->
	<div class="laboratory-footer">
		<a href="http://www.colorlabsproject.com" title="ColorLabs"><img src="<?php echo $this->assets_url; ?>images/colorlabs.png" alt="ColorLabs" /></a>
	</div>

</div>