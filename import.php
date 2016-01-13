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

$cli = false;
$longopts = array(
    "delimiter::",
    "encode",
    "parent::",
    "parentcolumn::",
    "file:",
);

// CSV configuration
$required_columns = array('titleText' => 0, 
    'resourceURL' => 0, 
    'resourceAltURL' => 0, 
    'parentResource' => 0, 
    'organization' => 0, 
    'role' => 0, 
    'title_id' => 0, 
    'dateFirstIssueOnline' => 0, 
    'numFirstVolOnline' => 0,
    'numFirstIssueOnline' => 0,
    'dateLastIssueOnline' => 0,
    'numLastVolOnline' => 0,
    'numLastIssueOnline' => 0,
    'firstAuthor' => 0,
    'embargoInfo' => 0,
    'coverageDepth' => 0, 
    'coverageText' => 0);


$options = getopt("", $longopts);
if ($options) {
  error_reporting(E_ERROR);
  include_once 'directory.php';
  $config = new Configuration();
  $cli = true;
  $encode = array_key_exists('encode', $options);
  $uploadfile = $options['file'];
  echo "file: $uploadfile\n";
  $delimiter = array_key_exists('delimiter', $options) ? $options['delimiter'] : "\t";
  echo "delimiter: $delimiter\n";
  if (($handle = fopen($uploadfile, "r")) !== FALSE) {
    if (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
      foreach ($data as $key => $value) {
        $available_columns[$key] = $value;
        foreach ($required_columns as $rkey => $rvalue) {
          if (tryToMatch($value, $rkey)) {
            $_POST[$rkey] = $key;
            echo "Matched $value => $rkey (column $key)\n";
          }
        }
      }
    }
  }
  if (array_key_exists('parent', $options)) {
    $_POST['genericParent'] = $options['parent'];
    echo "Generic parent resource: " . $options['parent'] . "\n";
  }
  if (array_key_exists('parentcolumn', $options)) {
    $_POST['parentResource'] = array_search($options['parentcolumn'], $available_columns);
    echo "Parent resource column: " . $options['parentcolumn'] . " (column " .array_search($options['parentcolumn'], $available_columns) . ")\n";
  }

  $deduping_config = explode(',', $config->settings->importISBNDedupingColumns); 
}

