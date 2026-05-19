<?php
session_start();
include "connectdb.php";

if (isset($_SESSION["user_id"])) {
    header("Location: /index.php");
    exit();
}

$email  = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        $sql = "SELECT user_id, full_name, role, password 
                FROM users 
                WHERE email = ? 
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);

        if (!$stmt->execute()) {
            die("SQL execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();

            $storedPassword = $row["password"];
            $passwordInfo   = password_get_info($storedPassword);
            $passwordIsHash = $passwordInfo["algo"] !== 0;

            if ($passwordIsHash) {
                $passwordIsCorrect = password_verify($password, $storedPassword);
            } else {
                // Legacy support for users imported before passwords were hashed.
                $passwordIsCorrect = hash_equals($storedPassword, $password);
            }

            if ($passwordIsCorrect) {
                if (!$passwordIsHash || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");

                    if ($updateStmt) {
                        $updateStmt->bind_param("si", $newHash, $row["user_id"]);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }

                session_regenerate_id(true);

                $_SESSION["user_id"]       = $row["user_id"];
                $_SESSION["full_name"]     = $row["full_name"];
                $_SESSION["role"]          = $row["role"];
                $_SESSION["last_activity"] = time();

                header("Location: /index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | SALCC Brooder</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<div class="login-bg">
    <div class="login-card">
        <img src="/images/salcc_black.png" style="width:100%; margin-bottom:25px;">

        <?php if (!empty($errors)): ?>
            <div class="notice error" style="text-align:left; margin-bottom:15px;">
                <?php foreach ($errors as $e): ?>
                    <p style="margin:4px 0; font-size:0.9rem;">
                        <?php echo htmlspecialchars($e); ?>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["timeout"])): ?>
            <div class="notice" style="background:#fff8e1; border-left-color:#f9a825; margin-bottom:15px;">
                <p style="color:#f57f17; margin:0; font-size:0.9rem;">
                    Session expired. Please log in again.
                </p>
            </div>
        <?php endif; ?>

        <p style="color:#666; font-size:0.9rem; margin-bottom:20px;">
            Sign in with your SALCC credentials.<br>
            Contact your administrator if you don't have an account.
        </p>

        <form action="login.php" method="post">
            <input 
                type="email" 
                name="email" 
                placeholder="Email address" 
                class="input-box"
                value="<?php echo htmlspecialchars($email); ?>" 
                required
            >

            <input 
                type="password" 
                name="password" 
                placeholder="Password" 
                class="input-box" 
                required
            >

            <button type="submit" class="btn-block">Log in</button>
        </form>
    </div>
</div>
</body>
</html>
