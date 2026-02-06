# RONIN-SHOP-SF
A professional online store application built with **Symfony 5.4** and **PHP 7.4**.  
This project demonstrates full-stack web development skills, including backend logic, database integration, templating with Twig, and responsive frontend design.  

It allows users to:

- Browse and search products
- Manage shopping carts and checkout
- Handle user accounts and roles
- Administer products, categories, and orders

## Table of Contents
- [About the Project](#about-the-project)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [License](#license)

## About the Project
**RONIN-SHOP-SF** is a professional online store application built with **Symfony 5.4 and PHP 7.4**.  
The project showcases a full-featured Ecommerce platform suitable for development demonstration purposes.

Key features include:

- User account management (registration, login, profile, admin roles)
- Product and category management
- Catalog browsing, search, filtering, and sorting
- Shopping cart with database persistence
- Checkout and order management
- Optional features: email notifications, REST API endpoints, and responsive UX/UI

This project demonstrates **full-stack web development skills**, including backend logic, database integration, templating with Twig, and frontend responsiveness.

## Features
The Ecommerce application implements the following main features:

### User Management
- [x] Create account
- [x] Login
- [x] Logout
- [x] Edit profile
- [x] Role management (admin)

### Product Management
- [x] Create product
- [x] Edit product
- [x] Delete product
- [x] Upload product images
- [x] Display price, stock, and description

### Category Management
- [x] Create category
- [x] Edit category
- [x] Delete category
- [x] Filter products by category

### Catalog and Search
- [x] List products
- [x] Product search
- [x] Filter by price
- [x] Sorting (most recent, popular)

### Shopping Cart
- [x] Add product to cart
- [x] Remove product from cart
- [x] Change quantity
- [x] Calculate total
- [x] Persistence of cart (database)

### Checkout and Orders
- [x] Address form
- [x] Create order
- [x] Create order items
- [x] Order summary before confirmation
- [x] Order confirmation
- [x] Update stock (optional)

### Order Management (Admin)
- [x] List orders
- [x] View order details
- [x] Change order status

### Emails (Extra)
- [x] Send order confirmation email
- [x] Password recovery

### REST API (Extra)
- [ ] Product list endpoint
- [ ] Product details endpoint
- [ ] Product search endpoint

### UX/UI (Extra)
- [ ] Breadcrumbs
- [ ] Success/error alerts
- [ ] Responsiveness (images, layout)

## Technologies Used
This project uses the following technologies and tools:

- **PHP 7.4** – Server-side scripting language powering the application.
- **Symfony 5.4** – Framework used for building the MVC architecture and managing routes, controllers, and templates.
- **Twig** – Template engine for rendering dynamic HTML views.
- **MySQL** – Relational database for storing products, orders, and users.
- **Doctrine ORM** – Object-Relational Mapper for database management.
- **Bootstrap 5** – Frontend framework for responsive design.
- **HTML5 & CSS3** – Markup and styling of the web pages.
- **JavaScript (Vanilla / Optional Vue.js)** – Client-side interactions and dynamic content.
- **Composer** – Dependency management for PHP packages.
- **Git & GitHub** – Version control and collaboration.

## Installation

1. **Clone the repository**
```bash
git clone https://github.com/username/ronin-shop-sf.git
cd ecommerce-symfony
```
2. Install PHP dependencies
```bash
composer install
```
3. Set up environment variables
```bash
cp .env .env.local
# Edit .env.local with your database credentials
```
4. Create and migrate the database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
5. Start the Symfony server
```bash
symfony server:start
```

## Usage
Once the Symfony server is running, open your browser at: http://localhost:8000

You can now:
- Browse products
- Add items to the cart
- Proceed to checkout
- Explore the admin panel

## Project Structure
```text
ronin-shop-sf/
├── bin/ # Symfony console commands
├── config/ # Application configuration (routes, services, packages)
├── migrations/ # Doctrine database migrations
├── public/ # Public web directory (document root)
│ └── index.php # Front controller
├── src/ # PHP source code (Controllers, Entities, Services)
│ ├── Command/
│ ├── Controller/
│ ├── Entity/
│ ├── Form/
│ ├── Repository/
│ └── Service/
├── templates/ # Twig templates for rendering views
├── tests/
├── translations/
├── fixtures/ # Optional: database fixtures for testing
├── var/ # Cache, logs, sessions
├── vendor/ # Composer dependencies
├── composer.json # PHP dependencies
└── README.md # Project documentation
...
```

## Contributing
Contributions are welcome! To contribute to this project:

1. Fork the repository.
2. Create a new branch for your feature or fix:
```bash
git checkout -b feature/new-feature
```
3. Commit your changes with a descriptive message:
```bash
git commit -m "Add new feature"
```
4. Push your branch to your fork:
```bash
git push origin feature/new-feature
```
5. Open a Pull Request on the main repository and describe your changes.

## License
This project is currently unlicensed. You may view or fork it for demo purposes.  
A proper license (e.g., MIT) may be added in the future.