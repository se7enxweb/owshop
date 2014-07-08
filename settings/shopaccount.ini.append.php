<?php /* #?ini charset="utf-8"?

[HandlerSettings]
ExtensionRepositories[]=owshop/handlers

[AccountSettings]
Handler=owuser
AccountNameFields[]
AccountNameFields[]=first_name
AccountNameFields[]=last_name
AccountEmailField=email
CustomerFields[]
DeliveryAddressFields[]
DeliveryAddressFields[]=street1
DeliveryAddressFields[]=street2
DeliveryAddressFields[]=zip
DeliveryAddressFields[]=place
DeliveryAddressFields[]=state
DeliveryAddressFields[]=country


[ConfirmOrderSettings]
Handler=owdefault

[first_name-FieldsDeliveryAddressSettings]
Name=First name
Required=true
Type=string
UserAccountFieldMapping=first_name
Autocomplete=true

[last_name-FieldsDeliveryAddressSettings]
Name=Last name
Required=true
Type=string
UserAccountFieldMapping=last_name
Autocomplete=true

[email-FieldsDeliveryAddressSettings]
Name=Email
Required=true
Type=email
UserAccountFieldMapping=user
Autocomplete=true

[street1-FieldsDeliveryAddressSettings]
Name=Street 1
Required=true
Type=string
UserAccountFieldMapping=street1

[street2-FieldsDeliveryAddressSettings]
Name=Street 2
Required=false
Type=string
UserAccountFieldMapping=street2

[zip-FieldsDeliveryAddressSettings]
Name=Zip code
Required=true
Type=string
UserAccountFieldMapping=zip_code

[place-FieldsDeliveryAddressSettings]
Name=Place
Required=true
Type=string
UserAccountFieldMapping=place

[state-FieldsDeliveryAddressSettings]
Name=State
Required=true
Type=string
UserAccountFieldMapping=state

[country-FieldsDeliveryAddressSettings]
Name=Country
Required=true
Type=country_list
UserAccountFieldMapping=country

*/