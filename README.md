# WHMCS Order Management Addon
WHMCS module to automate several order management tasks. Here are the following tasks currently automated:
- Pending order cancellation after 'X' days
- Unpaid invoice cancellation after 'X' days for terminated services
- Accept order on successful payment

# Installation
1. Download zip of repo
2. Copy the "order_management" folder to your WHMCS installation (modules/addons).
3. Rename the six "adminuser" variables in hooks.php to the username of the admin you'd like to use for the API calls. (Hopefully we can automate this - See Issue #2)

# Contributions
Feel free to fork the repo, make changes, then create a pull request! For ideas on what you can help with, check the project issues.
