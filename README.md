<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">CatBasic - Code Assignment</h1>
    <br>
</p>

This project is a implementation of a web application that consults the Cat API and displays information about several breeds. The implementation of the web application was done through the Yii framework and was deployed to the Heroku Cloud Application Platform.

This implementations contains 4 pages described as follows:

##### Landing page
The landing page displays cards with the name, image and a small description of 5 breeds picked randomly. This is done by using the Guzzle for HTTP requests. The landing page action make a request to the CatAPI requiring a list of the cat breeds. The list of cat breeds are shuffled and the elements from 0 to 4 are collected in order to present 5 random breeds. Then, new HTTP requests are done to get new public images for each cat breed. The data obtained from this process is cached through Redis. The results are presented by rendering the index.twig.

##### Search Page
Search results page displays results in cards using the same view of the landing page. Through a GET request it collects the results of the desired cat breed searched. In order to colect this data, a HTTP request is done to CatAPI to a route that searchs breeds by name. The response returns all breeds names that contains the query value. This response is cached through Redis and then images for each breed are collected through a HTTP request, the same way is done in the landing page. This data is also cached. 

##### Detail Page
By licking any breed should it leads to a detail page where information about the breed is displayed. A HTTP request is done to the API which returns information and image of the breed. This data is cached through Redis. The page renders detail.twig which presents also a card with information about the breed.

##### Alphabetic display page
A widget was created to allow users to browse breeds by alphabetical order. This widget was added to the NavBar presented in all pages. The widget renders button from A to Z that leads to a page similar to the search page but that displays only breeds for the specific letter. A HTTP request is done to the API that lists all breeds and only the breeds that starts with the letter selected are considered. The information is then cached through Redis to avoid requesting the API again. The same view index.twig is used to present the result.

### Install with Docker

Git clone from develop branch

    git clone -b develop https://github.com/rodrigoazs/catbasic-yii.git

Update your vendor packages

    docker-compose run --rm php composer update --prefer-dist
    
Run the installation triggers (creating cookie validation code)

    docker-compose run --rm php composer install    
    
Start the container

    docker-compose up -d
    
You can then access the application through the following URL:

    http://127.0.0.1:8000

**NOTES:** 
- Minimum required Docker engine version `17.04` for development (see [Performance tuning for volume mounts](https://docs.docker.com/docker-for-mac/osxfs-caching/))
- The default configuration uses a host-volume in your home directory `.docker-composer` for composer caches


TESTING
-------

Tests are located in `tests` directory. They are developed with [Codeception PHP Testing Framework](http://codeception.com/).
By default there are 3 test suites:

- `unit`
- `functional`
- `acceptance`

Tests can be executed by running

```
vendor/bin/codecept run
```

The command above will execute unit and functional tests. Unit tests are testing the system components, while functional
tests are for testing user interaction. Acceptance tests are disabled by default as they require additional setup since
they perform testing in real browser. 


### Running  acceptance tests

To execute acceptance tests do the following:  

1. Rename `tests/acceptance.suite.yml.example` to `tests/acceptance.suite.yml` to enable suite configuration

2. Replace `codeception/base` package in `composer.json` with `codeception/codeception` to install full featured
   version of Codeception

3. Update dependencies with Composer 

    ```
    composer update  
    ```

4. Download [Selenium Server](http://www.seleniumhq.org/download/) and launch it:

    ```
    java -jar ~/selenium-server-standalone-x.xx.x.jar
    ```

    In case of using Selenium Server 3.0 with Firefox browser since v48 or Google Chrome since v53 you must download [GeckoDriver](https://github.com/mozilla/geckodriver/releases) or [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/downloads) and launch Selenium with it:

    ```
    # for Firefox
    java -jar -Dwebdriver.gecko.driver=~/geckodriver ~/selenium-server-standalone-3.xx.x.jar
    
    # for Google Chrome
    java -jar -Dwebdriver.chrome.driver=~/chromedriver ~/selenium-server-standalone-3.xx.x.jar
    ``` 
    
    As an alternative way you can use already configured Docker container with older versions of Selenium and Firefox:
    
    ```
    docker run --net=host selenium/standalone-firefox:2.53.0
    ```

5. (Optional) Create `yii2basic_test` database and update it by applying migrations if you have them.

   ```
   tests/bin/yii migrate
   ```

   The database configuration can be found at `config/test_db.php`.


6. Start web server:

    ```
    tests/bin/yii serve
    ```

7. Now you can run all available tests

   ```
   # run all available tests
   vendor/bin/codecept run

   # run acceptance tests
   vendor/bin/codecept run acceptance

   # run only unit and functional tests
   vendor/bin/codecept run unit,functional
   ```

### Code coverage support

By default, code coverage is disabled in `codeception.yml` configuration file, you should uncomment needed rows to be able
to collect code coverage. You can run your tests and collect coverage with the following command:

```
#collect coverage for all tests
vendor/bin/codecept run --coverage --coverage-html --coverage-xml

#collect coverage only for unit tests
vendor/bin/codecept run unit --coverage --coverage-html --coverage-xml

#collect coverage for unit and functional tests
vendor/bin/codecept run functional,unit --coverage --coverage-html --coverage-xml
```

You can see code coverage output under the `tests/_output` directory.
