<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload</title>
</head>
<body>

    <h2>Upload Excel File</h2>
    <form action="bulk_upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="excel_file" required>
        <button type="submit" name="upload">Upload</button>
    </form>

</body>
</html>