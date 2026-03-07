<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - ResQLink</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
<div class="col-md-5 mx-auto">

<div class="card shadow p-4">

<h3 class="text-center mb-4">Login</h3>

<form action="login_action.php" method="POST">

<div class="mb-3">
<label>Email or Phone</label>
<input type="text" name="login_input" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<button type="submit" class="btn btn-danger w-100">Login</button>

</form>

<p class="text-center mt-3">
Don't have an account? 
<a href="register.php">Register</a>
</p>

</div>
</div>
</div>

</body>
</html>