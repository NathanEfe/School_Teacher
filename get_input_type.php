<?php
$type = $_POST['type'] ?? '';

$response = [
    "showFile" => false,
    "showUrl"  => false,
    "placeholder" => ""
];

if ($type === "file") {
    $response["showFile"] = true;
} elseif ($type === "video") {
    $response["showUrl"] = true;
    $response["placeholder"] = "Paste video link (e.g. YouTube, Vimeo)";
} elseif ($type === "link") {
    $response["showUrl"] = true;
    $response["placeholder"] = "Paste website/resource link";
} elseif ($type === "other") {
    $response["showUrl"] = true;
    $response["placeholder"] = "Paste resource URL or description link";
}

header('Content-Type: application/json');
echo json_encode($response);
