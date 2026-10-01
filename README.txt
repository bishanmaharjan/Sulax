SULAX SHOES COLLECTION - BCA E-COMMERCE PROJECT
================================================

TECHNOLOGY
- PHP 8+
- MySQL / MariaDB
- HTML5, CSS3, JavaScript
- XAMPP / Apache

INSTALLATION
1. Copy the sulax-shoes folder into C:\xampp\htdocs\
2. Start Apache and MySQL from XAMPP.
3. Open phpMyAdmin.
4. Import config/database.sql into MySQL.
   If you already have the old database, import database/migration_v2.sql instead.
5. Open http://localhost/sulax-shoes/

ADMIN LOGIN
Open http://localhost/sulax-shoes/admin/login.php (not the customer login page).
Email: admin@sulax.com
Password: admin123
Change the password before real deployment.

IMPORTANT
- Put real product images inside uploads/.
- Supported image types: JPG, JPEG, PNG, WEBP. Maximum 3 MB.
- The current Online Payment option is a DEMO label; no real payment gateway is connected.
- Customer reviews can be submitted after an order reaches Delivered status.
- Customers can cancel Pending/Processing orders; stock is restored automatically.
- Do not expose database.sql publicly on a production server. Apache rules are included.

MAIN FEATURES
- Product search, category filter and sorting
- Product details, size selection and quantity control
- Local browser shopping cart
- Login and registration
- Checkout and Cash on Delivery
- Stock-safe order placement
- Customer account, orders and profile
- Wishlist
- Verified customer reviews/ratings
- Customer order cancellation with stock restoration
- Contact form and admin message management
- Admin product/order/customer management
- Admin dashboard and low-stock notification
- Responsive layout

FINAL TEST CHECKLIST
[ ] Register a customer
[ ] Login
[ ] Add a product to wishlist
[ ] Select size and add to cart
[ ] Change cart quantity/remove item
[ ] Checkout and place COD order
[ ] Confirm stock decreases
[ ] Confirm order appears in My Orders
[ ] Login to admin
[ ] Change order status
[ ] Cancel an order and confirm stock restoration
[ ] Add/edit product and image
[ ] Submit a contact message and read it in Admin > Messages
[ ] After Delivered status, submit a product review
