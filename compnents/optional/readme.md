# Optional container
The purpose of the class is to provide type-level solution for representing optional values.
Helps to get rid of null checks and if -> else depth.

```php
$value = null;
if ($obj !== null) {
    $value = $obj->getValue();
} else {
    $value = "b";
}
```
can be replaced with
```php
$value = Optional::ofNullable($obj)
                   ->map(function () { return $obj -> getValue();})
                   ->orElse("b");
```
