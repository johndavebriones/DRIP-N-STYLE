# DRIP-N-STYLE

🛍️ Drip N’ Style — Clothing Store Web Application

A modern web-based clothing store built with PHP, MySQL, JavaScript, and Bootstrap.
This system supports full e-commerce functionality including product management, variants, cart, checkout, order processing, and admin controls.

📌 Features
👕 Customer Features

Browse products with filters (size, category, availability)

View product details & size/variant options

Add to cart and manage cart items

Secure checkout (GCash or Cash On Pickup)

Automatic payment + order creation

Customer profile with:

Saved addresses

Order history

Order item details with modal preview

🛠️ Admin Features

Dashboard overview

Product Management:

Add / Edit products

Soft delete + restore deleted products

Auto-stock tracking

Duplicate product validation

Variant-friendly product structure

Orders Management:

View all orders

Status updates (Pending → Confirmed → Ready for Pickup → Completed/Cancelled)

View full order details

Clickable order rows

Category Management

Payment records & linking to orders

🗄️ Database Structure Overview
Main Tables

users — customer/admin accounts

products — clothing items

categories — product classification

cart — each customer Cart

cart_items — products inside the cart

orders — each customer order

order_items — products inside an order

payments — payment method, status, ref

addresses — customer saved addresses

🛠️ Tech Stack
Frontend

HTML5

CSS3 (Modular styles: profile.css, shop.css, admin.css)

Bootstrap 5 (CDN or Local)

Vanilla JavaScript

AJAX (Admin Tools)

Backend

PHP (OOP + MVC-like structure)

MySQL / MariaDB

DAO Pattern (ProductDAO, OrderDAO, UserDAO)

Controllers + Helpers

Secure database connections