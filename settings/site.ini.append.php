<?php /* #?ini charset="utf-8"?

[RegionalSettings]
TranslationExtensions[]=owshop


[ShopSettings]
# This settings controls when the basket is cleared.
# It can contain the following values:
# - disabled - Means that the basket is cleared when the shop/checkout
#              trigger is done. In practice this means when a user
#              has payed the product and payment system is finished.
#              This is the default value since it means the user can
#              cancel the order and go back to the shop with the
#              basket still intact.
# - enabled - Means to clear the basket as soon as the user clicks
#             confirm in the shop/confirmorder trigger. This may
#             needed by some payment system, check the documentation
#             for the system to see if this needs to be enabled.
#             The inpact on the users is that the basket will not be
#             available when the payment is cancelled.
#
# To put it in context the entire checkout process consists of these triggers:
# - shop/confirmorder - The user shown the total price with shipping
#                       and other calculations. When the user clicks
#                       confirm the shop/checkout trigger is started.
# - shop/checkout     - Starts a new temporary order and runs any payment
#                       methods (or other workflows). Once it is done
#                       the order is activated and the basket is cleared.
ClearBasketOnCheckout=disabled
# This settings is used when a user logs out.
# It can contain the following values:
# - disabled - Means that the basket will NOT be cleared when a user logs out.
# - enabled  - Means that the basket will be cleared when a user logs out.
ClearBasketOnLogout=disabled
# Controls what happens after an item is added to the basket
# It can contain one of these entries:
# - basket - Redirect back to the basket to show the newly added item
# - reload - Redirect back to where the user was previously, this allows
#            the user to continue shopping.
RedirectAfterAddToBasket=basket

# Controls if we should send an order confirmation email to admin and the user or not
# when an order is completed and confirmed.
SendOrderEmail=enabled

*/
