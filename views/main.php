<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $laboratory;
if ( $this->model->current_action_response == 'cred' ) {
	return;
}
?>
<div class="outer-wrapper">
	<div id="laboratory-container" class="wrap">
	    
		<div class="laboratory-sidebar">
			<div class="laboratory-logo">
				<a href="http:www/colorlabsproject.com" title="ColorLabs"><?php echo esc_html( $this->name ); ?></a>
				<span class="version"><?php echo esc_html( $laboratory->version ); ?></span>
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
			<div class="laboratory-footer">
				<a href="http://www.colorlabsproject.com" title="ColorLabs"><img src="<?php echo $this->assets_url; ?>images/colorlabs.png" alt="ColorLabs" /></a>
			</div>
		</div>
		
		<?php
			foreach ( $this->model->components as $k => $v ) {
				if ( count( $v ) > 0 ) {
					include( $this->views_path . 'section.php' );
				}
			}
		?>
		
		
	</div><!--/#laboratory .wrap-->
</div><!-- .outer-wrapper -->