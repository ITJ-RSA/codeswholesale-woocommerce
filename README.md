codeswholesale-woocommerce
==========================

CodesWholesale integration plugin for WooCommerce


What it does
------------
* Adds a field to product to match with CodesWholesale's product in "General tab" 
* Buys and sends keys (text and images) when order is marked as complete
* Disable with plugin activation, WooCommerce's email notification about completed order
* Cron job to update stock information
* Cron job to check that all completed orders are full filled with keys
* Error reprting to admin's email
* Low balance notification
* Automatically complete order when payment is received


How to test
----------
1. Download zip file from [here](http://codeswholesale.com), install it into your WordPress and active plugin.
  - WooCommerce plugin is required.
  
2. CodesWholesale tab will apear in admin menu. Under this tab you can configure few things:
 - Environment: sandbox or live, for tests is recommended to use sandbox but if you want to go live choose "live" and put your API credentials.
 - If you'd like to automatically send keys after payment is recived e.g. from PayPal mark checkbox with "Automatically complete order when payment is received"
 - Balance value: While purchasing keys from [CodesWholesale.com](http://codeswholesale.com), script will check if your current balance is less than value from this field - if yes, it will send a notification email to shop's administrator.

3. Go to "Add product", put your details and in the "General tab" select [CodesWholesale's](http://codeswholesale.com) product to match. This will tell the script what product is buying your customer.
  - At first step choose "Test with text codes only"

4. Create and order at your front:
 - Add previously added product to cart.
 - Go through checkout process and put your real email (here you will need PayPal configured to sandbox if not choose bank transfer)
 - If you've marked "Automatically complete order when payment is received" in plugins configuration you should recive an email with keys
 - If you don't have PayPal's sandbox you have to go to admin panel and mark order as "complete", it will trigger script

Nice to have
------------
* Post back query from CodesWholesale to shop about stock instead cron (benefit: more accurate stock details)
* Post back query from CodesWholesale to shop to let know that preorder is ready to download
