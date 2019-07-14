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

function order_management_config() {
	return array(
		'name' => 'Order Management',
		'description' => 'Automates/extends several order related tasks.',
		'author' => 'Dylan Hansch',
		'language' => 'english',
		'version' => '1.2',
		'fields' => array(
			'cancelAfter' => array(
				'FriendlyName' =>  'Cancel unpaid orders and invoices after this many days',
				'Type' => 'text',
				'Size' => '25',
				'Default' => '14',
				'Description' => 'e.g. 14 days',
			),
			'enableCancelAgedOrders' => array(
				'FriendlyName' => 'Cancel unpaid pending orders after the configured number of days',
				'Type' => 'yesno',
				'Size' => '25',
				'Default' => 'on'
			),
			'enableCancelAgedInvoices' => array(
				'FriendlyName' => 'Cancel unpaid invoices after the configured number of days',
				'Type' => 'yesno',
				'Size' => '25',
				'Default' => 'on'
			),
			'enableAcceptPaidPendingOrders' => array(
				'FriendlyName' => 'Accept paid pending orders',
				'Type' => 'yesno',
				'Size' => '25',
				'Default' => 'on'
			)
		)
	);
}
