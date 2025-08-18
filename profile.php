<?php
session_start();

include "includes/auth.php";
include "includes/User.php";
include "includes/UserAddress.php";
include "includes/csrf.php";
include "includes/db.php";
include "includes/messages.php";
include "includes/Cart.php";

requireLogin();
$csrf_token = generateCSRFToken();

$userObj = new User($pdo);
$userAddress = new UserAddress($pdo);
$userData = $userObj->getUserById($_SESSION['user_id']);

$activeTab = $_GET['tab'] ?? 'profile';

if ($activeTab === 'addresses') {
    $userAddresses = $userAddress->getUserAddresses($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>My Account - Profile</title>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
                    <p class="user-email"><?php echo htmlspecialchars($userData['email'] ?? 'No email set'); ?></p>
                </div>
                
                <nav class="profile-nav">
                    <a href="?tab=profile" class="<?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person"></i> Personal Info
                    </a>
                    <a href="?tab=orders" class="<?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                        <i class="bi bi-box"></i> Order History
                    </a>
                    <a href="?tab=addresses" class="<?php echo $activeTab === 'addresses' ? 'active' : ''; ?>">
                        <i class="bi bi-geo-alt"></i> Addresses
                    </a>
                    <a href="?tab=security" class="<?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                        <i class="bi bi-shield-lock"></i> Security
                    </a>
                </nav>
            </div>
            
            <div class="profile-content">
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        <?php echo getErrorMessage($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <i class="bi bi-check-circle"></i>
                        <?php echo getSuccessMessage($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'profile'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-person"></i> Personal Information</h1>
                        
                        <form action="src/update_profile.php" method="POST" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <i class="bi bi-check-lg"></i> Save Changes
                            </button>
                        </form>
                    </div>

                <?php elseif ($activeTab === 'orders'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-box"></i> Order History</h1>
                        
                        <div class="orders-list">
                            <div class="no-orders">
                                <i class="bi bi-box"></i>
                                <h3>No orders yet</h3>
                                <p>Start shopping to see your order history here!</p>
                                <a href="products" class="shop-btn">Browse Products</a>
                            </div>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'addresses'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-geo-alt"></i> Shipping Addresses</h1>
                        
                        <div class="addresses-section">
                            <div class="add-address-card">
                                <i class="bi bi-plus-circle"></i>
                                <h3>Add New Address</h3>
                                <p>Add a shipping address for faster checkout</p>
                                <button class="add-btn" onclick="showAddressForm()">Add Address</button>
                            </div>
                            
                            <?php if (!empty($userAddresses)): ?>
                                <div class="addresses-grid">
                                    <?php foreach ($userAddresses as $address): ?>
                                        <div class="address-card">
                                            <?php if ($address['is_default']): ?>
                                                <div class="default-badge">Default</div>
                                            <?php endif; ?>
                                            <h4><?= htmlspecialchars($address['title']) ?></h4>
                                            <div class="address-details">
                                                <p><strong><?= htmlspecialchars($address['full_name']) ?></strong></p>
                                                <p><?= htmlspecialchars($address['street_address']) ?></p>
                                                <p><?= htmlspecialchars($address['city']) ?></p>
                                                <p><i class="bi bi-telephone"></i> <?= htmlspecialchars($address['phone']) ?></p>
                                            </div>
                                            <div class="address-actions">
                                                <?php if (!$address['is_default']): ?>
                                                    <button onclick="setDefaultAddress(<?= $address['id'] ?>)" class="btn-outline">
                                                        <i class="bi bi-star"></i> Set Default
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="editAddress(<?= $address['id'] ?>)" class="btn-outline">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button onclick="deleteAddress(<?= $address['id'] ?>)" class="btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-addresses">
                                    <i class="bi bi-geo-alt" style="font-size: 3rem; color: #64748b; margin-bottom: 1rem;"></i>
                                    <p>No saved addresses yet. Add your first address above!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="addressModal" class="address-modal" style="display: none;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 id="modalTitle"><i class="bi bi-geo-alt"></i> Add New Address</h3>
                                    <span class="close" onclick="hideAddressForm()">&times;</span>
                                </div>
                                <form id="addressForm" action="src/manage_address.php" method="POST" onsubmit="return handleFormSubmit(event)">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="add" id="formAction">
                                    <input type="hidden" name="address_id" value="" id="addressId">
                                    
                                    <div class="form-group">
                                        <label for="title">Address Title *</label>
                                        <input type="text" name="title" id="title" required placeholder="Home, Work, etc." maxlength="30">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="full_name">Full Name *</label>
                                        <input type="text" name="full_name" id="full_name" required maxlength="50">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="street_address">Street Address *</label>
                                        <input type="text" name="street_address" id="street_address" required maxlength="200">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="city">City *</label>
                                        <input type="text" name="city" id="city" required maxlength="50">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Phone Number *</label>
                                        <input type="tel" name="phone" id="phone" required maxlength="20">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_default" id="is_default" value="1">
                                            <span class="checkmark"></span>
                                            Set as default address
                                        </label>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="button" onclick="hideAddressForm()" class="btn-secondary">Cancel</button>
                                        <button type="submit" class="btn-primary" id="submitBtn">Add Address</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'security'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-shield-lock"></i> Security Settings</h1>
                        
                        <div class="security-section">
                            <div class="security-card">
                                <h3><i class="bi bi-key"></i> Change Password</h3>
                                <p>Keep your account secure with a strong password</p>
                                
                                <form action="src/update_password.php" method="POST" class="password-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" class="submit-btn">
                                        <i class="bi bi-shield-check"></i> Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function validateName(name) {
            const nameRegex = /^[a-zA-Z\s\u0621-\u064A]{2,50}$/;
            return nameRegex.test(name.trim());
        }

        function validatePhone(phone) {
            const phoneRegex = /^[+]?[\d\s\-\(\)]{7,20}$/;
            return phoneRegex.test(phone.trim());
        }

        function validateAddress(address) {
            return address.trim().length >= 5 && address.trim().length <= 200;
        }

        function validateCity(city) {
            const cityRegex = /^[a-zA-Z\s\u0621-\u064A]{2,50}$/;
            return cityRegex.test(city.trim());
        }

        function validateTitle(title) {
            return title.trim().length >= 2 && title.trim().length <= 30;
        }

        function showError(field, message) {
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) existingError.remove();
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
            field.style.borderColor = '#ef4444';
        }

        function clearError(field) {
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) existingError.remove();
            field.style.borderColor = '';
        }

        function validateAddressForm() {
            let isValid = true;
            
            const title = document.getElementById('title');
            const fullName = document.getElementById('full_name');
            const streetAddress = document.getElementById('street_address');
            const city = document.getElementById('city');
            const phone = document.getElementById('phone');

            clearError(title);
            clearError(fullName);
            clearError(streetAddress);
            clearError(city);
            clearError(phone);

            if (!validateTitle(title.value)) {
                showError(title, 'Title must be 2-30 characters long');
                isValid = false;
            }

            if (!validateName(fullName.value)) {
                showError(fullName, 'Full name must be 2-50 characters, letters only');
                isValid = false;
            }

            if (!validateAddress(streetAddress.value)) {
                showError(streetAddress, 'Address must be 5-200 characters long');
                isValid = false;
            }

            if (!validateCity(city.value)) {
                showError(city, 'City must be 2-50 characters, letters only');
                isValid = false;
            }

            if (!validatePhone(phone.value)) {
                showError(phone, 'Invalid phone number format');
                isValid = false;
            }

            return isValid;
        }

        function showAddressForm() {
            document.getElementById('addressModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-geo-alt"></i> Add New Address';
            document.getElementById('formAction').value = 'add';
            document.getElementById('submitBtn').textContent = 'Add Address';
            document.getElementById('addressForm').reset();
            document.getElementById('addressId').value = '';
            document.body.style.overflow = 'hidden';
        }

        function hideAddressForm() {
            document.getElementById('addressModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function editAddress(addressId) {
            <?php if (!empty($userAddresses)): ?>
                const addresses = <?= json_encode($userAddresses) ?>;
                const address = addresses.find(addr => addr.id == addressId);
                
                if (address) {
                    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-geo-alt"></i> Edit Address';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('submitBtn').textContent = 'Update Address';
                    document.getElementById('addressId').value = address.id;
                    document.getElementById('title').value = address.title;
                    document.getElementById('full_name').value = address.full_name;
                    document.getElementById('street_address').value = address.street_address;
                    document.getElementById('city').value = address.city;
                    document.getElementById('phone').value = address.phone;
                    document.getElementById('is_default').checked = address.is_default == 1;
                    
                    document.getElementById('addressModal').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            <?php endif; ?>
        }

        function deleteAddress(addressId) {
            if (confirm('Are you sure you want to delete this address?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'src/manage_address.php';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= $csrf_token ?>';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'address_id';
                idInput.value = addressId;
                
                form.appendChild(csrfInput);
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function setDefaultAddress(addressId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'src/manage_address.php';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= $csrf_token ?>';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'set_default';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'address_id';
            idInput.value = addressId;
            
            form.appendChild(csrfInput);
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            
            if (!validateAddressForm()) {
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            setTimeout(() => {
                document.getElementById('addressForm').submit();
            }, 100);
            
            return true;
        }

        function addRealTimeValidation() {
            const fields = ['title', 'full_name', 'street_address', 'city', 'phone'];
            
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('blur', function() {
                        const value = this.value;
                        
                        switch(fieldId) {
                            case 'title':
                                if (value && !validateTitle(value)) {
                                    showError(this, 'Title must be 2-30 characters long');
                                } else {
                                    clearError(this);
                                }
                                break;
                            case 'full_name':
                                if (value && !validateName(value)) {
                                    showError(this, 'Full name must be 2-50 characters, letters only');
                                } else {
                                    clearError(this);
                                }
                                break;
                            case 'street_address':
                                if (value && !validateAddress(value)) {
                                    showError(this, 'Address must be 5-200 characters long');
                                } else {
                                    clearError(this);
                                }
                                break;
                            case 'city':
                                if (value && !validateCity(value)) {
                                    showError(this, 'City must be 2-50 characters, letters only');
                                } else {
                                    clearError(this);
                                }
                                break;
                            case 'phone':
                                if (value && !validatePhone(value)) {
                                    showError(this, 'Invalid phone number format');
                                } else {
                                    clearError(this);
                                }
                                break;
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            addRealTimeValidation();
        });

        window.onclick = function(event) {
            const modal = document.getElementById('addressModal');
            if (event.target === modal) {
                hideAddressForm();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideAddressForm();
            }
        });
    </script>
</body>
</html>
