<?php
/**
 * WHMCS Order Management
 *
 * This addon automates several order related tasks such as
 * cancelling an order after 14 days it goes unpaid.
 * Will eventually rewrite description.
 *
 * Currently implemented features:
 * - Cancel unpaid order after 14 days
 * - Cancel unpaid invoice after 14 days
 * - Accept paid orders
 *
 * @version 1.2
 * @author Dylan Hansch <dylan@dylanhansch.net>
 */

if (!defined('WHMCS')) {
	die('This file cannot be accessed directly.');
}

use WHMCS\Database\Capsule;

/**
 * Get number of days after which to cancel an order and/or invoice that is still unpaid. Default is 14 days.
 */
function cancelAfterDays() {
	$cancelAfterDays = '14';

	try {
		$query = Capsule::table('tbladdonmodules')
			->select('value')
			->where('module', 'order_management')
			->where('setting', 'cancelAfter')
			->first();

		$cancelAfterResult = trim($query->value);

		if ($cancelAfterResult != '') {
			$cancelAfterDays = $cancelAfterResult;
		}
	} catch (\Exception $e) {
		logActivity('[Order Management] ' . $e);
	}

	return $cancelAfterDays;
}

function cancelAgedOrdersIsEnabled() {
	return isSettingOn('enableCancelAgedOrders');
}

function cancelAgedInvoicesIsEnabled() {
	return isSettingOn('enableCancelAgedInvoices');
}

function acceptPaidPendingOrdersIsEnabled() {
	return isSettingOn('enableAcceptPaidPendingOrders');
}

function isSettingOn($setting) {
	$isEnabled = true;

	try {
		$query = Capsule::table('tbladdonmodules')
			->select('value')
			->where('module', 'order_management')
			->where('setting', $setting)
			->first();

		$result = trim($query->value);

		if (count($query) == 1 && $result == '') {
			$isEnabled = false;
		}
	} catch (\Exception $e) {
		logActivity('[Order Management] ' . $e);
	}

	return $isEnabled;
}
