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
 * @version 1.0
 * @author Dylan Hansch <dylan@dylanhansch.net>
 */
 
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly.");
}

function order_management_config() {
	return array(
		'name' => 'Order Management',
		'description' => 'Automates/extends several order related tasks.',
		'author' => 'Dylan Hansch',
		'language' => 'english',
		'version' => '1.0',
		'fields' => array(
			'cancelAfter' => array(
				'FriendlyName' =>  'Cancel unpaid order after X days',
				'Type' => 'text',
				'Size' => '25',
				'Default' => '14',
				'Description' => 'e.g. 14 days',
			),
		)
	);
}
