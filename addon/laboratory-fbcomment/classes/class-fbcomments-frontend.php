<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

class FBComments_Frontend {
	public $token;

	/**
	 * Constructor.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct () {
        
        $this->init();
        
	} // End __construct()
	
    /**
	 * Initialise the code.
	 * @since  1.0.0
	 * @return void
	 */
	public function init() {
		add_filter('language_attributes', array( &$this, 'laboratory_fbcomments_schema' ) );
        add_action('wp_head', array( &$this, 'fbgraphinfo' ) );
        add_action('wp_footer', array( &$this, 'fbmlsetup' ), 100);
        add_filter('the_content', array( &$this, 'fbcommentbox' ), 100);
        add_filter('widget_text', array( &$this, 'do_shortcode' ) );
        add_shortcode('laboratory_fbcomments', array( &$this, 'laboratory_fbcomments_shortcode' ) );
	} // End init()
    
    //ADD XFBML
    
    public function laboratory_fbcomments_schema($attr) {
		global $laboratory_fbcomments;
		$options = $laboratory_fbcomments->settings->get_settings();
        
    if (!isset($options['fbns'])) {$options['fbns'] = "";}
    if (!isset($options['opengraph'])) {$options['opengraph'] = "";}
    	if ($options['opengraph']) {$attr .= "\n xmlns:og=\"http://ogp.me/ns#\"";}
    	if ($options['fbns']) {$attr .= "\n xmlns:fb=\"http://ogp.me/ns/fb#\"";}
    	return $attr;
    }
    
    //ADD OPEN GRAPH META
    public function fbgraphinfo() {
		global $laboratory_fbcomments;
		$options = $laboratory_fbcomments->settings->get_settings(); ?>
        <meta property="fb:app_id" content="<?php echo $options['appID']; ?>"/>
        <meta property="fb:admins" content="<?php echo $options['mods']; ?>"/>
    <?php
    }
    
    public function fbmlsetup() {
		global $laboratory_fbcomments;
		$options = $laboratory_fbcomments->settings->get_settings();
    if (!isset($options['fbml'])) {$options['fbml'] = "";}
    if ($options['fbml']) {
    ?>
    <!-- Facebook Comments by Laboratory: http://colorlabsproject.com/plugins/laboratory/ -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/<?php echo $options['language']; ?>/all.js#xfbml=1&appId=<?php echo $options['appID']; ?>";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <?php }}
    
    //COMMENT BOX
    public function fbcommentbox($content) {
		global $laboratory_fbcomments;
		$options = $laboratory_fbcomments->settings->get_settings();
    if (!isset($options['html5'])) {$options['html5'] = false;}
    if (!isset($options['linklove'])) {$options['linklove'] = false;}
    if (!isset($options['posts'])) {$options['posts'] = false;}
    if (!isset($options['pages'])) {$options['pages'] = false;}
    if (!isset($options['homepage'])) {$options['homepage'] = false;}
    if (!isset($options['count'])) {$options['count'] = false;}
    if (!isset($options['countstyle'])) {$options['countstyle'] = '';}
    if (!isset($options['titleclass'])) {$options['titleclass'] = '';}
    	if ((is_single() && $options['posts']) ||
           (is_page() && $options['pages']) ||
           ((is_home() || is_front_page()) && $options['homepage'])) {
    
    		if ($options['count']) {
    			if ($options['countstyle'] == '') {
    				$commentcount = "<p>";
    			} else {
    				$commentcount = "<p class=\"".$options['countstyle']."\">";
    			}
    			$commentcount .= "<fb:comments-count href=".get_permalink()."></fb:comments-count> ".$options['countmsg']."</p>";
    		}
    		if ($options['title'] != '') {
    			if ($options['titleclass'] == '') {
    				$commenttitle = "<h3>";
    			} else {
    				$commenttitle = "<h3 class=\"".$options['titleclass']."\">";
    			}
    			$commenttitle .= $options['title']."</h3>";
    		}
    		$content .= "<!-- Facebook Comments for WordPress: http://colorlabsproject.com/plugins/laboratory/ -->".$commenttitle.$commentcount;
    
          	if ($options['html5']) {
    			$content .=	"<div class=\"fbcomments\" data-href=\"".get_permalink()."\" data-num-posts=\"".$options['num']."\" data-width=\"".$options['width']."\" data-colorscheme=\"".$options['scheme']."\"></div>";
    
        } else {
        $content .= "<fb:comments href=\"".get_permalink()."\" num_posts=\"".$options['num']."\" width=\"".$options['width']."\" colorscheme=\"".$options['scheme']."\"></fb:comments>";
         }
    
        if ($options['linklove']) {
          $content .= '<p>Powered by <a href="http://colorlabsproject.com/plugins/laboratory/">Laboratory</a></p>';
        }
      }
    return $content;
    }
    
    public function laboratory_fbcomments_shortcode($fbatts) {
        global $laboratory_fbcomments;
        extract(shortcode_atts(array(
    		"lab_fbcomments" => $laboratory_fbcomments->settings->get_settings(),
    		"url" => get_permalink(),
        ), $fbatts));
        if (!empty($fbatts)) {
            foreach ($fbatts as $key => $option)
                $lab_fbcomments[$key] = $option;
    	}
    		if ($lab_fbcomments['count']) {
    			if ($lab_fbcomments['countstyle'] == '') {
    				$commentcount = "<p>";
    			} else {
    				$commentcount = "<p class=\"".$lab_fbcomments['countstyle']."\">";
    			}
    			$commentcount .= "<fb:comments-count href=".$url."></fb:comments-count> ".$lab_fbcomments[countmsg]."</p>";
    		}
    		if ($lab_fbcomments['title'] != '') {
    			if ($lab_fbcomments['titleclass'] == '') {
    				$commenttitle = "<h3>";
    			} else {
    				$commenttitle = "<h3 class=\"".$lab_fbcomments['titleclass']."\">";
    			}
    			$commenttitle .= $lab_fbcomments['title']."</h3>";
    		}
    		$fbcommentbox = "<!-- Facebook Comments for WordPress: http://colorlabsproject.com/plugins/laboratory/ -->".$commenttitle.$commentcount;
    
          	if ($lab_fbcomments['html5']) {
    			$fbcommentbox .= "<div class=\"fbcomments\" data-href=\"".$url."\" data-num-posts=\"".$lab_fbcomments['num']."\" data-width=\"".$lab_fbcomments['width']."\" data-colorscheme=\"".$lab_fbcomments['scheme']."\"></div>";
    
        } else {
        $fbcommentbox .= "<fb:comments href=\"".$url."\" num_posts=\"".$lab_fbcomments['num']."\" width=\"".$lab_fbcomments['width']."\" colorscheme=\"".$lab_fbcomments['scheme']."\"></fb:comments>";
         }
    
        if ($lab_fbcomments['linklove']) {
          $fbcommentbox .= '<p>Powered by <a href="http://colorlabsproject.com/plugins/laboratory/">Laboratory</a></p>';
        }
        return $fbcommentbox;
    }

}//END of FBComments_Frontend class

?>