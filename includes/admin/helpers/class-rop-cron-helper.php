<?php
/**
 * Used to manage WordPress Cron for Rop.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0rc
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Rop_Cron_Helper Class
 *
 * @since   8.0.0rc
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Cron_Helper {
	/**
	 * Cron action name.
	 */
	const CRON_NAMESPACE = 'rop_cron_job';

	/**
	 * Defines new schedules for cron use.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $schedules The schedules array.
	 *
	 * @return mixed
	 */
	public static function rop_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['5min'] ) ) {
			$schedules['5min'] = array(
				'interval' => 5 * 60,
				'display'  => __( 'Once every 5 minutes', 'tweet-old-post' ),
			);
		}
		if ( ! isset( $schedules['30min'] ) ) {
			$schedules['30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes', 'tweet-old-post' ),
			);
		}

		return $schedules;
	}

	/**
	 * Utility method to manage cron.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @return  array Current status.
	 */
	public function manage_cron( $request ) {
		if ( $request['action'] == 'start' ) {
			$this->create_cron();
		} elseif ( $request['action'] == 'stop' ) {
			$this->remove_cron();
		}

		return array(
			'current_status'   => $this->get_status(),
			'date_format'      => $this->convert_phpformat_to_js( Rop_Scheduler_Model::get_date_format() ),
			'current_php_date' => Rop_Scheduler_Model::get_date(),
			'current_time'     => Rop_Scheduler_Model::get_current_time(),
		);
	}

	/**
	 * Utility method to start a cron.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @return bool
	 */
	public function create_cron( $first = true ) {
		if ( ! wp_next_scheduled( self::CRON_NAMESPACE ) ) {
			if ( $first ) {
				$settings = new Rop_Global_Settings();
				$settings->update_start_time();
				wp_schedule_single_event( time() + 30, self::CRON_NAMESPACE );
			}
			wp_schedule_event( time(), '5min', self::CRON_NAMESPACE );
		}

		return true;
	}

	/**
	 * Utility method to stop a cron.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @return bool
	 */
	public function remove_cron() {
		$timestamp = wp_next_scheduled( self::CRON_NAMESPACE );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_NAMESPACE );
		}
		$settings = new Rop_Global_Settings();
		$settings->reset_start_time();

		return false;
	}

	/**
	 * Get cron status.
	 *
	 * @return bool Cron status.
	 */
	public function get_status() {
		return (bool) wp_next_scheduled( self::CRON_NAMESPACE );
	}

	private function convert_phpformat_to_js( $format ) {
		$replacements = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent
			'L' => '', // no equivalent
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js
			'I' => '', // no equivalent
			'O' => '', // no equivalent
			'P' => '', // no equivalent
			'T' => '', // no equivalent
			'Z' => '', // no equivalent
			'c' => '', // no equivalent
			'r' => '', // no equivalent
			'U' => 'X',
		];
		$momentFormat = strtr( $format, $replacements );

		return $momentFormat;
	}
}
