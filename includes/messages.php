<?php

function getErrorMessage($error) {
	$error_messages = [
		"invalid-credentials" => "Invalid username or password",
		"empty-fields" => "Please fill in all fields",
		"missing-data" => "Missing required data",
		"csrf-token-invalid" => "Security token invalid. Please try again.",
		"too-many-requests" => "Too many failed attempts. Please try again later.",
		"user-exists" => "Username already exists",
		"password-mismatch" => "Passwords do not match",
		"invalid-password" => "Invalid username or password",
		"invalid-username" => "Invalid username format",
		"session-timed-out" => "Your session has expired. Please login again.",
		"please-login" => "Please login to access this page",
		"username-already-exists" => "Username already exists. Please choose another.",
		"incorrect-password" => "Current password is incorrect.",
		"incorrect-current-password" => "Current password is incorrect.",
		"password-too-short" => "New password must be at least 8 characters long.",
		"passwords-dont-match" => "New and confirm passwords do not match.",
		"same-password" => "New password must be different from current password.",
		"update-failed" => "Update failed. Please try again."
	];

	return isset($error_messages[$error]) ? $error_messages[$error] : "An error occurred. Please try again.";
}

function getSuccessMessage($success) {
	$success_messages = [
		"registration-complete" => "Account created successfully! You can now login.",
		"username-updated" => "Username updated successfully!",
		"password-changed" => "Password changed successfully!",
		"logout-success" => "You have been logged out successfully."
	];

	return isset($success_messages[$success]) ? $success_messages[$success] : "Success!";
}