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
By clicking any breed should it leads to a detail page where information about the breed is displayed. A HTTP request is done to the API which returns information and image of the breed. This data is cached through Redis. The page renders detail.twig which presents also a card with information about the breed.

##### Alphabetic display page
A widget was created to allow users to browse breeds by alphabetical order. This widget was added to the NavBar presented in all pages. The widget renders button from A to Z that leads to a page similar to the search page but that displays only breeds for the specific letter. A HTTP request is done to the API that lists all breeds and only the breeds that starts with the letter selected are considered. The information is then cached through Redis to avoid requesting the API again. The same view index.twig is used to present the result.

### Deployment

The solution is deployed to Heroku Cloud Application Platform automatically through a GitHub connection. It automatic deploys from main branch after every push. The Redis Enterprise Cloud was added in the resources and the connection is configured in the file config/web.php.

To run the solution locally, the develop branch presents a modified docker-compose.yml file that contains a Redis container.

### Running locally with Docker

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
