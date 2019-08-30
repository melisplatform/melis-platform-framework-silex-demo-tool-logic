# melis-platform-framwork-silex-demo-tool-logic

This is a demo of a Silex module that is being rendered inside the Melis Platform.

### Prerequisites

This module requires the following: <br/>
- melisplatform/melis-cms-news
- melisplatform/melis-platform-framework-silex

The required modules will be automatically installed when using composer.
 
### Installing

```
composer require melisplatform/melis-platform-framework-silex-demo-tool-logic
```
 ### Usage
 
 In order to load and use a this provider, you must register this provider in the Silex application which is commonly located in ```/Silex/src/app.php```
 ```
 use MelisPlatformFrameworkSilex\Provider\MelisSilexDemoTooolLogicServiceProvider;
 
 $app = new Silex\Application();
 
 $app->register(new MelisSilexDemoTooolLogicServiceProvider());
 ```

**Note :** This provider has to be always registered first before the rest of silex providers.