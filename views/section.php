<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
?>
    
<div id="<?php echo $this->token . '-' . $k; ?>" class="module-list">
  <div class="module-inner">
    <?php
    foreach ( $v as $i => $j ) {
      include( $this->model->config->views_path . 'component-item.php' );        
    }
    ?>
  </div>
  <div class="module-settings-loader">
    <div class="settings-inner"><div class="settings-scroller"></div></div>
    <div class="settings-header-fixed"><h3></h3></div>
    <a href="#" class="btn btn-close">&times; <?php _e('Close','laboratory');?></a>
    <div class="ajax-loader"></div>
  </div>
</div>
