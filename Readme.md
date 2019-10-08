<p align="center"><img src="https://www.checkout.com/static/img/checkout-logo/logo.svg" width="380"></p>

# Prestashop 1.7.x Extension

[Checkout.com](https://www.checkout.com "Checkout.com") is a software platform that has integrated 100% of the value chain to create payment infrastructures that truly make a difference.

This extension allows shop owners to process online payments (card / alternative payments) using:
  - **Frames.js** - Payment form embedded within your website
  - **Alternative payments** - Shoppers can pay using local payment options (Sofort, iDEAL, Boleto, etc...)

# Installation
You can find a full installation guide [here](https://github.com/checkout/checkout-prestashop1.7-plugin/wiki/Installation)

# Initial Setup
If you do not have an account yet, simply go to [checkout.com](https://checkout.com/) and hit the "Get Test Account" button.

# Keys
There are 3 keys that you need to configure in the NON-PCI extension, while for the PCI version you just need 2:
- **Secret Key**
- **Public Key** (not needed for PCI extension)
- **Private Shared Key**

> The Private Shared Key is generated when you [configure the Webhook URL](https://docs.checkout.com/docs/business-level-administration#section-manage-webhook-url) in the Checkout HUB.

# URLs
In order to successfully complete 3D Secure transactions, and to keep Prestashop order statuses in sync you need to configure the following URLs in your Checkout HUB as follows:

| Type | URL Example | Description |
| ------ | ------ | ------ |
| Redirections **Success**| _example.com_**/index.php?fc=module&module=checkoutcom&controller=success** | Redirect after 3D Secure |
| Redirections **Fail**| _example.com_**/index.php?fc=module&module=checkoutcom&controller=failure** | Redirect after 3D Secure |
| Webhook | _example.com_**/index.php?fc=module&module=checkoutcom&controller=webhook** | Sync Prestashop |

> You can see a guide on how to set the URLs in the HUB [here](https://docs.checkout.com/docs/business-level-administration#section-manage-channel-urls) ; You can find test card details [here](https://docs.checkout.com/docs/testing#section-credit-cards)

# Going LIVE

Upon receiving your live credentials from your account manager, here are the required steps to start processing live transactions:

- In the plugin settings, place your **live** keys
- Switch the _Endpoint URL mode_ to **live**.
- Make sure you have configured the Redirection and Webhook URLs correctly in your **live** Checkout.com HUB


# Reference

You can find our complete Documentation [here](http://docs.checkout.com/).
If you would like to get an account manager, please contact us at sales@checkout.com
For help during the integration process you can contact us at integration@checkout.com
For support, you can contact us at support@checkout.com

_Checkout.com is authorised and regulated as a Payment institution by the UK Financial Conduct Authority._
