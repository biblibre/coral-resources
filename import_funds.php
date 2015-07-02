<?php

/*
**************************************************************************************************************************
** CORAL Resources Module v. 1.2
**
** Copyright (c) 2010 University of Notre Dame
**
** This file is part of CORAL.
**
** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
**
** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
**
**************************************************************************************************************************
*/

include_once 'directory.php';
$resourceObj = new Resource();
$filename = $argv[1];
$delimiter = "\t";

$handle = fopen($filename, "r");
$row = 0;
while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
    if ($row == 0) {
        foreach ($data as $key => $value) {
            $cols[$value] = $key;
        }
    } else {
        $title_id = $data[$cols['id_titre']];
        if (count($resourceObj->getResourceByTitleId($title_id)) == 1) {
            $resources = $resourceObj->getResourceByTitleId($title_id);
            $resource = $resources[0];
            $rp = new ResourcePayment();
            $rp->resourceID = $resource->resourceID;
            $rp->fundName = $data[$cols['fund_name1']];
            $rp->paymentAmount = cost_to_integer($data[$cols['payment_amount_local']]);
            $rp->currencyCode = ($data[$cols['transaction_currency_code']]) ? $data[$cols['transaction_currency_code']] : 'EUR';
            $rp->year = $data[$cols['fiscal_year']];
            $rp->costNote = $data[$cols['note']];
            $rp->invoiceNum = $data[$cols['invoice_number']];
            $rp->orderTypeID = 2;
            $rp->subscriptionStartDate = $data[$cols['invoice_date']];
            $rp->save();
            echo $title_id  . " fund saved\n";

        } else {
            echo "Warning: " . count($resourceObj->getResourceByTitleId($title_id))  . " resource(s) found for " . $title_id . "\n";
        }
    }
    $row++;
}
?>
