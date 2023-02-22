<?php

/**
 * Main plugin class file.
 *
 * @package WordPress Plugin Template/Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class.
 */
class eno_dashboard
{

    /**
     * The single instance of eno-dashboard.
     *
     * @var     object
     * @access  private
     * @since   1.0.0
     */
    private static $_instance = null; //phpcs:ignore

    /**
     * Local instance of eno-dashboard_Admin_API
     *
     * @var eno_dashboard_Admin_API|null
     */
    public $admin = null;

    /**
     * Settings class object
     *
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version; //phpcs:ignore

    /**
     * The token.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token; //phpcs:ignore

    /**
     * The main plugin file.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for JavaScripts.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * Application of Announcements across ENO Dashboards
     *
     * @var     string
     * @access  public
     * @since   1.1.0
     */
    public static $announcement;

    /**
     * Constructor funtion.
     *
     * @param string $file File constructor.
     * @param string $version Plugin version.
     */
    public function __construct($file = '', $version = '1.0.0')
    {
        $this->_version = $version;
        $this->_token   = 'eno-dashboard';

        // Load plugin environment variables.
        $this->file       = $file;
        $this->dir        = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        register_activation_hook($this->file, array($this, 'install'));

        // Load frontend JS & CSS.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 10);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);

        // Load admin JS & CSS.
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);

        // Load API for generic admin functions.
        if (is_admin()) {
            add_action('admin_menu', array($this, 'eno_dashboard_menu'));
        }

