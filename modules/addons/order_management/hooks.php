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

add_hook('PreAutomationTask', 1, function($vars) {
	/*
	 * Accept paid but still pending orders and cancel aged orders
	 */
	$command = 'GetOrders';
	$values = array(
		'status' => 'Pending',
		'limitnum' => '100',
	);

	$results = localAPI($command, $values);

	if ($results['result'] == 'success') {
		for ($i = 0; $i < $results['numreturned']; $i++) {
			$order = $results['orders']['order'][$i];

			$orderID = $order['id'];
			$date = $order['date'];
			$paymentStatus = $order['paymentstatus'];

			// Don't want to automate anything with free orders for example
			if ($paymentStatus == null) {
				logActivity('[Order Management] Skipping order #' . $orderID . '. Payment status null. Might be a free order. Manual intervention required for this order.');
				continue;
			}

			if ($paymentStatus == 'Paid') {
				$command = 'AcceptOrder';
				$values = array(
					'orderid' => $orderID,
				);

				$acceptOrderResults = localAPI($command, $values);

				if ($acceptOrderResults['result'] != 'success') {
					logActivity('[Order Management] An error occured accepting order ' . $orderID . ': ' . $acceptOrderResults['result']);
				}
			} else if (strtotime($date) < strtotime('-14 days')) {
				$command = 'CancelOrder';
				$postData = array(
					'orderid' => $orderID,
				);

				$cancelOrderResults = localAPI($command, $postData);

				if ($cancelOrderResults['result'] != 'success') {
					logActivity('[Order Management] An error occured with cancelling order ' . $orderID . ': ' . $cancelOrderResults['result']);
				}
			}
		}
	} else {
		logActivity('[Order Management] An error occured with getting orders: ' . $results['result']);
	}

	/*
	 * Cancel aged invoices
	 */
	$command = 'GetInvoices';
	$values = array(
		'status' => 'Unpaid',
		'limitnum' => '100',
	);

	$results = localAPI($command, $values);

	if ($results['result'] == 'success') {
		for ($i = 0; $i < $results['numreturned']; $i++) {
			$invoice = $results['invoices']['invoice'][$i];

			$invoiceID = $invoice['id'];
			$date = $invoice['duedate'];

			if(strtotime($date) < strtotime('-14 days')) {
				$command = 'UpdateInvoice';
				$values = array(
					'invoiceid' => $invoiceID,
					'status' => 'Cancelled',
				);

				$cancelInvoiceResults = localAPI($command, $values);

				if ($cancelInvoiceResults['result'] != 'success') {
					logActivity('[Order Management] An error occured with cancelling invoice ' . $invoiceID . ': ' . $cancelInvoiceResults['result']);
				}
			}
		}
	} else {
		logActivity('[Order Management] An error occured with getting invoices: ' . $results['result']);
	}
});

add_hook('InvoicePaid', 1, function($vars) {
	$paidInvoiceID = $vars['invoiceid'];

	/*
	 * Accept paid orders
	 */
	$command = 'GetOrders';
	$values = array(
		'status' => 'Pending',
		'limitnum' => '100',
	);

	$results = localAPI($command, $values);

	if ($results['result'] == 'success') {
		for ($i = 0; $i < $results['numreturned']; $i++) {
			$order = $results['orders']['order'][$i];

			$invoiceID = $order['invoiceid'];

			if ($invoiceID == $paidInvoiceID) {
				$orderID = $order['id'];

				$command = 'AcceptOrder';
				$values = array(
					'orderid' => $orderID,
				);

				$acceptOrderResults = localAPI($command, $values);

				if ($acceptOrderResults['result'] != 'success') {
					logActivity('[Order Management] An error occured accepting order ' . $orderID . ': ' . $acceptOrderResults['result']);
				}
			}
		}
	} else {
		logActivity('[Order Management] An error occured with getting orders: ' . $results['result']);
	}
});
