# LoginApp - Google OAuth & Twilio Integration

## Overview
LoginApp is a CodeIgniter-based web application that allows users to log in using Google OAuth, access their Google Calendar events, and set up automated phone call reminders using Twilio.

## Features
- Google OAuth authentication
- Fetch events from Google Calendar
- Save & update user phone numbers
- Automated Twilio call reminders
- Cron job for event checking

---

## Installation
### Prerequisites
- PHP 7.4 or later
- CodeIgniter 3
- MySQL Database
- Composer
- Google OAuth credentials
- Twilio API credentials
- XAMPP/WAMP (if using Windows)

### Step 1: Clone Repository
```sh
git clone https://github.com/anasmelila/LoginApp.git
cd loginapp
```

### Step 2: Install Dependencies
Run the following command to install required dependencies:
```sh
composer install
```

### Step 3: Install Google API & Twilio SDK
Install Google API Client:
```sh
composer require google/apiclient
```
Install Twilio SDK:
```sh
composer require twilio/sdk
```

### Step 4: Configure Database
1. Create a database named `loginapp`.
2. Import the `loginapp.sql` file into your MySQL database.
3. Update `application/config/database.php` with your database credentials:

```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'loginapp',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
);
```

### Step 5: Configure Google OAuth
1. Go to [Google Developer Console](https://console.developers.google.com/).
2. Create a new project and enable the **Google Calendar API**.
3. Generate OAuth 2.0 credentials.
4. Add your callback URL (`http://localhost/loginapp/auth/google_login`) in the **OAuth Consent Screen**.
5. Update `application/controllers/Auth.php`:

```php
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri(base_url('auth/google_login'));
```

### Step 6: Configure Twilio
1. Create an account on [Twilio](https://www.twilio.com/).
2. Get your **Account SID**, **Auth Token**, and **Twilio Phone Number**.
3. Update `application/controllers/Cron_job.php`:

```php
private $twilio_sid = 'YOUR_TWILIO_SID';
private $twilio_token = 'YOUR_TWILIO_AUTH_TOKEN';
private $twilio_from_number = 'YOUR_TWILIO_PHONE_NUMBER';
```

### Step 7: Run the Application
Start the local server:
```sh
php -S localhost:8000 -t public/
```
Visit `http://localhost/loginapp` in your browser.

---

## Usage
### 1. Login via Google
- Click on **Login with Google**.
- Grant permissions to access your Google Calendar.
- You will be redirected to the dashboard.

### 2. Add Your Mobile Number
- Go to **Dashboard**.
- Enter your mobile number and save it.

### 3. Cron Job Setup
To check for events and trigger Twilio calls automatically:

#### Linux (Cron Job)
Edit the crontab:
```sh
crontab -e
```
Add the following line:
```sh
*/5 * * * * php /path/to/index.php cron_job check_calendar_events
```

#### Windows (Task Scheduler)
1. Create a `.bat` file (e.g., `cron_job.bat`) with the following content:
```sh
@echo off
D:\xampp\php\php.exe D:\xampp\htdocs\loginapp\index.php cron_job check_calendar_events
```
2. Schedule it to run every 5 minutes in **Task Scheduler**.

---

## Database Structure
### `users` Table
| Column       | Type         | Description          |
|-------------|-------------|----------------------|
| id          | INT (AUTO_INCREMENT) | Primary Key |
| name        | VARCHAR(255) | User's Name |
| email       | VARCHAR(255) | User's Email |
| google_access_token | TEXT | OAuth Token |
| phone       | VARCHAR(15) | User's Phone Number |

---

## Troubleshooting
### 1. Google Login Not Working?
- Ensure the callback URL is correctly set in the Google Developer Console.
- Check if Google Calendar API is enabled.

### 2. Twilio Calls Not Working?
- Ensure you’re using a verified phone number in **Twilio Trial Mode**.
- Check Twilio logs for errors.

### 3. Cron Job Not Executing?
- Run manually: `php index.php cron_job check_calendar_events`
- Check cron logs (`/var/log/syslog` on Linux).

---


