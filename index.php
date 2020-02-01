<?php require_once (__DIR__ . '/crest.php');

$arSelect = ['ID', 'TITLE', 'CREATED_BY', 'RESPONSIBLE_ID', 'GROUP_ID', 'PARENT_ID', 'UF_AUTO_787777913516', 'UF_AUTO_557402252838'];
$updateFields = [];

function getTaskInfo($taskId, $select = []) {
    $taskInfo = CRest::call('tasks.task.get', [
                'taskId' => $taskId,
                'select' => $select
    ]);
    if (!isset($taskInfo['result']['task'])) {
        $result = false;
    } else {
        $result = $taskInfo['result']['task'];
    }
    return $result;
}

function getTaskList($filter = [], $select = [], $order = []) {
    $taskList = CRest::call('tasks.task.list', [
                'filter' => $filter,
                'select' => $select,
                'order' => $order
    ]);
    if (empty($taskList['result']['tasks'])) {
        return false;
    } else {
        return $taskList['result']['tasks'];
    }
}

function updateTask($taskId, $fields) {
    $update = CRest::call('tasks.task.update', [
                'taskId' => $taskId,
                'fields' => $fields
    ]);
    if (is_array($update['result']['task'])) {
        $result = true;
    } else if (isset($update['error'])) {
        $result = false;
    }
    return $result;
}

if (!empty($_REQUEST['event'])) {
    
    $taskID = $_REQUEST['data']['FIELDS_AFTER']['ID'];
    $Task = getTaskInfo($taskID, $arSelect);

    switch ($_REQUEST['event']) {
        case 'ONTASKADD':
            break;
        case 'ONTASKUPDATE':
            if (!empty($Task['parentId'])) {
                
                $parentTaskId = $Task['parentId'];
                $parentTask = getTaskInfo($parentTaskId, $arSelect);
                $subTasks = getTaskList(['PARENT_ID' => $parentTaskId], $arSelect);

                $numberOneSum = 0;
                $numberTwoSum = 0;

                if (is_array($subTasks)) {
                    foreach ($subTasks as $sTask) {
                        if (!empty($sTask['ufAuto557402252838'])) {
                            $numberOneSum += $sTask['ufAuto557402252838'];
                        }
                        if (!empty($sTask['ufAuto787777913516'])) {
                            $numberTwoSum += $sTask['ufAuto787777913516'];
                        }

                        $numberOneSum > 0 ? $updateFields['UF_AUTO_557402252838'] = $numberOneSum : null;
                        $numberTwoSum > 0 ? $updateFields['UF_AUTO_787777913516'] = $numberTwoSum : null;
                    }

                    if (
                            intval($parentTask['ufAuto557402252838']) !== intval($numberOneSum) || 
                            intval($parentTask['ufAuto787777913516']) !== intval($numberTwoSum)
                        ) {
                        
                        $updateParentTask = updateTask($parentTaskId, $updateFields);
                        
                    }
                }
            }
            break;
    }
}