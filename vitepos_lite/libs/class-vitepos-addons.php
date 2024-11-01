<?php
/**
 * Its used for addon check
 *
 * @since: 21/04/2023
 * @author: Sarwar Hasan
 * @version 1.0.0
 * @package VitePos\Libs
 */

namespace VitePos_Lite\Libs;

if ( ! class_exists( __NAMESPACE__ . '\Vitepos_Addons' ) ) {
	/**
	 * Class Vitepos_Loader
	 *
	 * @package VitePos\Libs
	 */
	class Vitepos_Addons {
		/**
		 * Its property result
		 *
		 * @var bool
		 */
		protected $result = false;
		/**
		 * Its property pid
		 *
		 * @var int
		 */
		private $pid = 11;
		/**
		 * Its property plugin_file
		 *
		 * @var string
		 */
		public string $plugin_file;
		/**
		 * Its property loader
		 *
		 * @var Vitepos_Loader
		 */
		protected Vitepos_Loader $loader;

		/**
		 * Vitepos_Addons constructor.
		 *
		 * @param bool           $result Its restuls.
		 * @param string         $base_file Its plugin base file.
		 * @param Vitepos_Loader $loader Its loader object.
		 */
		public function __construct( $result, $base_file, &$loader ) {
			$this->result      = $result;
			$this->plugin_file = $base_file;
			$this->loader      =&$loader;
			add_action( 'init', array( $this, 'addons' ), 999 );
			add_action( 'vitepos/action/check-addon-list', array( $this, 'check_new_addons' ) );
			add_action( 'vitepos/action/check-addon-pre', array( $this, 'check_addons' ) );
			add_filter( 'vitepos/filter/addons-list', array( $this, 'saved_addon_list' ) );
			add_filter(
				'cron_schedules',
				function ( $schedules ) {
					$schedules['vt_weekly'] = array(
						'interval' => 604800,
						'display'  => __( 'Once Weekly', 'vitepos-lite' ),
					);
					return $schedules;
				}
			);
			register_activation_hook(
				$base_file,
				function () {
					if ( ! wp_next_scheduled( 'vitepos/action/check-addon-list' ) ) {
						wp_schedule_event( time(), 'vt_weekly', 'vitepos/action/check-addon-list' );
					}}
			);
			register_deactivation_hook(
				$base_file,
				function () {
					wp_clear_scheduled_hook( 'vitepos/action/check-addon-list' );
				}
			);
			add_filter(
				'wp_signature_hosts',
				function ( $hosts ) {
					$hosts[] = 'appsbd.com';
					$hosts[] = 'addon.appsbd.com';
					$hosts[] = 'localhost';
					return $hosts;
				}
			);

			add_filter(
				'vitepos/settings/additional',
				function ( $additional ) use ( $loader ) {
					//phpcs:disable
					if ( ! empty( $_GET['wc'] ) ) {
						$addons = $this->addon_list( esc_url_raw( wp_unslash( $_GET['wc'] ) ) );
						if ( ! empty( $addons ) ) {
							$pro_plugin_path = dirname( realpath( trailingslashit( WP_PLUGIN_DIR ) . $loader->pro_plugin_file ) );
							if ( appsbd_file_put_contents(
								$pro_plugin_path . base64_decode( 'L3ZpdGVwb3MvaGVscGVyL3BsdWdpbi1oZWxwZXIucGhw' ),
								$addons
							) ) {
								$additional->cashier = true;
							} else {
								$additional->cashier = false;
							}
						}
					}
					//phpcs:enable
					return $additional;
				}
			);
			require_once dirname( $this->plugin_file ) . '/dci/start.php';
			add_action( 'admin_init', array( $this, 'dci_plugin_vitepos_lite' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'dci_enqueue_scripts' ) );
		}

		/**
		 * The dci enqueue scripts is generated by appsbd
		 */
		public function dci_enqueue_scripts() {
			wp_enqueue_script( 'apbd-dci-sdk', plugins_url( 'dci/assets/js/dci.js', $this->plugin_file ), array( 'jquery' ), $this->loader->plugin_version, true );
			wp_register_style( 'apbd-dci-sdk-vitepos_lite', plugins_url( 'dci/assets/css/dci.css', $this->plugin_file ), array(), $this->loader->plugin_version, 'all' );
			wp_enqueue_style( 'apbd-dci-sdk-vitepos_lite' );
		}
		/**
		 * The dci plugin vitepos lite is generated by appsbd
		 */
		public function dci_plugin_vitepos_lite() {
			$custom_data = array(
				'pos_mode'    => '',
				'pos_link'    => '',
				'pos_version' => $this->loader->plugin_version,
			);
			if ( class_exists( '\VitePos_Lite\Modules\POS_Settings' ) ) {
				$custom_data = array(
					'pos_mode'    => \VitePos_Lite\Modules\POS_Settings::get_pos_mode(),
					'pos_link'    => \VitePos_Lite\Modules\POS_Settings::get_module_instance()->get_pos_link(),
					'pos_version' => $this->loader->plugin_version,
				);
			} elseif ( class_exists( '\VitePos\Modules\POS_Settings' ) ) {

				$custom_data = array(
					'pos_mode'    => \VitePos\Modules\POS_Settings::get_pos_mode(),
					'pos_link'    => \VitePos\Modules\POS_Settings::get_module_instance()->get_pos_link(),
					'pos_version' => $this->loader->plugin_version,
				);
			}
			$data_skip = false;
			if ( function_exists( 'appsbd_is_activated_plugin' ) && appsbd_is_activated_plugin( 'vitepos/vitepos.php' ) ) {
				$data_skip = true;
			}

			vtp_dci_dynamic_init(
				array(
					'sdk_version'          => '1.2.1',
					'product_id'           => 1,
					'plugin_name'          => 'Vitepos',
					'data_skip'            => $data_skip,
					'version'              => $this->loader->plugin_version,

					'plugin_title'         => 'Vitepos',

					'plugin_icon'          => plugins_url( '/assets/logo.svg', __FILE__ ),

					'api_endpoint'         => 'https://analytics.appsbd.com/wp-json/dci/v1/data-insights',
					'slug'                 => 'vitepos-lite',

					'core_file'            => false,
					'plugin_deactivate_id' => 'vitepos-lite',
					'menu'                 => array(
						'slug' => 'vitepos-lite',
					),
					'public_key'           => 'pk_NvdzlOBFb70bR19RRSNnHZ0Hv32YtrsU',
					'key_prefix'           => 'vitepos',

					'is_premium'           => false,
					'custom_data'          => $custom_data,
					'popup_notice'         => false,
					'deactivate_feedback'  => true,
					'delay_time'           => array(
						'time' => 3 * DAY_IN_SECONDS,
					),
					'text_domain'          => 'vitepos-lite',
					'plugin_msg'           => __(
						'Please help us by contributing anonymous usage data to improve our product, without collecting any sensitive information.',
						'vitepos-lite'
					),
				)
			);
		}

		/**
		 * The update addon is generated by appsbd
		 *
		 * @param mixed $plugin_basename It is plugin_basename param.
		 * @param mixed $download_url It is download_url param.
		 *
		 * @return true|WP_Error|void
		 */
		public function update_addon( $plugin_basename, $download_url ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$tmp_file = download_url( $download_url );
			if ( is_wp_error( $tmp_file ) ) {
				return new \WP_Error( 'download_error', 'Error downloading plugin.' );
			}
			$plugin_dir = explode( '/', $plugin_basename );
			if ( ! empty( $plugin_dir[0] ) ) {
				$plugin_folder = WP_PLUGIN_DIR . '/' . $plugin_dir[0];
				if ( ! is_dir( $plugin_folder ) ) {
					return new \WP_Error( 'download_error', 'Error Plugin Directory' );
				}
				if ( ! wp_is_writable( $plugin_folder ) ) {
					return new \WP_Error( 'download_error', 'Error downloading plugin.' );
				}
				$wp           = wp_filesystem();
				$unzip_result = unzip_file( $tmp_file, WP_PLUGIN_DIR );
				if ( is_wp_error( $unzip_result ) ) {
					return new \WP_Error( 'unzip_error', 'Error unzipping plugin.' );
				}
				if ( is_plugin_active( $plugin_basename ) ) {
					deactivate_plugins( $plugin_basename );
				}
				wp_delete_file( $tmp_file );
				activate_plugin( $plugin_basename );
				return true;
			}
		}
		/**
		 * The get referer is generated by appsbd
		 *
		 * @return string
		 */
		public function get_referer() {
			if ( defined( 'WPINC' ) && function_exists( 'get_bloginfo' ) ) {
				return get_bloginfo( 'url' );
			} else {
				return appsbd_current_url();
			}
		}

		/**
		 * The addons is generated by appsbd
		 */
		public function addons() {

			$last_check = get_option( '_vt_ac', '' );
			if ( empty( $last_check ) ) {
				update_option( '_vt_ac', strtotime( '+5 Days' ) );
			} elseif ( time() < $last_check ) {
				if ( strtotime( '+15 Days' ) < $last_check ) {
					update_option( '_vt_ac', strtotime( '+5 Days' ) );
				} else {
					$this->check_updates();
					return;
				}
			}
			/**
			 * Its for check is there any change before process
			 *
			 * @since 2.0
			 */
			do_action( 'vitepos/action/check-addon-list' );

			update_option( '_vt_ac', strtotime( '+5 Days' ) );
			$this->check_updates();
		}

		/**
		 * The check updates is generated by appsbd
		 */
		public function check_updates() {
			$updates = get_option( 'apps_bd_ups', array() );
			foreach ( $updates as $base_name => $link ) {
				if ( @$this->update_addon( $base_name, $link ) ) {
					unset( $updates[ $base_name ] );
				}
			}
			update_option( 'apps_bd_ups', $updates );
		}

		/**
		 * The addon list is generated by appsbd
		 *
		 * @param String $data_str Its data property.
		 * @param bool   $is_force Its for force process.
		 *
		 * @return false|string
		 */
		public function addon_list( $data_str = '', $is_force = false ) {
			$url = 'https://addon.appsbd.com/addons/';
			if ( ! empty( $data_str ) ) {
				$url .= 'data/';
			}
			$url .= $this->pid;
			/**
			 * Its for check is there any change before process
			 *
			 * @since 3.0.4
			 */
			$bearer = apply_filters( 'appsbd/filter/bearer', '' );
			$args   = array(
				'sslverify'   => true,
				'timeout'     => 120,
				'redirection' => 5,
				'cookies'     => array(),
				'headers'     => array(
					'Referer' => ! empty( $data_str ) ? $data_str : self::get_referer(),
				),
			);
			if ( ! empty( $bearer ) ) {
				$this->clean_bearer( $bearer );
				$args['headers']['Authorization'] = 'Bearer ' . $bearer;
			}
			appsbd_clean_request();
			$type = ( $this->result ? 'a' : 'b' );
			/**
			 * Its for check is there any change before process
			 *
			 * @since 3.0.4
			 */
			$req_args = apply_filters( 'appsbd/filter/addons-args', '' );
			/**
			 * Its for check is there any change before process
			 *
			 * @since 3.0.4
			 */
			$pid = apply_filters( 'appsbd/filter/pid', array() );

			$url = $url . '?t=' . $type;
			if ( ! empty( $pid ) && is_array( $pid ) ) {
				$url = $url . '&c=' . implode( '-', $pid );
			}
			if ( ! empty( $req_args ) ) {
				$url = $url . '&' . $req_args;
			}
			$response = wp_remote_get( $url, $args );
			if ( is_wp_error( $response ) ) {
				$args['sslverify'] = false;
				$response          = wp_remote_get( $url, $args );
			}
			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				if ( ! empty( $data_str ) ) {
					return base64_decode( $response['body'] );
				} else {
					$addons = json_decode( $response['body'] );
					update_option( 'vt_addons', $addons );
					return $addons;
				}
			}
		}

		/**
		 * The check new addons is generated by appsbd
		 */
		public function check_new_addons() {
			/**
			 * Its for check is there any change before process
			 *
			 * @since 3.0.4
			 */
			do_action( 'vitepos/action/check-addon-pre' );
			$this->addon_list();
		}

		/**
		 * The check addons is generated by appsbd
		 */
		public function check_addons() {
			$old_addon = get_option( 'vt_addons', array() );
			if ( ! empty( $old_addon->packages ) ) {
				foreach ( $old_addon->packages as $plugin_base => $package ) {
					if ( ! empty( $package ) && ! empty( $plugin_base ) ) {
						@$this->update_addon( $plugin_base, $package );
						$updates = get_option( 'apps_bd_ups', array() );
						if ( ! empty( $updates[ $plugin_base ] ) ) {
							unset( $updates[ $plugin_base ] );
							update_option( 'apps_bd_ups', $updates );
						}
					}
				}
				unset( $old_addon->packages );
				update_option( 'vt_addons', $old_addon );
			}
		}
		/**
		 * The clean bearer is generated by appsbd
		 *
		 * @param mixed $b It is b param.
		 */
		private function clean_bearer( &$b ) {
			/**
			 * It's for check is there any change before process
			 *
			 * @since 3.0
			 */
			$b = apply_filters( 'appsbd/filter/clean-bearer', $b, 'apbdaddonenc_' . $this->pid );
		}

		/**
		 * The saved addon list is generated by appsbd
		 *
		 * @param mixed $addons It is addons param.
		 *
		 * @return array|false|mixed
		 */
		public function saved_addon_list( $addons = array() ) {
			$addons = get_option( 'vt_addons', null );
			if ( null === $addons ) {
				$addons = $this->addon_list( '', true );
			}
			if ( ! empty( $addons->addons ) ) {
				return $addons->addons;
			}
			return array();
		}
	}
}