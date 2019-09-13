# test_project

This project allows the editing of Customer and Product records via an authorized API.


## Authorization

There is one user with an auth key of `auth`. Add `X-AUTH-TOKEN` to the header to access the following endpoints.


## Customers

Use the following endpoints to perform CRUD actions on Customers.

### GET: /customer

Returns the Customer creation form.

### POST: /customer

Post the first_name, last_name, and date_of_birth values to the Customer table. Accepts input from both the /customer GET endpoint and an API POST request.

### GET: /customer/{uuid}

Get the uuid, first_name, last_name, and date_of_birth of the user.

### PUT/PATCH: /customer/{uuid}

Update the first_name, last_name, and/or date_of_birth of the given user.

### DELETE: /customer/{uuid}

Delete the first customer record matching that UUID.


## Products

Use the following endpoints to perform CRUD actions on Products.

### GET: /product

Returns the Product creation form.

### POST: /product

Post the issn, name and customer_uuid values to the Product table. Accepts input from both the /customer GET endpoint and an API POST request.

### GET: /product/{issn}

Get the issn, name and customer_uuid of the product.

### PUT/PATCH: /product/{issn}

Update the issn, name and/or customer_uuid of the given product.

### DELETE: /product/{issn}

Delete the first customer record matching that ISSN.


## Loading Sample Data via Fixtures

Sample User, Customer, and Product data is loaded via the following fixture:

    php bin/console doctrine:fixtures:load
    

## Commands

There is one command to find pending products.


### Pending Products

Execute the following command to find all Products still in the `pending` state for over a week:

    php bin/console app:pending-products

