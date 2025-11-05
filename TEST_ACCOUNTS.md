# Test Accounts - NFC Business Card

This document contains all test accounts available for testing the application in different environments.

## 🔐 Admin Accounts

### Super Admin Account

- **Email**: `admin@nfcgo.com`
- **Password**: `admin123`
- **Access Level**: Super Administrator
- **Permissions**: Full system access
- **Features**:
  - User management (create, edit, delete)
  - NFC card management
  - System analytics and monitoring
  - Content moderation
  - System configuration
  - Database management

### Regular Admin Account

- **Email**: `admin2@nfcgo.com`
- **Password**: `admin123`
- **Access Level**: Administrator
- **Permissions**: Limited admin access
- **Features**:
  - User management (view, edit)
  - NFC card management
  - Basic system monitoring
  - Content moderation

## 👤 User Accounts

### Free Tier User

- **Email**: `john@example.com`
- **Password**: `password`
- **Subscription**: Free
- **Access Level**: Standard User
- **Features**:
  - Profile creation and customization
  - Social media links
  - Basic analytics
  - NFC tag activation (limited)
  - Profile sharing

### Pro Tier User

- **Email**: `test@example.com`
- **Password**: `password`
- **Subscription**: Pro
- **Access Level**: Pro User
- **Features**:
  - All free tier features
  - Advanced analytics
  - NFC card management
  - Custom themes
  - Priority support
  - Enhanced customization options

## 🧪 Testing Scenarios

### Admin Testing

1. **Login as Super Admin**

   - Test admin dashboard access
   - Verify user management capabilities
   - Test NFC card management
   - Check system analytics

2. **Login as Regular Admin**
   - Verify limited admin access
   - Test user management restrictions
   - Check NFC card management access

### User Testing

1. **Free Tier Testing**

   - Login as John Doe
   - Test profile customization
   - Verify social media links
   - Check basic analytics access
   - Test NFC tag activation

2. **Pro Tier Testing**
   - Login as Test User
   - Test advanced features
   - Verify NFC card management
   - Check custom themes
   - Test enhanced analytics

## 🔒 Security Notes

### Default Passwords

⚠️ **IMPORTANT**: These are test accounts with default passwords.
**NEVER use these passwords in production!**

### Password Requirements for Production

- Minimum 8 characters
- Mix of uppercase, lowercase, numbers, and symbols
- No common words or patterns
- Unique for each account

### Recommended Production Passwords

- **Super Admin**: Use a strong, unique password (16+ characters)
- **Regular Admin**: Use a strong, unique password (16+ characters)
- **Users**: Enforce strong password policy

## 📱 NFC Testing

### Test NFC Tags

- **Tag ID**: `test-nfc-123`
- **Status**: Active
- **User**: John Doe (Free Tier)
- **Functionality**: Profile sharing on tap

### NFC Card Testing

- **Card ID**: Generated during testing
- **Status**: Pending/Active
- **User**: Test User (Pro Tier)
- **Functionality**: Physical card management

## 🧹 Cleanup Commands

### Remove Test Users (Development Only)

```bash
# Run cleanup seeder to remove unwanted users
php artisan db:seed --class=CleanupSeeder

# Or manually remove specific users
php artisan tinker
User::where('email', 'unwanted@example.com')->delete();
```

### Reset Test Data

```bash
# Reset entire database
php artisan migrate:fresh --seed

# Reset specific tables
php artisan migrate:refresh --path=database/migrations/2025_07_28_052221_create_users_table.php
```

## 📊 Test Data Summary

### Current Test Users: 4

- 2 Admin accounts (Super + Regular)
- 1 Free tier user
- 1 Pro tier user

### Test Data Includes

- User profiles with sample information
- Social media links
- NFC tags and cards
- Sample analytics data
- Profile images and logos

## 🚀 Deployment Considerations

### Alpha Testing

- Keep all test accounts
- Use test data for validation
- Monitor user interactions
- Collect feedback on features

### Beta Testing

- Consider removing some test accounts
- Use production-like data
- Focus on real user scenarios
- Monitor performance and security

### Production

- **REMOVE ALL TEST ACCOUNTS**
- **CHANGE ALL DEFAULT PASSWORDS**
- Use real user data only
- Implement proper security measures

### For Testing Issues

- Check application logs
- Verify database connections
- Test API endpoints
- Review error messages

### For Account Management

- Use admin dashboard for user management
- Check user permissions
- Verify subscription status
- Monitor user activity

---

**Last Updated**: August 16, 2025
**Environment**: Development/Testing
**Next Review**: Before production deployment
