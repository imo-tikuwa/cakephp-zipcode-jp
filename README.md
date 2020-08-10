# ZipcodeJp plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require imo-tikuwa/cakephp-zipcode-jp
```

Load the plugin in Application.php.
```
class Application extends BaseApplication
{
    public function bootstrap()
    {
        $this->addPlugin('ZipcodeJp', ['routes' => true]);
    }
```

## Initialization
```
bin\cake.bat migrations migrate --plugin ZipcodeJp
bin\cake.bat initialize_zipcode_jp
```

## Usage(server side)
```
$zipcode = '1010032';

$this->loadModel('ZipcodeJp.ZipcodeJps');
$result = $this->ZipcodeJps->findByZipcode($zipcode);
```
