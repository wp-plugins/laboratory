<?php 
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $laboratory; 
?>
<div id="laboratory" class="wrap laboratory_setting <?php echo esc_attr( $this->token ); ?>">

	<div class="settings-header">
		<?php screen_icon( 'laboratory' ); ?>
		<h2><?php echo esc_html( $this->name ); ?></h2>
	</div>
	

		<form action="options.php" method="post">
			<?php $this->settings_tabs(); ?>
			<?php settings_fields( $this->token ); ?>
			<?php $this->laboratory_do_settings_sections( $this->token ); ?>
			<?php submit_button(); ?>
		</form>

</div><!--/#laboratory-->