        // Handle localisation.
        $this->load_plugin_textdomain();
        add_action('init', array($this, 'load_localisation'), 0);
    } // End __construct ()

    public function eno_dashboard_menu()
    {
        add_menu_page('ENO Dashboard Page', 'ENO Dashboard', 'edit_posts', 'eno-dashboard', array($this, 'eno_dashboard_page_render'), plugin_dir_url(__FILE__) . '../assets/images/icon_eno.png', 0);
        add_submenu_page('eno-dashboard', 'Poll Guideline Page', 'Poll Guidelines', 'edit_posts', 'eno-dashboard-poll', array($this, 'eno_dashboard_poll_page_render'));
        add_submenu_page('eno-dashboard', 'ENO Advertisement Page', 'Advertisements', 'edit_posts', 'eno-dashboard-advertisement', array($this, 'eno_dashboard_advertisement_page_render'));
        add_submenu_page('eno-dashboard', 'ENO Dashboard Settings', 'ENO Dashboard Settings', 'manage_options', 'eno-dashboard-settings', array($this, 'eno_dashboard_settings_page_render'));
    }

    public function eno_dashboard_page_render()
    {
        ?>
        <div style="margin: auto; text-align: center">
            <h1><?php echo self::$announcement ?></h1>
            <h1>This page isn't done yet! Check back later!</h1>
            <h2>Psst, Our Poll Guidelines are done! Check them out <a href="/wp-admin/admin.php?page=eno-dashboard-poll">here</a></h2>
            <h2>Also check out advertising <a href="/wp-admin/admin.php?page=eno-dashboard-advertisement">here</a></h2>
        </div>
        <?php
    }

    public function eno_dashboard_settings_page_render()
    {
        ?>
        <label for="query">Public Announcements:</label>

        <form method="get">
            <input type="hidden" id="page" name="page" value="eno-dashboard-settings">
            <textarea id="query" rows="4" cols="50" name="query"><?php echo self::$announcement ?></textarea>
            <input type="submit" name="submit">
        </form>

        <?php
        if(isset($_GET['query'])) {
            self::$announcement = $_GET['query'];
            echo self::$announcement;
        }?>
        <?php
    }

    public function eno_dashboard_advertisement_page_render()
    {
        ?>
        <div style="margin-left: 150px; margin-right: 150px; text-align: left">
            <h3>Thank you for your willingness to help ENO and sell an ad! I have your answers from a Google Form from a long time ago with your company that you want to sell an ad to. If you do not remember, just let me know, and I can remind you what you said. We finally just got the go-ahead to sell the ads, and it’s been a long time, so I get it! You can follow the simple directions and information below, or feel free to text me separately!</h3>
            <p>-Neena Sidhu
                <br />412-721-3223
                <br />sidhunee000@k12.prosper-isd.net
            </p>
            <br />
            <h1><strong>What to do/say:</strong></h1>
            <p><u>Three options: Over the phone, in-person, or E-Mail</u></p>

            <h2>Over the phone:</h2>
            <ul>
                <li>Call the local number</li>
            </ul>
            <li>Ask for a manager nicely in a non-awkward way, explaining you are a student from Prosper High School or something along those lines. </li>
            <li>Say something along the lines of the following. You can also leave a voicemail/cold call. Also ask for an email where you could possibly send the advertisement-level document if they are interested.</li>
            <ul>
                <li>“Hi, my name is _____. I am calling from Eagle Nation Online, Prosper High School’s newspaper. We are fundraising and I was wondering if you would be interested in purchasing an advertisement spot, and we have many places to do so. We have options for print and online in different sizes and locations.” </li>
            </ul>
            </ul>
            <br />
            <h2>In-person:</h2>
            <ul>
                <li>Walk in and ask for a manager in a nice way, and start a conversation.</li>
            </ul>
            <li>You can talk to/explain the situation and what different packages are, maybe take a copy of the Times and give them a sheet explaining different packages. Visuals help!</li>
            <li>If they want to think about it, leave your contact information</li>
            </ul>
            <br />
            <h2>E-Mail:</h2>
            <ul>
                <li>Find the local business’s email address. Use a personal account so you can see the response because the school blocks you from getting those. </li>
            </ul>
            <li>Attach the advertising packages doc</li>
            <li>Introduce yourself professionally, and use something along the following lines:</li>
            </ul>
            <br /><br />
            <p>Good morning/afternoon/evening,</p>

            <p> My name is _____ and I am a freshman/sophomore/junior/senior at Prosper High School. I am on the newspaper staff of Eagle Nation Online and Eagle Nation Times, and we are selling ads to fund our paper. We have many different packages available, which are all attached on the following document. If you have any questions, let me know!
                <br />Thank you,
                <br />Your Name
                <br />Your email address
            </p>
        </div>
        <?php
    }

    public function eno_dashboard_poll_page_render()
    {
        ?>
        <div style="margin: auto; text-align: center">
            <h1 style="color: #00a000">ENO Poll Guidelines</h1>
            <h3>With the new website design we are implementing Poll Guidelines that must be followed or the poll will be taken down.<br />The guidelines below are the ones that should be followed at all times.</h3>
            <br />
            <h2 style="font-weight: bold">Guidelines:</h2>
            <ol style="list-style-position: inside">
                <li>Poll Titles can only be up to two lines, no more.</li>
                <li>Polls must have four answers, no more or less.</li>
                <li>Polls must be checked by the creator to ensure that they fit in the space allocated to them at any time.</li>
                <li>Polls can only be published when the previous poll has finished.</li>
                <li>Polls may not cross calendar years, I.e. New Years 2023.</li>
                <li>Polls on stories must be checked by Alex in advance because of the spacing requirements on them. <em>(Guidelines are not written)</em></li>
                <li>Every poll must be approved by either Alex, or two editorial board members, Front Page polls must be approved by Alex and an editorial board member.</li>
            </ol>
            <br />
            <h3>To edit polls that appear on the front page of ENO, click <a href="https://eaglenationonline.com/wp-admin/edit.php?post_type=sno_poll">here</a>.</h3>
            <h3>To edit polls that appear on ALL stories on ENO, click <a href="https://eaglenationonline.com/wp-admin/admin.php?page=wp-polls%2Fpolls-manager.php">here</a>.</h3>
            <br />
            <h3>Any and all questions can be forwarded to Alex <em>(<a href="mailto:drumric001@k12.prosper-isd.net">drumric001@k12.prosper-isd.net</a>)</em> directly through Google Chat or an email.</h3>
        </div>
        <?php
    }

    /**
     * Register post type function.
     *
     * @param string $post_type Post Type.
     * @param string $plural Plural Label.
     * @param string $single Single Label.
     * @param string $description Description.
     * @param array  $options Options array.
     *
     * @return bool|string|eno_dashboard_Post_Type
     */
    public function register_post_type($post_type = '', $plural = '', $single = '', $description = '', $options = array())
    {

        if (!$post_type || !$plural || !$single) {
            return false;
        }

        $post_type = new eno_dashboard_Post_Type($post_type, $plural, $single, $description, $options);

        return $post_type;
    }

    /**
     * Wrapper function to register a new taxonomy.
     *
     * @param string $taxonomy Taxonomy.
     * @param string $plural Plural Label.
     * @param string $single Single Label.
     * @param array  $post_types Post types to register this taxonomy for.
     * @param array  $taxonomy_args Taxonomy arguments.
     *
     * @return bool|string|eno_dashboard_Taxonomy
     */
    public function register_taxonomy($taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array())
    {

        if (!$taxonomy || !$plural || !$single) {
            return false;
        }

        $taxonomy = new eno_dashboard_Taxonomy($taxonomy, $plural, $single, $post_types, $taxonomy_args);

        return $taxonomy;
    }

    /**
     * Load frontend CSS.
     *
     * @access  public
     * @return void
     * @since   1.0.0
     */
    public function enqueue_styles()
    {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-frontend');
    } // End enqueue_styles ()

    /**
     * Load frontend Javascript.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function enqueue_scripts()
    {
        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend' . $this->script_suffix . '.js', array('jquery'), $this->_version, true);
        wp_enqueue_script($this->_token . '-frontend');
    } // End enqueue_scripts ()

    /**
     * Admin enqueue style.
     *
     * @param string $hook Hook parameter.
     *
     * @return void
     */
    public function admin_enqueue_styles($hook = '')
    {
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');
    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     *
     * @access  public
     *
     * @param string $hook Hook parameter.
     *
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_scripts($hook = '')
    {
        wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin' . $this->script_suffix . '.js', array('jquery'), $this->_version, true);
        wp_enqueue_script($this->_token . '-admin');
    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function load_localisation()
    {
        load_plugin_textdomain('eno-dashboard', false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_localisation ()

    /**
     * Load plugin textdomain
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function load_plugin_textdomain()
    {
        $domain = 'eno-dashboard';

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_plugin_textdomain ()

    /**
     * Main eno-dashboard Instance
     *
     * Ensures only one instance of eno-dashboard is loaded or can be loaded.
     *
     * @param string $file File instance.
     * @param string $version Version parameter.
     *
     * @return Object eno-dashboard instance
     * @see eno-dashboard()
     * @since 1.0.0
     * @static
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }

        return self::$_instance;
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, esc_html(__('Cloning of eno-dashboard is forbidden')), esc_attr($this->_version));
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, esc_html(__('Unserializing instances of eno-dashboard is forbidden')), esc_attr($this->_version));
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install()
    {
        $this->_log_version_number();
    } // End install ()

    /**
     * Log the plugin version number.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    private function _log_version_number()
    { //phpcs:ignore
        update_option($this->_token . '_version', $this->_version);
    } // End _log_version_number ()

}
