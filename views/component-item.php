<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

	$css_class = '';

	if ( in_array( $i, $this->model->closed_components ) ) {
		$css_class .= ' closed';
	}

	$version = '';
	if ( isset( $this->model->components[$k][$i]->current_version ) ) {
		$version = $this->model->components[$k][$i]->current_version;
	}
	if ( $version == '' && isset( $this->model->components[$k][$i]->version ) ) {
		$version = $this->model->components[$k][$i]->version;
	}

?>
<div id="<?php echo esc_attr( $i ); ?>" class="module-item <?php echo esc_attr( $this->model->get_status_token( $i, $k ) ) . $css_class; ?>">
	<div class="module-innercontainer">

	<div class="module-top">
		<div class="module-title-action">
			<a class="module-action hide-if-no-js" href="#close-component"></a>
		</div>
    <div class="module-title">
    	<h4>
    		<?php
                $module_status = $this->model->get_status_label( $i, $k );
                $status_label = ( $module_status == 'Enabled' ) ? 'label-active' : '';
            ?>
            <span class="status-label label <?php echo $status_label; ?>"><?php echo $module_status; ?></span>
    		<span class="title"><?php echo esc_html( $j->title ); ?></span>
    		<?php if ( $version != '' ) { ?>
    		<span class="version">
    			<?php echo $version; ?>
    			
    		</span>
    		<?php } ?>
    	</h4>
    </div>
	</div><!-- .module-top -->

	<div class="module-inside">
    <div class="info">
    	<div class="module-image">
    		<img src="<?php echo esc_url( $this->model->get_screenshot_url( $i, $k ) ); ?>" alt="thumb" />
    	</div>
    	<div class="module-content">
    		<p>
		    	<?php
		    		if ( isset( $j->short_description ) ) {
		    			echo esc_html( $j->short_description );
		    		}
		    	?>
    		
    		</p>
    	</div>
    	
    	<div class="actions">
    		<form method="post" name="component-actions" action="" >
    		<?php wp_nonce_field( $i ); ?>
    		<div>
    			<?php
    				echo $this->model->get_action_button( $i, $k );
					
    				if ( isset( $this->model->components[$k][$i]->settings ) && $this->model->components[$k][$i]->settings != '' ) {
    					$class = '';
    					if ( $this->model->get_status_token( $i, $k ) == 'disabled' ) {
    						$class = ' hidden';
    					}
    					echo '<span class="settings-link' . $class . '"><a class="btn" href="' . admin_url( 'admin.php?page=' . urlencode( $this->model->components[$k][$i]->settings ) ) . '">' . __( 'Settings', 'laboratory' ) . '</a></span>' . "\n";
    				}
	    			?>
    			<input type="hidden" name="component-type" value="<?php echo esc_attr( $k ); ?>" />
    			<input type="hidden" name="component-path" value="<?php echo esc_attr( $j->filepath ); ?>" />
    			<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" class="ajax-loading" id="ajax-loading" alt="<?php esc_attr_e( 'Loading', 'laboratory' ); ?>" />
    		</div>
    		<div class="clear"></div>
    		</form>
    	</div>
    	
    	<div class="clear"></div>
    </div>
	</div><!-- .module-inside -->

	</div><!-- .module-innercontainer -->
</div>