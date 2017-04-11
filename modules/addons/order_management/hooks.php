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
     * Cancel aged orders
     */
    $command = 'GetOrders';
    $values = array(
        'status' => 'Pending',
        'limitnum' => '100',
    );
    $adminuser = 'admin';
    
    $results = localAPI($command, $values, $adminuser);
    
    if ($results['result'] == 'success') {
        $numReturned = $results['numreturned'] - 1;
        for ($i = 0; $i <= $numReturned; $i++) {
            $orderID = $results['orders']['order'][$i]['id'];
            $date = $results['orders']['order'][$i]['date'];
            
            if(strtotime($date) < strtotime("-14 days")) {
                $command = 'CancelOrder';
                $postData = array(
                    'orderid' => $orderID,
                );
                $adminuser = 'admin';
                
                $cancelOrderResults = localAPI($command, $postData, $adminuser);
                
                if ($cancelOrderResults['result'] != 'success') {
                    logActivity("An error occured with cancelling order $orderID: " . $cancelOrderResults['result']);
                }
            }
        }
    } else {
        logActivity("An error occured with getting orders: " . $results['result']);
    }
    
    /*
     * Cancel aged invoices
     */
    $command = 'GetInvoices';
    $values = array(
        'status' => 'Unpaid',
        'limitnum' => '100',
    );
    $adminuser = 'admin';
    
    $results = localAPI($command, $values, $adminuser);
    
    if ($results['result'] == 'success') {
        $numReturned = $results['numreturned'] - 1;
        for ($i = 0; $i <= $numReturned; $i++) {
            $invoiceID = $results['invoices']['invoice'][$i]['id'];
            $date = $results['invoices']['invoice'][$i]['duedate'];

            if(strtotime($date) < strtotime("-14 days")) {
                $command = 'UpdateInvoice';
                $values = array(
                    'invoiceid' => $invoiceID,
                    'status' => 'Cancelled',
                );
                $adminuser = 'admin';
                
                $cancelInvoiceResults = localAPI($command, $values, $adminuser);
                
                if ($cancelInvoiceResults['result'] != 'success') {
                    logActivity("An error occured with cancelling invoice $invoiceID: " . $cancelInvoiceResults['result']);
                }
            }
        }
    } else {
        logActivity("An error occured with getting invoices: " . $results['result']);
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
    $adminuser = 'admin';
    
    $results = localAPI($command, $values, $adminuser);
    
    if ($results['result'] == 'success') {
        $numReturned = $results['numreturned'] - 1;
        for ($i = 0; $i <= $numReturned; $i++) {
            $invoiceID = $results['orders']['order'][$i]['invoiceid'];
            if ($invoiceID == $paidInvoiceID) {
                $orderID = $results['orders']['order'][$i]['id'];
                
                $command = 'AcceptOrder';
                $values = array(
                    'orderid' => $orderID,
                );
                $adminuser = 'admin';
                
                $acceptOrderResults = localAPI($command, $values, $adminuser);
                
                if ($acceptOrderResults['result'] != 'success') {
                    logActivity("An error occured accepting order $invoiceID: " . $acceptOrderResults['result']);
                }
            }
        }
    } else {
        logActivity("An error occured with getting orders: " . $results['result']);
    }
});
