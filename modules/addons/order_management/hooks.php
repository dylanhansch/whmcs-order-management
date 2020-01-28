<?php
if (!defined('WHMCS')) {
	die('This file cannot be accessed directly.');
}

require_once(__DIR__ . '/lib/extras.php');

add_hook('AfterCronJob', 1, function($vars) {
	if (!acceptPaidPendingOrdersIsEnabled() && !cancelAgedOrdersIsEnabled()) {
		return;
	}

	$cancelAfterDays = cancelAfterDays();

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

			if ($paymentStatus == 'Paid' && acceptPaidPendingOrdersIsEnabled()) {
				$command = 'AcceptOrder';
				$values = array(
					'orderid' => $orderID,
				);

				$acceptOrderResults = localAPI($command, $values);

				if ($acceptOrderResults['result'] != 'success') {
					logActivity('[Order Management] An error occured accepting order ' . $orderID . ': ' . $acceptOrderResults['result']);
				}
			} else if (strtotime($date) < strtotime('-' . $cancelAfterDays . ' days') && cancelAgedOrdersIsEnabled()) {
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
});

add_hook('AfterCronJob', 2, function($vars) {
	if (!cancelAgedInvoicesIsEnabled()) {
		return;
	}

	$cancelAfterDays = cancelAfterDays();

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

			if(strtotime($date) < strtotime('-' . $cancelAfterDays . ' days')) {
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
	if (!acceptPaidPendingOrdersIsEnabled()) {
		return;
	}

	$paidInvoiceID = $vars['invoiceid'];

	/*
	 * Accept paid orders
	 */
	$command = 'GetInvoice';
	$values = array(
		'invoiceid' => $paidInvoiceID
	);

	$invoiceResults = localAPI($command, $values);

	if ($invoiceResults['result'] == 'success') {
		$userID = $invoiceResults['userid'];

		$command = 'GetOrders';
		$values = array(
			'status' => 'Pending',
			'limitnum' => '100',
			'userid' => $userID
		);

		$orderResults = localAPI($command, $values);

		if ($orderResults['result'] == 'success') {
			for ($i = 0; $i < $orderResults['numreturned']; $i++) {
				$order = $orderResults['orders']['order'][$i];

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

					// Reduce unnecessary loops
					break;
				}
			}
		} else {
			logActivity('[Order Management] An error occured with getting orders: ' . $orderResults['result']);
		}
	} else {
		logActivity('[Order Management] An error occured with getting invoice: ' . $invoiceResults['result']);
	}
});
