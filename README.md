CKFinder 3 Bundle for Symfony 3
===============================

This repository contains the CKFinder 3 bundle for Symfony 3.
If you're looking for bundle for Symfony 2, please refer [here](https://github.com/ckfinder/ckfinder-symfony2-bundle).

## Installation

1. Add Composer dependency and install the bundle.

	```bash
	composer require ckfinder/ckfinder-symfony3-bundle
	```

2. Enable the bundle in `AppKernel.php`.

	``` php
	// app/AppKernel.php
	
	public function registerBundles()
	{
		$bundles = [
			// ...
			new CKSource\Bundle\CKFinderBundle\CKSourceCKFinderBundle(),
		];
	}
	```	

3. Run the command to download the CKFinder distribution package.

	After installing the bundle you need to download CKFinder distribution package. It is not shipped
	with the bundle due to different license terms. To install it, run the following Symfony command:
	
	```bash
	php bin/console ckfinder:download
	```
	
	It will download the code and place it in the `Resource/public` directory of the bundle. After that you may also want to install
	assets, so the `web` directory will be updated with CKFinder code.
	
	```bash
	php bin/console assets:install web
	```

4. Enable bundle routing in `app/config/routing.yml`.

	```yaml
	# app/config/routing.yml
	
	ckfinder_connector:
	    resource: "@CKSourceCKFinderBundle/Resources/config/routing.yml"
	    prefix:   /
	```

5. Create a directory for CKFinder files and allow for write access to it. By default CKFinder expects it to be placed in `web/userfiles` (this can be altered in configuration).

	```bash
	mkdir -m 777 web/userfiles
	```

**NOTE:** Since usually setting permissions to 0777 is insecure, it is advisable to change the group ownership of the directory to the same user as Apache and add group write permissions instead. Please contact your system administrator in case of any doubts.

At this point you should see the connector JSON response after navigating to the `/ckfinder/connector?command=Init` route.
Authentication for CKFinder is not configured yet, so you will see an error response saying that CKFinder is not enabled.

## Configuring Authentication

CKFinder connector authentication is managed by the `ckfinder.connector.auth` service, which by default is defined in
the `CKSourceCKFinderBundle\Authentication\Authentication` class. It contains the `authenticate` method that should return a Boolean value to decide if the user should have access to CKFinder.
As you can see the default service implementation is not complete and simply returns `false` inside the `authenticate` method,
but you can find it useful as a starting stub code.

To configure authentication for the CKFinder connector you need to create a class that implements `CKSource\Bundle\CKFinderBundle\Authentication\AuthenticationInterface`,
and point the CKFinder connector to use it.

A basic implementation that returns `true` from the `authenticate` method (which is obviously **not secure**) can look like below:

```php
// src/AppBundle/CustomCKFinderAuth/CustomCKFinderAuth.php

namespace AppBundle\CustomCKFinderAuth;

use CKSource\Bundle\CKFinderBundle\Authentication\Authentication as AuthenticationBase;

class CustomCKFinderAuth extends AuthenticationBase
{
    public function authenticate()
    {
        return true;
    }
}
```

You may have noticed that `AuthenticationInterface` extends `ContainerAwareInterface`, so you have access to the service
container from the authentication class scope.

When your custom authentication is ready, you need to tell the CKFinder connector to start using it. To do that add the following option to your configuration:

```yaml
# app/config/config.yml

ckfinder:
    connector:
        authenticationClass: AppBundle\CustomCKFinderAuth\CustomCKFinderAuth
```

## Configuration Options

The default CKFinder connector configuration is taken from the `CKSourceCKFinderBundle::Resources/config/ckfinder_config.php` file.
Thanks to the Symfony configuration merging mechanism there are multiple ways you can overwrite it. The default configuration
is provided in form of a regular PHP file, but you can use the format you prefer (YAML, XML) as long as the structure is valid.

The simplest way to overwrite the default configuration is copying the `ckfinder_config.php` file to your application `config`
directory, and then importing it in the main configuration file:

```yaml
# app/config/config.yml

imports:
    ...
    - { resource: ckfinder_config.php }
```

From now all connector configuration options will be taken from `app/config/ckfinder_config.php`.

Another way to configure CKFinder is to include required options under the `ckfinder` node, directly in your `app/config/config.yml`.

```yaml
# app/config/config.yml

ckfinder:
    connector:
        licenseName: LICENSE-NAME
        licenseKey: LICENSE-KEY
        authenticationClass: AppBundle\CustomCKFinderAuth\CustomCKFinderAuth

        resourceTypes:
            MyResource:
                name: MyResource
                backend: default
                directory: myResource
```

**Note**: All options that are not defined will be taken from the default configuration file.

To find out more about possible connector configuration options please refer to [CKFinder 3 – PHP Connector Documentation](http://docs.cksource.com/ckfinder3-php/configuration.html).

The CKFinder bundle provides two extra options:
- `authenticationClass` &ndash; the name of the CKFinder authentication service class (defaults to `CKSource\Bundle\CKFinderBundle\Authentication\Authentication`)
- `connectorClass` &ndash; the name of the CKFinder connector service class (defaults to `CKSource\CKFinder\CKFinder`)

## Usage

The bundle code contains a few usage examples that you may find useful. To enable them uncomment the `ckfinder_examples`
route in `CKSourceCKFinderBundle::Resources/config/routing.yml`:

```yaml
ckfinder_examples:
    pattern:     /ckfinder/examples/{example}
    defaults: { _controller: CKSourceCKFinderBundle:CKFinder:examples, example: null }
```

After that you can navigate to the `/ckfinder/examples` path and have a look at the list of available examples. To find out about the code behind them, check the `CKFinderController` class (`CKSourceCKFinderBundle::Controller/CKFinderController.php`).

### Including the Main CKFinder JavaScript File in Templates

The preferred way to include `ckfinder.js` in a template is including the CKFinder setup template, like presented below:

```twig
{% include "CKSourceCKFinderBundle::setup.html.twig" %}
```

The included template renders the required `script` tags and configures a valid connector path.

```html
<script type="text/javascript" src="/bundles/cksourceckfinder/ckfinder/ckfinder.js"></script>
<script>CKFinder.config( { connectorPath: '/ckfinder/connector' } );</script>
```

### CKFinder File Chooser

The bundle registers a form field type &mdash; `CKFinderFileChooserType` &mdash; that allows for easy integration of CKFinder as a file chooser in your forms.
After choosing the file in CKFinder the corresponding input field is automaticaly filled with the file URL. You can see a working example under the `/ckfinder/examples/filechooser` path. 

The file chooser field is built on top of the regular `text` type, so it inherits all configuration options. It also provides a few custom options:

 Name          | Type      | Default Value | Description 
---------------|-----------|---------------|-------------
 `mode`        | `string`  | `popup`       | Mode in which CKFinder will be opened after clicking the "Browse" button. Allowed values are `modal` and `popup`.
 `button_text` | `string`  | `Browse`      | The text displayed in the button.
 `button_attr` | `array`   | `[]`          | Attributes for the button element.
 
A simple usage example may look like below:

```php
$form = $this->createFormBuilder()
             ->add('file_chooser1', CKFinderFileChooserType::class, [
                 'label' => 'Photo',
                 'button_text' => 'Browse photos',
                 'button_attr' => [
                     'class' => 'my-fancy-class'
                 ]
             ])
             ->getForm();
```
 
**Note**: To use CKFinder file chooser in your forms you still need to include the main CKFinder JavaScript file in your template (see *Including the main CKFinder JavaScript file in templates*).

