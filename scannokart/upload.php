<?php
header('Content-Type: application/json');

// 1. Define the destination
$target_dir = "/var/www/html/uploads/";
$file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

// 2. Security: Rename the file so hackers can't guess paths
$new_filename = time() . "_" . bin2hex(random_bytes(4)) . "." . $file_ext;
$target_file = $target_dir . $new_filename;

// 3. Move the file
if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {

    $python_bin = "OCR/openocr/bin/python3";
    $openocr_bin = "OCR/openocr/bin/openocr";
    $clean_script = "/var/www/html/clean.py";

    $command = "export HOME=/tmp && /OCR/openocr/bin/openocr --task ocr --input_path $target_file 2>/dev/null";
    $ocr_result = shell_exec($command);

    echo json_encode([
        "status" => "success",
        "ocr_data" => $ocr_result
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Upload failed. Check folder permissions (733)."
    ]);
}
?>
