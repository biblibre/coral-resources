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
include_once 'user.php';
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
            $rp = new ResourceNote();
            $rp->resourceID = $resource->resourceID;

            $rp->noteTypeID = 4;
            if ($data[$cols['statut']] == 'p') 
                $rp->noteTypeID = 9;

            if ($data[$cols['statut']] == 'i') 
                $rp->noteTypeID = 10;

            $rp->tabName = 'Product';
            $rp->noteText = $data[$cols['titre']] . "\n" . $data[$cols['contenu']];
            $rp->updateDate = date( 'Y-m-d' );
            $rp->updateLoginID = 'admin';
            $rp->save();
            echo $title_id  . " note saved\n";

        } else {
            echo "Warning: " . count($resourceObj->getResourceByTitleId($title_id))  . " resource(s) found for " . $title_id . "\n";
        }
    }
    $row++;
}
?>
