<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>

<h1><?php echo "Test (from PHP)"; ?></h1>

<h2 id="js-output"></h2>

<script>

    document.getElementById("js-output").innerText = "Test (from JavaScript)";
</script>

</body>
</html>
