# Charuta - Skincare E-commerce Website

Charuta is a skincare e-commerce website developed using PHP and MySQL. It allows users to explore skincare products, create accounts, manage their shopping carts, and place orders through a simple interface.

## Project Overview

Charuta is designed to provide a simple online shopping experience for skincare products. Users can browse products, view product details, add products to their carts, and manage their orders.

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Local Server:** XAMPP
- **Code Editor:** Visual Studio Code
- **Version Control:** Git and GitHub

## Features

- User registration and login
- Browse skincare products
- View product details
- Add products to the shopping cart
- Update and remove cart items
- Checkout and place orders
- View order history
- Cancel eligible orders
- Store user and order information in MySQL

## Project Structure

```text
Charuta/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
├── includes/
│   └── db.php
├── index.php
├── products.php
├── product_details.php
├── login.php
├── register.php
├── cart.php
├── add_to_cart.php
├── remove_from_cart.php
├── checkout.php
├── place_order.php
├── orders.php
└── README.md
```

## Database

The project uses MySQL. The database is named `charuta` and includes the following main tables:

- `users` - Stores user account information.
- `products` - Stores skincare product information.
- `cart` - Stores users' cart items.
- `orders` - Stores order and delivery information.
- `order_items` - Stores the products included in each order.

## Installation and Setup

1. Install [XAMPP](https://www.apachefriends.org/).
2. Clone or download this repository into the XAMPP `htdocs` folder.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a database named `charuta`.
6. Import the project's database SQL file, if available.
7. Configure the database connection in `includes/db.php` using your local MySQL settings.
8. Open the project in your browser:

   `http://localhost/Charuta/`

## Project Repository

[Charuta on GitHub](https://github.com/myshamonsur/Charuta)

## Developer

**Fatima Jannat Abonty**  
GitHub: [@myshamonsur](https://github.com/myshamonsur)

## License

This project was developed for academic purposes.