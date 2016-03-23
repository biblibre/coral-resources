<?php
require 'vendor/autoload.php';
$server = "http://coral.local/resources/api/";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<link rel="stylesheet" href="pure-min.css">
</head>
<h1>Simple Resources module API client</h1>
<h2>Propose a resource</h2>
<?php
if ($_POST['submitProposeResourceForm']) {
    $fieldNames = array("titleText", "descriptionText", "providerText", "resourceURL", "resourceAltURL", "noteText", "resourceTypeID", "resourceFormatID", "acquisitionTypeID", "administeringSiteID");
    $headers = array("Accept" => "application/json");
    $body = array();
    foreach ($fieldNames as $fieldName) {
        $body[$fieldName] = $_POST[$fieldName];
    }
    $response = Unirest\Request::post($server . "proposeResource/", $headers, $body);
    if ($response->body->resourceID) {
        echo "<p>The resource was correctly submitted (resource " . $response->body->resourceID . ")</p>";
    } else {
        echo "<p>The resource could not be submitted. (error: " . $response->body->error . ")</p>";
    }
    echo '<a href="index.php">Submit another resource</a>';
} else {
  // Checking if the API is up
  $response = Unirest\Request::get($server . "version/", $headers, $body);
  if ($response->code != 200) {
      if ($response->code == 403) {
        echo "<p>You are not authorized to use this service.</p>";
        echo $response->body;
      }
      if ($response->code == 500) {
        echo "<p>This service encountered an error.</p>";
      }
  } else {
?>
<form name="proposeResourceForm" action="index.php" method="POST" class="pure-form pure-form-aligned" style="margin:50px">
<fieldset>
<legend>Product</legend>
<div class="pure-control-group">
<label for="titleText">Title: </label><input name="titleText" type="text" /><br />
</div>
<div class="pure-control-group">
<label for="descriptionText">Description: </label><textarea name="descriptionText"></textarea><br />
</div>
<div class="pure-control-group">
<label for="providerText">Provider: </label><input name="providerText" type="text" /><br />
</div>
<div class="pure-control-group">
<label for="resourceURL">URL: </label><input name="resourceURL" type="text" /><br />
</div>
<div class="pure-control-group">
<label for="resourceAltURL">URL Alt: </label><input name="resourceAltURL" type="text" /><br />
</div>
</fieldset>
<fieldset>
<legend>Format</legend>
<?php getResourceFormatsAsRadio($server); ?>
</fieldset>
<fieldset>
<legend>Acquisition Type</legend>
<?php getAcquisitionTypesAsRadio($server); ?>
</fieldset>
<fieldset>
<legend>Resource Type</legend>
<?php getResourceTypesAsRadio($server); ?>
</fieldset>
<fieldset>
<legend>Library</legend>
<?php getAdministeringSitesAsCheckBoxes($server); ?>
</fieldset>
<fieldset>
<legend>Notes</legend>
<label for="noteText">Include any additional information</label>
<textarea name="noteText"></textarea><br />
</fieldset>
<input type="submit" name="submitProposeResourceForm" />
</form>
<?php
}
}

function getResourceTypesAsRadio($server) {
    $response = Unirest\Request::post($server . "getResourceTypes/", $headers, $body);
    foreach ($response->body as $resourceType) {
        echo ' <input type="radio" name="resourceTypeID" value="' . $resourceType->resourceTypeID . '">' . $resourceType->shortName;
    }
}

function getAcquisitionTypesAsRadio($server) {
    $response = Unirest\Request::post($server . "getAcquisitionTypes/", $headers, $body);
    foreach ($response->body as $resourceType) {
        echo ' <input type="radio" name="acquisitionTypeID" value="' . $resourceType->acquisitionTypeID . '">' . $resourceType->shortName;
    }
}

function getResourceFormatsAsRadio($server) {
    $response = Unirest\Request::post($server . "getResourceFormats/", $headers, $body);
    foreach ($response->body as $resourceType) {
        echo ' <input type="radio" name="resourceFormatID" value="' . $resourceType->resourceFormatID . '">' . $resourceType->shortName;
    }
}

function getAdministeringSitesAsCheckBoxes($server) {
    $response = Unirest\Request::post($server . "getAdministeringSites/", $headers, $body);
    foreach ($response->body as $resourceType) {
        echo ' <input type="checkbox" name="administeringSiteID[]" value="' . $resourceType->administeringSiteID . '">' . $resourceType->shortName;
    }
}



?>
</html>
