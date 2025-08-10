<?php

function getUserIP() {
    return $_SERVER['REMOTE_ADDR'];
}

function isIpBlocked($ip, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $attempt = $stmt->fetch();
    
    if (!$attempt) {
        return false;
    }
    
    if ($attempt['blocked_until'] == null) {
        return false;
    }
    
    if (time() > strtotime($attempt['blocked_until'])) {
        $stmt = $pdo->prepare("UPDATE login_attempts SET blocked_until = ? WHERE ip_address = ?");        
        $stmt->execute([null, $ip]);
        return false;
    }
    
    return true;
}

function recordFailedAttempt($ip, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    if (!$stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempts, last_attempt) VALUES (?, 1, ?)");
        $stmt->execute([$ip, date("Y-m-d H:i:s")]);
    } else {
        $login_attempt = $stmt->fetch();
        $stmt = $pdo->prepare("UPDATE login_attempts SET attempts = ?, last_attempt = ? WHERE ip_address = ?");
        $stmt->execute([++$login_attempt['attempts'], date("Y-m-d H:i:s"), $ip]);

        if ($login_attempt['attempts'] > 4) {
            blockUser($ip, $pdo);
        }
    }
}

function resetAttempts($ip, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $stmt = $pdo->prepare("UPDATE login_attempts SET attempts = 0 WHERE ip_address = ?");
    $stmt->execute([$ip]);
}

function blockUser($ip, $pdo) {
    $stmt = $pdo->prepare("UPDATE login_attempts SET blocked_until = ? WHERE ip_address = ?");
    $stmt->execute([date("Y-m-d H:i:s", time() + (30 * 60)), $ip]);
}
