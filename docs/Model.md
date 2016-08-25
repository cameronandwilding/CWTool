Models
======

The Model interface is for small objects representing a stored record. These subclasses usually contain mostly fields and necessary loaders.

Example of a weather info model where the data is in files on the drive:

```php

class WeatherInfo implements Model {

      /**
       * @var float
       */
      protected $temp;

      public function __construct($temp) {
        $this->temp = $temp;
      }

      /**
       * Save object.
       */
      public function save() {
        // ... save to file ...
      }
    
      /**
       * Delete object.
       */
      public function delete() {
        // ... delete file ...
      }
      
      public function getTemp() {
        return $this->temp;
      }
      
      public static loadFromFile($name) {
        $content = get_file_content($name);
        // .. parsing and instantiating ...
        return new static($temp);
      }
      
}
```

Fields suppose to be at least protected. Access is via public getters.
