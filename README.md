# Yu-Gi-Me!

This is a simple project made with the intent of learning a little of the Facebook API, Docker and MongoDB.

The project consists of a PHP application that generates a Facebook post showing to which Yu-Gi-Oh! monster the user is "related" (right now is just a random choice  :sweat_smile:).

It consumes both the user data and monster card data from multiple sources to generate the post image. 

![Example image](https://jesm.github.io/Yu-Gi-Me/img/example.jpg)

## Credits
* Custom user card generated with [Yugioh Card Maker](http://www.yugiohcardmaker.net/);
* Card data obtained from [Yugioh Prices](http://docs.yugiohprices.apiary.io/#);
* [Yu-Gi-Oh! Wikia](http://yugioh.wikia.com/wiki/Yu-Gi-Oh!_Wikia), for additional card data;
* Post background image designed by Starline - [Freepik.com](http://freepik.com/).

## Setup
The project was developed using PHP 7.0 and MongoDB 3.3.

If you're using Docker Compose, just enter the project directory through the terminal and get ready to go with:
```
docker-compose up -d
```
### Configuring the application

Once your environment is ready, copy the `config.example.php` file to `config.php`, then edit its content:
* Fill the `facebook` values with the `app-id` and `app-secret` of your Facebook application;
* Insert the correct URI for the Mongo database (in case of Docker Compose, just replace `localhost` with `db`);
* The `predefined_user_cards` key allows you to relate Facebook user IDs to any monster card related in the `card_names.json` file.

### Importing the card data
To import the card data necessary for the application to run, just execute:
```
php -f setup.php
```
Or, with Docker Compose:
```
docker-compose exec web php -f setup.php
```
After a probably long wait for the importation to finish, you can access it with your browser.
