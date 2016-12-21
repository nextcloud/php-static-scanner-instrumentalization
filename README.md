# PHP Static Scanner Instrumentalization

Static security scanners usually are not clever enough to detect our injection of parameters in the Nextcloud source code.

This instrumentalization script loops over a given directory and instrumentalizes the source code by directly injecting
a `$_GET` on code related to the Nextcloud appframework. So the original code would look like:

```php
<?php
use OCP\AppFramework\Controller;

class Foo extends Controller {
    public function list($index, $bar) {
        // Logic of the code
    }
}
```

`$index` in the function `list` here would automatically be read from `$_GET`, to make the static scanners aware of that
the resulting code would look like:

```php
<?php
use OCP\AppFramework\Controller;

class Foo extends Controller {
    public function list() {
        $index = $_GET['index'];
        $bar = $_GET['bar'];
        // Logic of the code
    }
}
```
