# TODO: Add User Password Field to Admin Form

## Tasks
- [x] Add password input field to admin/cadastrar_usuario.php form
- [x] Update PHP processing in admin/cadastrar_usuario.php to handle password validation and hashing
- [x] Test the updated form to ensure password is properly saved

## Information Gathered
- Database schema already includes `senha_usuario` VARCHAR(255) NOT NULL
- API (api/usuarios.php) supports password hashing with PASSWORD_BCRYPT
- Public registration form (registro.html) already has password field
- Admin form (admin/cadastrar_usuario.php) is missing password field, preventing admins from setting user passwords

## Plan
1. Add a password input field to the form in admin/cadastrar_usuario.php
2. Update the PHP validation and insertion logic to include password hashing
3. Ensure password is required and meets minimum length (e.g., 6 characters)

## Summary
Successfully added the user password field to the admin user creation form. The form now includes:
- Password input field with required validation
- Minimum length validation (6 characters)
- Password hashing using PASSWORD_BCRYPT before database insertion
- Updated SQL query to include senha_usuario column

The admin can now set user passwords when creating new users through the admin panel.
