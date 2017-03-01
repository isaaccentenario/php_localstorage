# PHP Local Storage

A PHP class that enables the storage of information in a server-side file, similar to PHP session, but which enables the encryption of content and the personalization of storage methods and the location of the data

## Getting started

Include the LocalStorage.php file

```
<?php
require "LocalStorage.php"; 

```

## Calling class

```

$storage = new localStorage;
```

## Setting up data

```
$bar = array('lorem' => 'ipsum' ); // can be any string

$storage->setItem('foo', json_encode( $bar ) ); // only strings (yet)

$storage->save(); // returns true or false

```

## Getting data

```
$data = $storage->getItem('foo'); // returns a string or false in case of failure
```

## Customizing temp directory

```
$storage->setTempDirectory('/home/zooboomafoo/localstorage/data');
```

## Changing filename structure

The default cache filename is formed by a prefix, a md5 string (representing the item index that you insert) and a extension. You can to change the prefix and the extension.

```
$storage->setPrefix('filename_prefix_xyz_');

$storage->setExtension('.myext');
```

## Cleaning the data
If you want to delete all cached data, you can to use:

```
$storage->clear(); // wow, all cached data is removed (if is permitted by sistem)
```

## Getting eventual errors

```
$last_error = $storage->getError(); // returns a string with error
```

# Cryptography

The class can encrypt saved content and decrypt to a presentable content

## Enable data cryptography

```
$storage->encrypt( true ); // default is FALSE

$storage->setEncryptionKey( 'insert your cryptography key here' ); 
//WARNING: this key are used to decrypt content. Use the same key to encrypt and decrypt
// or your data will be lost

$storage->setItem('foo', 'bar');

$storage->save(); // saving the encrypted content

echo $storage->getItem('foo'); // bar
```