<?php

function getErrorMessage($error) {
	$error_messages = [
		"invalid-credentials" => "Invalid username or password",
		"empty-fields" => "Please fill in all fields",
		"empty-required-fields" => "Please fill in all required fields (username and email)",
		"missing_fields" => "Please fill in all required fields.",
		"missing-data" => "Missing required data",
		"csrf-token-invalid" => "Security token invalid. Please try again.",
		"too-many-requests" => "Too many failed attempts. Please try again later.",
		"user-exists" => "Username already exists",
		"email-exists" => "Email already exists",
		"password-mismatch" => "Passwords do not match",
		"invalid-password" => "Invalid username or password",
		"invalid-username" => "Invalid username format",
		"invalid-email" => "Please enter a valid email address",
		"session-timed-out" => "Your session has expired. Please login again.",
		"please-login" => "Please login to access this page",
		"username-already-exists" => "Username already exists. Please choose another.",
		"incorrect-password" => "Current password is incorrect.",
		"incorrect-current-password" => "Current password is incorrect.",
		"password-too-short" => "New password must be at least 8 characters long.",
		"passwords-dont-match" => "New and confirm passwords do not match.",
		"same-password" => "New password must be different from current password.",
		"update-failed" => "Update failed. Please try again.",
		"invalid-price" => "Price must be greater than 0.",
		"invalid-stock" => "Stock quantity cannot be negative.",
		"invalid-category" => "Please select a valid category.",
		"invalid-name" => "Product name must be between 2 and 100 characters.",
		"invalid-image-url" => "Please provide a valid image URL ending in .jpg, .png, .gif, or .webp.",
		"image-upload-failed" => "Image upload failed. Please try again or use a URL instead.",
		"image-processing-unavailable" => "Image processing not available. Please use image URLs instead or enable GD extension.",
		"database-error" => "Database error. Please try again.",
		"invalid_token" => "Security token invalid. Please try again.",
		"invalid_product" => "Invalid product selected.",
		"product_not_found" => "Product not found.",
		"delete_failed" => "Failed to delete product. Please try again.",
		"update_failed" => "Failed to update product. Please try again.",
		"demo_mode_disabled" => "Demo mode: Product editing is disabled for this account to maintain the demo environment.",
		"demo_mode_edit_disabled" => "Demo mode: Product editing is disabled for this account. View products from the admin panel instead.",
		"settings_error" => "Settings update failed. Please try again."
	];

	return isset($error_messages[$error]) ? $error_messages[$error] : "An error occurred. Please try again.";
}

function getSuccessMessage($success) {
	$success_messages = [
		"registration-complete" => "Account created successfully! You can now login.",
		"username-updated" => "Username updated successfully!",
		"password-changed" => "Password changed successfully!",
		"profile-updated" => "Profile updated successfully!",
		"logout-success" => "You have been logged out successfully.",
		"logged-out" => "You have been logged out successfully.",
		"product-added" => "Product added successfully!",
		"product-updated" => "Product updated successfully!",
		"product_deleted" => "Product deleted successfully!",
		"settings_updated" => "Store settings updated successfully!"
	];

	return isset($success_messages[$success]) ? $success_messages[$success] : "Success!";
}
