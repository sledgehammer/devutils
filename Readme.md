Development Utilities
----------------------

DevUtils is an inspection system for projects that use [composer](http://getcomposer.org).

* Running phpunit with improved debugging and navigation.
* Run utility commands.

## Installation

Download or clone the devutils repository to the next to your webapp into a `devutils` folder.
```
git clone https://github.com/sledgehammer/devutils.git
cd devutils
composer install
```

## Usage

Copy the `devutils/public` folder into the DocumentRoot of your webapp and rename the folder to `devutils`.

Browse to the `/devutils/` url and login using your shell/ssh credentials.
(Hint: type `id` in a terminal to view your username.) 