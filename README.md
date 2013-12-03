TmsSearchBundle
======================

The search bundle provides an API for indexing data.

You can start indexing your data with Elastic Search.


Installation
------------

To install this bundle please follow these steps:

First, add the dependencies in your `composer.json` file:

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

Then, install the bundle with the command:

```sh
composer update
```

Finally, enable the bundle in your application kernel:

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


How to use it
-------------

### Configuration


``` yaml
# app/config/config.yml

tms_search:
    indexes:
        tms_participation:                                                  # Name of your index
            class: Tms\Bundle\ParticipationBundle\Document\Participation    # Class of the element
            indexer:
                service_name: tms_search.indexer.elasticsearch              # Indexer you want to use
                options:
                    host: %tms_search_host%                                 # Indexer host (required)
                    port: %tms_search_port%                                 # Indexer port (required)
                    collection_name: participation                          # Indexer collection name (optionnal)
                    query_limit: 20                                         # Indexer query limit

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
You must define a key and a value for each field you want to index.
If the field is a stringified json object, you have to declare it in the options.
Then, each field of the json object will be indexed.


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

### API

#### Search operations

Here are some examples of query:
``` php
$query = 'John';            // Match. Search on all fields
$query = 'Jo*';             // Wildcard. Search on all fields
$query = 'firstName:John';  // Match on a specific field
$query = 'phone:064*';      // Wildcard on a specific field
````

``` php
$indexName = 'tms_participation';                                   // After the index name you defined in app/config/config.yml
$searchIndexHandler = $this->container->get('tms_search.handler');  // Get the search service
$data = $searchIndexHandler->search($indexName, $query);            // Returns elements in array
```
These search operations will return an array of elements. 
If you want to fetch the object directly from a search operation:
``` php
$data = $searchIndexHandler->searchAndFetchEntity($indexName, $query);   // ORM
$data = $searchIndexHandler->searchAndFetchDocument($indexName, $query); // ODM
```
Optional: You can specify a custom Document Manager when you call these functions.
Example with a Doctrine MongoDB document manager from a connection named 'custom':
``` php
$documentManager = $this->container->get('doctrine_mongodb.odm.custom_document_manager');
$data = $searchIndexHandler->searchAndFetchDocument($indexName, $query, $documentManager);
```

#### Indexing operations

``` php
// $participation is a document fetched from the repository
$searchIndexHandler->index($participation); // Create or Update an index. Returns boolean
$searchIndexHandler->unIndex($participation); // Delete an index. Returns boolean
```

#### Pagination

When the `search` function is called, the response looks like this:

```
array (size=5)
  'total'   => int
  'count'   => int
  'data'    => array()
  'page'    => int
  'hasNext' => boolean
```

If hasNext is true, you may want to get the data from the next page (by default, the search is made on the first page):

``` php
$page = 2;
$data = $searchIndexHandler->search($indexName, $query, $page);
```

The max size of the elements returned by the function is 10 by default.
This value can be overwritten with the query_limit option of the indexer.



Elastic Search
--------------

In order to install Elastic Search, you have to follow these steps:

1. Download and unzip the latest Elasticsearch distribution. 
You can find it here: http://www.elasticsearch.org/download/

2. Launch a node (an instance of elastic search):

``` sh
bin/elasticsearch -f
```

Now, you can use a web client to browse your indexed data or get statistics from your elastic search cluster.
See Elasticsearch HEAD plugin or Elastic Search HQ.

Links:

http://mobz.github.io/elasticsearch-head/

http://www.elastichq.org/


