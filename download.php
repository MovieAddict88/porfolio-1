<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Document</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f0f0; }
        .download-box { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        h1 { color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 0.75rem; background-color: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background-color: #218838; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .error { background-color: #f8d7da; color: #721c24; }
        .success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="download-box">
        <h1>Secure Download</h1>
        <p>Please enter the password you were given to download the document.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="verify-download.php" method="POST">
            <div class="form-group">
                <label for="password_code">Password Code</label>
                <input type="text" id="password_code" name="password_code" required>
            </div>
            <button type="submit">Download File</button>
        </form>
    </div>
</body>
</html>