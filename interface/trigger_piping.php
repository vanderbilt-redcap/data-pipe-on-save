<?php
require_once APP_PATH_DOCROOT.'ProjectGeneral/header.php';
if (!defined("SUPER_USER")) {
    define("SUPER_USER",false);
}
$project_id = (int)$_REQUEST['pid'];
$module = new \Vanderbilt\DataPipeOnSaveExternalModule\DataPipeOnSaveExternalModule($project_id);
$project = new \Project($project_id);

$userRights = \UserRights::getUserDetails($project_id);
$recordList = \Records::getRecordList($project_id,(!SUPER_USER && $userRights['data_access_group'] != "" ? array($userRights['data_access_group']) : array()));
echo "<h2>Record List</h2>
<form method='post'>
<select name='submit_record'>
<option value=''></option>";
foreach ($recordList as $recordID) {
    echo "<option value='$recordID' ".($_POST['submit_record'] == $recordID ? 'selected' : '').">$recordID</option>";
}
echo "</select>
<input type='submit' value='Submit'/>
</form>";

if (isset($_POST['submit_record'])) {
    $record_id = db_real_escape_string($_POST['submit_record']);
    $eventForms = $project->eventsForms;
    $formList = $project->forms;

    $fields = array($project->table_pk);
    foreach ($formList as $formName => $fData) {
        $fields[] = $formName . "_complete";
    }

    $currentData = \Records::getData(
        array(
            'project_id' => $project_id, 'records' => $record_id, 'fields' => $fields, 'return_format' => 'array', 'includeRepeatingFields' => true
        )
    );
    $recordData = $currentData[$record_id];

    foreach ($eventForms as $event => $formList) {
        foreach ($formList as $formName) {
            if ($project->isRepeatingForm($event, $formName) && isset($recordData['repeat_instances'][$event][$formName])) {
                foreach ($recordData['repeat_instances'][$event][$formName] as $instance => $instanceData) {
                    if ($instanceData[$formName."_complete"] == "") continue;
                    $module->pipeDataToDestinationProjects($project_id, $record_id, $event, $formName, $instance);
                }
            } elseif ($project->isRepeatingEvent($event) && isset($recordData['repeat_instances'][$event][''])) {
                foreach ($recordData['repeat_instances'][$event][''] as $instance => $instanceData) {
                    if ($instanceData[$formName."_complete"] == "") continue;
                    $module->pipeDataToDestinationProjects($project_id, $record_id, $event, $formName, $instance);
                }
            } else {
                if (isset($recordData[$event])) {
                    if ($recordData[$event][$formName."_complete"] == "") continue;
                    $module->pipeDataToDestinationProjects($project_id, $record_id, $event, $formName);
                }
            }
        }
    }
}

