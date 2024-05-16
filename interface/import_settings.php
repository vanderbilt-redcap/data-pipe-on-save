<?php
require_once APP_PATH_DOCROOT.'ProjectGeneral/header.php';

$project_id = (int)$_REQUEST['pid'];
$module = new \Vanderbilt\DataPipeOnSaveExternalModule\DataPipeOnSaveExternalModule($project_id);

$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

if (isset($_POST['csv_submit']) && isset($_FILES['csv_file']) && $_FILES['csv_file']['name'] != "") {
    $fileData = $_FILES['csv_file'];
    if (in_array($fileData['type'],$csvMimes)) {
        //$fileContents = file_get_contents($fileData['tmp_name']);
        $file = fopen($fileData['tmp_name'], 'r');
        $settings = array();
        while (($line = fgetcsv($file)) !== FALSE) {
            //$line is an array of the csv elements
            $settings[] = $line;
        }
        fclose($file);
        $projectSettings = $module->framework->getProjectSettings($project_id);
        foreach ($projectSettings as $settingName => $settingArray) {
            if ($settingName == 'enabled' || $settingName == 'version') continue;
            $module->framework->removeProjectSetting($settingName,$project_id);
        }
        $module->importSettingsFromCSV($settings,true);
    }
}
?>

<form method="post" enctype="multipart/form-data">
    <label for="csv_file">Settings CSV File to Import</label>
    <input type="file" name="csv_file" />
    <input type="submit" value="Import Settings" name="csv_submit" />
</form>
