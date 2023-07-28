# PHP Helper Functions
by Stephen Ginn at Crema Design Studio

This repo contains a collection of reusable PHP helper functions.

## Installation
You can install the package via composer:
```bash
composer config repositories.crema/helpers git https://github.com/cremadesign/helpers
composer require crema/helpers:@dev
```

Add the composer autoloader to your PHP file:
```php
require_once '../vendor/autoload.php';
```

## Usage

### Arrays and Strings

#### Get Ordinal ("st", "nd", "rd") for a number
```php
echo ordinal(2);
```

#### Return a random array item
```php
$array = ["one", "two", "three", "four"];
echo random($array);
```

#### Sends back a web-safe slug from a string
```php
echo slugify("Lorem#Ipsum &$ Dolar Sit Amet!");
```

### Search

#### Search a string for a query
```php
echo contains($query, $string);
```

#### Use a switch statement to check if a string contains a word
```php
$hs = new Haystack("yourwebsite.com");

switch ($hs) {
	case $hs->contains('.test'):
		echo "this is a local site";
	break;
	case $hs->contains('.com'):
		echo "this is a live site";
	break;
	default:
		echo "we don't know what this site is";
}
```

#### Find item in object
```php
header('Content-Type: application/json');
$data = findItem($obj, $query);
print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
```

### File Related

#### Delete directory
```php
removeDir("cache");
```

#### Delete directories
```php
removeDirs([
	"cache",
	"api/.cached"
]);
```

### Debugging

#### Log PHP messages to file
```php
logger("This is a test");
```

#### Log message to browser console
**Warning:** This function probably should not be used, since it breaks the gulp autorefresh.
```php
echo console("This is a test");
```

#### Print JSON array to screen
```php
$products = json_decode(file_get_contents("https://dummyjson.com/products/1"), true);
printJSON($products);
```

#### Print JSON file to screen
```php
echo printData("filename.json");
```

# Dreamhost API

## Installation
- Follow the installation steps above.
- Add a file named credentials.json to the base of your project with the following info:
```
{
	"userid": "DH_USER_ID",
	"apikey": "DH_API_KEY",
	"ip": "DEFAULT_HOST_IP"
}
```

## Usage

Add the composer autoloader to your PHP file:
```php
use Crema\DreamhostApi;

$credentials = json_decode(file_get_contents("../credentials.json"));
$account = $credentials->dreamhost;
$dreamhost = new DreamhostApi($account);
```

### Get Records
```php
$response = $dreamhost->getRecords("thor.website.com");
printJSON($response);
```

### Get Domains
```php
$response = $dreamhost->getDomains();
printJSON($response);
```

### Add Record
```php
$response = $dreamhost->addRecord("thor.website.com", $account->ip);
printJSON($response);
```

### Add Records
```php
$response = $dreamhost->addRecords("thor.website.com", $account->ip);
printJSON($response);
```
