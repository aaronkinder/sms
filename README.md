# A2P 10DLC Compliance Solution

## Overview
This solution helps businesses comply with A2P (Application-to-Person) Campaign registration requirements that went into effect September 1st, 2023. As per new regulations, all SMS and MMS messages sent to U.S. phone numbers using +1 10DLC numbers must be sent via an approved A2P campaign, or they will be blocked.

## Features

### Multi-Tenant Support
- Domain-based company identification
- Support for multiple businesses/domains
- DBA (Doing Business As) name handling
- Separate branding per domain

### Subscriber Management
- Secure subscriber database
- Phone number validation for US numbers
- Email validation
- Opt-in consent tracking
- IP address logging for audit trails
- Subscriber history tracking

### Security Features
- Math CAPTCHA verification
- Rate limiting to prevent abuse
- Input sanitization
- Secure password handling
- Admin authentication
- Session management
- XSS prevention
- SQL injection protection

### Compliance Tools
- Proper opt-in consent collection
- Unsubscribe mechanism
- Privacy policy integration
- Terms of service integration
- Activity logging
- IP tracking for audit purposes

### Admin Interface
- Subscriber management dashboard
- Domain management interface
- Edit subscriber information
- Remove subscribers
- View subscription history
- Multi-domain administration

## Technical Requirements

### Server Requirements
- PHP 8.0 or higher
  - Required Extensions:
    - mysqli
    - session
    - json
    - mbstring
    - PDO
    - openssl
  - php.ini Settings:
    - max_execution_time = 300
    - memory_limit = 256M
    - post_max_size = 20M
    - upload_max_filesize = 20M

- MySQL 5.7 or higher
  - InnoDB engine support
  - utf8mb4 character set support
  - Minimum 100MB storage space
  - Recommended settings:
    - max_connections = 150
    - innodb_buffer_pool_size = 256M

- Web Server
  - Apache 2.4+ or Nginx 1.18+
  - mod_rewrite enabled (Apache)
  - .htaccess support (Apache)
  - URL rewriting capability
  - SSL/TLS support for HTTPS

### Client Requirements
- Modern web browser with JavaScript enabled
  - Chrome 90+
  - Firefox 88+
  - Safari 14+
  - Edge 90+
- Cookies enabled
- Minimum screen resolution: 1024x768

### Recommended Server Specifications
- CPU: 2+ cores
- RAM: 4GB minimum
- Storage: 10GB+ available space
- Network: 100Mbps connection
- Dedicated IP address recommended

## Installation

1. Clone the repository to your web server
2. Navigate to setup.php in your browser
3. Enter the following information:
   - Company name
   - Domain name
   - DBA name (optional)
   - Database host
   - Database name
   - Database username
   - Database password
   - Admin password (minimum 15 characters)
4. The setup script will:
   - Create necessary database tables
   - Configure the system
   - Set up admin credentials
   - Initialize domain mappings
   - Create required settings

## Security Features

### Rate Limiting
- Prevents abuse by limiting submissions per IP
- Configurable cooldown period (default: 60 seconds)
- Maximum submissions per IP address (default: 5)
- Protects against spam and DoS attacks

### Input Validation
- Phone number format validation (US format)
- Email format validation
- Input sanitization
- XSS prevention
- SQL injection protection through prepared statements

### Admin Security
- Secure password requirements (15+ characters)
- Password hashing using modern algorithms
- Session-based authentication
- Protected admin interface
- Domain-specific access controls

## Compliance Features

### Opt-in Process
- Clear consent collection
- Terms acceptance tracking
- Privacy policy acknowledgment
- IP address logging
- Timestamp recording
- Domain-specific consent tracking

### Unsubscribe Mechanism
- Easy opt-out process
- Immediate processing
- Confirmation messaging
- Unsubscribe logging
- Domain-specific unsubscribe handling

## File Structure
```
├── admin.php                    # Admin interface
├── admin_domains.php           # Domain management interface
├── setup.php                   # Installation script
├── process.php                 # Form processing
├── subscribe.php               # Subscription page
├── unsubscribe.php            # Unsubscribe page
├── privacy_policy.php         # Privacy policy
├── terms_of_service.php       # Terms of service
├── includes/
│   ├── config.php            # Configuration
│   └── unsubscribe_validation.php # Unsubscribe validation
├── js/
│   ├── validation.js         # Client-side validation
│   └── unsubscribe-validation.js # Unsubscribe validation
├── css/
│   └── style.css            # Styling
└── backups/                  # Database backup storage
    └── .htaccess            # Backup protection
```

## Backup System
- Automated database backup functionality
- Secure backup storage
- Protected backup directory
- Backup file naming with timestamps

## Multi-Tenant Features
- Support for multiple domains
- Domain-specific company names
- Optional DBA name support
- Separate branding per domain
- Domain-based subscriber management
- Individual privacy policies
- Domain-specific terms of service

## Author
Aaron Kinder (aaron@aaronkinder.com)

## Version
1.1.0

## License
All rights reserved. This solution is proprietary and confidential.

## Support
For technical support or inquiries, please contact aaron@aaronkinder.com
