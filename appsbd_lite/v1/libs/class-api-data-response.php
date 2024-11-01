<?php
/**
 * Its pos data response model
 *
 * @since: 21/09/2021
 * @author: Sarwar Hasan
 * @version 1.0.0
 * @package Appsbd\V1\libs
 */

namespace Appsbd_Lite\V1\libs;

/**
 * Class API Data Response
 *
 * @package Appsbd\V1\libs;
 */
class API_Data_Response {
	/**
	 * Its property page
	 *
	 * @var int|mixed|string
	 */
	public $page    = 0;
	/**
	 * Its property limit
	 *
	 * @var int|mixed|string
	 */
	public $limit   = 0;
	/**
	 * Its property total
	 *
	 * @var int
	 */
	public $total   = 0;
	/**
	 * Its property records
	 *
	 * @var int
	 */
	public $records = 0;
	/**
	 * Its property rowdata
	 *
	 * @var array
	 */
	public $rowdata = array();
	/**
	 * Its property data
	 *
	 * @var null
	 */
	public $data    = null;

	/**
	 * API_Data_Response constructor.
	 *
	 * @param array $pay_load Its array parameter.
	 */
	public function __construct( $pay_load = array() ) {
		$this->limit = AppInput::post_value( 'limit', 10 );
		$this->page  = AppInput::post_value( 'page', 1 );
	}

	/**
	 * The set total records is generated by appsbd
	 *
	 * @param int $record_counter Its record counter.
	 *
	 * @return bool Its bool parameter.
	 */
	public function set_total_records( $record_counter ) {

		$this->records = (int) $record_counter;
		if ( $this->records > 0 ) {
			if ( ! empty( $this->limit ) ) {
				$this->total = ceil( $this->records / $this->limit );
			} else {
				$this->total = 1;
			}
			return true;
		}
		return false;
	}

	/**
	 * The set default order is generated by appsbd
	 *
	 * @param mixed $prop its default prop.
	 * @param mixed $ord Its order ASC or DESC.
	 */
	public function set_default_order( $prop, $ord = 'ASC' ) {
		if ( empty( $this->sort_by ) ) {
			$this->sort_by = array(
				array(
					'prop' => $prop,
					'ord' => $ord,
				),
			);
		}
	}
	/**
	 * The limit start is generated by appsbd.
	 *
	 * @return float|int|mixed|string Its type .
	 */
	public function limit_start() {
		if ( empty( $this->limit ) ) {
			return 0;
		}
		return ( intval( $this->limit ) * $this->page ) - $this->limit;
	}

	/**
	 * The SetRowData is generated by appsbd
	 *
	 * @param array $data Its data parameter.
	 */
	public function set_row_data( $data ) {
		$this->rowdata = $data;
	}

	/**
	 * The SetData is generated by appsbd
	 *
	 * @param array $data Its data parameter.
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}
}
