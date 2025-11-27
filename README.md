## Overview

Build a high-performance Laravel backend for a real-time location tracking system. It must handle GPS data every second from 10,000+ users and store only each user’s latest location, optimized for very high write frequency and low latency 


## Features

- Create secure JWT-based authentication for users.
- Build an API endpoint that receives GPS data every second and updates the user’s
  latest location.
- Use Redis as the primary in-memory store for fast read/write and auto-expire
  inactive users.
- Periodically sync Redis data to MySQL to maintain a persistent copy.
- Implement WebSocket broadcasts so the admin dashboard receives live location
  updates.


## Setup Instructions

1.  **Clone the repository:**

    ```bash
    git clone <repository_url>
    cd <project_directory>
    ```

2.  **Install Composer dependencies:**

    ```bash
    composer install
    composer require predis/predis
    composer require laravel/reverb
    composer require tymon/jwt-aut
    ```

3.  **Copy the `.env.example` file to `.env` and configure your database:**

    ```bash
    cp .env.example .env
    # Edit the .env file with your database credentials
    ```

4.  **Generate the application key:**

    ```bash
    php artisan key:generate
    ```

5.  **Run database migrations and seed:**
    ```bash
    php artisan migrate:fresh --seed
    ```
6.  **Serve the application:**
    ```bash
    php artisan serve
    php artisan reverb:start
    php artisan jwt:secret
    ```


  
