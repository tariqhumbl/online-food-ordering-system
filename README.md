# Tailor Management App

This is a Tailor Management App built with Laravel 11 and Vue 3. The app allows tailors to manage their shops, customers, orders, measurements,  appointments and Invoice.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Database](#database)
- [Running the Application](#running-the-application)
- [API Endpoints](#api-endpoints)
- [Frontend](#frontend)
- [License](#license)

## Installation

1. Clone the repository:

    ```bash
    git git clone https://Tariq_ullah@bitbucket.org/y-and-l-solutions/tailor-management.git
    cd tailor-management
    ```

2. Install the dependencies:

    ```bash
    composer install
    npm install
    ```

3. Copy the `.env.example` file to `.env`:

    ```bash
    cp .env.example .env
    ```

4. Generate the application key:

    ```bash
    php artisan key:generate
    ```

## Configuration

1. Update the `.env` file with your database configuration:

    ```env
    APP_KEY=base64:____________

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=tailor-app
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    ```mailtrap
    MAIL_MAILER=smtp
    MAIL_SCHEME=null
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=40b19f36dcbf70
    MAIL_PASSWORD=22778b64b41bf1
    MAIL_FROM_ADDRESS="hello@example.com"
    MAIL_FROM_NAME="${APP_NAME}"

2. Set up other necessary environment variables as needed.

## Database

1. Run the database migrations:

    ```bash
    php artisan migrate
    ```

2. (Optional) Seed the database with initial data:

    ```bash
    php artisan db:seed
    ```

## Running the Application

1. Start the Laravel development server:

    ```bash
    php artisan serve
    ```

2. Start the Vue development server:

    ```bash
    npm run dev
    ```

3. Open your browser and navigate to `http://localhost:8000`.

## API Endpoints

Here are some of the main API endpoints available in the application:

- **Authentication** (Hight Priority)
  - `POST /api/register` - Register a new user
  - `POST /api/auth/login` - Login for existing users
  - `POST /api/auth/reset-password-request` - Rest  password request (Forgot Password)
  - `POST /api/auth/reset-password` - Complete password reset
  - `POST /api/change-password` - Complete password change
  - `Get /api/profile` - Authenticated user information
  - `Put /api/auth/profile` - Authenticated user information updated
  - `Get /api/auth/logout` - Authenticated user logout

- **Admin Dashboard** (Hight Priority)
  - `GET /api/admin/get-all-user-list` - List all users
  - `resource /api/admin/tailor-shop` - create, get, update and delete tailor shop
  - `resource /api/admin/tailors` - Create a new Tailor, assign into specific shop and send credentials via email,
  - `PUT /api/admin/change-tailor-shop/{id}` - Change Tailor Shop 
  - `resource /api/admin/tailors/{id}` - Delete a tailor (user) Account

- **User Dashboard / Customer Management** (Hight Priority)
  - `resource /api/user/profile` - user profile
  - `resource /api/user/customer-information` - create, get, update and delete customer information
  - `get /api/user/customer-orders/{id}` -  (Fetch orders for a customer)

 - **User Dashboard / measurements Management** (Hight Priority)
  - `resource /api/user/measurements` -   create, get, update and delete customer  measurements

- **User Dashboard / Order Management**  (Hight Priority)
  - `resource /api/user/orders` - create, get, update and delete
  - `GET /api/user/get-tailor-user` - Get details of a specific order


- **User Dashboard / Appointment Scheduling** (Hight Priority)
  - `resource /api/user/appointments` - create, get, update and delete appointment

- **Tailor Dashboard/ Invoice Management** (Hight Priority)
  - `GET /api/tailor/invoices` - create, get and delete invoices
  - `GET /api/invoice/{id}` - Get details of a specific Invoice
  - `GET /api/tailor/register-user` - Tailor Manage user, create and delete
  - `resource /api/tailor/appointments` - tailor get user appointment ( Low Priority, pending)
  - `resource /api/tailor/orders` - tailor get user Orders (Low Priority, pending) 
 
 - **Inventory Management**  (Low Priority)
  - `resource /api/inventories` - create, get, update and delete Inventory

  - **Reports & Analytics**  (Low Priority)
  - `resource /api/reports` - create, get, update and delete Reports
 
 - **Notifications System**  (Low Priority)
  - `resource /api/notifications` - create, get, update and delete Notifications

   - **Settings & Backup System**  (Low Priority)
  - `resource /api/settings` - create, get, update and delete Settings
  - `resource /api/backup` - create, get, update and delete backup
  - `resource /api/restore` - create, get, update and delete restore



## Frontend

The frontend of the application is built with Vue 3. To start the development server, run:

```bash
npm run dev
