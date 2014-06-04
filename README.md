Magento Delete All Images Script
================================

Clean removal of all product images on your Magento store. 
Utilises Magento's SOAP API to loop through products, retrieve image list and delete them.

To configure your will require..
- Magento SOAP Username
- Magento SOAP Password
- Magento SOAP URL

You can optionally configure...
- Logging
- File to Log to (If Logging is enabled)
- HTTP Authentication (If your require this to access the API)
- Retry Attempts
- Timeout Period

This doesn't require SSH access to the server as only uses Magento SOAP API.
Please ensure you have access to this, not blocked by firewalls and that your SOAP user has the correct permissions to delete product images.

