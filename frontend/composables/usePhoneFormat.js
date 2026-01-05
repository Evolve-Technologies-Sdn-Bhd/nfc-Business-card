/**
 * Composable for phone number formatting with Malaysia (+60) country code
 */
export const usePhoneFormat = () => {
  /**
   * Formats a phone number to Malaysia +60 format
   * @param {string} input - The raw phone number input
   * @returns {string} - Formatted phone number with +60 prefix
   */
  const formatPhoneNumber = (input) => {
    if (!input) return '';
    
    // Remove all spaces, dashes, parentheses, and special characters except +
    let cleaned = String(input).replace(/[\s\-()]/g, '');
    
    // If already starts with +60, return as is
    if (cleaned.startsWith('+60')) {
      return cleaned;
    }
    
    // If starts with 60, add +
    if (cleaned.startsWith('60')) {
      return '+' + cleaned;
    }
    
    // If starts with 0, remove it and add +60
    if (cleaned.startsWith('0')) {
      return '+60' + cleaned.substring(1);
    }
    
    // Otherwise, add +60 prefix
    return '+60' + cleaned;
  };

  /**
   * Formats a phone number for display (with spaces)
   * @param {string} phone - The phone number (should already be in +60 format)
   * @returns {string} - Formatted for display: +60 12-345 6789
   */
  const formatPhoneDisplay = (phone) => {
    if (!phone) return '';
    
    const formatted = formatPhoneNumber(phone);
    
    // Format: +60 XX-XXX XXXX or +60 XXX-XXX XXXX
    if (formatted.length >= 12) {
      const countryCode = formatted.slice(0, 3); // +60
      const remaining = formatted.slice(3);
      
      if (remaining.length === 9) {
        // Mobile: +60 12-345 6789
        return `${countryCode} ${remaining.slice(0, 2)}-${remaining.slice(2, 5)} ${remaining.slice(5)}`;
      } else if (remaining.length === 10) {
        // Landline: +60 3-1234 5678
        return `${countryCode} ${remaining.slice(0, 1)}-${remaining.slice(1, 5)} ${remaining.slice(5)}`;
      }
    }
    
    return formatted;
  };

  /**
   * Validates a phone number
   * @param {string} phone - The phone number to validate
   * @returns {object} - { valid: boolean, error: string }
   */
  const validatePhone = (phone) => {
    if (!phone) {
      return { valid: true, error: '' }; // Empty is valid (optional field)
    }
    
    const formatted = formatPhoneNumber(phone);
    const digitsOnly = formatted.replace(/\D/g, '');
    
    // Malaysia phone numbers should be 10-12 digits (including country code 60)
    if (digitsOnly.length < 10) {
      return { valid: false, error: 'Phone number too short' };
    }
    
    if (digitsOnly.length > 12) {
      return { valid: false, error: 'Phone number too long' };
    }
    
    return { valid: true, error: '' };
  };

  return {
    formatPhoneNumber,
    formatPhoneDisplay,
    validatePhone
  };
};
