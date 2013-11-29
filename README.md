TmsSearchBundle
======================

The search bundle provides an API for indexing data.

You can start indexing your data with Elastic Search.


Installation
------------

To install this bundle please follow the next steps:

First add the dependencies in your `composer.json` file:

```json
"repositories": [
    ...,
    {
        "type": "vcs",
        "url": "https://github.com/Tessi-Tms/TmsSearchBundle.git"
    }
],
"require": {
        ...,
        "elasticsearch/elasticsearch": "~0.4",
        "tms/search-bundle": "dev-master"
    },
```

Then install the bundles with the command:

```sh
composer update
```

Enable the bundle in your application kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //
        new Tms\Bundle\SearchBundle\TmsSearchBundle(),
    );
}
```

Bundles are now installed.


How to use it
-------------

### Configuration


``` yaml
# app/config/config.yml

tms_search:
    indexes:
        tms_participation:
            class: Tms\Bundle\ParticipationBundle\Document\Participation
            indexer:
                service_name: tms_search.indexer.elasticsearch
                options:
                    host: %tms_search_host%
                    port: %tms_search_port%
                    collection_name: participation

```


``` yaml
# app/config/parameters.yml and app/config/parameters.yml.dist

parameters:
    # ...
    tms_search_host: localhost
    tms_search_port: 9200
```

### Model

Your model must implement the IndexableElement Interface.

You must define a key and a value for each field you want ton index.

If the field is a stringified json object, you have to indicate it.

Then, each field of this json object will be indexed.


``` php
<?php
use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;

/**
 * {@inheritdoc}
 */
public function getIndexedData()
{
    $indexedData = array(
        array(
            'key' => 'search',
            'value' => $this->getSearch(),
            'options' => array(
                'type' => 'json'
            )
        ),
        array(
            'key' => 'source',
            'value' => $this->getSource(),
        ),
    );

    return $indexedData;
}
```

### Elastic Search

In order to install Elastic Search, you have to follow these steps:

1. Download and unzip the latest Elasticsearch distribution. You can find it here: http://www.elasticsearch.org/download/

2. To launch a node (an instance of elastic search):

``` sh
.bin/elasticsearch -f
```

Moreover, you can use a web client to browse your indexed data or get other stats from your elastic search cluster.
Check for Elasticsearch HEAD plugin or Elastic Search HQ.


