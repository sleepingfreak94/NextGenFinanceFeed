# NextGen Finance Feed

A portfolio project that aggregates financial news for companies, providing a clean, modern interface with user authentication, admin control, and automated news fetching.

---

## Features

- **User Authentication**
  - Signup, Login, Logout
  - Forgot Password functionality
- **Admin Panel**
  - Add, Edit, Delete companies
  - Fetch latest news articles for companies using **Brave API**
- **News Management**
  - AI-powered summarization using **Hugging Face Transformers API**
  - Pagination for browsing articles
- **Responsive UI**
  - Modern and mobile-friendly design using **custom CSS**
- **Static Pages**
  - About Us
  - Privacy Policy
  - Contact Us (with email functionality)
- **Automation Testing**
  - PHPUnit tests implemented for core functionalities

---

## Tech Stack

- **Frontend**
  - HTML5, CSS3 (Custom styles)
- **Backend**
  - PHP 8+
- **Database**
  - MySQL (with prepared statements for security)
- **APIs**
  - [Brave Search API](https://brave.com/search/api/) for fetching latest news
  - [Hugging Face API](https://huggingface.co/) for text summarization
- **Email**
  - PHP `mail()` for sending messages via Contact Form
- **Testing**
  - PHPUnit for automation testing

---

## Project Structure
NextGenFinanceFeed/
│
├── admin/                  # Admin dashboard and scripts
│   ├── admin.php
│   ├── sync_companies.php
│   ├── sync_nifty50.php
│   ├── header.php
│   └── footer.php
│
├── assets/
│   ├── css/
│   │   └── styles.css      # Main stylesheet
│   └── images/             # Images 
│
├── includes/
│   ├── db/
│   │   ├── db.php          # Database connection
│   │   └── functions.php   # Helper functions
│   ├── phpmailer/          # PHPMailer library
│   │   ├── Exception.php
│   │   ├── PHPMailer.php
│   │   └── SMTP.php
│   ├── config.php          # API keys and configs
│   ├── header.php          # Common header
│   └── footer.php          # Common footer
│
├── tests/                  # Automation test files
│
├── about.php               # About page
├── contact.php             # Contact page (LinkedIn & Email)
├── privacy.php             # Privacy policy page
├── allnews.php             # Paginated news
├── company.php             # Company-specific news
├── news_detail.php         # Single news detail
├── index.php               # Homepage
├── signup.php              # Registration page
├── signin.php              # Login page
├── logout.php              # Logout
├── nifty50.php             # Nifty 50 news
├── sp500.php               # S&P 500 news
├── vendor/                         # Composer dependencies (added by Composer)
│
├── .env                            # Actual secrets (NOT committed)
├── .env.example                    # Template for other developers
├── .gitignore                      # Ignores .env, /vendor/, etc.
├── composer.json                   # Added by Composer for vlucas/phpdotenv
├── composer.lock
├── schema.sql
└── README.md               # Project documentation

## Setup Instructions  

1. Clone or download this repository.  
2. Move it to your **XAMPP htdocs folder** (or WAMP equivalent).  
3. Create a MySQL database and import the provided `schema.sql`.  
4. Update `includes/config.php` with:  
   - Database credentials  
   - Brave API Key  
   - HuggingFace Token  
   - Email SMTP details for PHPMailer  
5. Start Apache and MySQL from XAMPP.  
6. Open project in browser.

## About  

This is a **portfolio project** created by **Kshitij Sharma** to showcase:  
- Automation testing  
- CI/CD pipeline  
- Secure coding  

## Contact  

- **LinkedIn**: [Kshitij Sharma](https://www.linkedin.com/in/kshitij-sharma-6305b2139/)  
- **Email**: [kshitijsharma94@gmail.com](mailto:kshitijsharma94@gmail.com)  