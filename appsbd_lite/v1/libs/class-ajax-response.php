<?php
/**
 * Its pos appsbd-ajax-confirm-response model
 *
 * @since: 21/09/2021
 * @author: Sarwar Hasan
 * @version 1.0.0
 * @package Appsbd\V1\libs
 */

namespace Appsbd_Lite\V1\libs;

if ( ! class_exists( __NAMESPACE__ . '\Ajax_Response' ) ) {
	/**
	 * Class appsbd_ajax_confirm_response
	 *
	 * @package Appsbd\V1\libs
	 */
	class Ajax_Response {
		/**
		 * Its property status
		 *
		 * @var bool
		 */
		public $status = false;
		/**
		 * Its property msg
		 *
		 * @var null
		 */
		public $msg = null;
		/**
		 * Its property data
		 *
		 * @var null
		 */
		public $data = null;

		/**
		 * The set response is generated by appsbd
		 *
		 * @param mixed $status Its status param.
		 * @param null  $data Its data param.
		 */
		public function set_response( $status, $data = null ) {
			$this->status = $status;
			$this->msg    = \Appsbd_Lite\V1\Core\Kernel_Lite::get_msg_for_api();
			$this->data   = $data;
		}

		/**
		 * The display with response is generated by appsbd
		 *
		 * @param mixed $status Its status param.
		 * @param null  $data Its data param.
		 */
		public function display_with_response( $status, $data = null ) {
			$this->set_response( $status, $data );
			$this->display();
		}

		/**
		 * The display is generated by appsbd
		 */
		public function display() {
			wp_send_json( $this );
		}
	}
}
