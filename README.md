# ZipcodeJp plugin for CakePHP 3 and 4

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
# for CakePHP4
composer require imo-tikuwa/cakephp-zipcode-jp "2.*"

# for CakePHP3
composer require imo-tikuwa/cakephp-zipcode-jp "1.*"
```

Load the plugin in Application.php.
```php
class Application extends BaseApplication
{
    public function bootstrap()
    {
        $this->addPlugin('ZipcodeJp', ['routes' => true]);
    }
}
```

## Initialization
Execute migration to create `zipcode_jps` table.
When you execute the `initialize_zipcode_jp` command, the latest postal code data of Japan Post will be acquired and stored in the database.
```
bin\cake.bat migrations migrate --plugin ZipcodeJp
bin\cake.bat initialize_zipcode_jp
```

## Usage(server side)
```php
$zipcode = '1010032';

$this->loadModel('ZipcodeJp.ZipcodeJps');
$result = $this->ZipcodeJps->findByZipcode($zipcode);
```

## Usage(client side)
```html
<input type="text" id="zipcode" maxlength="7" placeholder="ここに郵便番号を入力" />
```
```js
$("#zipcode").on("keyup", function(){
    $("#pref").text('');
    $("#city").text('');
    $("#address").text('');
    if ($(this).val().length == 7) {
        let requesturl = '/zipcode-jp/' + $(this).val() + '.json';
        $.ajax({
            type: 'get',
            url: requesturl,
            dataType: 'json'
        }).done(function(result) {
            if (result != null) {
                $("#pref").text(result['pref']);
                $("#city").text(result['city']);
                $("#address").text(result['address']);
            }
        });
    }
});
```
