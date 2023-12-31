<?php

if (!is_array($argv) || sizeof($argv) < 4) {
    die("\nPlease pass the file path, data path,  the thread_id and the output path as param\n");
}

if (!file_exists($argv[1] . "messages.ndjson")) {
    die("\nError : Can't find " . $argv[1] . "messages.ndjson" . "\n");
}

foreach (file($argv[1] . "messages.ndjson") as $line) {
    $line = json_decode($line, true);
    break;
}
if (!is_array($line) || !sizeof($line)) {
    die("\nError : Can't read " . $argv[1] . "messages.ndjson\n");
}
$threads = [];
if (substr($argv[2], 0, 7) == "thread-") {
    $threads[] = (int)substr($argv[2], 7);
} else {
    foreach (file($argv[1] . "messages.ndjson") as $line) {
        $line = json_decode($line, true);
        if (
            array_key_exists("address", $line)
            && strpos($line["address"], $argv[2]) !== false
        ) {
            $threads[] = $line["thread_id"];
        }
    }
    $threads = array_unique($threads);
}

if (sizeof($threads) == 0) {
    die("\nError : Can't find a thread with this number : " . $argv[2] . "\n");
}
if (sizeof($threads) > 1) {
    echo "\nError : " . sizeof($threads) . " threads with this number. Try using this instead of the phone number :";
    foreach ($threads as $thread_id) {
        echo "\n - \"thread-" . $thread_id . "\"";
    }
    echo "\n";
    die();
}

$l = [];
foreach (file($argv[1] . "messages.ndjson") as $line) {
    $line = json_decode($line, true);
    if (in_array($line["thread_id"], $threads)) {
        if (!array_key_exists("date", $line)) {
            $date_field = "date_sent";
        } else {
            $date_field = "date";
        }
        $date = $line[$date_field];
        if ($date < 1000000000000) {
            $date *= 1000;
        }
        $date_in_range = true;
        if (array_key_exists(4, $argv)) {
            $date_from = date("U", strtotime($argv[4])) * 1000;
            if ($date < $date_from) {
                $date_in_range = false;
            }
        }
        if (array_key_exists(5, $argv)) {
            $date_to = date("U", strtotime($argv[5])) * 1000;
            if ($date > $date_to) {
                $date_in_range = false;
            }
        }
        if ($date_in_range) {
            $l[$date] = $line;
        }
    }
}
ksort($l);

$export_dir = dirname($argv[3]);

@mkdir($export_dir . "/export_data");
$e = "<table>";
foreach ($l as $date => $line) {
    $e .= "<tr><td colspan=3 style='background-color:#CCC;'></td></tr>";
    if (!array_key_exists("type", $line) || $line["type"] == 1) {
        $e .= "<tr><td></td>";
    } else {
        $e .= "<tr style='color:#006699;font-weight:bold;'><td style='background-color:#006699;font-weight:bold;'>&nbsp;</td>";
    }


    $e .= "<td style='white-space:nowrap;padding:10px;padding-right:20px;'>";
    $e .= date("Y-m-d H:i:s", (int)$date / 1000);
    $e .= "</td>";
    $e .= "<td style='padding:10px;'>";
    if (array_key_exists("__parts", $line)) {
        foreach ($line["__parts"] as $part) {
            if (array_key_exists("text", $part)) {
                $e .= strip_tags($part["text"]);
            }
            if (
                array_key_exists("_data", $part)
                && strpos($part["_data"], "/app_parts/") !== false
            ) {
                $data_path = $part["_data"];
                $data_path_array = explode("/app_parts/", $data_path);
                $data_path = $argv[1] . "/" . $data_path_array[1];
                if (file_exists($data_path)) {
                    copy($data_path, $export_dir . "/export_data/" . $data_path_array[1]);
                    $e .= '<img src="' . $export_dir . "/export_data/" . $data_path_array[1] . '" width=300><br>';
                }
            }
        }
    }
    if (array_key_exists("body", $line)) {
        $e .= $line["body"];
    }


    $e .= "</td>";
    $e .= "</tr>";
}
$e .= "<table>";


file_put_contents($argv[3], $e);
echo "\nExport done to " . $argv[3] . ".\n" . sizeof($l) . " lines\n";
