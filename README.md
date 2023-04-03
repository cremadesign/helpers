# PHP Helper Functions
by Stephen Ginn at Crema Design Studio

This repo contains a collection of reusable PHP helper functions.

## Installation
You can install the package via composer:
```php
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
echo printJSON($products);
```

#### Print JSON file to screen
```php
echo printData("filename.json");
```