if (!$cli) {
session_start();
include_once 'directory.php';
//print header
$pageTitle=_('Resources import');
include 'templates/header.php';

?><div id="importPage"><h1><?php echo _("CSV File import");?></h1><?php
if ($_POST['submit']) {
  $delimiter = $_POST['delimiter'];
  if ($delimiter == "TAB") $delimiter = "\t";
  $uploaddir = 'attachments/';
  $uploadfile = $uploaddir . basename($_FILES['uploadFile']['name']);
  if (move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile)) {  
    print '<p>'._("The file has been successfully uploaded.").'</p>';
  
  // Let's analyze this file
  if (($handle = fopen($uploadfile, "r")) !== FALSE) {
    if (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
      $columns_ok = true;
      foreach ($data as $key => $value) {
        $available_columns[$value] = $key;
      } 
    } else {
      $error = _("Unable to get columns headers from the file");
    }
  } else {
    $error = _("Unable to open the uploaded file");
  }
  } else {
    $error = _("Unable to upload the file");
  }
  if ($error) {
    print "<p>"._("Error: ").$error.".</p>";
  } else {
    print "<p>"._("Please choose columns from your CSV file:")."</p>";
    print "<form action=\"import.php\" method=\"post\">";
    foreach ($required_columns as $rkey => $rvalue) {
      print "<label for=\"$rkey\">" . $rkey . "</label><select name=\"$rkey\">";
      print '<option value=""></option>';
      foreach ($available_columns as $akey => $avalue) {
        print "<option value=\"$avalue\"";
        if (tryToMatch($akey, $rkey)) print ' selected="selected"';
        print ">$akey</option>";
      } 
      print '</select><br />';
    }
    print "You can also enter a default fallback parent resource: ";
    print "<input type=\"text\" name=\"genericParent\" />";
    print "<input type=\"hidden\" name=\"delimiter\" value=\"$delimiter\" />";
    print "<input type=\"hidden\" name=\"uploadfile\" value=\"$uploadfile\" />";
    print "<input type=\"submit\" name=\"matchsubmit\" id=\"matchsubmit\" /></form>";
  }
}
}
          /*
          // Convert to UTF-8
          if (($cli && $encode) || $_POST['matchsubmit']) 
              $data = array_map(function($row) { return mb_convert_encoding($row, 'UTF-8'); }, $data);
        
          // Let's insert data
          $resource->createLoginID    = $loginID;
          $resource->createDate       = date( 'Y-m-d' );
          $resource->updateLoginID    = '';
          $resource->updateDate       = '';
          $resource->statusID         = 1;
          if ($cli) echo "=> Importing " . $data[$_POST['titleText']] . "\n";
          foreach(array('titleText', 'descriptionText', 'resourceURL', 'resourceAltURL', 'numFirstVolOnline', 'numFirstIssueOnline', 'numLastVolOnline', 'numLastIssueOnline', 'firstAuthor', 'embargoInfo', 'coverageDepth', 'providerText', 'coverageText', 'title_id') as $field) {
            $value = $data[$_POST[$field]];
            if ($value != '') { 
/*
              $encoding = mb_detect_encoding($value);
              $encoding = detectUTF8($value);
              if ($encoding) {
*//*
                $resource->$field = $value;
*//*
              } else {
                if ($cli) echo "Warning: non-utf8 data ignored ($encoding $value)";
              }
            }
          }
*/
/*
          // TODO: Date handling has to be fixed.
          $resource->dateFirstIssueOnline = $data[$_POST['dateFirstIssueOnline']] ? $data[$_POST['dateFirstIssueOnline']] . "-1-1" : null;
          $resource->dateLastIssueOnline = $data[$_POST['dateLastIssueOnline']] ? $data[$_POST['dateLastIssueOnline']] . "-1-1" : null;
*/
if ($_POST['matchsubmit']) {
  $delimiter = $_POST['delimiter'];
  $deduping_config = explode(',', $config->settings->importISBNDedupingColumns); 
  $uploadfile = $_POST['uploadfile'];
}
if ($cli || $_POST['matchsubmit']) {
      $tool = new ImportTool();
    /*  $delimiter = $_POST['delimiter'];
      $deduping_config = explode(',', $config->settings->importISBNDedupingColumns);
      $uploadfile = $_POST['uploadfile'];*/
      if (($handle = fopen($uploadfile, "r")) !== FALSE) {
            $row = 0;
            while ($line = fgetcsv($handle, 0, $delimiter)) {

                  if ($row == 0) {
                        print "<h2>Settings</h2>";
                        print "<p>Importing and deduping isbnOrISSN on the following columns: ";
                        foreach ($line as $key => $value) {
                              if (in_array($value, $deduping_config)) {
                                    $deduping_columns[] = $key;
                                    print $value . " ";
                              }
                        }
                        print ".</p>";
                  } else {
                        $datas = array();
                        $identifiers = array();

                        $datas['titleText'] = $line[$_POST['titleText']];
                        $datas['title_id'] = $line[$_POST['title_id']];
                        $datas['resourceURL'] = $line[$_POST['resourceURL']];
                        $datas['resourceAltURL'] = $line[$_POST['resourceAltURL']];
                        if ($_POST['genericParent']) {
                            $datas['parentResource'] = $_POST['genericParent'];
                        } else {
                            $datas['parentResource'] = $line[$_POST['parentResource']];
                        }
                        $org = $_POST['organization'];
                        if (($line[$org] != null )&&($line[$org] != '')){
                              $datas['organization']=array($line[$_POST['role']] =>$line[$org]);
                        }

                        foreach ($deduping_columns as $column) {
                              array_push($identifiers, $line[$column]);
                        }
//print_r($identifiers);
                        $tool->addResource($datas, $identifiers);
                  }
              $row++;    
            }
      } 
    print "<h2>Results</h2>";
    print "<p>" . ($row- 1) . _(" rows have been processed. ").ImportTool::getNbInserted()._(" rows have been inserted.")."</p>";
    print "<p>". ImportTool::getNbParentInserted()._(" parents have been created. ").ImportTool::getNbParentAttached()._(" resources have been attached to an existing parent.")."</p>";
    print "<p>".ImportTool::getNbOrganizationsInserted()._(" organizations have been created");
    if (count(ImportTool::getArrayOrganizationsCreated()) > 0) print " (" . implode(',', ImportTool::getArrayOrganizationsCreated()) . ")";
    print ". ".ImportTool::getNbOrganizationsAttached()._(" resources have been attached to an existing organization")."</p>";
} else {
          
?>
<p><?php echo _("The first line of the CSV file must contain column names, and not data. These names will be used during the import process.");?></p>
<form enctype="multipart/form-data" action="import.php" method="post" id="importForm">
  <fieldset>
  <legend><?php echo _("File selection");?></legend>
  <label for="uploadFile"><?php echo _("CSV File");?></label>
  <input type="file" name="uploadFile" id="uploadFile" />
  </fieldset>
  <fieldset>
  <legend><?php echo _("Import options");?></legend>
  <label for="CSV delimiter"><?php echo _("CSV delimiter");?></label>
  <select name="delimiter">
    <option value=",">, <?php echo _("(comma)");?></option>
    <option value=";">; <?php echo _("(semicolon)");?></option>
    <option value="|">| <?php echo _("(pipe)");?></option>
    <option value="TAB"><?php echo _("tabulation");?></option>
  </select>
  </fieldset>
  <input type="submit" name="submit" value="<?php echo _("Upload");?>" />
</form>

<?php
}
?>
</div>
<?php
//print footer
include 'templates/footer.php';

  function tryToMatch($csv, $coral) {
    return ($csv == $coral || camelize($csv) == $coral || kbartMatching($csv) == $coral); 
  }

  function camelize($scored) {
    return lcfirst(
      implode(
        '',
        array_map(
          'ucfirst',
          array_map(
            'strtolower',
            explode(
              '_', $scored)))));
  }

  function kbartMatching($csv) {
    $kbartMatching = array('publication_title' => 'titleText',
    'title_url' => 'resourceURL',
    'publisher_name' => 'organization',
    'print_identifier' => 'isbnOrIssn',
    'online_identifier' => 'isbnOrIssn', 
    'coverage_notes' => 'coverageText');
    return (array_key_exists($csv, $kbartMatching) ? $kbartMatching[$csv] : null);
  }

function detectUTF8($string)
{
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
}
?>
